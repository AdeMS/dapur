<?php

declare(strict_types=1);

use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Psr\Container\ContainerInterface;

/**
 * Setup middleware pipeline:
 */
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    // Error handling middleware harus diletakkan di awal (paling luar) untuk menangkap semua Exception.
    $app->pipe(ErrorHandler::class);
    $app->pipe(ServerUrlMiddleware::class);

    // Masukkan middleware tambahan di sini yang ingin Anda jalankan pada setiap permintaan:
    // - bootstrapping
    // - pre-conditions
    // - modifications to outgoing responses
    //
    // Piped Middleware dapat berupa callable atau nama layanan. Middleware 
    // juga dapat diteruskan sebagai array; setiap item dalam array harus 
    // akhirnya merujuk ke middleware (yaitu, callable atau nama layanan).
    //
    // Middleware dapat dilampirkan ke jalur tertentu, memungkinkan Anda 
    // untuk mencampur dan mencocokkan aplikasi di bawah domain umum. Handler 
    // dalam setiap middleware yang dilampirkan dengan cara ini akan melihat 
    // URI dengan segmen jalur yang cocok dihapus.
    //
    // Misalnya, jalur "/api/member/profile" hanya meneruskan "/member/profile" ke $apiMiddleware
    // - $app->pipe('/api', $apiMiddleware);
    // - $app->pipe('/docs', $apiDocMiddleware);
    // - $app->pipe('/files', $filesMiddleware);

    // Daftarkan middleware routing dalam pipeline middleware.
    // Middleware ini mendaftarkan atribut permintaan Mezzio\Router\RouteResult.
    $app->pipe(RouteMiddleware::class);

    // Berikut menangani kegagalan routing untuk kondisi umum:
    // - Permintaan HEAD tetapi tidak ada rute yang menjawab metode itu
    // - Permintaan OPTIONS tetapi tidak ada rute yang menjawab metode itu
    // - metode tidak diizinkan
    // Urutan di sini penting; MethodNotAllowedMiddleware harus ditempatkan
    // setelah Implicit*Middleware.
    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);

    // Menanamkan UrlHelper dengan hasil routing:
    $app->pipe(UrlHelperMiddleware::class);

    // Mendaftarkan middleware DispatchMiddleware untuk mengeksekusi handler yang sesuai berdasarkan hasil routing.
    $app->pipe(DispatchMiddleware::class);

    // Pada titik ini, jika tidak ada middleware yang mengembalikan respons,
    // NotFoundHandler akan dijalankan; middleware ini mengembalikan respons 404.
    $app->pipe(NotFoundHandler::class);
};
