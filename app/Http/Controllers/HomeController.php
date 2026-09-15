<?php

namespace App\Http\Controllers;

use App\Services\BusinessConfiguratorService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(BusinessConfiguratorService $configurator): View
    {
        $services = [
            [
                'title' => 'Praca dyplomowa',
                'alt' => 'Oprawiona praca dyplomowa',
                'image' => asset('images/produkty/oprawa-prac-i-bindowanie.png'),
                'href' => route('services.diploma'),
            ],
        ];

        foreach ($configurator->catalog() as $product) {
            $services[] = [
                'title' => $product['name'],
                'alt' => $product['name'],
                'image' => $product['image'],
                'href' => route('services.business', ['product' => $product['id']]).'#produkty',
            ];
        }

        return view('home', compact('services'));
    }
}
