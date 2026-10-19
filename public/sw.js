// public/sw.js
self.addEventListener('install', (e) => {
    console.log('[Service Worker] Installed');
});

self.addEventListener('fetch', (e) => {
    // Membiarkan aplikasi berjalan normal secara online
});