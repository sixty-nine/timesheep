<?php

namespace SixtyNine\Timesheep\Helper;

use Doctrine\DBAL\Connection;

class Doctrine
{
    public static function truncateAll(Connection $connection)
    {
        $connection->getConfiguration()->setSQLLogger();
        $isSqlite = 'pdo_sqlite' === $connection->getDriver()->getName();

        if (!$isSqlite) {
            $connection->prepare("SET FOREIGN_KEY_CHECKS = 0;")->execute();
        }

        foreach ($connection->getSchemaManager()->listTableNames() as $tableNames) {
            $sql = 'DELETE FROM ' . $tableNames;
            $connection->prepare($sql)->execute();
        }

        if (!$isSqlite) {
            $connection->prepare("SET FOREIGN_KEY_CHECKS = 1;")->execute();
        }
    }
}
