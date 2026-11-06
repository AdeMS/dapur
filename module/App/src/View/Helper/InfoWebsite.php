<?php

declare(strict_types=1);

namespace App\View\Helper;

/**
 * Judul dan tema website yang sedang dilayani, untuk dipakai di layout:
 *
 *     $this->infoWebsite()->judul()
 *     $this->infoWebsite()->host()
 *     $this->infoWebsite()->tema()
 *
 * Nilainya dari $config['website'] (diisi paket multi-website dari website.php).
 * Tanpa pengaturan tersebut, nilai bawaan Dapur AdeMS yang dipakai.
 */
final class InfoWebsite
{
    private const JUDUL_BAWAAN = 'Dapur AdeMS';
    private const TEMA_BAWAAN  = 'dapur';

    /** @param array<string, mixed> $website isi $config['website'] */
    public function __construct(
        private readonly array $website = []
    ) {
    }

    public function __invoke(): self
    {
        return $this;
    }

    public function judul(): string
    {
        $judul = $this->website['judul'] ?? null;

        return is_string($judul) && trim($judul) !== '' ? trim($judul) : self::JUDUL_BAWAAN;
    }

    /** Nama domain website dari konfigurasi, atau null bila tidak ada (Dapur satu website atau dashboard). */
    public function host(): ?string
    {
        $host = $this->website['host'] ?? null;

        return is_string($host) && $host !== '' ? $host : null;
    }

    /** Nama tema warna untuk atribut data-tema; nama yang tidak sah memakai tema bawaan. */
    public function tema(): string
    {
        $tema = $this->website['tema'] ?? null;

        return is_string($tema) && preg_match('/^[a-z0-9-]+$/', $tema) === 1 ? $tema : self::TEMA_BAWAAN;
    }
}
