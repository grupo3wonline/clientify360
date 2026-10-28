<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\Media;
use App\Models\User;
use App\Support\PrivacyLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/** Files attached to a client (Media Library, private `media` disk). */
final class ClientAttachmentService
{
    public const COLLECTION = 'attachments';

    /** @return Collection<int, Media> */
    public function list(Client $client): Collection
    {
        /** @var Collection<int, Media> */
        return $client->getMedia(self::COLLECTION)->sortByDesc('created_at')->values();
    }

    /** @throws ValidationException */
    public function store(Client $client, UploadedFile $file, User $user): Media
    {
        try {
            /** @var Media */
            return $client->addMedia($file)
                ->usingName($file->getClientOriginalName())
                ->withCustomProperties(['uploaded_by' => $user->id, 'uploaded_by_name' => $user->name])
                ->toMediaCollection(self::COLLECTION);
        } catch (FileCannotBeAdded $e) {
            Log::warning('Client attachment rejected', ['client_id' => $client->id, 'exception' => $e::class]);
        } catch (Throwable $e) {
            Log::error('Client attachment could not be stored', ['client_id' => $client->id, 'error' => $e->getMessage()]);
        }

        throw ValidationException::withMessages(['file' => 'Não foi possível salvar o arquivo. Tente novamente.']);
    }

    /** Scoped by parent: media of another client (or account) is a 404. */
    public function find(Client $client, string $mediaId): Media
    {
        /** @var Media */
        return $client->media()
            ->where('collection_name', self::COLLECTION)
            ->whereKey($mediaId)
            ->firstOrFail();
    }

    public function download(Client $client, Media $media, User $user): BinaryFileResponse
    {
        $path = $media->getPath();

        if (!is_file($path)) {
            Log::error('Media file missing', ['media_id' => $media->id]);

            throw new NotFoundHttpException('O arquivo não foi encontrado.');
        }

        PrivacyLog::record('attachment.downloaded', 'Download de anexo do cliente', $user, $client, [
            'media_id' => $media->id,
            'file_name' => $media->file_name,
        ]);

        return response()->download($path, $media->file_name);
    }

    public function delete(Media $media): void
    {
        $media->delete();
    }
}
