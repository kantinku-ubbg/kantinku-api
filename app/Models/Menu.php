<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['category_id', 'name', 'slug', 'description', 'price', 'stock', 'image_url', 'is_available'])]
class Menu extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword, fn (Builder $q) => $q->where('name', 'like', "%{$keyword}%"));
    }

    public function scopeOfCategory(Builder $query, ?int $categoryId): Builder
    {
        return $query->when($categoryId, fn (Builder $q) => $q->where('category_id', $categoryId));
    }

    public function scopeAvailable(Builder $query, ?bool $available): Builder
    {
        return $query->when($available !== null, fn (Builder $q) => $q->where('is_available', $available));
    }

    public function scopePriceBetween(Builder $query, ?int $min, ?int $max): Builder
    {
        return $query
            ->when($min !== null, fn (Builder $q) => $q->where('price', '>=', $min))
            ->when($max !== null, fn (Builder $q) => $q->where('price', '<=', $max));
    }

    public static function makeSlug(string $name): string
    {
        return Str::slug($name);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
