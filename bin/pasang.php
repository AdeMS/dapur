<?php

/**
 * Memasang dan memperbarui tabel database semua modul yang terpasang (membutuhkan paket adems/dapur-database).
 *
 *   php dapur pasang                  tampilkan rencana, lalu tanya [y/N] sebelum dijalankan
 *   php dapur pasang --periksa        hanya tampilkan rencana; kode keluar 2 bila ada yang belum dijalankan
 *   php dapur pasang --ya             jalankan tanpa bertanya (untuk skrip)
 *   php dapur pasang user             hanya modul user (beserta modul yang dibutuhkannya)
 *
 * Dengan paket multi-website, --website=<domain> memasang tabel modul website itu di database website itu.
 */

declare(strict_types=1);

use AdeMS\Dapur\Database\PabrikPemasang;
use AdeMS\Dapur\Database\Perintah;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

if (! class_exists(Perintah::class)) {
    fwrite(STDERR, 'Perintah pasang membutuhkan paket adems/dapur-database. Pasang lewat composer.local.json, lalu composer update.' . PHP_EOL);
    exit(1);
}

// Pertanyaan [y/N] hanya diajukan bila dijalankan di terminal; dari skrip, pakai --ya.
$tanya = stream_isatty(STDIN)
    ? static function (string $pesan): string {
        echo $pesan;

        return rtrim((string) fgets(STDIN), "\r\n");
    }
    : null;

try {
    $container = require 'config/container.php';
    $pemasang  = PabrikPemasang::dariContainer($container);
} catch (Throwable $e) {
    while ($e->getPrevious() !== null) {
        $e = $e->getPrevious();
    }

    fwrite(STDERR, 'Gagal: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

exit((new Perintah($pemasang, $tanya))->jalankan(array_values(array_slice($argv, 2))));
