<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\ParcelImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use SplFileObject;

class ImportParcelsFromCsv extends Command
{
    protected $signature = 'parcels:import
        {path : Ruta del CSV exportado desde Excel}
        {--client= : ID o nombre del cliente al que pertenece la importación}
        {--delimiter=, : Delimitador del archivo (por defecto ",")}
    ';

    protected $description = 'Importa bultos y datos de liquidación desde un CSV y actualiza el histórico de eventos';

    public function __construct(private readonly ParcelImporter $importer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->argument('path');
        $delimiter = (string) $this->option('delimiter');
        $clientKey = $this->option('client');

        if (! is_string($path) || ! file_exists($path)) {
            $this->error(sprintf('No se encontró el archivo en la ruta %s', (string) $path));
            return self::FAILURE;
        }

        if (! $clientKey) {
            $this->error('Debes indicar el cliente destino con --client=ID (o nombre exacto).');
            return self::FAILURE;
        }

        $client = $this->resolveClient($clientKey);

        if (! $client) {
            $this->error(sprintf('No se encontró un cliente que coincida con "%s".', $clientKey));
            return self::FAILURE;
        }

        $rows = $this->readCsvIntoRows($path, $delimiter);

        if (empty($rows)) {
            $this->warn('El archivo no contiene datos para importar.');
            return self::INVALID;
        }

        $summary = $this->importer->import($client, $rows);

        $this->info(sprintf(
            'Importación completada para %s: %d filas procesadas, %d creadas, %d actualizadas, %d sin cambios.',
            $client->name,
            $summary['total'],
            $summary['created'],
            $summary['updated'],
            $summary['skipped'],
        ));

        return self::SUCCESS;
    }

    protected function resolveClient(string $key): ?Client
    {
        if (is_numeric($key)) {
            return Client::find((int) $key);
        }

        return Client::where('name', $key)->first();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function readCsvIntoRows(string $path, string $delimiter = ','): array
    {
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($delimiter);

        $headers = [];
        $rows = [];

        foreach ($file as $index => $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if ($index === 0) {
                $headers = array_map(static fn ($header) => is_string($header) ? trim($header) : $header, $row);
                continue;
            }

            $values = array_pad($row, count($headers), null);
            $rows[] = array_combine($headers, $values);
        }

        return array_filter($rows, static fn ($row) => Arr::where($row, fn ($value) => $value !== null && $value !== '') !== []);
    }
}
