<?php

declare(strict_types=1);

use Laminas\Cache\Storage\StorageInterface;
use Laminas\ConfigAggregator\ConfigAggregator;

return [
    // Cache konfigurasi: menyimpan hasil penggabungan semua konfigurasi
    // ke data/cache/config-cache.php agar tidak dihitung ulang setiap request.
    // Ubah ke false (misalnya di config/autoload/local.php) selama pengembangan.
    ConfigAggregator::ENABLE_CACHE => true,

    // Cache data aplikasi (laminas-cache).
    // Ambil dari container dengan salah satu nama berikut:
    // - Laminas\Cache\Storage\StorageInterface   (storage laminas-cache)
    // - Psr\SimpleCache\CacheInterface           (PSR-16)
    // - Psr\Cache\CacheItemPoolInterface         (PSR-6)
    'caches' => [
        StorageInterface::class => [
            'adapter' => 'filesystem',
            'options' => [
                'cache_dir' => dirname(__DIR__, 2) . '/data/cache/app',
                'namespace' => 'dapur',
                'ttl'       => 3600,
            ],
            'plugins' => [
                // Diperlukan agar data selain string (array, objek) dapat disimpan
                ['name' => 'serializer'],
            ],
        ],
    ],
];
