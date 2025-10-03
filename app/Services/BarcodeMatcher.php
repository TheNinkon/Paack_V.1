<?php

namespace App\Services;

use App\Models\ProviderBarcode;
use App\Support\ClientContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BarcodeMatcher
{
    public function __construct(private readonly ClientContext $clientContext)
    {
    }

    public function match(string $code): ?ProviderBarcode
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        $query = ProviderBarcode::query()
            ->with(['provider'])
            ->where('active', true)
            ->whereHas('provider', function (Builder $builder): void {
                $clientId = $this->clientContext->clientId() ?? auth()->user()?->client_id;
                if ($clientId) {
                    $builder->where('client_id', $clientId);
                }
            })
            ->orderBy('priority')
            ->orderBy('label');

        /** @var Collection<int, ProviderBarcode> $barcodes */
        $barcodes = $query->get();

        foreach ($barcodes as $barcode) {
            $pattern = $this->normalizePattern($barcode->pattern_regex);

            if ($pattern === null) {
                continue;
            }

            if (@preg_match($pattern, $code) === 1) {
                return $barcode;
            }
        }

        return null;
    }

    private function normalizePattern(string $pattern): ?string
    {
        $trimmed = trim($pattern);

        if ($trimmed === '') {
            return null;
        }

        $delimiters = ['/', '#', '~'];

        if (in_array($trimmed[0], $delimiters, true) && strrpos($trimmed, $trimmed[0]) !== 0) {
            return $trimmed;
        }

        return '~'.str_replace('~', '\~', $trimmed).'~u';
    }
}
