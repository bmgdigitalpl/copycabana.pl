<?php

namespace App\Http\Controllers;

use App\Services\BusinessConfiguratorService;
use Illuminate\View\View;

class BusinessPrintController extends Controller
{
    public function __invoke(BusinessConfiguratorService $configurator): View
    {
        return view('druk-dla-firm', [
            'b2bCatalog' => $configurator->catalog(),
            'b2bDeliveries' => [
                ['id' => 'pickup', 'name' => 'Odbiór w Katowicach', 'price' => config('business.shipping.pickup'), 'hint' => 'ul. Bankowa 11, 40-007 Katowice'],
                ['id' => 'parcel', 'name' => 'Paczkomat', 'price' => config('business.shipping.parcel'), 'hint' => 'Wybierz punkt z listy InPost'],
                ['id' => 'courier', 'name' => 'Kurier', 'price' => config('business.shipping.courier'), 'hint' => 'Dostawa pod wskazany adres'],
            ],
        ]);
    }
}
