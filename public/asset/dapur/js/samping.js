/**
 * Menu samping (placeholder 'menu-samping') di layar kecil.
 *
 * - Mengukur tinggi navbar ke variabel CSS --dapur-navbar-tinggi, agar panel menu tepat berada di bawahnya.
 * - Mengubah tombol menu (hamburger) menjadi tanda silang selama panel terbuka, seperti menu navbar biasa.
 *
 * Dimuat oleh layout hanya bila menu samping berisi, sesudah Bootstrap.
 */
(function () {
    'use strict';

    var navbar = document.querySelector('header .navbar');
    var panel = document.getElementById('dapurSamping');
    var tombol = document.querySelector('.dapur-toggler-samping');

    if (navbar) {
        var ukur = function () {
            document.documentElement.style.setProperty('--dapur-navbar-tinggi', navbar.offsetHeight + 'px');
        };
        ukur();
        window.addEventListener('resize', ukur);
    }

    if (panel && tombol) {
        panel.addEventListener('show.bs.offcanvas', function () {
            tombol.classList.remove('collapsed');
            tombol.setAttribute('aria-expanded', 'true');
        });
        panel.addEventListener('hide.bs.offcanvas', function () {
            tombol.classList.add('collapsed');
            tombol.setAttribute('aria-expanded', 'false');
        });
    }
})();
