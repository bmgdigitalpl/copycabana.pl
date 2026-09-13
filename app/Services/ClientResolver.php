<?php

namespace App\Services;

use App\Models\Client;

class ClientResolver
{
    /**
     * @param  array{name: string, email: string, phone?: string|null, company?: string|null, nip?: string|null}  $customer
     */
    public function resolve(array $customer, bool $marketingConsent): Client
    {
        $now = now();
        $attributes = [
            'email' => $customer['email'],
            'name' => $customer['name'],
            'phone' => $customer['phone'] ?? null,
            'company' => $customer['company'] ?? null,
            'nip' => $customer['nip'] ?? null,
            'privacy_policy_version' => config('privacy.policy_version'),
            'privacy_policy_accepted_at' => $now,
            'retention_until' => $now->copy()->addDays((int) config('privacy.client_retention_days')),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $updateColumns = [
            'name',
            'phone',
            'company',
            'nip',
            'privacy_policy_version',
            'privacy_policy_accepted_at',
            'retention_until',
            'updated_at',
        ];

        if ($marketingConsent) {
            $attributes['marketing_consent'] = true;
            $attributes['marketing_consent_at'] = $now;
            $updateColumns[] = 'marketing_consent';
            $updateColumns[] = 'marketing_consent_at';
        }

        Client::query()->upsert([$attributes], ['email'], $updateColumns);

        return Client::query()->where('email', $customer['email'])->firstOrFail();
    }
}
