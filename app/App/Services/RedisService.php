<?php

declare(strict_types=1);

namespace App\Services;

use Predis\Client;

class RedisService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
        ]);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function get(string $data): mixed
    {
        $cachedResult = $this->client->get($data);

        return $cachedResult ? json_decode($cachedResult, false, 512, JSON_THROW_ON_ERROR) : null;
    }

    public function set(string $key, mixed $data)
    {
        return $this->client->set($key, json_encode($data, JSON_THROW_ON_ERROR));
    }
}
