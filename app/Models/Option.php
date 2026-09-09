<?php

namespace App\Models;

use Database\Factories\OptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'input_type', 'pricing_model', 'is_required', 'is_active', 'sort_order'])]
class Option extends Model
{
    /** @use HasFactory<OptionFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'is_active' => 'boolean'];
    }

    /** @return HasMany<OptionValue, $this> */
    public function values(): HasMany
    {
        return $this->hasMany(OptionValue::class)->orderBy('sort_order');
    }

    /** @return BelongsToMany<Product, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('sort_order')->orderBy('sort_order');
    }

    /** @param Builder<Option> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
