@php
    $editorFields = collect(old('fields', $fields))->map(fn ($field) => [
        ...$field, 'required' => (string) (int) ($field['required'] ?? true), 'price' => $field['price'] ?? 0, 'min' => $field['min'] ?? 1, 'max' => $field['max'] ?? 1000,
        'values' => collect($field['values'] ?? [])->map(fn ($value) => [...$value, 'price' => $value['price'] ?? 0])->all(),
    ])->all();
@endphp
<section class="mt-8" x-data="{ fields: {{ Illuminate\Support\Js::from($editorFields) }} }">
    <h2 class="text-xl font-bold">Opcje konfiguratora B2B</h2>
    <p class="mt-2 text-sm text-slate-400">Kolejność pól odpowiada kolejności w konfiguratorze. Kod qty oznacza nakład. Ceny są dopłatami do wyceny; końcową ofertę zatwierdza pracownia.</p>
    <div class="mt-4 grid gap-5">
        <template x-for="(field, index) in fields" :key="index">
            <div class="grid gap-3 rounded border p-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="text-sm">Kod opcji<input class="mt-1 w-full rounded border p-2" :name="'fields['+index+'][key]'" x-model="field.key" pattern="[a-zA-Z0-9_-]+" required></label>
                    <label class="text-sm">Nazwa opcji<input class="mt-1 w-full rounded border p-2" :name="'fields['+index+'][label]'" x-model="field.label" required></label>
                    <label class="text-sm">Typ<select class="mt-1 w-full rounded border p-2" :name="'fields['+index+'][type]'" x-model="field.type"><option value="chips">Wybór wariantu</option><option value="number">Liczba</option></select></label>
                    <label class="text-sm">Wymagana<select class="mt-1 w-full rounded border p-2" :name="'fields['+index+'][required]'" x-model="field.required"><option value="1">Tak</option><option value="0">Nie</option></select></label>
                </div>
                <template x-if="field.type === 'number'">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <label class="text-sm">Minimum<input class="mt-1 w-full rounded border p-2" type="number" min="1" :name="'fields['+index+'][min]'" x-model="field.min" required></label>
                        <label class="text-sm">Maksimum<input class="mt-1 w-full rounded border p-2" type="number" min="1" :name="'fields['+index+'][max]'" x-model="field.max" required></label>
                        <label class="text-sm">Stawka za jednostkę (zł)<input class="mt-1 w-full rounded border p-2" type="number" min="0" step="0.01" :name="'fields['+index+'][price]'" x-model="field.price" required></label>
                    </div>
                </template>
                <template x-if="field.type === 'chips'">
                    <div class="grid gap-3">
                        <template x-for="(value, valueIndex) in field.values" :key="valueIndex">
                            <div class="grid gap-2 rounded border p-3 sm:grid-cols-3">
                                <label class="text-sm">Kod wariantu<input class="mt-1 w-full rounded border p-2" :name="'fields['+index+'][values]['+valueIndex+'][value]'" x-model="value.value" required></label>
                                <label class="text-sm">Nazwa<input class="mt-1 w-full rounded border p-2" :name="'fields['+index+'][values]['+valueIndex+'][label]'" x-model="value.label" required></label>
                                <label class="text-sm">Dopłata (zł)<input class="mt-1 w-full rounded border p-2" type="number" min="0" step="0.01" :name="'fields['+index+'][values]['+valueIndex+'][price]'" x-model="value.price" required></label>
                                <button type="button" class="text-left text-sm text-red-400" @click="field.values.splice(valueIndex, 1)">Usuń wariant</button>
                            </div>
                        </template>
                        <button type="button" class="justify-self-start rounded border px-3 py-2 text-sm" @click="field.values.push({ value: '', label: '', price: 0 })">+ Dodaj wariant</button>
                    </div>
                </template>
                <div class="flex gap-4 text-sm">
                    <button type="button" @click="if(index > 0) fields.splice(index - 1, 0, fields.splice(index, 1)[0])">↑ Wyżej</button>
                    <button type="button" @click="if(index < fields.length - 1) fields.splice(index + 1, 0, fields.splice(index, 1)[0])">↓ Niżej</button>
                    <button type="button" class="text-red-400" @click="fields.splice(index, 1)" :disabled="fields.length <= 1">Usuń opcję</button>
                </div>
            </div>
        </template>
    </div>
    <button type="button" class="mt-4 rounded border px-4 py-2" @click="fields.push({ key: '', label: '', type: 'chips', required: '1', min: 1, max: 1000, price: 0, values: [{ value: '', label: '', price: 0 }] })">+ Dodaj opcję</button>
</section>
