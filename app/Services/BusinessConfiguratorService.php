<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Validator;

class BusinessConfiguratorService
{
    /** @return Collection<int, Product> */
    public function productsForSlugs(array $slugs): Collection
    {
        return Product::query()
            ->active()
            ->businessConfigurator()
            ->whereIn('slug', $slugs)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy('slug');
    }

    /** @return array<int, array<string, mixed>> */
    public function catalog(): array
    {
        return Product::query()
            ->active()
            ->businessConfigurator()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Product $product): array => $this->catalogProduct($product))
            ->all();
    }

    /** @param array<int, array<string, mixed>> $items */
    public function validate(array $items, Validator $validator): void
    {
        $products = $this->productsForSlugs(array_column($items, 'product_slug'));

        foreach ($items as $index => $item) {
            $product = $products->get($item['product_slug'] ?? null);
            if ($product === null) {
                continue;
            }

            $configuration = $item['configuration'] ?? [];
            $fields = $this->fields($product);
            $fieldsByKey = collect($fields)->keyBy('key');

            foreach ($configuration as $key => $value) {
                $field = $fieldsByKey->get($key);
                if (! is_array($field)) {
                    $validator->errors()->add("items.{$index}.configuration.{$key}", 'Wybrana opcja nie jest dostępna dla tego produktu.');

                    continue;
                }

                $this->validateValue($validator, $index, $field, $value);
            }

            foreach ($fields as $field) {
                if (($field['required'] ?? true) && ! array_key_exists($field['key'], $configuration)) {
                    $validator->errors()->add("items.{$index}.configuration.{$field['key']}", "Wybierz wymaganą opcję: {$field['label']}.");
                }
            }
        }
    }

    /**
     * @param  array<string, string|null>  $configuration
     * @return array<string, array{label: string, value: string, display: string}>
     */
    public function snapshot(Product $product, array $configuration): array
    {
        $snapshot = [];

        foreach ($this->fields($product) as $field) {
            $key = $field['key'];
            if (! array_key_exists($key, $configuration) || $configuration[$key] === null) {
                continue;
            }

            $value = (string) $configuration[$key];
            $selectedValue = collect($field['values'] ?? [])->firstWhere('value', $value);
            $display = is_array($selectedValue) ? $selectedValue['label'] : $value;

            $snapshot[$key] = [
                'label' => $field['label'],
                'value' => $value,
                'display' => $display,
            ];
        }

        return $snapshot;
    }

    /** @return array<string, mixed> */
    private function catalogProduct(Product $product): array
    {
        $configuration = $product->configuration ?? [];
        $configurator = $configuration['configurator'] ?? [];

        return [
            'id' => $product->slug,
            'name' => $product->name,
            'icon' => $configurator['icon'] ?? 'fa-print',
            'image' => $product->imageUrl(),
            'desc' => $product->description,
            'params' => $this->fields($product),
        ];
    }

    /** @return array<int, array{key: string, label: string, type: string, required: bool, min?: int, max?: int, values?: array<int, array{value: string, label: string}>}> */
    private function fields(Product $product): array
    {
        $configuration = $product->configuration ?? [];
        $fields = $configuration['configurator']['fields'] ?? [];

        return is_array($fields) ? $fields : [];
    }

    /** @param array{key: string, label: string, type: string, min?: int, max?: int, values?: array<int, array{value: string, label: string}>} $field */
    private function validateValue(Validator $validator, int $index, array $field, mixed $value): void
    {
        $attribute = "items.{$index}.configuration.{$field['key']}";
        if (! is_string($value) || $value === '') {
            $validator->errors()->add($attribute, "Wybierz wartość opcji: {$field['label']}.");

            return;
        }

        if ($field['type'] === 'chips') {
            $allowedValues = collect($field['values'] ?? [])->pluck('value')->all();
            if (! in_array($value, $allowedValues, true)) {
                $validator->errors()->add($attribute, "Wybrana wartość opcji {$field['label']} nie jest dostępna.");
            }

            return;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $validator->errors()->add($attribute, "Podaj liczbę całkowitą dla opcji: {$field['label']}.");

            return;
        }

        $number = (int) $value;
        if ($number < ($field['min'] ?? PHP_INT_MIN) || $number > ($field['max'] ?? PHP_INT_MAX)) {
            $validator->errors()->add($attribute, "Podaj wartość od {$field['min']} do {$field['max']} dla opcji: {$field['label']}.");
        }
    }
}
