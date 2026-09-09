<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'number', 'buyer_name', 'buyer_email', 'buyer_company', 'buyer_nip',
    'buyer_address', 'currency', 'net_total', 'vat_total', 'gross_total', 'vat_rate',
    'status', 'issued_at', 'due_at',
])]
class Invoice extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'buyer_address' => 'array',
            'net_total' => 'decimal:2',
            'vat_total' => 'decimal:2',
            'gross_total' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
