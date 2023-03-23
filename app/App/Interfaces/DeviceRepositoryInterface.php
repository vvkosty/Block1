<?php

declare(strict_types=1);

namespace App\Interfaces;

use DateTime;
use Ds\Stack;

interface DeviceRepositoryInterface
{
    public function create(int $deviceId, array $tags);

    public function createBatch(array $devices): void;

    public function edit(int $deviceId, array $tags): void;

    public function search(Stack $infixStack): array;

    public function recreateEmails(): void;

    public function removeByCreateDates(DateTime $from, DateTime $to): void;

    public function updateTagValue(string $tagName, int $deviceId, string $tagValue): void;
}
