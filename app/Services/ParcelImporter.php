<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Parcel;
use App\Support\ClientContext;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ParcelImporter
{
    public function __construct(
        private readonly ClientContext $clientContext,
        private readonly ParcelEventRecorder $eventRecorder
    ) {
    }

    /**
     * @param  iterable<int, array<int|string, mixed>>  $rows
     * @return array{total:int, created:int, updated:int, skipped:int}
     */
    public function import(Client $client, iterable $rows): array
    {
        $previousClient = $this->clientContext->client();
        $this->clientContext->setClient($client);

        $summary = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        foreach ($rows as $row) {
            $summary['total']++;

            $normalized = $this->normalizeRow($row);
            $code = $this->extractCode($normalized);

            if (! $code) {
                $summary['skipped']++;
                continue;
            }

            $attributes = $this->buildAttributes($normalized);

            $parcel = Parcel::firstWhere('code', $code);
            $created = false;
            $updated = false;

            if (! $parcel) {
                $parcel = Parcel::create(array_merge(['code' => $code], $attributes));
                $summary['created']++;
                $created = true;

                $this->eventRecorder->record($parcel, 'parcel_import_created', [
                    'description' => __('Bulto creado desde importación'),
                    'payload' => $this->eventPayload($normalized),
                ]);
            } else {
                $parcel->fill(array_filter($attributes, static fn ($value) => $value !== null));

                if ($parcel->isDirty()) {
                    $parcel->save();
                    $summary['updated']++;
                    $updated = true;

                    $this->eventRecorder->record($parcel, 'parcel_import_updated', [
                        'description' => __('Bulto actualizado desde importación'),
                        'payload' => $this->eventPayload($normalized),
                    ]);
                } else {
                    $summary['skipped']++;
                }
            }

            $parcel->forceFill([
                'metadata' => array_filter(array_merge($parcel->metadata ?? [], [
                    'last_import' => $normalized,
                ]), static fn ($value) => $value !== null),
            ])->save();

            if (! $created && ! $updated) {
                $this->eventRecorder->record($parcel, 'parcel_import_synced', [
                    'description' => __('Importación sin cambios (datos sincronizados)'),
                    'payload' => $this->eventPayload($normalized),
                ]);
            }
        }

        $this->clientContext->setClient($previousClient);

        return $summary;
    }

    /**
     * @param  array<int|string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            if ($key === '' || $key === null) {
                continue;
            }

            $slug = Str::slug((string) $key, '_');
            $normalized[$slug] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function extractCode(array $row): ?string
    {
        $candidates = [
            'envios_recogidas',
            'codigo',
            'code',
        ];

        foreach ($candidates as $candidate) {
            $value = Arr::get($row, $candidate);
            if (is_string($value) && $value !== '') {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function buildAttributes(array $row): array
    {
        return [
            'address_line' => Arr::get($row, 'direccion'),
            'city' => Arr::get($row, 'ciudad'),
            'state' => Arr::get($row, 'provincia'),
            'postal_code' => Arr::get($row, 'codigo_postal'),
            'stop_code' => Arr::get($row, 'parada'),
            'liquidation_code' => Arr::get($row, 'liquidacion'),
            'liquidation_reference' => Arr::get($row, 'codigo_de_liquidacion'),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function eventPayload(array $row): array
    {
        return array_filter([
            'raw' => $row,
        ]);
    }
}
