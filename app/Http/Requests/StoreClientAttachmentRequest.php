<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;

final class StoreClientAttachmentRequest extends FormRequest
{
    public const MAX_KB = 10240;

    public const EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'txt', 'csv', 'doc', 'docx', 'xls', 'xlsx'];

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.self::MAX_KB,
                'mimes:'.implode(',', self::EXTENSIONS),
                'mimetypes:'.implode(',', Client::ATTACHMENT_MIME_TYPES),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $format = 'Formato não permitido. Envie PDF, imagem, texto, Word ou Excel.';

        return [
            'file.required' => 'Selecione um arquivo.',
            'file.file' => 'Selecione um arquivo.',
            'file.uploaded' => 'O arquivo deve ter no máximo 10 MB.',
            'file.max' => 'O arquivo deve ter no máximo 10 MB.',
            'file.mimes' => $format,
            'file.mimetypes' => $format,
        ];
    }
}
