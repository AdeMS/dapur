<?php

declare(strict_types=1);

namespace App\Error;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * File log error: satu kesalahan per baris, dalam format JSON (JSON Lines), agar mudah dibaca program
 * (misalnya perintah `php dapur log-error`) maupun manusia.
 *
 * Yang dicatat: kode kesalahan, waktu (UTC), host, metode, alamat (tanpa query string), status, kelas, pesan,
 * file, baris, jejak pemanggilan tanpa nilai argumen, dan kesalahan sebelumnya. Isi formulir, cookie, dan header
 * TIDAK dicatat, agar kata sandi dan session tidak ikut tersimpan.
 *
 * File yang melewati ukuran maksimal dipindah menjadi <file>.1 (menimpa yang lama), lalu file baru dimulai.
 * Folder log dibuat dengan izin 2770 dan file dengan izin 660, agar anggota grup web server bisa membacanya,
 * tetapi pengguna lain tidak.
 */
final class LogError
{
    /** Atribut request berisi kode kesalahan, dibaca template error::error. */
    public const ATRIBUT_KODE = 'dapur.kode_error';

    public const UKURAN_MAKS_BAWAAN = 5 * 1024 * 1024;

    private const JEJAK_MAKS = 40;

    public function __construct(
        public readonly string $file,
        private readonly int $ukuranMaks = self::UKURAN_MAKS_BAWAAN,
    ) {
    }

    /** Kode acak pendek untuk ditampilkan di halaman error dan dicari di log, misalnya "7F3A09C2". */
    public static function buatKode(): string
    {
        return strtoupper(bin2hex(random_bytes(4)));
    }

    /**
     * Mencatat satu kesalahan. Tidak pernah melempar exception: kegagalan menulis log tidak boleh membuat
     * halaman error ikut gagal.
     *
     * @return bool apakah catatan berhasil ditulis
     */
    public function catat(Throwable $e, ?ServerRequestInterface $request, int $status, string $kode): bool
    {
        try {
            $uri   = $request?->getUri();
            $baris = json_encode([
                'kode'       => $kode,
                'waktu'      => gmdate('Y-m-d\TH:i:s\Z'),
                'host'       => $uri?->getHost() ?? '',
                'metode'     => $request?->getMethod() ?? '',
                'alamat'     => $uri?->getPath() ?? '',
                'status'     => $status,
                'kelas'      => $e::class,
                'pesan'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'baris'      => $e->getLine(),
                'jejak'      => self::jejak($e),
                'sebelumnya' => self::sebelumnya($e),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

            if (! is_string($baris) || ! $this->siapkanFile()) {
                return false;
            }

            $baru = ! is_file($this->file);
            if (@file_put_contents($this->file, $baris . "\n", FILE_APPEND | LOCK_EX) === false) {
                return false;
            }

            if ($baru) {
                @chmod($this->file, 0660);
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Catatan terbaru lebih dulu. Bila kode diberikan, hanya catatan dengan kode itu (juga dicari di <file>.1).
     *
     * @return list<array<string, mixed>>
     */
    public function baca(int $jumlah = 10, ?string $kode = null): array
    {
        $hasil = [];
        foreach ([$this->file, $this->file . '.1'] as $file) {
            if (! is_file($file) || ! is_readable($file)) {
                continue;
            }

            $baris = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach (array_reverse($baris) as $json) {
                $catatan = json_decode($json, true);
                if (! is_array($catatan)) {
                    continue;
                }

                if ($kode !== null && strcasecmp((string) ($catatan['kode'] ?? ''), $kode) !== 0) {
                    continue;
                }

                $hasil[] = $catatan;
                if (count($hasil) >= max(1, $jumlah)) {
                    return $hasil;
                }
            }
        }

        return $hasil;
    }

    /** Membuat folder log bila belum ada dan memindahkan file yang sudah terlalu besar. */
    private function siapkanFile(): bool
    {
        $folder   = dirname($this->file);
        $belumAda = [];
        while (! is_dir($folder) && $folder !== dirname($folder)) {
            $belumAda[] = $folder;
            $folder     = dirname($folder);
        }

        foreach (array_reverse($belumAda) as $baru) {
            if (! @mkdir($baru) && ! is_dir($baru)) {
                return false;
            }

            // Izin diatur setelah mkdir karena hasil mkdir terpotong oleh umask web server.
            @chmod($baru, 02770);
        }

        clearstatcache(true, $this->file);
        if (is_file($this->file) && (int) @filesize($this->file) > $this->ukuranMaks) {
            @rename($this->file, $this->file . '.1');
        }

        return true;
    }

    /** @return list<string> jejak pemanggilan tanpa nilai argumen, misalnya "file.php:12 Kelas->metode()" */
    private static function jejak(Throwable $e): array
    {
        $jejak = [];
        foreach (array_slice($e->getTrace(), 0, self::JEJAK_MAKS) as $t) {
            $lokasi  = isset($t['file']) ? $t['file'] . ':' . ($t['line'] ?? 0) : '[internal]';
            $fungsi  = ($t['class'] ?? '') . ($t['type'] ?? '') . ($t['function'] ?? '') . '()';
            $jejak[] = $lokasi . ' ' . $fungsi;
        }

        return $jejak;
    }

    /** @return list<array{kelas: string, pesan: string, file: string, baris: int}> */
    private static function sebelumnya(Throwable $e): array
    {
        $daftar = [];
        for ($p = $e->getPrevious(); $p !== null && count($daftar) < 10; $p = $p->getPrevious()) {
            $daftar[] = ['kelas' => $p::class, 'pesan' => $p->getMessage(), 'file' => $p->getFile(), 'baris' => $p->getLine()];
        }

        return $daftar;
    }
}
