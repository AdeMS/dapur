# Dapur AdeMS

Tempat Ade M Saragih untuk bermain dan belajar membuat website.

Dapur AdeMS adalah kerangka awal (skeleton) website berbasis [Mezzio](https://docs.mezzio.dev/)
dan [Bootstrap](https://getbootstrap.com/). Setelah dipasang, Anda langsung mendapatkan layout
yang siap pakai, halaman bawaan berisi panduan, dan halaman error. Selanjutnya Anda tinggal
menambahkan halaman Anda sendiri.

## Fitur

- **Mezzio** sebagai framework PSR-15 middleware, dengan router FastRoute.
- **laminas-view** sebagai template engine, dengan layout `layout::default`.
- **Bootstrap 5** dan **Bootstrap Icons** untuk tampilan.
- **Halaman bawaan** yang tampil selama halaman home belum dibuat, lengkap dengan
  langkah-langkah membuat modul halaman home.
- **Halaman error** untuk 404 (halaman tidak ditemukan) dan kesalahan lainnya.
- **Cache** dengan laminas-cache: cache konfigurasi dan cache data aplikasi (PSR-6 dan PSR-16).

## Persyaratan

- PHP 8.2, 8.3, 8.4, atau 8.5
- [Composer](https://getcomposer.org/download/)
- Web server dengan document root diarahkan ke folder `public/`
  (Apache dengan `mod_rewrite`, atau PHP built-in server untuk pengembangan)
- Folder `data/cache/` harus bisa ditulis oleh web server

## Instalasi

1. Clone repository ini:

   ```bash
   git clone https://github.com/AdeMS/dapur.git
   cd dapur
   ```

2. Pasang dependensi:

   ```bash
   composer install
   ```

3. Salin aset Bootstrap dan Bootstrap Icons ke folder `public/asset/bootstrap`:

   ```bash
   composer setup-bootstrap
   composer setup-bootstrap-icons
   ```

   > **Catatan untuk Windows:** kedua script di atas memakai perintah `rm` dan `cp`, yang tidak
   > tersedia di Command Prompt. Salin foldernya secara manual:
   > - `vendor/twbs/bootstrap/dist` → `public/asset/bootstrap`
   > - `vendor/twbs/bootstrap-icons/font` → `public/asset/bootstrap/font`

4. Jalankan server pengembangan:

   ```bash
   composer serve
   ```

   Lalu buka <http://localhost:8080/>. Jika berhasil, Anda akan melihat halaman
   **Selamat Datang di Dapur AdeMS**.

## Struktur Folder

```
dapur/
├── bin/
│   └── clear-config-cache.php ← menghapus cache konfigurasi
├── config/
│   ├── autoload/
│   │   └── cache.global.php   ← pengaturan cache
│   ├── config.php          ← daftar ConfigProvider yang digabungkan
│   ├── container.php       ← membuat service container
│   ├── pipeline.php        ← urutan middleware
│   └── routes.php          ← daftar route aplikasi
├── data/
│   └── cache/              ← file cache (isinya tidak masuk Git)
│       ├── config-cache.php   ← cache konfigurasi
│       └── app/            ← cache data aplikasi
├── module/
│   └── App/                ← modul bawaan Dapur AdeMS
│       ├── src/
│       │   ├── Cache/      ← factory cache PSR-6 dan PSR-16
│       │   ├── ConfigProvider.php
│       │   └── Middleware/ ← DefaultHomeMiddleware (halaman bawaan)
│       └── templates/
│           ├── dapur/      ← template halaman bawaan
│           ├── error/      ← template halaman error (404 dan lainnya)
│           └── layout/     ← layout::default
└── public/                 ← document root web server
    ├── index.php
    └── asset/
```

## Membuat Halaman Home

Selama belum ada route bernama `home`, alamat `/` akan menampilkan halaman bawaan Dapur AdeMS.
Halaman tersebut berisi panduan langkah demi langkah untuk membuat modul halaman home Anda
sendiri. Ringkasnya:

1. Buat modul baru di `module/`, misalnya `module/Home/`, berisi handler, factory,
   template, dan `ConfigProvider`.
2. Daftarkan namespace modul di bagian `autoload` pada `composer.json`, lalu jalankan
   `composer dump-autoload`.
3. Tambahkan `Home\ConfigProvider::class` ke `config/config.php`.
4. Tambahkan route `home` di `config/routes.php`:

   ```php
   $app->get('/', Home\Handler\HomePageHandler::class, 'home');
   ```

5. Hapus cache konfigurasi agar perubahan di langkah 3 berlaku:

   ```bash
   composer clear-config-cache
   ```

Begitu route `home` ditambahkan, halaman bawaan otomatis digantikan oleh halaman Anda.
Contoh kode lengkapnya bisa dilihat langsung di halaman bawaan setelah instalasi.

## Cache

Dapur AdeMS memakai [laminas-cache](https://docs.laminas.dev/laminas-cache/) dengan adapter
Filesystem. Pengaturannya ada di `config/autoload/cache.global.php`.

### Cache konfigurasi

Semua konfigurasi (`config/config.php`, ConfigProvider setiap modul, dan `config/autoload/`)
digabungkan sekali, lalu disimpan ke `data/cache/config-cache.php`. Request berikutnya langsung
memakai file tersebut sehingga lebih cepat.

> **Penting:** selama file cache ada, perubahan konfigurasi tidak akan terlihat. Setiap kali
> Anda mengubah konfigurasi, misalnya menambahkan modul di `config/config.php` atau mengubah
> ConfigProvider, hapus cache konfigurasi:
>
> ```bash
> composer clear-config-cache
> ```
>
> Atau hapus file `data/cache/config-cache.php` secara manual.

Perubahan pada `config/routes.php`, `config/pipeline.php`, dan template tidak di-cache,
sehingga langsung berlaku.

Untuk mematikan cache konfigurasi selama pengembangan, buat file `config/autoload/local.php`:

```php
<?php

return [
    Laminas\ConfigAggregator\ConfigAggregator::ENABLE_CACHE => false,
];
```

Lalu jalankan `composer clear-config-cache` sekali.

### Cache data aplikasi

Untuk menyimpan data, misalnya hasil query atau respons API, ambil cache dari container
dengan salah satu nama service berikut:

| Nama service | Jenis |
|---|---|
| `Psr\SimpleCache\CacheInterface` | PSR-16 (paling sederhana) |
| `Psr\Cache\CacheItemPoolInterface` | PSR-6 |
| `Laminas\Cache\Storage\StorageInterface` | Storage laminas-cache |

Contoh di dalam handler, dengan cache disuntikkan lewat factory:

```php
use Psr\SimpleCache\CacheInterface;

public function __construct(
    private readonly CacheInterface $cache
) {
}

public function handle(ServerRequestInterface $request): ResponseInterface
{
    $data = $this->cache->get('data-saya');

    if ($data === null) {
        $data = $this->ambilDataYangLambat();
        $this->cache->set('data-saya', $data, 600); // simpan selama 10 menit
    }

    // ...
}
```

Data cache disimpan di `data/cache/app/` dengan masa berlaku bawaan 1 jam.

## Lisensi

Dapur AdeMS dirilis di bawah lisensi [BSD 3-Clause](LICENSE.md).
Lihat juga [COPYRIGHT.md](COPYRIGHT.md).
