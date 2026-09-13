<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ProductImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'catalog_image_path' => [
                'nullable',
                'string',
                Rule::in(self::catalogImagePaths()),
            ],
            'image' => [
                'nullable',
                File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('5mb'),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function catalogImagePaths(): array
    {
        return collect(glob(public_path('images/produkty/*')) ?: [])
            ->filter(fn (string $path): bool => is_file($path))
            ->filter(fn (string $path): bool => in_array(
                strtolower(pathinfo($path, PATHINFO_EXTENSION)),
                ['jpg', 'jpeg', 'png', 'webp'],
                true,
            ))
            ->map(fn (string $path): string => 'images/produkty/'.basename($path))
            ->sort()
            ->values()
            ->all();
    }
}
