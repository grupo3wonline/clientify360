<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Quote */
final class QuoteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'title' => $this->title,
            'status' => $this->status,
            'client' => $this->whenLoaded('client', fn (): ?array => $this->client === null ? null : [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ]),
            'valid_until' => $this->valid_until === null ? null : Carbon::parse($this->valid_until)->toDateString(),
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'total' => $this->total,
            'payment_terms' => $this->payment_terms,
            'notes' => $this->notes,
            'approved_at' => $this->approved_at === null ? null : Carbon::parse($this->approved_at)->toIso8601String(),
            'items' => QuoteItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
