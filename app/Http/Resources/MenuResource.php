<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Menu
 */
class MenuResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'price_formatted' => 'Rp '.number_format($this->price, 0, ',', '.'),
            'stock' => $this->stock,
            'image_url' => $this->image_url,
            'is_available' => $this->is_available,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
