<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tag;

final class TagService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Tag $tag, array $data): Tag
    {
        $tag->update($data);

        return $tag;
    }

    public function delete(Tag $tag): void
    {
        $tag->clients()->detach();
        $tag->delete();
    }
}
