<?php

namespace Tests\Feature;

use App\Services\CoverColorSorter;
use Tests\TestCase;

class CoverColorSorterTest extends TestCase
{
    public function test_it_puts_light_neutrals_first_then_the_colour_wheel_then_dark_neutrals(): void
    {
        $sorted = app(CoverColorSorter::class)->sort([
            'black' => ['hex' => '#1A1B21'],
            'navy' => ['hex' => '#20263C'],
            'red' => ['hex' => '#921D2B'],
            'white' => ['hex' => '#F0F0F0'],
            'beige' => ['hex' => '#B09374'],
            'green' => ['hex' => '#1E3835'],
            'grey' => ['hex' => '#8C8B89'],
            'violet' => ['hex' => '#572F76'],
            'brown' => ['hex' => '#43241E'],
            'lime' => ['hex' => '#8D8800'],
        ]);

        $this->assertSame(['white', 'grey', 'beige', 'lime', 'green', 'navy', 'violet', 'red', 'brown', 'black'], array_keys($sorted));
    }

    public function test_a_colour_without_a_valid_hex_goes_last_and_keeps_its_data(): void
    {
        $sorted = app(CoverColorSorter::class)->sort([
            'broken' => ['hex' => 'red', 'label' => 'Zepsuty'],
            'white' => ['hex' => '#F0F0F0'],
        ]);

        $this->assertSame(['white', 'broken'], array_keys($sorted));
        $this->assertSame('Zepsuty', $sorted['broken']['label']);
    }
}
