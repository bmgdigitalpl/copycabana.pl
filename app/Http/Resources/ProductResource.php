<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'image_path' => $this->image_path,
            'calculator_type' => $this->calculator_type,
            'starting_price' => $this->starting_price,
            'configuration' => $this->configuration,
            'options' => $this->whenLoaded('options', fn () => $this->options->map(fn ($option) => [
                'id' => $option->id,
                'name' => $option->name,
                'code' => $option->code,
                'input_type' => $option->input_type,
                'pricing_model' => $option->pricing_model,
                'is_required' => $option->is_required,
                'values' => $option->values->map(fn ($value) => [
                    'id' => $value->id,
                    'label' => $value->label,
                    'value' => $value->value,
                    'price_modifier' => $value->price_modifier,
                ]),
            ])),
        ];
    }
}
