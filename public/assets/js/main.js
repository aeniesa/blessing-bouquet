/**
 * main.js — interaksi dasar beranda + pendaftaran service worker (PWA).
 */
(function () {
    'use strict';

    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('nav-utama');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var terbuka = nav.classList.toggle('terbuka');
            toggle.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
        });
        nav.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') {
                nav.classList.remove('terbuka');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('sw.js').catch(function () {
                /* PWA opsional; abaikan bila gagal (mis. bukan secure context). */
            });
        });
    }
})();
