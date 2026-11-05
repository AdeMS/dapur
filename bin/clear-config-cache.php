<?php

/**
 * Menghapus file cache konfigurasi (data/cache/config-cache.php).
 *
 * Jalankan setiap kali konfigurasi diubah, misalnya setelah menambahkan modul:
 * composer clear-config-cache
 */

declare(strict_types=1);

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

// Lokasi cache dihitung dengan aturan yang sama seperti config/config.php, tanpa memuat
// konfigurasi (memuat konfigurasi justru membuat ulang cache yang hendak dihapus).
$config = [
    'config_cache_path' => defined('DAPUR_CONFIG_CACHE_PATH')
        ? (string) DAPUR_CONFIG_CACHE_PATH
        : dirname(__DIR__) . '/data/cache/config-cache.php',
];

if (! file_exists($config['config_cache_path'])) {
    printf("File cache konfigurasi '%s' tidak ada; tidak ada yang dihapus.%s", $config['config_cache_path'], PHP_EOL);
    exit(0);
}

if (false === unlink($config['config_cache_path'])) {
    printf("Gagal menghapus file cache konfigurasi '%s'.%s", $config['config_cache_path'], PHP_EOL);
    exit(1);
}

printf("File cache konfigurasi '%s' berhasil dihapus.%s", $config['config_cache_path'], PHP_EOL);
