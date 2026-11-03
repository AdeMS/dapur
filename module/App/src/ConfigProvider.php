<?php

declare(strict_types=1);

namespace App;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'templates' => $this->getTemplates(),
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