<?php

declare(strict_types=1);

namespace App;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
            'view_helpers' => $this->getViewHelpers(),
        ];
    }

    public function getViewHelpers(): array
    {
        return [
            'aliases'   => [
                'infoWebsite' => View\Helper\InfoWebsite::class,
            ],
            'factories' => [
                View\Helper\InfoWebsite::class => View\Helper\InfoWebsiteFactory::class,
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                Middleware\DefaultHomeMiddleware::class => Middleware\DefaultHomeMiddlewareFactory::class,

                // Cache data aplikasi (lihat config/autoload/cache.global.php)
                \Psr\SimpleCache\CacheInterface::class   => Cache\SimpleCacheFactory::class,
                \Psr\Cache\CacheItemPoolInterface::class => Cache\CacheItemPoolFactory::class,

                // Koneksi database (lihat config/autoload/database.local.php.dist)
                \Laminas\Db\Adapter\AdapterInterface::class => Db\AdapterFactory::class,

                // Log error (lihat App\Error\LogErrorFactory)
                Error\LogError::class => Error\LogErrorFactory::class,
            ],
            // Setiap kesalahan dicatat ke log error sebelum halaman error dibuat, di mode apa pun
            'delegators' => [
                \Mezzio\Middleware\ErrorResponseGenerator::class => [Error\PencatatErrorDelegator::class],
            ],
            'aliases' => [
                \Laminas\Db\Adapter\Adapter::class => \Laminas\Db\Adapter\AdapterInterface::class,
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'dapur'  => [__DIR__ . '/../templates/dapur'],
                'layout' => [__DIR__ . '/../templates/layout'],
                'error'  => [__DIR__ . '/../templates/error'],
            ],
        ];
    }
}
