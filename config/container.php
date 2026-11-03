<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

// Memuat konfigurasi dari file config.php
$config = require __DIR__ . '/config.php';

// Mengambil dependensi dari konfigurasi
$dependencies                       = $config['dependencies'];
$dependencies['services']['config'] = $config;

// Membuat instance ServiceManager dengan dependensi yang telah diambil
return new ServiceManager($dependencies);
