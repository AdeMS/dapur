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
                'error'  => [__DIR__ . '/../templates/error'],
            ],
        ];
    }
}