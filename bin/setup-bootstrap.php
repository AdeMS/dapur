<?php

/**
 * Menyiapkan aset layout (dijalankan otomatis sesudah composer install/update):
 * Bootstrap, Bootstrap Icons, dan file inti Dapur di module/App/asset (CSS, JS, gambar).
 *
 * - Bila modul aset (adems/dapur-asset 1.1 ke atas) aktif di config/modul.local.php: semuanya dilayani lewat PHP
 *   (/asset/twbs/bootstrap/<versi>/..., /asset/adems/dapur/...), jadi salinan di public/asset tidak diperlukan dan
 *   dihapus.
 * - Selain itu: vendor/twbs/bootstrap/dist dan vendor/twbs/bootstrap-icons/font disalin ke public/asset/bootstrap, dan
 *   module/App/asset ke public/asset/dapur. Penyalinan memakai PHP, jadi berjalan juga di Windows.
 */

declare(strict_types=1);

chdir(dirname(__DIR__));

const TUJUAN      = 'public/asset/bootstrap';
const INTI_ASAL   = 'module/App/asset';
const INTI_TUJUAN = 'public/asset/dapur';
const MODUL_ASET  = 'AdeMS\\Dapur\\Asset\\ConfigProvider';
const ASET_INTI   = 'AdeMS\\Dapur\\Asset\\AsetComposer::ASET_INTI';

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

// Menghapus salinan: hanya file yang isinya sama dengan aslinya, lalu folder yang menjadi kosong. File lain di folder
// itu (mis. ditambahkan sendiri) dibiarkan.
$hapusSalinan = static function (string $asal, string $tujuan) use (&$hapusSalinan): int {
    if (is_link($tujuan) || ! is_dir($tujuan)) {
        return 0;
    }

    $sisa = 0;
    foreach (scandir($tujuan) ?: [] as $nama) {
        if ($nama === '.' || $nama === '..') {
            continue;
        }

        $a = $asal . '/' . $nama;
        $t = $tujuan . '/' . $nama;
        if (is_dir($t) && ! is_link($t)) {
            $sisa += $hapusSalinan($a, $t);
        } elseif (is_file($a) && is_file($t) && ! is_link($t) && hash_file('sha256', $a) === hash_file('sha256', $t)) {
            unlink($t);
        } else {
            $sisa++;
        }
    }

    if ($sisa === 0) {
        rmdir($tujuan);
    }

    return $sisa;
};

try {
    $modul = is_file('config/modul.local.php') ? include 'config/modul.local.php' : [];
    $aktif = is_array($modul) && in_array(MODUL_ASET, $modul, true);

    // Modul aset 1.0 belum mengenal aset inti adems/dapur: file inti tetap disalin agar layout tidak kehilangan CSS-nya.
    if (is_file('vendor/autoload.php')) {
        require 'vendor/autoload.php';
    }
    $kenalInti = $aktif && defined(ASET_INTI);

    if ($aktif) {
        $hapus(TUJUAN);
        echo 'Bootstrap dilayani modul aset (/asset/twbs/bootstrap/...); salinan di ' . TUJUAN . ' tidak diperlukan.' . PHP_EOL;
    } else {
        $hapus(TUJUAN);
        $salin('vendor/twbs/bootstrap/dist', TUJUAN);
        $salin('vendor/twbs/bootstrap-icons/font', TUJUAN . '/font');
        echo 'Bootstrap dan Bootstrap Icons disalin ke ' . TUJUAN . '.' . PHP_EOL;
    }

    if ($kenalInti) {
        $sisa = $hapusSalinan(INTI_ASAL, INTI_TUJUAN);
        echo 'File inti dilayani modul aset (/asset/adems/dapur/...); salinan di ' . INTI_TUJUAN . ' tidak diperlukan.' . PHP_EOL;
        if ($sisa > 0) {
            echo "  {$sisa} file di " . INTI_TUJUAN . ' bukan salinan file inti (atau sudah diubah), jadi dibiarkan.' . PHP_EOL;
        }
    } else {
        $salin(INTI_ASAL, INTI_TUJUAN);
        echo 'File inti ' . INTI_ASAL . ' disalin ke ' . INTI_TUJUAN . '.' . PHP_EOL;
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Gagal menyiapkan Bootstrap: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
