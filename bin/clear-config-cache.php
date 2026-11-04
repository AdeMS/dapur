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

$config = include 'config/config.php';

if (! isset($config['config_cache_path'])) {
    echo "Lokasi cache konfigurasi tidak ditemukan; tidak ada yang dihapus." . PHP_EOL;
    exit(0);
}

if (! file_exists($config['config_cache_path'])) {
    printf("File cache konfigurasi '%s' tidak ada; tidak ada yang dihapus.%s", $config['config_cache_path'], PHP_EOL);
    exit(0);
}

if (false === unlink($config['config_cache_path'])) {
    printf("Gagal menghapus file cache konfigurasi '%s'.%s", $config['config_cache_path'], PHP_EOL);
    exit(1);
}

printf("File cache konfigurasi '%s' berhasil dihapus.%s", $config['config_cache_path'], PHP_EOL);
