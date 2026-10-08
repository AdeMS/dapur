<?php

declare(strict_types=1);

/**
 * Menampilkan semua pesan error di lingkungan pengembangan, 
 * tetapi menyembunyikannya di lingkungan lain sperti lingkungan produksi.
 */
if ($_SERVER['DAPUR_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set("display_errors", '1');
}

/**
 * Mengubah direktori kerja saat ini ke direktori root proyek,
 * yang ditentukan oleh variabel lingkungan DAPUR_ROOT, atau ke direktori induk
 * jika DAPUR_ROOT tidak ditentukan. Hal ini memastikan bahwa semua file dan sumber daya
 * yang diperlukan dapat diakses dengan benar dari direktori root proyek.
 */
chdir($_SERVER['DAPUR_ROOT'] ?? dirname(__DIR__));

/**
 * Memuat autoloader yang dihasilkan oleh Composer untuk mengelola dependensi 
 * dan autoloading kelas.
 */
require getcwd() . '/vendor/autoload.php';

(function() {
    /** 
     * Memuat konfigurasi container dependency injection dari file config/container.php 
     * dan menyimpannya dalam variabel $container. Container ini akan digunakan untuk
     * mengelola dan menyuntikkan dependensi ke dalam aplikasi, seperti layanan, middleware,
     * dan komponen lainnya. Dengan menggunakan container, kita dapat mengatur dependensi dengan lebih mudah
     * dan menjaga kode tetap bersih dan terorganisir. 
     * 
     * @var \Psr\Container\ContainerInterface $container 
    */
    $container = require getcwd() . '/container/container.php';

    /** 
     * Mengambil instance aplikasi Mezzio dari container menggunakan kunci \Mezzio\Application::class
     * dan menyimpannya dalam variabel $app. Aplikasi ini akan digunakan untuk mengkonfigurasi
     * middleware, routing, dan menjalankan aplikasi.
     * 
     * @var \Mezzio\Application $dapur 
    */
    $dapur = $container->get(\Mezzio\Application::class);

    /** 
     * Mengambil instance MiddlewareFactory dari container menggunakan kunci \Mezzio\MiddlewareFactory::class
     * dan menyimpannya dalam variabel $factory. MiddlewareFactory ini akan digunakan untuk membuat
     * instance middleware yang diperlukan dalam pipeline aplikasi.
     * 
     * @var \Mezzio\MiddlewareFactory $factory 
    */
    $factory = $container->get(\Mezzio\MiddlewareFactory::class);

    /**
     * Menjalankan konfigurasi pipeline dan routing aplikasi dengan memanggil file config/pipeline.php
     * dan config/routes.php, serta menyuntikkan $dapur, $factory,
     * dan $container sebagai argumen. File-file ini akan berisi konfigurasi untuk middleware dan routing aplikasi,
     * yang akan menentukan bagaimana aplikasi merespons permintaan HTTP yang masuk. Dengan memis
     * kan konfigurasi ini ke dalam file terpisah, kita dapat menjaga kode tetap modular dan mudah dikelola.
     */
    (require getcwd() . '/container/pipeline.php')($dapur, $factory, $container);
    (require getcwd() . '/container/router.php')($dapur, $factory, $container);

    /**
     * Menjalankan aplikasi dengan memanggil metode run() pada instance $dapur. 
     * Ini akan memulai loop aplikasi dan mulai menerima serta merespons permintaan HTTP yang masuk
     * sesuai dengan konfigurasi middleware dan routing yang telah ditentukan sebelumnya.
     */
    $dapur->run();
})();