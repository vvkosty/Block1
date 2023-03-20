<?php

declare(strict_types=1);

namespace App\Core;

use App\Documents\Device;
use App\Interfaces\DeviceRepositoryInterface;
use App\Repositories\Mongo\DeviceRepository;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use MongoDB\Client;

class AppMongo extends App
{
    public DocumentManager $objectManager;

    public function __construct()
    {
        $this->createDatabaseConnection();
    }

    public function createDatabaseConnection(): void
    {
        $dirname = dirname(__DIR__, 2);

        $config = new Configuration();
        $config->setProxyDir($dirname . '/Proxies');
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir($dirname . '/Hydrators');
        $config->setHydratorNamespace('Hydrators');
        $config->setPersistentCollectionDir($dirname . '/PersistentCollections');
        $config->setPersistentCollectionNamespace('PersistentCollections');
        $config->setDefaultDB($_ENV['DB_MONGO_DBNAME']);
        $config->setMetadataDriverImpl(AnnotationDriver::create($dirname . '/Documents'));

        $client = new Client(
            sprintf(
                "mongodb://%s:%s@%s:%s/%s",
                $_ENV['DB_MONGO_USER'],
                $_ENV['DB_MONGO_PASSWORD'],
                $_ENV['DB_MONGO_HOST'],
                $_ENV['DB_MONGO_PORT'],
                $_ENV['DB_MONGO_DBNAME']
            )
        );
        $this->objectManager = DocumentManager::create($client, $config);
    }

    public function getDeviceRepository(): DeviceRepositoryInterface
    {
        return $this->deviceRepository ?? new DeviceRepository(
            $this->objectManager,
            $this->objectManager->getUnitOfWork(),
            new ClassMetadata(Device::class)
        );
    }
}
