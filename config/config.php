<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

// Lokasi file cache konfigurasi.
// Cache hanya dibuat jika 'config_cache_enabled' bernilai true (lihat config/autoload/cache.global.php).
// Hapus file ini dengan `composer clear-config-cache` setiap kali konfigurasi diubah.
// Bawaan: data/cache/config-cache.php. Lokasi ini bisa diganti dengan mendefinisikan konstanta
// DAPUR_CONFIG_CACHE_PATH sebelum file ini dimuat (dipakai oleh fitur multi-website agar setiap
// website memiliki cache konfigurasi sendiri).
$cacheConfig = [
    'config_cache_path' => defined('DAPUR_CONFIG_CACHE_PATH')
        ? (string) DAPUR_CONFIG_CACHE_PATH
        : dirname(__DIR__) . '/data/cache/config-cache.php',
];

// Modul tambahan yang aktif di inti (opsional, diabaikan Git).
// Isinya daftar nama ConfigProvider modul, misalnya:
//     return [AdeMS\Dapur\MultiWebsite\ConfigProvider::class];
$modulInti = is_file(__DIR__ . '/modul.local.php') ? require __DIR__ . '/modul.local.php' : [];

// Memuat konfigurasi dari berbagai penyedia konfigurasi
$aggregator = new ConfigAggregator([
    \Laminas\Serializer\ConfigProvider::class,
    \Laminas\Cache\ConfigProvider::class,
    \Laminas\Cache\Storage\Adapter\Filesystem\ConfigProvider::class,
    \Laminas\View\ConfigProvider::class,
    \Mezzio\LaminasView\ConfigProvider::class,
    \Laminas\ServiceManager\ConfigProvider::class,
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,
    \Mezzio\Helper\ConfigProvider::class,
    \Mezzio\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,

    // Lokasi file cache konfigurasi
    new ArrayProvider($cacheConfig),

    // Default App module config
    App\ConfigProvider::class,

    // Modul tambahan inti dari config/modul.local.php: dimuat sebelum config/autoload,
    // agar pengaturan modul tetap bisa ditimpa lewat config/autoload/*.php
    ...(is_array($modulInti) ? $modulInti : []),

    // Memuat konfigurasi tambahan dari config/autoload/:
    // `global.php`, `*.global.php`, `local.php`, dan `*.local.php`
    new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),

    // Lapisan website: diisi paket multi-website lewat konstanta DAPUR_CONFIG_PROVIDERS
    // sebelum file ini dimuat. Paling akhir, agar konfigurasi website menimpa semua bawaan inti.
    ...(defined('DAPUR_CONFIG_PROVIDERS') && is_array(DAPUR_CONFIG_PROVIDERS) ? DAPUR_CONFIG_PROVIDERS : []),
], $cacheConfig['config_cache_path']);

// Mengembalikan konfigurasi yang telah digabungkan
return $aggregator->getMergedConfig();
