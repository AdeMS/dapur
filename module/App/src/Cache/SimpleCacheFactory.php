<?php

declare(strict_types=1);

namespace App\Cache;

use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Storage\StorageInterface;
use Psr\Container\ContainerInterface;

/**
 * Membuat cache PSR-16 (Psr\SimpleCache\CacheInterface) dari storage laminas-cache.
 */
final class SimpleCacheFactory
{
    public function __invoke(ContainerInterface $container): SimpleCacheDecorator
    {
        return new SimpleCacheDecorator($container->get(StorageInterface::class));
    }
}
