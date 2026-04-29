<?php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;

class DbConnectionFactory
{
    public static function createTransportConnection(ConfigManager $configManager): ?Connection
    {
        return self::loadDb($configManager, 'transport');
    }

    public static function createStatsConnection(ConfigManager $configManager, Connection $db): Connection
    {
        return self::loadDb($configManager, 'statstransport') ?? $db;
    }

    private static function loadDb(ConfigManager $configManager, string $config): ?Connection
    {
        $dbConfig = $configManager->getConfig($config);
        if ($dbConfig === null || $dbConfig->isEmpty()) {
            return null;
        }
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $dbConfig->get('mysqlHost'),
            'database' => $dbConfig->get('mysqlDatabase'),
            'username' => $dbConfig->get('mysqlUser'),
            'password' => $dbConfig->get('mysqlPassword'),
            'charset' => $dbConfig->get('mysqlConnectionEncoding'),
            'collation' => 'utf8mb4_bin',
            'prefix' => $dbConfig->get('mysqlTablesPrefix'),
            'options' => [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_TIMEOUT => 1,
            ],
        ]);
        $capsule->setFetchMode(PDO::FETCH_ASSOC);
        if ($config === 'transport') {
            $capsule->setAsGlobal();
        }
        $connection = $capsule->getConnection();
        if ($pdo = $connection->getPdo()) {
            $pdo->query('SET sql_mode = ""');
        }
        return $connection;
    }
}
