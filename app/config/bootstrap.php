<?php

declare(strict_types=1);

use App\Core\AppBuilder;
use App\Enums\AppDatabase;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppBuilder::createApp(AppDatabase::MONGODB);
