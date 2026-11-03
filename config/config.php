<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

// Memuat konfigurasi dari berbagai penyedia konfigurasi
$aggregator = new ConfigAggregator([
    \Laminas\View\ConfigProvider::class,
    \Mezzio\LaminasView\ConfigProvider::class,
    \Laminas\ServiceManager\ConfigProvider::class,
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,
    \Mezzio\Helper\ConfigProvider::class,
    \Mezzio\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,

    // Default App module config
    App\ConfigProvider::class,
]);

// Mengembalikan konfigurasi yang telah digabungkan
return $aggregator->getMergedConfig();
