<?php

declare(strict_types=1);

namespace App\Core;

use App\Enums\AppDatabase;
use RuntimeException;

class AppBuilder
{
    public static function createApp(AppDatabase $appDatabase): App
    {
        return match ($appDatabase) {
            AppDatabase::MONGODB => new AppMongo(),
            AppDatabase::POSTGRESQL => new AppPostgres(),
            default => throw new RuntimeException('Can\'t create app'),
        };
    }
}
