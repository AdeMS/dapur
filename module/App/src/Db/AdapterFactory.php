<?php

declare(strict_types=1);

namespace App\Db;

use Laminas\Db\Adapter\Adapter;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerInterface;

/**
 * Membuat koneksi database (Laminas\Db\Adapter\AdapterInterface) dari konfigurasi `db`.
 *
 * Factory bawaan laminas-db belum mendukung laminas-servicemanager 4,
 * sehingga Dapur AdeMS memakai factory ini sebagai gantinya.
 */
final class AdapterFactory
{
    public function __invoke(ContainerInterface $container): Adapter
    {
        $config = $container->get('config')['db'] ?? [];

        if (! is_array($config) || $config === []) {
            throw new ServiceNotCreatedException(
                'Koneksi database belum diatur. Salin config/autoload/database.local.php.dist '
                . 'menjadi database.local.php, isi datanya, lalu jalankan composer clear-config-cache.'
            );
        }

        return new Adapter($config);
    }
}
