<?php

namespace App\Service;


use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class DatabaseService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {}

    /**
     * @throws Exception
     */
    public function truncateTable(string $class) : void
    {
        $cmd = $this->entityManager->getClassMetadata($class);
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
            $connection->executeQuery('DELETE FROM '.$cmd->getTableName());
            // Beware of ALTER TABLE here--it's another DDL statement and will cause
            // an implicit commit.
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
        }
    }
}
