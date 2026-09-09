<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['option_id', 'label', 'value', 'price_modifier', 'is_active', 'sort_order'])]
class OptionValue extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['price_modifier' => 'decimal:2', 'is_active' => 'boolean'];
    }

    /** @return BelongsTo<Option, $this> */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
