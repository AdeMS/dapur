<?php

declare(strict_types=1);

namespace App\Middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Menampilkan halaman bawaan pada halaman utama (/) jika belum ada route yang menanganinya.
 */
final class DefaultHomeMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TemplateRendererInterface $template
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() !== '/') {
            return $handler->handle($request);
        }

        // Merender template dapur::default-home di dalam layout::default
        return new HtmlResponse($this->template->render('dapur::default-home', [
            'layout' => 'layout::default',
        ]));
    }
}
