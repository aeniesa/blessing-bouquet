/**
 * sw.js — Service Worker dasar (PWA) [DITAMBAHKAN, §24].
 * - Aset statis: cache-first.
 * - Navigasi halaman: network-first + fallback cache, lalu halaman offline.
 * - Panel admin & permintaan non-GET tidak di-cache.
 */
const CACHE = 'bb-cache-v6';
const OFFLINE_URL = 'offline.html';
const ASET_STATIS = [
    './',
    './index.php',
    './katalog.php',
    './tentang.php',
    './kontak.php',
    './daftar-pesanan.php',
    './offline.html',
    './assets/css/style.css',
    './assets/js/main.js',
    './assets/img/icon-192.png',
    './assets/img/icon-512.png',
    './assets/img/ornamen-sprig.svg',
    './assets/img/ornamen-pembatas.svg',
    './assets/img/ornamen-sudut.svg',
    './manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(ASET_STATIS)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;
    if (url.pathname.includes('/admin/')) return;

    // Navigasi halaman: network-first
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(CACHE).then((c) => c.put(req, copy));
                    return res;
                })
                .catch(() =>
                    caches.match(req).then((cached) => cached || caches.match(OFFLINE_URL))
                )
        );
        return;
    }

    // Aset statis: cache-first
    event.respondWith(
        caches.match(req).then((cached) => {
            if (cached) return cached;
            return fetch(req).then((res) => {
                if (res.ok && (url.pathname.includes('/assets/') || url.pathname.endsWith('.png') || url.pathname.endsWith('.css') || url.pathname.endsWith('.js'))) {
                    const copy = res.clone();
                    caches.open(CACHE).then((c) => c.put(req, copy));
                }
                return res;
            });
        })
    );
});
