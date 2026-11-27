<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_code', 'user_id', 'status', 'order_type', 'total_price', 'note'])]
class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'paid', 'preparing', 'ready', 'completed', 'cancelled'];

    public const TYPES = ['dine_in', 'take_away'];

    protected function casts(): array
    {
        return [
            'total_price' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status, fn (Builder $q) => $q->where('status', $status));
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword, fn (Builder $q) => $q->where('order_code', 'like', "%{$keyword}%"));
    }

    public function recalculateTotal(): int
    {
        $total = (int) $this->items()->sum('subtotal');
        $this->update(['total_price' => $total]);

        return $total;
    }
}
