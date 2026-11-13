<?php

declare(strict_types=1);

namespace App\Error;

use Psr\Container\ContainerInterface;

/**
 * Membuat LogError dari pengaturan 'log':
 *
 *     'log' => [
 *         'error'                => '/lokasi/error.log',   // bawaan: data/log/error.log di folder inti
 *         'error_ukuran_maks_mb' => 5,                     // lebih dari ini, file dipindah ke error.log.1
 *     ],
 *
 * Paket multi-website mengarahkan log setiap website ke folder data website itu sendiri.
 */
final class LogErrorFactory
{
    public function __invoke(ContainerInterface $container): LogError
    {
        $log = $container->get('config')['log'] ?? [];

        return new LogError(
            (string) ($log['error'] ?? dirname(__DIR__, 4) . '/data/log/error.log'),
            (int) (max(1, (float) ($log['error_ukuran_maks_mb'] ?? 5)) * 1024 * 1024),
        );
    }
}
