<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'status' => $this->status,
            'order_type' => $this->order_type,
            'total_price' => $this->total_price,
            'total_price_formatted' => 'Rp '.number_format($this->total_price, 0, ',', '.'),
            'note' => $this->note,
            'customer' => new UserResource($this->whenLoaded('user')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
