<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description', 'is_active'])]
class Category extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword, fn (Builder $q) => $q->where('name', 'like', "%{$keyword}%"));
    }

    public function scopeActive(Builder $query, ?bool $active): Builder
    {
        return $query->when($active !== null, fn (Builder $q) => $q->where('is_active', $active));
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
