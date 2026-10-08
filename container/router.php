<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

/**
 * laminas-router route configuration
 *
 * @see https://docs.laminas.dev/laminas-router/
 *
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/:id', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $routeDirectory = __DIR__ . '/route';
    $globalRouteFile = $routeDirectory . '/global.php';

    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    $host = preg_replace('/:\d+$/', '', $host);
    $host = trim($host, '.');

    if (! preg_match('/^[a-z0-9.-]+$/', $host)) {
        $host = '';
    }

    $hostRouteFile = $routeDirectory . '/' . $host . '.php';
    $defaultRouteFile = $routeDirectory . '/default.php';
    $selectedRouteFile = $host !== '' && is_file($hostRouteFile)
        ? $hostRouteFile
        : $defaultRouteFile;

    $routes = require $globalRouteFile;
    $selectedRoutes = require $selectedRouteFile;
    $packageRoutes = $container->get('config')['routes'] ?? [];

    if (! is_array($routes) || ! is_array($selectedRoutes) || ! is_array($packageRoutes)) {
        throw new RuntimeException('File konfigurasi route harus mengembalikan array.');
    }

    foreach (array_merge($packageRoutes, $routes, $selectedRoutes) as $route) {
        $appRoute = $app->route(
            $route['path'],
            $route['middleware'],
            $route['allowed_methods'] ?? [],
            $route['name'] ?? null
        );

        if (isset($route['options'])) {
            $appRoute->setOptions($route['options']);
        }
    }
};
