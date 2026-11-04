<?php

declare(strict_types=1);

namespace App\Cache;

use Laminas\Cache\Psr\CacheItemPool\CacheItemPoolDecorator;
use Laminas\Cache\Storage\StorageInterface;
use Psr\Container\ContainerInterface;

/**
 * Membuat cache PSR-6 (Psr\Cache\CacheItemPoolInterface) dari storage laminas-cache.
 */
final class CacheItemPoolFactory
{
    public function __invoke(ContainerInterface $container): CacheItemPoolDecorator
    {
        return new CacheItemPoolDecorator($container->get(StorageInterface::class));
    }
}
