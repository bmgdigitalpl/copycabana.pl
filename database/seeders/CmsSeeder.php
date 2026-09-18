<?php

namespace Database\Seeders;

use App\Models\CmsSection;
use App\Services\CmsContent;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $content = app(CmsContent::class);

        foreach ($content->defaults() as $key => $payload) {
            CmsSection::query()->firstOrCreate(
                ['key' => $key],
                [
                    'name' => $content->label($key),
                    'payload' => $payload,
                ],
            );
        }
    }
}
