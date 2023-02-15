<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DeviceService;

class DeviceController
{
    public function __construct(
        public DeviceService $service
    ) {
    }

    public function create(array $params): int
    {
        $deviceId = (int)$params['deviceId'];
        unset($params['deviceId']);

        return $this->service->create($deviceId, $params);
    }

    public function edit(int $deviceId, array $tags): void
    {
        $this->service->edit($deviceId, $tags);
    }

    /**
     * @return int[]
     */
    public function search(string $query): array
    {
        return $this->service->search($query);
    }
}
