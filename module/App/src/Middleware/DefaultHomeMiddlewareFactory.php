<?php

declare(strict_types=1);

namespace App\Middleware;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

final class DefaultHomeMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): DefaultHomeMiddleware
    {
        return new DefaultHomeMiddleware($container->get(TemplateRendererInterface::class));
    }
}
