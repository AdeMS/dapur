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

## Persyaratan

- PHP 8.2, 8.3, 8.4, atau 8.5
- [Composer](https://getcomposer.org/download/)
- Web server dengan document root diarahkan ke folder `public/`
  (Apache dengan `mod_rewrite`, atau PHP built-in server untuk pengembangan)

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
├── config/
│   ├── config.php          ← daftar ConfigProvider yang digabungkan
│   ├── container.php       ← membuat service container
│   ├── pipeline.php        ← urutan middleware
│   └── routes.php          ← daftar route aplikasi
├── module/
│   └── App/                ← modul bawaan Dapur AdeMS
│       ├── src/
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

Begitu route `home` ditambahkan, halaman bawaan otomatis digantikan oleh halaman Anda.
Contoh kode lengkapnya bisa dilihat langsung di halaman bawaan setelah instalasi.

## Lisensi

Dapur AdeMS dirilis di bawah lisensi [BSD 3-Clause](LICENSE.md).
Lihat juga [COPYRIGHT.md](COPYRIGHT.md).
