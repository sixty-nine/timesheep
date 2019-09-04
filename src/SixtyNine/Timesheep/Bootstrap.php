<?php

namespace SixtyNine\Timesheep;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Doctrine\DBAL\Driver\PDOSqlite\Driver;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Bootstrap
{
    /**
     * @var ContainerBuilder
     */
    private static $container;
    /**
     * @var Config
     */
    private static $config;
    /**
     * @var string
     */
    private static $baseDir = __DIR__.'/../../../';
    /**
     * @var LoggerInterface|null
     */
    private static $logger;

    /**
     * @param  LoggerInterface|null $logger
     * @return ContainerBuilder
     */
    public static function boostrap(LoggerInterface $logger = null): ContainerBuilder
    {
        if (file_exists(self::$baseDir.'/.env')) {
            $dotenv = Dotenv::create(self::$baseDir);
            $dotenv->load();
        }

        self::$config = new Config();
        self::$logger = $logger;
        $container = self::getContainer();
        $container->register('config', Config::class);
        $container
            ->register('em', EntityManager::class)
            ->setFactory([self::class, 'createEntityManager']);

        self::registerServices($container);

        return $container;
    }

    /**
     * @return ContainerBuilder
     */
    public static function getContainer(): ContainerBuilder
    {
        if (null === self::$container) {
            self::$container = new ContainerBuilder();
        }

        return self::$container;
    }

    /**
     * @return Config
     * @throws \Exception
     */
    public static function getConfig(): Config
    {
        /** @var Config $config */
        $config = self::getContainer()->get('config');
        return $config;
    }

    /**
     * @return EntityManager
     * @throws \Exception
     */
    public static function getEntityManager(): EntityManager
    {
        /**
 * @var EntityManager $em
*/
        $em =self::getContainer()->get('em');
        return $em;
    }

    /**
     * @return EntityManager
     * @throws DBALException
     * @throws ORMException
     */
    public static function createEntityManager(): EntityManager
    {
        $paths = [__DIR__.'/Storage/Entity'];
        $config = Setup::createAnnotationMetadataConfiguration(
            $paths,
            (bool)self::$config->get('dev-mode'),
            null,
            null,
            false
        );
        $connection = DriverManager::getConnection(
            [
            'url' => self::$config->get('db.url'),
            ],
            $config
        );
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        return EntityManager::create($connection, $config);
    }

    private static function registerServices(ContainerBuilder $container): void
    {
        $container->get('config');
    }
}
