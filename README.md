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
- **Tema terang, gelap, dan otomatis** yang bisa dipilih pengunjung dari navbar.
- **Halaman bawaan** yang tampil selama halaman home belum dibuat, lengkap dengan
  langkah-langkah membuat modul halaman home.
- **Halaman error** untuk 404 (halaman tidak ditemukan) dan kesalahan lainnya.
- **Cache** dengan laminas-cache: cache konfigurasi dan cache data aplikasi (PSR-6 dan PSR-16).
- **Koneksi database** dengan laminas-db: MySQL/MariaDB, PostgreSQL, atau SQLite.

## Persyaratan

- PHP 8.2, 8.3, 8.4, atau 8.5
- [Composer](https://getcomposer.org/download/)
- Web server dengan document root diarahkan ke folder `public/`
  (Apache dengan `mod_rewrite`, atau PHP built-in server untuk pengembangan;
  lihat [Memakai Apache dan HTTPS](#memakai-apache-dan-https))
- Folder `data/cache/` harus bisa ditulis oleh web server
  (lihat [Izin folder cache](#izin-folder-cache))
- Jika memakai database: ekstensi PHP `pdo_mysql`, `pdo_pgsql`, atau `pdo_sqlite`
  (lihat [Database](#database))

## Instalasi

1. Unduh Dapur AdeMS dengan salah satu cara berikut.

   **Dengan Composer (disarankan):**

   ```bash
   composer create-project adems/dapur dapur
   cd dapur
   ```

   **Dengan Git:**

   ```bash
   git clone https://github.com/AdeMS/dapur.git
   cd dapur
   composer install
   ```

   Kedua cara di atas otomatis menyalin aset Bootstrap dan Bootstrap Icons ke folder
   `public/asset/bootstrap`. Jika perlu menyalin ulang, jalankan:

   ```bash
   composer setup-bootstrap
   composer setup-bootstrap-icons
   ```

   > **Catatan untuk Windows:** penyalinan aset memakai perintah `rm` dan `cp`, yang tidak
   > tersedia di Command Prompt, sehingga langkah ini akan gagal. Salin foldernya secara manual:
   > - `vendor/twbs/bootstrap/dist` → `public/asset/bootstrap`
   > - `vendor/twbs/bootstrap-icons/font` → `public/asset/bootstrap/font`

2. Jalankan server pengembangan:

   ```bash
   composer serve
   ```

   Lalu buka <http://localhost:8080/>. Jika berhasil, Anda akan melihat halaman
   **Selamat Datang di Dapur AdeMS**.

### Memakai Apache dan HTTPS

File `public/.htaccess` mengalihkan semua permintaan `http://` ke `https://`. Di server yang
sudah memiliki sertifikat SSL, ini membuat website Anda selalu diakses dengan aman.

Jika Anda memakai Apache **tanpa SSL**, misalnya di komputer sendiri (XAMPP, Laragon, dan
sejenisnya), website akan dialihkan ke `https://` lalu gagal dibuka. Untuk itu, nonaktifkan
pengalihan dengan memberi tanda `#` pada dua baris berikut di `public/.htaccess`:

```apache
# Mengalihkan permintaan http ke https
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

Pengalihan ini memakai kode `301` (permanen), sehingga browser bisa mengingatnya. Jika website
masih dialihkan setelah baris tersebut dinonaktifkan, hapus cache browser Anda.

Server pengembangan `composer serve` tidak membaca `.htaccess`, sehingga tidak terpengaruh.

## Struktur Folder

```
dapur/
├── bin/
│   ├── cek-database.php             ← menguji koneksi database
│   └── clear-config-cache.php       ← menghapus cache konfigurasi
├── config/
│   ├── autoload/
│   │   ├── cache.global.php         ← pengaturan cache
│   │   └── database.local.php.dist  ← contoh pengaturan database
│   ├── config.php                   ← daftar ConfigProvider yang digabungkan
│   ├── container.php                ← membuat service container
│   ├── modul.local.php              ← daftar modul tambahan (opsional, tidak masuk Git)
│   ├── multiwebsite.local.php       ← daftar website (hanya bila multi-website dipakai, tidak masuk Git)
│   ├── pipeline.php                 ← urutan middleware
│   └── routes.php                   ← daftar route aplikasi
├── dapur                            ← pintu masuk perintah: php dapur
├── data/
│   ├── cache/                       ← file cache (isinya tidak masuk Git)
│   │   ├── app/                     ← cache data aplikasi
│   │   └── config-cache.php         ← cache konfigurasi
│   └── database/                    ← file database SQLite (isinya tidak masuk Git)
├── module/
│   └── App/                         ← modul bawaan Dapur AdeMS
│       ├── src/
│       │   ├── Cache/               ← factory cache PSR-6 dan PSR-16
│       │   ├── Db/                  ← factory koneksi database
│       │   ├── Middleware/          ← DefaultHomeMiddleware (halaman bawaan)
│       │   └── ConfigProvider.php
│       └── templates/
│           ├── dapur/               ← template halaman bawaan
│           ├── error/               ← template halaman error (404 dan lainnya)
│           └── layout/              ← layout::default
└── public/                          ← document root web server
    ├── .htaccess                    ← aturan Apache (rewrite dan pengalihan ke HTTPS)
    ├── asset/
    │   ├── bootstrap/               ← aset Bootstrap (disalin otomatis, tidak masuk Git)
    │   └── dapur/
    │       ├── css/style.css        ← tema warna dan gaya Dapur AdeMS
    │       ├── img/                 ← logo dan gambar latar
    │       └── js/tema.js           ← pemilih tema terang, gelap, dan otomatis
    └── index.php                    ← pintu masuk aplikasi
```

## Cara Konfigurasi Disusun

Bagian ini menjelaskan konsep konfigurasi Mezzio dan Laminas yang dipakai Dapur AdeMS. Memahaminya
akan memudahkan Anda menambah modul, mengubah pengaturan, dan membaca dokumentasi Laminas.

### ConfigProvider dan penggabungan konfigurasi

Konfigurasi aplikasi Mezzio adalah **satu array PHP besar**. Array ini tidak ditulis di satu file,
melainkan disusun dari banyak potongan oleh
[laminas-config-aggregator](https://docs.laminas.dev/laminas-config-aggregator/) di
`config/config.php`. Setiap potongan berasal dari:

- **ConfigProvider**, yaitu kelas kecil milik sebuah modul atau paket. Saat dipanggil, kelas ini
  mengembalikan array konfigurasi modul tersebut: daftar layanan (`dependencies`), lokasi template,
  dan pengaturan bawaannya. Contohnya `module/App/src/ConfigProvider.php`.
- **File PHP** di `config/autoload/` yang masing-masing mengembalikan array.

Potongan-potongan itu digabung berurutan. Bila dua potongan memakai kunci yang sama, **potongan yang
belakangan menang**. Array di dalamnya digabung per kunci, jadi Anda cukup menulis kunci yang ingin
diubah, tidak perlu menyalin seluruh pengaturan.

Urutan di Dapur AdeMS:

1. ConfigProvider paket Laminas dan Mezzio (view, router, cache, dan lainnya)
2. `App\ConfigProvider`, modul bawaan Dapur AdeMS
3. ConfigProvider modul tambahan dari `config/modul.local.php`
4. `config/autoload/*.global.php`, lalu `config/autoload/*.local.php`
5. Lapisan website dari paket multi-website, bila dipakai

Karena `config/autoload/` dibaca setelah modul, di sanalah tempat yang tepat untuk mengubah pengaturan
bawaan modul tanpa menyentuh kode modul itu.

Hasil penggabungan disimpan di cache (lihat [Cache konfigurasi](#cache-konfigurasi)). Itulah sebabnya
setiap perubahan konfigurasi baru berlaku setelah `composer clear-config-cache`.

### Arti `.global.php`, `.local.php`, dan `.dist`

Penamaan file di `config/autoload/` mengikuti kebiasaan Laminas:

| Akhiran | Isi | Masuk Git? |
|---|---|---|
| `*.global.php` | Pengaturan yang sama di semua instalasi, misalnya `cache.global.php` | Ya |
| `*.local.php` | Pengaturan khusus satu instalasi: password database, alamat server, atau pengaturan pengembangan | Tidak |
| `*.local.php.dist` | Contoh isi file `.local.php`. Salin, hapus akhiran `.dist`, lalu isi | Ya |

File `.local.php` dibaca setelah `.global.php`, sehingga pengaturan lokal selalu menang. Dengan cara
ini rahasia seperti password tidak pernah ikut ter-commit, dan satu repo bisa dipasang di laptop,
server uji, dan server live dengan pengaturan masing-masing.

### File di `config/` yang bukan bagian dari array konfigurasi

Beberapa file sengaja berada langsung di `config/`, **bukan** di `config/autoload/`. Isinya tidak ikut
digabung ke array konfigurasi. File-file itu mengatur cara aplikasi dirakit:

| File | Kegunaan | Kapan dibaca |
|---|---|---|
| `config.php` | Menentukan potongan konfigurasi mana yang digabung dan urutannya | Saat konfigurasi disusun |
| `container.php` | Membuat service container dari konfigurasi | Setelah konfigurasi jadi |
| `pipeline.php` | Urutan middleware yang dilalui setiap request | Saat aplikasi dijalankan |
| `routes.php` | Daftar alamat (route) dan handler-nya | Saat aplikasi dijalankan |
| `modul.local.php` | Daftar ConfigProvider modul tambahan | **Sebelum** penggabungan |
| `multiwebsite.local.php` | Daftar website (paket multi-website) | **Sebelum** penggabungan, bahkan sebelum `config.php` |

`modul.local.php` dan `multiwebsite.local.php` tidak boleh dipindah ke `config/autoload/`, karena
isinya diperlukan **sebelum** penggabungan dimulai:

- `modul.local.php` menentukan modul mana yang ikut digabung. File di `config/autoload/` baru dibaca
  di tengah penggabungan, jadi sudah terlambat untuk menambah modul. Isinya, yaitu daftar nama kelas,
  juga akan ikut tercampur ke array konfigurasi.
- `multiwebsite.local.php` dipakai untuk mengenali website dari nama host. Dari situ ditentukan lokasi
  cache konfigurasi website dan konfigurasi website mana yang dimuat. Bila file ini ada di dalam
  konfigurasi, daftar website baru bisa dibaca setelah cache konfigurasi dibaca, padahal cache mana
  yang harus dibaca bergantung pada daftar itu.

Keduanya tetap memakai akhiran `.local.php` karena artinya sama: khusus satu instalasi dan tidak masuk
Git. Pola ini juga ada di kerangka aplikasi Laminas, misalnya `config/modules.config.php` dan
`config/development.config.php` yang berada di `config/`, bukan di `config/autoload/`.

Dokumentasi lebih lanjut:
[Mezzio: konfigurasi](https://docs.mezzio.dev/mezzio/v3/features/container/config/) dan
[laminas-config-aggregator](https://docs.laminas.dev/laminas-config-aggregator/).

## Perintah Dapur

```bash
php dapur                     # daftar perintah
php dapur clear-config-cache
php dapur cek-database
```

Bila paket [adems/dapur-multiwebsite](https://github.com/AdeMS/dapur-multiwebsite) terpasang, perintah
websitenya tersedia di sini juga, misalnya `php dapur buat-website contoh.com`.

Perintah yang sama bisa dijalankan lewat Composer dengan `composer dapur ...`. Composer membuang opsi yang
ditulis tanpa `--` tanpa memberi pesan, jadi perintah yang memakai opsi harus ditulis seperti ini:

```bash
composer dapur -- buat-website contoh.com --alias=www.contoh.com
```

## Perintah Composer

| Perintah | Kegunaan |
|---|---|
| `composer serve` | Menjalankan server pengembangan di <http://localhost:8080/> |
| `composer clear-config-cache` | Menghapus cache konfigurasi; jalankan setiap kali konfigurasi diubah (otomatis saat `composer install` dan `composer update`) |
| `composer cek-database` | Menguji koneksi database |
| `composer setup-bootstrap` | Menyalin ulang aset Bootstrap ke `public/asset/bootstrap` |
| `composer setup-bootstrap-icons` | Menyalin ulang Bootstrap Icons ke `public/asset/bootstrap/font` |

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

## Tampilan

### Tema terang dan gelap

Ikon di ujung kanan navbar membuka pilihan tema:

| Pilihan | Keterangan |
|---|---|
| **Terang** | Selalu memakai tema terang |
| **Gelap** | Selalu memakai tema gelap; gambar latar ikut digelapkan |
| **Otomatis** | Mengikuti pengaturan terang/gelap di perangkat pengunjung (bawaan) |

Pilihan pengunjung disimpan di browser (`localStorage`), sehingga tetap berlaku saat website
dibuka kembali. Tema diterapkan oleh `public/asset/dapur/js/tema.js` sebelum halaman tampil,
jadi tidak ada kedipan warna saat halaman dimuat.

Template Anda sebaiknya memakai kelas warna Bootstrap yang mengikuti tema, misalnya
`text-body`, `text-body-secondary`, `bg-body`, atau `bg-body-tertiary`, bukan warna tetap
seperti `text-dark` atau `bg-white`. Dengan begitu halaman Anda tetap terbaca di kedua tema.

### Menambahkan menu navigasi

Tambahkan item menu di `module/App/templates/layout/default.phtml`, di dalam `<ul>` pada
bagian bertanda `Menu navigasi website`:

```html
<ul class="navbar-nav mb-2 mb-lg-0">
    <li class="nav-item"><a class="nav-link" href="/">Beranda</a></li>
    <li class="nav-item"><a class="nav-link" href="/tentang">Tentang</a></li>
</ul>
```

Di layar lebar (992px ke atas), menu sejajar dengan teks di dalam panel konten. Di layar yang
lebih kecil, navbar tersusun dengan tombol menu (hamburger) di kiri, logo di tengah, serta
pemilih tema dan menu kanan di kanan; menu navigasi masuk ke tombol menu. Tombol ini otomatis
disembunyikan selama menu belum berisi item, dan muncul kembali begitu Anda menambahkan item
pertama.

Modul juga bisa menambahkan menu tanpa mengubah layout, lewat empat placeholder yang dirender
layout:

| Placeholder | Letak |
|---|---|
| `menu` | Setelah item menu navigasi; di layar kecil ikut masuk ke tombol menu |
| `menu-sebelum-tema` | Di sebelah kiri pemilih tema, selalu terlihat (juga di HP), misalnya untuk tombol layar penuh |
| `menu-kanan` | Di sebelah kanan pemilih tema, selalu terlihat (juga di HP), misalnya untuk menu user |
| `menu-samping` | Menu samping di kiri halaman, misalnya untuk dashboard; lihat di bawah |

Isi keempatnya dengan item `<li>` dari template modul:

```php
<?php $this->placeholder('menu')->captureStart() ?>
    <li class="nav-item"><a class="nav-link" href="/laporan">Laporan</a></li>
<?php $this->placeholder('menu')->captureEnd() ?>
```

Bila Anda memakai layout sendiri, salin bagian yang menampilkan ketiga placeholder dari layout
bawaan agar menu modul (misalnya [adems/dapur-dashboard](https://github.com/AdeMS/dapur-dashboard))
tampil.

### Menu samping

Bila placeholder `menu-samping` berisi item, layout menampilkan menu samping di kiri halaman:

- Di layar lebar, menu samping berupa panel di samping isi halaman yang tetap terlihat saat
  halaman digulir.
- Di layar kecil, menu samping disembunyikan dan dibuka dengan tombol menu (hamburger) di kiri
  navbar sebagai panel geser ([offcanvas Bootstrap](https://getbootstrap.com/docs/5.3/components/offcanvas/)).
  Item dari placeholder `menu`, bila ada, ikut tampil di panel ini.

```php
<?php $this->placeholder('menu-samping')->captureStart() ?>
    <li class="dapur-samping-judul">Laporan</li>
    <li class="nav-item"><a class="nav-link active" href="/laporan"><i class="bi bi-graph-up"></i>Ringkasan</a></li>
    <li class="nav-item"><a class="nav-link" href="/laporan/bulanan"><i class="bi bi-calendar3"></i>Bulanan</a></li>
<?php $this->placeholder('menu-samping')->captureEnd() ?>
```

Kelas `active` menandai halaman yang sedang dibuka, dan `dapur-samping-judul` membuat judul kecil
di atas item. Halaman tanpa item menu samping tampil seperti biasa tanpa panel di kiri.

### Mengubah warna tema

Warna tema Dapur diatur dengan variabel CSS di `public/asset/dapur/css/style.css`, pada
bagian `[data-tema=dapur]` (mode terang) dan `[data-tema=dapur][data-bs-theme=dark]` (mode
gelap):

| Variabel | Kegunaan |
|---|---|
| `--bs-link-color`, `--bs-link-hover-color` | Warna tautan |
| `--dapur-bingkai-bg` | Warna latar navbar dan footer |
| `--dapur-panel-rgb` | Warna panel konten (`.jumbotron`), dalam format `R, G, B` |
| `--dapur-panel-padding` | Jarak tepi panel ke teks di dalamnya |

Atribut `data-tema="dapur"` pada `<html>` di layout menentukan tema warna yang dipakai. Untuk
membuat tema warna sendiri, salin semua aturan di `style.css` yang diawali `[data-tema=dapur]`
(termasuk warna accordion, menu dropdown, dan badge) dengan nama lain, misalnya
`[data-tema=laut]`. Ubah warnanya, lalu ganti atribut di layout menjadi `data-tema="laut"`.

### Judul dan tema per website

Layout dan halaman bawaan membaca judul serta tema lewat view helper `infoWebsite()`:

```php
$this->infoWebsite()->judul(); // judul website, bawaan "Dapur AdeMS"
$this->infoWebsite()->host();  // nama domain website, null bila tidak ada
$this->infoWebsite()->tema();  // nilai data-tema, bawaan "dapur"
```

Nilainya berasal dari `$config['website']`, yang diisi oleh paket
[adems/dapur-multiwebsite](https://github.com/AdeMS/dapur-multiwebsite) dari `website.php`. Tanpa
pengaturan tersebut, nilai bawaan yang dipakai. Nama tema harus sudah memiliki aturan warna di CSS
(lihat [Mengubah warna tema](#mengubah-warna-tema)).

## Modul Tambahan

### Memasang paket tambahan

Paket Composer tambahan untuk instalasi Anda (misalnya `adems/dapur-*`) ditulis di file
`composer.local.json`, bukan di `composer.json`. File ini digabungkan otomatis oleh
[composer-merge-plugin](https://github.com/wikimedia/composer-merge-plugin) dan tidak masuk Git,
sehingga `composer.json` tetap sama dengan rilis Dapur dan pembaruan lewat Git tidak bentrok.

```json
{
    "require": {
        "adems/dapur-multiwebsite": "^1.0"
    }
}
```

Lalu jalankan:

```bash
composer update
```

> Jangan memakai `composer require` untuk paket tambahan, karena perintah itu menulis ke
> `composer.json`.

### Mengaktifkan modul

Modul tambahan diaktifkan dengan mencantumkan ConfigProvider-nya di file
`config/modul.local.php` (kecuali modul yang bekerja sendiri, seperti
`adems/dapur-multiwebsite`). Buat file ini bila belum ada:

```php
<?php

// Modul tambahan yang aktif di inti Dapur AdeMS, satu ConfigProvider per baris.
return [
    NamaModul\ConfigProvider::class,
];
```

Modul dari file ini dimuat setelah modul bawaan `App` dan sebelum `config/autoload/`, sehingga
pengaturan modul tetap bisa ditimpa lewat `config/autoload/*.php`. File ini tidak masuk Git,
jadi daftar modul bisa berbeda di setiap instalasi tanpa mengubah `config/config.php`.

Setelah mengubah daftar modul, jalankan `composer clear-config-cache`.

## Cache

Dapur AdeMS memakai [laminas-cache](https://docs.laminas.dev/laminas-cache/) dengan adapter
Filesystem. Pengaturannya ada di `config/autoload/cache.global.php`.

### Cache konfigurasi

Semua konfigurasi (`config/config.php`, ConfigProvider setiap modul, dan `config/autoload/`)
digabungkan sekali, lalu disimpan ke `data/cache/config-cache.php`. Request berikutnya langsung
memakai file tersebut sehingga lebih cepat.

> **Penting:** selama file cache ada, perubahan konfigurasi tidak akan terlihat. Setiap kali
> Anda mengubah konfigurasi, misalnya menambahkan modul di `config/modul.local.php` atau mengubah
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

### Izin folder cache

Web server (misalnya Apache) harus bisa menulis ke `data/cache/` dan `data/cache/app/`.
Jika tidak bisa, website tetap berjalan, tetapi **cache konfigurasi tidak akan dibuat tanpa
pesan error apa pun**, dan cache data aplikasi akan gagal saat dipakai.

Cari tahu dulu user yang menjalankan web server Anda, misalnya `www-data` (Debian/Ubuntu)
atau `apache` (RHEL/CentOS), lalu pilih salah satu cara berikut.

**Cara 1 — Grup (disarankan untuk server produksi)**

Jadikan web server pemilik folder, dan beri grup Anda hak tulis:

```bash
chown -R <user-web-server>:<grup-anda> data/cache
chmod -R 2770 data/cache
```

Arti angka `2770`:

- `2` (setgid): file baru di dalam folder otomatis ikut grup tersebut, sehingga Anda tetap
  bisa menghapus cache, misalnya dengan `composer clear-config-cache`.
- `77`: pemilik (web server) dan grup boleh membaca dan menulis.
- `0`: user lain di server tidak boleh masuk ke folder sama sekali.

Angka `0` terakhir penting jika Anda memakai [database](#database): cache konfigurasi
`data/cache/config-cache.php` ikut menyimpan password database, dan file tersebut dibuat dengan
izin `644` (bisa dibaca semua user). Dengan folder `2770`, user lain tetap tidak bisa membukanya.
Jika website Anda tidak memakai database, `2775` juga boleh dipakai.

Perintah `chown` biasanya perlu dijalankan sebagai root. Hal yang sama berlaku untuk folder
`data/database/` jika Anda memakai SQLite.

**Cara 2 — 777 (praktis untuk pengembangan atau server pribadi)**

```bash
chmod 777 data/cache data/cache/app
```

> **Perhatian:** dengan izin `777`, semua user di server bisa membaca dan menulis ke folder
> tersebut, termasuk membaca password database di cache konfigurasi. Di server bersama
> (shared hosting), user lain juga bisa menaruh file PHP palsu sebagai cache konfigurasi yang
> kemudian dijalankan oleh website Anda. Jangan gunakan cara ini di server bersama.

Pada cara 2, file dan subfolder cache data aplikasi tetap dibuat oleh web server dengan izin
bawaan `0700`/`0600`, sehingga Anda tidak bisa menghapusnya dari akun Anda sendiri. Jika Anda
perlu menghapusnya lewat terminal, tambahkan opsi berikut pada `options` di
`config/autoload/cache.global.php`:

```php
'dir_permission'  => 0777,
'file_permission' => 0666,
```

Di Windows, pengaturan ini umumnya tidak diperlukan.

## Database

Dapur AdeMS memakai [laminas-db](https://docs.laminas.dev/laminas-db/) untuk koneksi ke
MySQL/MariaDB, PostgreSQL, atau SQLite. Secara bawaan koneksi database belum diatur, dan website
tetap berjalan tanpa database. Koneksi baru dibuka saat ada handler yang memakainya.

Yang sudah disediakan:

| Bagian | Keterangan |
|---|---|
| `config/autoload/database.local.php.dist` | Contoh pengaturan untuk MySQL/MariaDB, PostgreSQL, dan SQLite |
| `Laminas\Db\Adapter\AdapterInterface` | Service koneksi database di container (alias: `Laminas\Db\Adapter\Adapter`) |
| `composer cek-database` | Menguji koneksi dan menampilkan jenis serta versi server database |
| `data/database/` | Lokasi bawaan file SQLite (isinya tidak masuk Git) |

Sudah diuji dengan MariaDB 10.7, PostgreSQL 17, dan SQLite 3.

> PHP perlu ekstensi PDO sesuai database Anda: `pdo_mysql`, `pdo_pgsql`, atau `pdo_sqlite`.
> Cek dengan `php -m`.

### Mengatur koneksi

1. Salin file contoh:

   ```bash
   cp config/autoload/database.local.php.dist config/autoload/database.local.php
   ```

2. Buka `config/autoload/database.local.php`, biarkan aktif satu contoh sesuai database Anda,
   lalu isi nama database, username, dan password. Contoh untuk MySQL/MariaDB:

   ```php
   return [
       'db' => [
           'driver'   => 'Pdo_Mysql',
           'hostname' => '127.0.0.1',
           'port'     => 3306,
           'database' => 'nama_database',
           'username' => 'nama_pengguna',
           'password' => 'kata_sandi',
           'charset'  => 'utf8mb4',
       ],
   ];
   ```

   Untuk PostgreSQL gunakan `'driver' => 'Pdo_Pgsql'` (port `5432`). Untuk SQLite cukup
   `'driver' => 'Pdo_Sqlite'` dan `'database'` berisi lokasi file, misalnya
   `data/database/dapur.sqlite`. Pada SQLite, folder `data/database/` harus bisa ditulis
   oleh web server (lihat [Izin folder cache](#izin-folder-cache); caranya sama).

3. Hapus cache konfigurasi, lalu uji koneksinya:

   ```bash
   composer clear-config-cache
   composer cek-database
   ```

   Jika berhasil, akan tampil pesan seperti `Berhasil terhubung ke database MySQL 10.7.3-MariaDB.`
   Jika gagal, pesan error dari database akan ditampilkan.

### Keamanan password database

- File `*.local.php` di `config/autoload/` tidak masuk Git, sehingga password Anda tidak ikut
  ter-commit.
- Di server, batasi agar hanya Anda dan web server yang bisa membaca file pengaturan:

  ```bash
  chgrp <grup-web-server> config/autoload/database.local.php
  chmod 640 config/autoload/database.local.php
  ```

- Selama cache konfigurasi aktif, isi pengaturan ini juga tersimpan di
  `data/cache/config-cache.php`. Gunakan izin `2770` untuk folder `data/cache/` (lihat
  [Izin folder cache](#izin-folder-cache)).
- Folder `config/` dan `data/` berada di luar document root `public/`, jadi tidak bisa dibuka
  dari browser.

### Jika koneksi gagal

| Pesan | Penyebab dan solusi |
|---|---|
| `Koneksi database belum diatur.` | File `database.local.php` belum ada, atau cache konfigurasi lama masih dipakai. Jalankan `composer clear-config-cache`. |
| `could not find driver` | Ekstensi PDO untuk database tersebut belum aktif di PHP. |
| `Access denied for user ...` | Username atau password salah, atau user database tidak diizinkan dari host tersebut. Di MySQL/MariaDB, `localhost` (lewat socket) dan `127.0.0.1` (lewat TCP) dianggap host yang berbeda. |
| `Connection refused` | Server database tidak berjalan, atau `hostname`/`port` salah. |

### Memakai koneksi di handler

Ambil `Laminas\Db\Adapter\AdapterInterface` dari container di factory handler:

```php
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

final class ArtikelHandlerFactory
{
    public function __invoke(ContainerInterface $container): ArtikelHandler
    {
        return new ArtikelHandler($container->get(AdapterInterface::class));
    }
}
```

Lalu jalankan query di dalam handler:

```php
use Laminas\Db\Adapter\AdapterInterface;

public function __construct(
    private readonly AdapterInterface $db
) {
}

public function handle(ServerRequestInterface $request): ResponseInterface
{
    $hasil = $this->db->query(
        'SELECT id, judul FROM artikel WHERE terbit = ?',
        [1]
    );

    foreach ($hasil as $baris) {
        // $baris['judul'] ...
    }

    // ...
}
```

Gunakan tanda `?` untuk nilai dari pengguna agar aman dari SQL injection. Untuk kebutuhan
yang lebih lengkap, misalnya query builder (`Laminas\Db\Sql\Sql`) atau `TableGateway`, lihat
[dokumentasi laminas-db](https://docs.laminas.dev/laminas-db/).

> **Catatan:** jangan tambahkan `Laminas\Db\ConfigProvider` ke `config/config.php`. Factory
> bawaan laminas-db 2.x belum mendukung laminas-servicemanager 4 dan akan menyebabkan fatal
> error. Dapur AdeMS sudah menyediakan factory penggantinya di `module/App/src/Db/`, dan
> `composer.json` mencegah laminas-component-installer menambahkannya secara otomatis.

## Lisensi

Dapur AdeMS dirilis di bawah lisensi [BSD 3-Clause](LICENSE.md).
Lihat juga [COPYRIGHT.md](COPYRIGHT.md).
