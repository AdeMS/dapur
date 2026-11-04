<?php

/**
 * Menguji koneksi database sesuai konfigurasi `db`.
 *
 * Jalankan setelah membuat config/autoload/database.local.php:
 * composer cek-database
 */

declare(strict_types=1);

use Laminas\Db\Adapter\AdapterInterface;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$container = require 'config/container.php';
$config    = $container->get('config');

if (empty($config['db'])) {
    echo "Koneksi database belum diatur." . PHP_EOL;
    echo "Salin config/autoload/database.local.php.dist menjadi database.local.php dan isi datanya." . PHP_EOL;
    echo "Jika file tersebut sudah ada, jalankan dulu: composer clear-config-cache" . PHP_EOL;
    exit(1);
}

try {
    /** @var AdapterInterface $adapter */
    $adapter = $container->get(AdapterInterface::class);
    $adapter->query('SELECT 1')->execute();
} catch (Throwable $e) {
    // Ambil pesan error paling dasar (dari PDO), bukan pesan pembungkus dari container
    while ($e->getPrevious() !== null) {
        $e = $e->getPrevious();
    }

    printf("Gagal terhubung ke database (driver %s): %s%s", $config['db']['driver'] ?? '-', $e->getMessage(), PHP_EOL);
    exit(1);
}

$resource = $adapter->getDriver()->getConnection()->getResource();
$versi    = $resource instanceof PDO ? ' ' . $resource->getAttribute(PDO::ATTR_SERVER_VERSION) : '';

printf("Berhasil terhubung ke database %s%s.%s", $adapter->getPlatform()->getName(), $versi, PHP_EOL);
