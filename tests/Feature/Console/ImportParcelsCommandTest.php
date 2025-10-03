<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Services\ParcelImporter;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportParcelsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_imports_and_updates_parcels(): void
    {
        $client = Client::factory()->create();
        $importer = app(ParcelImporter::class);

        $rows = [
            [
                'direccion' => 'CALLE MALUQUER SALVADOR, 23, GIRONA',
                'envios_recogidas' => '0082800082808820496183001',
                'liquidacion' => 'L20250911',
                'codigo_de_liquidacion' => 'L20250911_001721170U0036',
                'parada' => 'T202509110029030029039700449452',
            ],
        ];

        $summary = $importer->import($client, $rows);

        $this->assertSame(1, $summary['total']);
        $this->assertSame(1, $summary['created']);
        $this->assertDatabaseHas('parcels', [
            'client_id' => $client->id,
            'code' => '0082800082808820496183001',
            'liquidation_code' => 'L20250911',
            'liquidation_reference' => 'L20250911_001721170U0036',
            'stop_code' => 'T202509110029030029039700449452',
        ]);

        $rows[0]['direccion'] = 'CALLE MIGDIA, 23, LOCAL 1, GIRONA';
        $rows[0]['liquidacion'] = 'L20250912';
        $rows[0]['codigo_de_liquidacion'] = 'L20250912_001721170U0036';

        $secondSummary = $importer->import($client, $rows);

        $this->assertSame(1, $secondSummary['updated']);
        $this->assertDatabaseHas('parcels', [
            'client_id' => $client->id,
            'code' => '0082800082808820496183001',
            'address_line' => 'CALLE MIGDIA, 23, LOCAL 1, GIRONA',
            'liquidation_code' => 'L20250912',
        ]);
    }

    public function test_command_requires_client_argument(): void
    {
        $this->artisan('parcels:import', ['path' => __FILE__])
            ->assertExitCode(Command::FAILURE);

        $this->artisan('parcels:import', ['path' => __FILE__, '--client' => 'Cliente inexistente'])
            ->assertExitCode(Command::FAILURE);
    }
}
