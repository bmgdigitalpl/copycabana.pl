<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OptionRequest;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OptionController extends Controller
{
    public function index(): View
    {
        return view('admin.options.index', ['options' => Option::query()->withCount('values')->with('products')->orderBy('sort_order')->paginate(25)]);
    }

    public function create(): View
    {
        return view('admin.options.form', ['option' => new Option, 'products' => Product::query()->orderBy('name')->get()]);
    }

    public function store(OptionRequest $request): RedirectResponse
    {
        $this->save($request, new Option);

        return redirect()->route('admin.options.index')->with('status', 'Opcja została dodana.');
    }

    public function edit(Option $option): View
    {
        return view('admin.options.form', ['option' => $option->load('values', 'products'), 'products' => Product::query()->orderBy('name')->get()]);
    }

    public function update(OptionRequest $request, Option $option): RedirectResponse
    {
        $this->save($request, $option);

        return redirect()->route('admin.options.index')->with('status', 'Opcja została zaktualizowana.');
    }

    public function destroy(Option $option): RedirectResponse
    {
        $option->delete();

        return back()->with('status', 'Opcja została usunięta.');
    }

    private function save(OptionRequest $request, Option $option): void
    {
        $data = $request->validated();
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $option->fill(collect($data)->except(['values', 'products'])->all())->save();
        $option->values()->delete();
        $option->values()->createMany(array_map(fn (array $value, int $index): array => [
            ...$value,
            'sort_order' => $value['sort_order'] ?? $index,
            'is_active' => $value['is_active'] ?? true,
        ], $data['values'], array_keys($data['values'])));
        $option->products()->sync(collect($data['products'] ?? [])->mapWithKeys(fn (int $id, int $index): array => [$id => ['sort_order' => $index]])->all());
    }
}
