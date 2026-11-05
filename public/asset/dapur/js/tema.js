/*!
 * Pemilih tema (terang, gelap, otomatis) untuk Dapur AdeMS.
 *
 * Muat file ini di <head> tanpa `defer`, agar tema diterapkan sebelum halaman
 * tampil dan tidak terjadi kedipan warna. Pilihan pengunjung disimpan di
 * localStorage; "otomatis" mengikuti pengaturan terang/gelap di perangkat.
 */
(() => {
    'use strict';

    const KUNCI = 'dapur-tema';
    const IKON  = {
        light: 'bi-sun-fill',
        dark:  'bi-moon-stars-fill',
        auto:  'bi-circle-half',
    };
    const NAMA  = {
        light: 'terang',
        dark:  'gelap',
        auto:  'otomatis',
    };

    const ambilPilihan = () => {
        try {
            const pilihan = localStorage.getItem(KUNCI);
            return pilihan in IKON ? pilihan : 'auto';
        } catch (e) {
            return 'auto';
        }
    };

    const simpanPilihan = (pilihan) => {
        try {
            if (pilihan === 'auto') {
                localStorage.removeItem(KUNCI);
            } else {
                localStorage.setItem(KUNCI, pilihan);
            }
        } catch (e) {
            // localStorage tidak tersedia (misalnya mode privat); pilihan hanya berlaku di halaman ini
        }
    };

    const perangkatGelap = window.matchMedia('(prefers-color-scheme: dark)');

    const terapkan = (pilihan) => {
        const mode = pilihan === 'auto' ? (perangkatGelap.matches ? 'dark' : 'light') : pilihan;
        document.documentElement.setAttribute('data-bs-theme', mode);
    };

    const tampilkanPilihan = (pilihan) => {
        const tombol = document.querySelector('#pilihTema');
        if (! tombol) {
            return;
        }

        const ikon = tombol.querySelector('.tema-ikon-aktif');
        ikon.classList.remove(...Object.values(IKON));
        ikon.classList.add(IKON[pilihan]);
        tombol.setAttribute('aria-label', `Pilih tema (saat ini: ${NAMA[pilihan]})`);

        document.querySelectorAll('[data-tema-pilihan]').forEach((item) => {
            const aktif = item.getAttribute('data-tema-pilihan') === pilihan;
            item.classList.toggle('active', aktif);
            item.setAttribute('aria-pressed', aktif ? 'true' : 'false');
        });
    };

    terapkan(ambilPilihan());

    perangkatGelap.addEventListener('change', () => {
        if (ambilPilihan() === 'auto') {
            terapkan('auto');
        }
    });

    window.addEventListener('DOMContentLoaded', () => {
        tampilkanPilihan(ambilPilihan());

        document.querySelectorAll('[data-tema-pilihan]').forEach((item) => {
            item.addEventListener('click', () => {
                const pilihan = item.getAttribute('data-tema-pilihan');
                simpanPilihan(pilihan);
                terapkan(pilihan);
                tampilkanPilihan(pilihan);
            });
        });
    });
})();
