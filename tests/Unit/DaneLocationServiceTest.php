<?php

namespace Tests\Unit;

use App\Services\Geography\DaneLocationService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DaneLocationServiceTest extends TestCase
{
    public function test_it_resolves_a_coordinate_to_official_divipola_data(): void
    {
        Http::fake(['geoportal.dane.gov.co/*' => Http::response(['features' => [['attributes' => [
            'DPTO_CCDGO' => '68', 'MPIO_CCDGO' => '217', 'MPIO_CDPMP' => '68217',
            'DPTO_CNMBRE' => 'SANTANDER', 'MPIO_CNMBRE' => 'COROMORO',
        ]]]])]);

        $location = app(DaneLocationService::class)->locate(6.2, -73.1);

        $this->assertSame('COROMORO', $location['municipality']);
        $this->assertSame('68217', $location['municipality_code']);
        $this->assertSame('SANTANDER', $location['department']);
        Http::assertSent(fn ($request) => $request['geometry'] === '-73.1,6.2' && (int) $request['inSR'] === 4326);
    }
}
