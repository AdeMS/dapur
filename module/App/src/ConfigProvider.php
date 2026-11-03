<?php

declare(strict_types=1);

namespace App;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                Middleware\DefaultHomeMiddleware::class => Middleware\DefaultHomeMiddlewareFactory::class,
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'layout' => [__DIR__ . '/../templates/layout'],
                'error'  => [__DIR__ . '/../templates/error'],
            ],
        ];
    }
}
