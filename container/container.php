<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;

// Load configuration
$config = require __DIR__ . '/config/global.php';

// Mengambil hostname tanpa port
$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$host = preg_replace('/:\d+$/', '', $host);
$host = trim($host, '.');

// Mencegah karakter yang tidak valid pada nama file
if (!preg_match('/^[a-z0-9.-]+$/', $host)) {
    $host = '';
}

$hostConfig = __DIR__ . '/config/' . $host . '.php';
$defaultConfig = __DIR__ . '/config/default.php';

// Gunakan konfigurasi domain, atau default.php jika tidak ditemukan
$configFile = $host && is_file($hostConfig)
    ? $hostConfig
    : $defaultConfig;

if (is_file($configFile)) {
    $additionalConfig = require $configFile;

    if (!is_array($additionalConfig)) {
        throw new RuntimeException(
            sprintf('File konfigurasi harus mengembalikan array: %s', $configFile)
        );
    }

    // Konfigurasi domain/default menimpa konfigurasi global
    $config = array_replace_recursive($config, $additionalConfig);
}

$dependencies                       = $config['dependencies'];
$dependencies['services']['config'] = $config;

// Build container
return new ServiceManager($dependencies);
