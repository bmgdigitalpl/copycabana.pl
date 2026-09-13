<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InPostPointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.inpost.base_url' => 'https://api-pl-points.easypack24.net/v1']);
    }

    public function test_valid_post_code_returns_normalized_inpost_points(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://api-pl-points.easypack24.net/v1/points*' => Http::response([
                'items' => [
                    [
                        'name' => 'KAT01M',
                        'address' => ['line1' => 'Bankowa 11'],
                        'address_details' => [
                            'city' => 'Katowice',
                            'post_code' => '40-007',
                            'street' => 'Bankowa',
                            'building_number' => '11',
                        ],
                        'opening_hours' => '24/7',
                        'location_description' => 'Przy punkcie CopyCabana',
                        'location' => ['latitude' => 50.2649, 'longitude' => 19.0238],
                    ],
                ],
            ]),
        ]);

        $response = $this->getJson(route('api.inpost.points', ['post_code' => '40-007']));

        $response->assertOk()
            ->assertJsonPath('points.0.name', 'KAT01M')
            ->assertJsonPath('points.0.address', 'Bankowa 11')
            ->assertJsonPath('points.0.city', 'Katowice')
            ->assertJsonPath('points.0.post_code', '40-007')
            ->assertJsonPath('points.0.opening_hours', '24/7')
            ->assertJsonPath('points.0.latitude', 50.2649);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api-pl-points.easypack24.net/v1/points?type=parcel_locker&per_page=25&post_code=40-007');
    }

    public function test_city_search_returns_points_from_inpost(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://api-pl-points.easypack24.net/v1/points*' => Http::response(['items' => [
                ['name' => 'KRA01A', 'address_details' => ['city' => 'Kraków']],
            ]]),
        ]);

        $response = $this->getJson(route('api.inpost.points', ['city' => 'Kraków']));

        $response->assertOk()->assertJsonPath('points.0.name', 'KRA01A');
    }

    public function test_search_requires_a_post_code_or_city(): void
    {
        $response = $this->getJson(route('api.inpost.points'));

        $response->assertUnprocessable()->assertInvalid(['post_code', 'city']);
    }

    public function test_upstream_failure_returns_an_empty_points_list(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://api-pl-points.easypack24.net/v1/points*' => Http::response([], 503),
        ]);

        $response = $this->getJson(route('api.inpost.points', ['post_code' => '40-007']));

        $response->assertOk()->assertExactJson(['points' => []]);
    }

    public function test_connection_failure_returns_an_empty_points_list(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://api-pl-points.easypack24.net/v1/points*' => Http::failedConnection(),
        ]);

        $response = $this->getJson(route('api.inpost.points', ['post_code' => '40-008']));

        $response->assertOk()->assertExactJson(['points' => []]);
    }
}
