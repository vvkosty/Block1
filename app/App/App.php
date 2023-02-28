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
            'driver' => $_ENV['DB_DRIVER'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'host' => $_ENV['DB_HOST'],
            'dbname' => $_ENV['DB_DBNAME'],
        ];

        $config = ORMSetup::createAttributeMetadataConfiguration(['App\Entities']);
        $connection = DriverManager::getConnection($dbParams, $config);
        $this->entityManager = new EntityManager($connection, $config);
    }
}
