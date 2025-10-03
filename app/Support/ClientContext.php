<?php

namespace App\Support;

use App\Models\Client;

class ClientContext
{
    protected ?int $clientId = null;
    protected ?Client $client = null;

    public function setClient(?Client $client): void
    {
        $this->client = $client;
        $this->clientId = $client?->id;
    }

    public function setClientId(?int $clientId): void
    {
        $this->clientId = $clientId;
        $this->client = null;
    }

    public function clientId(): ?int
    {
        return $this->clientId;
    }

    public function client(): ?Client
    {
        if ($this->clientId && $this->client === null) {
            $this->client = Client::find($this->clientId);
        }

        return $this->client;
    }

    public function reset(): void
    {
        $this->clientId = null;
        $this->client = null;
    }
}
