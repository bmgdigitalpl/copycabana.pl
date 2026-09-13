<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'name', 'email', 'phone', 'company', 'nip', 'billing_address',
    'marketing_consent', 'marketing_consent_at', 'privacy_policy_version',
    'privacy_policy_accepted_at', 'deletion_requested_at', 'anonymized_at',
    'retention_until',
])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'billing_address' => 'array',
            'marketing_consent' => 'boolean',
            'marketing_consent_at' => 'datetime',
            'privacy_policy_accepted_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'retention_until' => 'datetime',
        ];
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<QuoteRequest, $this> */
    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class);
    }

    /** @return HasOne<User, $this> */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /** @return HasMany<DataRequest, $this> */
    public function dataRequests(): HasMany
    {
        return $this->hasMany(DataRequest::class);
    }

    public function anonymize(): void
    {
        $this->user()->update(['disabled_at' => now()]);

        $this->orders()->whereDoesntHave('invoice')->update([
            'customer_name' => 'Klient zanonimizowany',
            'customer_email' => 'anonimowy-'.$this->id.'@example.invalid',
            'customer_phone' => null,
            'customer_company' => null,
            'billing_address' => null,
            'shipping_address' => null,
        ]);

        $this->forceFill([
            'name' => 'Klient zanonimizowany',
            'email' => 'anonimowy-'.$this->id.'@example.invalid',
            'phone' => null,
            'company' => null,
            'nip' => null,
            'billing_address' => null,
            'marketing_consent' => false,
            'marketing_consent_at' => null,
            'privacy_policy_version' => null,
            'privacy_policy_accepted_at' => null,
            'deletion_requested_at' => null,
            'retention_until' => null,
            'anonymized_at' => now(),
        ])->save();
    }
}
