<?php

declare(strict_types=1);

namespace App\View\Helper;

use Psr\Container\ContainerInterface;

final class InfoWebsiteFactory
{
    public function __invoke(ContainerInterface $container): InfoWebsite
    {
        $config  = $container->has('config') ? $container->get('config') : [];
        $website = $config['website'] ?? [];

        return new InfoWebsite(is_array($website) ? $website : []);
    }
}
