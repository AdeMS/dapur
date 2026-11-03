<?php

declare(strict_types=1);

// Mengubah direktori kerja saat ini ke direktori induk dari file ini
chdir(dirname(__DIR__));

// Memuat autoloader dari Composer
require 'vendor/autoload.php';

// Memulai aplikasi Laminas Mezzio
(function (): void {
    // Memuat konfigurasi melalui container
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require 'config/container.php';

    // Memuat aplikasi dan factory middleware dari container
    /** @var \Mezzio\Application $app */
    $app     = $container->get(\Mezzio\Application::class);
    $factory = $container->get(\Mezzio\MiddlewareFactory::class);

    // Memuat pipeline dan rute aplikasi
    (require 'config/pipeline.php')($app, $factory, $container);
    (require 'config/routes.php')($app, $factory, $container);

    // Menjalankan aplikasi
    $app->run();
})();
