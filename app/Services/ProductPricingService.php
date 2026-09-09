<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class ProductPricingService
{
    /**
     * Calculate a server-side price from the current product and selected option IDs.
     * Client-supplied display prices are deliberately ignored.
     *
     * @param  array<int, int|string>  $selectedValueIds
     * @return array{unit_price: float, configuration: array<int, array<string, mixed>>}
     */
    public function calculate(Product $product, array $selectedValueIds): array
    {
        $values = $product->options()
            ->with(['values' => fn ($query) => $query->where('is_active', true)])
            ->get()
            ->flatMap(fn ($option) => $option->values)
            ->whereIn('id', array_map('intval', $selectedValueIds));

        $selectedByOption = $values->groupBy('option_id');
        $price = (float) ($product->starting_price ?? 0);
        $configuration = [];

        foreach ($product->options()->active()->with('values')->get() as $option) {
            $selected = $selectedByOption->get($option->id, collect());
            if ($option->is_required && $selected->isEmpty()) {
                throw ValidationException::withMessages(['items' => "Wybierz wymaganą opcję: {$option->name}."]);
            }

            if ($option->input_type === 'select' && $selected->count() > 1) {
                throw ValidationException::withMessages(['items' => "Wybierz tylko jedną wartość opcji: {$option->name}."]);
            }

            foreach ($selected as $value) {
                $modifier = (float) $value->price_modifier;
                $price = $option->pricing_model === 'percentage'
                    ? $price + ($price * $modifier / 100)
                    : $price + $modifier;

                $configuration[] = [
                    'option' => $option->name,
                    'value' => $value->label,
                    'price_modifier' => $value->price_modifier,
                ];
            }
        }

        return ['unit_price' => round(max($price, 0), 2), 'configuration' => $configuration];
    }
}
