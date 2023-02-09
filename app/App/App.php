<?php

declare(strict_types=1);

namespace App;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class App
{
    public EntityManager $entityManager;

    public function __construct()
    {
        $dbParams = [
            'driver' => 'pdo_pgsql',
            'user' => 'block1',
            'password' => 'block1',
            'host' => 'postgresql',
            'dbname' => 'block1',
        ];

        $config = ORMSetup::createAttributeMetadataConfiguration(['App\Entities']);
        $connection = DriverManager::getConnection($dbParams, $config);
        $this->entityManager = new EntityManager($connection, $config);
    }
}
