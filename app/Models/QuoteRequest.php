<?php

namespace App\Models;

use App\Enums\QuoteRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'reference',
    'status',
    'client_id',
    'customer_name',
    'customer_email',
    'customer_phone',
    'company_name',
    'nip',
    'shipping_method',
    'shipping_address',
    'requested_by_date',
    'invoice_required',
    'privacy_policy_version',
    'privacy_policy_accepted_at',
    'notes',
    'admin_notes',
    'idempotency_key',
    'idempotency_fingerprint',
])]
class QuoteRequest extends Model
{
    protected $hidden = ['idempotency_key', 'idempotency_fingerprint'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => QuoteRequestStatus::class,
            'shipping_address' => 'array',
            'requested_by_date' => 'date',
            'invoice_required' => 'boolean',
            'privacy_policy_accepted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasMany<QuoteRequestItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }

    /** @return HasMany<QuoteRequestFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(QuoteRequestFile::class);
    }

    /** @return HasMany<QuoteOffer, $this> */
    public function offers(): HasMany
    {
        return $this->hasMany(QuoteOffer::class);
    }

    /** @return HasOne<QuoteOffer, $this> */
    public function latestOffer(): HasOne
    {
        return $this->hasOne(QuoteOffer::class)->latestOfMany('version');
    }
}
