<?php

declare(strict_types=1);

namespace App\Error;

use Psr\Container\ContainerInterface;

/** Delegator untuk Mezzio\Middleware\ErrorResponseGenerator: membungkusnya dengan PencatatError. */
final class PencatatErrorDelegator
{
    public function __invoke(ContainerInterface $container, string $nama, callable $callback): PencatatError
    {
        return new PencatatError($callback(), $container->get(LogError::class));
    }
}
