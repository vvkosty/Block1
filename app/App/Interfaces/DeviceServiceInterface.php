<?php

declare(strict_types=1);

namespace App\Interfaces;

use DateTime;

interface DeviceServiceInterface
{
    public function create(int $deviceId, array $tags): int;

    public function edit(int $deviceId, array $tags): void;

    public function search(string $query): array;

    public function removeByCreateDate(DateTime $from, DateTime $to): void;

    public function recreateEmails(): void;

    public function updateTagValue(int $deviceId, string $tagName, string $tagValue): void;
}
