<?php

/**
 * Menampilkan catatan log error terbaru, atau satu catatan lengkap berdasarkan kodenya.
 *
 *   php dapur log-error                 10 kesalahan terakhir
 *   php dapur log-error --jumlah=30     30 kesalahan terakhir
 *   php dapur log-error 7F3A09C2        satu kesalahan beserta jejak pemanggilannya
 *
 * Dengan paket multi-website:
 *
 *   php dapur log-error --website=<domain>   log website tertentu
 *   php dapur log-error --semua              log inti dan semua website digabung, terbaru lebih dulu
 *   php dapur log-error --semua 7F3A09C2     mencari kode di inti dan semua website
 */

declare(strict_types=1);

use App\Error\LogError;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$kode   = null;
$jumlah = 10;
$semua  = false;
foreach (array_slice($argv, 2) as $argumen) {
    if (str_starts_with($argumen, '--jumlah=')) {
        $jumlah = max(1, min(500, (int) substr($argumen, 9)));
    } elseif ($argumen === '--semua') {
        $semua = true;
    } elseif (! str_starts_with($argumen, '--')) {
        $kode = $argumen;
    }
}

$container = require 'config/container.php';
$logInti   = $container->get(LogError::class);
assert($logInti instanceof LogError);

// Daftar file log yang dibaca: nama => lokasi (null bila folder website belum ada)
$file = ['' => $logInti->file];
if ($semua) {
    $kelas = 'AdeMS\Dapur\MultiWebsite\Perintah';
    if (! class_exists($kelas) || ! method_exists($kelas, 'fileLogErrorSemua')) {
        fwrite(STDERR, 'Opsi --semua membutuhkan paket adems/dapur-multiwebsite 1.2 atau lebih baru.' . PHP_EOL);
        exit(1);
    }

    $file = (new $kelas(getcwd()))->fileLogErrorSemua($logInti->file);
    if ($file === null) {
        fwrite(STDERR, "Multi-website belum aktif: 'config/multiwebsite.local.php' tidak ditemukan." . PHP_EOL);
        exit(1);
    }
}

$catatan = [];
$masalah = false;
$batas   = $kode === null ? $jumlah : 1;
foreach ($file as $nama => $lokasi) {
    $label = $nama === '' ? '' : $nama . ': ';
    if ($lokasi === null) {
        printf("%sfolder website tidak ditemukan%s", $label, PHP_EOL);
        continue;
    }

    if (! is_file($lokasi)) {
        if (! $semua) {
            printf("Belum ada kesalahan yang tercatat (%s).%s", $lokasi, PHP_EOL);
            exit(0);
        }

        // Folder log yang tidak bisa dibuka membuat file di dalamnya terlihat tidak ada.
        if (is_dir(dirname($lokasi)) && ! is_executable(dirname($lokasi))) {
            printf("%sfolder log '%s' tidak bisa dibaca akun ini%s", $label, dirname($lokasi), PHP_EOL);
            $masalah = true;
        }

        continue;
    }

    if (! is_readable($lokasi)) {
        printf("%sfile log '%s' tidak bisa dibaca akun ini; jalankan dengan akun yang berhak.%s", $label, $lokasi, PHP_EOL);
        $masalah = true;
        continue;
    }

    array_push($catatan, ...(new LogError($lokasi))->baca($batas, $kode));
}

// Gabungan beberapa file: urutkan dari yang terbaru, lalu potong sesuai jumlah.
usort($catatan, static fn (array $a, array $b): int => strcmp((string) ($b['waktu'] ?? ''), (string) ($a['waktu'] ?? '')));
$catatan = array_slice($catatan, 0, $batas);

if ($catatan === []) {
    if ($kode !== null) {
        printf("Kode '%s' tidak ditemukan di log.%s", $kode, PHP_EOL);
        exit(1);
    }

    echo 'Belum ada kesalahan yang tercatat.' . PHP_EOL;
    exit($masalah ? 1 : 0);
}

if ($semua) {
    echo PHP_EOL;
}

$zona = new DateTimeZone(date_default_timezone_get());
foreach ($catatan as $c) {
    $waktu = (new DateTimeImmutable((string) ($c['waktu'] ?? 'now')))->setTimezone($zona)->format('Y-m-d H:i:s T');
    printf(
        "[%s] %s  %s  %s %s%s%s",
        $c['kode'] ?? '-',
        $waktu,
        $c['status'] ?? '-',
        $c['metode'] ?? '',
        $c['host'] ?? '',
        $c['alamat'] ?? '',
        PHP_EOL,
    );
    printf("  %s: %s%s  di %s:%s%s", $c['kelas'] ?? '-', $c['pesan'] ?? '', PHP_EOL, $c['file'] ?? '-', $c['baris'] ?? '-', PHP_EOL);
    foreach ($c['sebelumnya'] ?? [] as $p) {
        printf("  sebelumnya %s: %s%s    di %s:%s%s", $p['kelas'] ?? '-', $p['pesan'] ?? '', PHP_EOL, $p['file'] ?? '-', $p['baris'] ?? '-', PHP_EOL);
    }

    if ($kode !== null) {
        echo '  Jejak pemanggilan:' . PHP_EOL;
        foreach ($c['jejak'] ?? [] as $i => $j) {
            printf("    #%d %s%s", $i, $j, PHP_EOL);
        }
    }

    echo PHP_EOL;
}

if (! $semua) {
    printf("File log: %s%s", $logInti->file, PHP_EOL);
}

exit($masalah ? 1 : 0);
