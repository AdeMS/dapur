<?php

/**
 * Menyiapkan Bootstrap dan Bootstrap Icons untuk layout (dijalankan otomatis sesudah composer install/update).
 *
 * - Bila modul aset (adems/dapur-asset) aktif di config/modul.local.php: Bootstrap dilayani lewat PHP dari paket
 *   Composer (/asset/twbs/bootstrap/<versi>/...), jadi salinan di public/asset/bootstrap tidak diperlukan dan dihapus.
 * - Selain itu: vendor/twbs/bootstrap/dist dan vendor/twbs/bootstrap-icons/font disalin ke public/asset/bootstrap,
 *   seperti sebelumnya. Penyalinan memakai PHP, jadi berjalan juga di Windows.
 */

declare(strict_types=1);

chdir(dirname(__DIR__));

const TUJUAN      = 'public/asset/bootstrap';
const MODUL_ASET  = 'AdeMS\\Dapur\\Asset\\ConfigProvider';

$hapus = static function (string $folder) use (&$hapus): void {
    if (is_link($folder) || is_file($folder)) {
        unlink($folder);

        return;
    }

    if (! is_dir($folder)) {
        return;
    }

    foreach (scandir($folder) ?: [] as $nama) {
        if ($nama !== '.' && $nama !== '..') {
            $hapus($folder . '/' . $nama);
        }
    }
    rmdir($folder);
};

$salin = static function (string $asal, string $tujuan) use (&$salin): void {
    if (! is_dir($asal)) {
        throw new RuntimeException("Folder tidak ditemukan: {$asal}. Jalankan composer install lebih dulu.");
    }

    if (! is_dir($tujuan) && ! mkdir($tujuan, 0775, true) && ! is_dir($tujuan)) {
        throw new RuntimeException("Folder tidak bisa dibuat: {$tujuan}");
    }

    foreach (scandir($asal) ?: [] as $nama) {
        if ($nama === '.' || $nama === '..') {
            continue;
        }

        is_dir($asal . '/' . $nama)
            ? $salin($asal . '/' . $nama, $tujuan . '/' . $nama)
            : copy($asal . '/' . $nama, $tujuan . '/' . $nama);
    }
};

try {
    $modul = is_file('config/modul.local.php') ? include 'config/modul.local.php' : [];
    if (is_array($modul) && in_array(MODUL_ASET, $modul, true)) {
        $hapus(TUJUAN);
        echo 'Bootstrap dilayani modul aset (/asset/twbs/bootstrap/...); salinan di ' . TUJUAN . ' tidak diperlukan.' . PHP_EOL;
        exit(0);
    }

    $hapus(TUJUAN);
    $salin('vendor/twbs/bootstrap/dist', TUJUAN);
    $salin('vendor/twbs/bootstrap-icons/font', TUJUAN . '/font');
    echo 'Bootstrap dan Bootstrap Icons disalin ke ' . TUJUAN . '.' . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'Gagal menyiapkan Bootstrap: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
