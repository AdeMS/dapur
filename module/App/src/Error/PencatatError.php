<?php

declare(strict_types=1);

namespace App\Error;

use Laminas\Stratigility\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Membungkus pembuat halaman error Mezzio (ErrorResponseGenerator, atau Whoops di mode development):
 * setiap kesalahan dicatat ke log error lebih dulu, lalu halaman error dibuat seperti biasa.
 *
 * Kode kesalahan diberikan ke template lewat atribut request LogError::ATRIBUT_KODE, sehingga pengunjung bisa
 * menyebutkan kode itu kepada admin, dan admin mencarinya dengan `php dapur log-error <kode>`.
 */
final class PencatatError
{
    /** @var callable(Throwable, ServerRequestInterface, ResponseInterface): ResponseInterface */
    private $pembuat;

    /** @param callable(Throwable, ServerRequestInterface, ResponseInterface): ResponseInterface $pembuat */
    public function __construct(callable $pembuat, private readonly LogError $log)
    {
        $this->pembuat = $pembuat;
    }

    public function __invoke(Throwable $e, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $kode = LogError::buatKode();
        $this->log->catat($e, $request, Utils::getStatusCode($e, $response), $kode);

        try {
            return ($this->pembuat)($e, $request->withAttribute(LogError::ATRIBUT_KODE, $kode), $response);
        } catch (Throwable $gagal) {
            // Halaman error sendiri gagal dibuat (misalnya layout memerlukan database yang sedang mati):
            // catat juga, lalu kirim halaman sederhana tanpa template.
            $kodeKedua = LogError::buatKode();
            $this->log->catat($gagal, $request, 500, $kodeKedua);

            $response = $response->withStatus(500)->withHeader('Content-Type', 'text/html; charset=utf-8');
            $response->getBody()->write(sprintf(
                '<!DOCTYPE html><html lang="id"><meta charset="utf-8"><title>Terjadi kesalahan</title>'
                . '<p>Maaf, terjadi kesalahan saat memproses permintaan Anda.</p><p>Kode kesalahan: %s, %s</p></html>',
                $kode,
                $kodeKedua,
            ));

            return $response;
        }
    }
}
