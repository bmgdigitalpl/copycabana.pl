<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IdempotencyService
{
    public function key(Request $request): string
    {
        $key = trim($request->header('Idempotency-Key', ''));

        if ($key === '' || mb_strlen($key) > 100) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Nagłówek Idempotency-Key jest wymagany i może mieć maksymalnie 100 znaków.',
            ]);
        }

        return $key;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function fingerprint(string $scope, array $data): string
    {
        return hash('sha256', $scope.'|'.json_encode($this->normalize($data), JSON_THROW_ON_ERROR));
    }

    public function matches(?string $fingerprint, string $expected): bool
    {
        return is_string($fingerprint) && hash_equals($fingerprint, $expected);
    }

    private function normalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map($this->normalize(...), $value);
        }

        ksort($value);

        return array_map($this->normalize(...), $value);
    }
}
