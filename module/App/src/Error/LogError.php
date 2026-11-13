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
 * Error yang lebih tua dari batas hari (bawaan 90; 0 = tidak dihapus menurut umur) tidak lagi dibaca, dan dibuang
 * dari file saat error baru dicatat, paling sering sekali sehari. File yang melewati ukuran maksimal dipindah menjadi
 * <file>.1 (menimpa yang lama), lalu file baru dimulai.
 * Folder log dibuat dengan izin 2770 dan file dengan izin 660, agar anggota grup web server bisa membacanya,
 * tetapi pengguna lain tidak.
 */
final class LogError
{
    /** Atribut request berisi kode kesalahan, dibaca template error::error. */
    public const ATRIBUT_KODE = 'dapur.kode_error';

    public const UKURAN_MAKS_BAWAAN = 5 * 1024 * 1024;

    /** Umur error paling lama, dalam hari; 0 berarti tidak dihapus menurut umur. */
    public const HARI_BAWAAN = 90;

    /** Format waktu di log; bisa dibandingkan sebagai teks. */
    private const FORMAT_WAKTU = 'Y-m-d\TH:i:s\Z';

    private const JEJAK_MAKS = 40;

    public function __construct(
        public readonly string $file,
        private readonly int $ukuranMaks = self::UKURAN_MAKS_BAWAAN,
        private readonly int $hari = self::HARI_BAWAAN,
    ) {
    }

    /** Waktu (UTC, format log) error tertua yang masih disimpan, atau null bila tidak dibatasi umur. */
    public function batasWaktu(?int $sekarang = null): ?string
    {
        return $this->hari > 0 ? gmdate(self::FORMAT_WAKTU, ($sekarang ?? time()) - $this->hari * 86400) : null;
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
                'waktu'      => gmdate(self::FORMAT_WAKTU),
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

            $this->buangKedaluwarsa();

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
        foreach ($this->terbaru() as $catatan) {
            if ($kode !== null && strcasecmp((string) ($catatan['kode'] ?? ''), $kode) !== 0) {
                continue;
            }

            $hasil[] = $catatan;
            if (count($hasil) >= max(1, $jumlah)) {
                break;
            }
        }

        return $hasil;
    }

    /**
     * Jumlah kesalahan yang tercatat sesudah waktu tertentu (UTC, format seperti kunci 'waktu'), paling banyak
     * $maks. Pembacaan berhenti di catatan pertama yang lebih lama, jadi cukup ringan untuk dipanggil di setiap
     * halaman, misalnya untuk angka notifikasi.
     */
    public function jumlahSejak(?string $waktu, int $maks = 99): int
    {
        $jumlah = 0;
        foreach ($this->terbaru() as $catatan) {
            if ($waktu !== null && strcmp((string) ($catatan['waktu'] ?? ''), $waktu) <= 0) {
                break;
            }

            if (++$jumlah >= $maks) {
                break;
            }
        }

        return $jumlah;
    }

    /**
     * Semua catatan yang belum kedaluwarsa, dari yang terbaru: file ini lalu <file>.1, masing-masing dibaca mundur
     * dari akhir file per blok, sehingga catatan terbaru didapat tanpa membaca seluruh file.
     *
     * @return \Generator<int, array<string, mixed>>
     */
    public function terbaru(): \Generator
    {
        $batas = $this->batasWaktu();
        foreach ([$this->file, $this->file . '.1'] as $file) {
            foreach (self::barisMundur($file) as $json) {
                $catatan = json_decode($json, true);
                if (! is_array($catatan)) {
                    continue;
                }

                // Catatan disimpan berurutan menurut waktu: sesudah yang kedaluwarsa, sisanya lebih tua lagi.
                if ($batas !== null && strcmp((string) ($catatan['waktu'] ?? ''), $batas) < 0) {
                    return;
                }

                yield $catatan;
            }
        }
    }

    /**
     * Membuang catatan kedaluwarsa dari file ini dan <file>.1. Agar ringan, file baru ditulis ulang bila catatan
     * tertuanya sudah lewat sehari dari batas, sehingga penulisan ulang terjadi paling sering sekali sehari.
     */
    private function buangKedaluwarsa(): void
    {
        $batas = $this->batasWaktu();
        if ($batas === null) {
            return;
        }

        $pemicu = gmdate(self::FORMAT_WAKTU, time() - ($this->hari + 1) * 86400);
        foreach ([$this->file . '.1', $this->file] as $file) {
            $tertua = self::waktuBarisPertama($file);
            if ($tertua === null || strcmp($tertua, $pemicu) >= 0) {
                continue;
            }

            $h = @fopen($file, 'c+');
            if ($h === false) {
                continue;
            }

            try {
                if (! flock($h, LOCK_EX)) {
                    continue;
                }

                $sisa = '';
                while (($b = fgets($h)) !== false) {
                    $c = json_decode($b, true);
                    if (is_array($c) && strcmp((string) ($c['waktu'] ?? ''), $batas) >= 0) {
                        $sisa .= $b;
                    }
                }

                ftruncate($h, 0);
                rewind($h);
                fwrite($h, $sisa);
                fflush($h);
                flock($h, LOCK_UN);
            } finally {
                fclose($h);
            }

            if ($file !== $this->file && $sisa === '') {
                @unlink($file);
            }
        }
    }

    /** Waktu catatan pertama (tertua) di file, atau null bila file kosong atau tidak bisa dibaca. */
    private static function waktuBarisPertama(string $file): ?string
    {
        if (! is_file($file) || ! is_readable($file)) {
            return null;
        }

        $h = @fopen($file, 'rb');
        if ($h === false) {
            return null;
        }

        $baris = fgets($h);
        fclose($h);
        $c = is_string($baris) ? json_decode($baris, true) : null;

        return is_array($c) && is_string($c['waktu'] ?? null) ? $c['waktu'] : null;
    }

    /** @return \Generator<int, string> baris tidak kosong, dari akhir file ke awal */
    private static function barisMundur(string $file): \Generator
    {
        if (! is_file($file) || ! is_readable($file)) {
            return;
        }

        $h = @fopen($file, 'rb');
        if ($h === false) {
            return;
        }

        try {
            fseek($h, 0, SEEK_END);
            $posisi = ftell($h);
            $sisa   = '';
            while ($posisi > 0) {
                $baca    = min(8192, $posisi);
                $posisi -= $baca;
                fseek($h, $posisi);
                $sisa  = fread($h, $baca) . $sisa;
                $baris = explode("\n", $sisa);

                // Potongan pertama mungkin baris yang belum lengkap; simpan untuk blok berikutnya.
                $sisa = $posisi > 0 ? array_shift($baris) : '';
                foreach (array_reverse($baris) as $b) {
                    if (trim($b) !== '') {
                        yield $b;
                    }
                }
            }

            if (trim($sisa) !== '') {
                yield $sisa;
            }
        } finally {
            fclose($h);
        }
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
