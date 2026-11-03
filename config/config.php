<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

// Memuat konfigurasi dari berbagai penyedia konfigurasi
$aggregator = new ConfigAggregator([
    
]);

// Mengembalikan konfigurasi yang telah digabungkan
return $aggregator->getMergedConfig();
