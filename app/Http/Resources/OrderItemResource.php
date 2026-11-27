<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_id' => $this->menu_id,
            'menu' => new MenuResource($this->whenLoaded('menu')),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
        ];
    }
}
