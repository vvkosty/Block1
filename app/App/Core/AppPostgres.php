<?php

declare(strict_types=1);

namespace App\Core;

use App\Entities\Device;
use App\Interfaces\DeviceRepositoryInterface;
use App\Repositories\Postgres\DeviceRepository;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class AppPostgres extends App
{
    public EntityManager $objectManager;

    public function __construct()
    {
        $this->createDatabaseConnection();
    }

    public function createDatabaseConnection(): void
    {
        $dbParams = [
            'driver' => $_ENV['DB_POSTGRES_DRIVER'],
            'user' => $_ENV['DB_POSTGRES_USER'],
            'password' => $_ENV['DB_POSTGRES_PASSWORD'],
            'host' => $_ENV['DB_POSTGRES_HOST'],
            'dbname' => $_ENV['DB_POSTGRES_DBNAME'],
        ];

        $config = ORMSetup::createAttributeMetadataConfiguration(
            ['App\Entities'],
            false,
            null,
            new RedisAdapter($this->getRedisService()->getClient()),
        );
        $connection = DriverManager::getConnection($dbParams, $config);
        $this->objectManager = new EntityManager($connection, $config);
    }

    public function getDeviceRepository(): DeviceRepositoryInterface
    {
        return $this->deviceRepository ?? new DeviceRepository(
            $this->objectManager,
            new ClassMetadata(Device::class)
        );
    }
}
