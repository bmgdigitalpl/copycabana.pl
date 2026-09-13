<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class InPostService
{
    /**
     * @return array<int, array{
     *     name: string,
     *     address: string,
     *     city: string,
     *     post_code: string,
     *     opening_hours: string|null,
     *     description: string|null,
     *     latitude: float|null,
     *     longitude: float|null,
     * }>
     */
    public function searchPoints(?string $postCode = null, ?string $city = null): array
    {
        $postCode = trim((string) $postCode) ?: null;
        $city = trim((string) $city) ?: null;

        if (! $postCode && ! $city) {
            return [];
        }

        $query = [
            'type' => 'parcel_locker',
            'per_page' => 25,
        ];

        if ($postCode) {
            $query['post_code'] = $postCode;
        } else {
            $query['city'] = $city;
        }

        $cacheKey = 'inpost.points.'.md5((string) json_encode($query));

        return Cache::remember($cacheKey, now()->addHour(), function () use ($query): array {
            $client = Http::baseUrl((string) config('services.inpost.base_url'))
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(5)
                ->retry(2, 200);

            $token = config('services.inpost.token');
            if (is_string($token) && $token !== '') {
                $client = $client->withToken($token);
            }

            try {
                $response = $client->get('points', $query);
            } catch (ConnectionException|RequestException) {
                return [];
            }

            if (! $response->successful()) {
                return [];
            }

            $items = $response->json('items', []);
            if (! is_array($items)) {
                return [];
            }

            return collect($items)
                ->map(fn (mixed $item): ?array => is_array($item) ? $this->normalizePoint($item) : null)
                ->filter()
                ->values()
                ->all();
        });
    }

    /**
     * @param  array<string, mixed>  $point
     * @return array{
     *     name: string,
     *     address: string,
     *     city: string,
     *     post_code: string,
     *     opening_hours: string|null,
     *     description: string|null,
     *     latitude: float|null,
     *     longitude: float|null,
     * }|null
     */
    private function normalizePoint(array $point): ?array
    {
        $name = trim((string) ($point['name'] ?? ''));
        if ($name === '') {
            return null;
        }

        $addressDetails = is_array($point['address_details'] ?? null) ? $point['address_details'] : [];
        $address = is_array($point['address'] ?? null) ? $point['address'] : [];
        $location = is_array($point['location'] ?? null) ? $point['location'] : [];

        $street = trim((string) ($addressDetails['street'] ?? ''));
        $buildingNumber = trim((string) ($addressDetails['building_number'] ?? ''));
        $addressLine = trim((string) ($address['line1'] ?? ''));

        return [
            'name' => $name,
            'address' => $addressLine !== '' ? $addressLine : trim($street.' '.$buildingNumber),
            'city' => trim((string) ($addressDetails['city'] ?? '')),
            'post_code' => trim((string) ($addressDetails['post_code'] ?? '')),
            'opening_hours' => $this->nullableString($point['opening_hours'] ?? null),
            'description' => $this->nullableString($point['location_description'] ?? null),
            'latitude' => $this->nullableFloat($location['latitude'] ?? null),
            'longitude' => $this->nullableFloat($location['longitude'] ?? null),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function nullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
