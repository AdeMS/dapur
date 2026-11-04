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
