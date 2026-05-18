const CACHE = 'vaca-v1';

self.addEventListener('install', e => {
    e.waitUntil(caches.open(CACHE).then(c => c.add('/vaca/')));
    self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', e => {
    if (e.request.method !== 'GET') return;
    // API: solo red, sin cache
    if (e.request.url.includes('/api.php')) return;
    // Navegación: red primero, cache como fallback
    e.respondWith(
        fetch(e.request)
            .then(r => {
                if (r.ok) {
                    const clone = r.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                }
                return r;
            })
            .catch(() => caches.match(e.request))
    );
});
