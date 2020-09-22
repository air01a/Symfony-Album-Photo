self.addEventListener('install', function(e) {
    e.waitUntil(
      caches.open('fox-store').then(function(cache) {
        return cache.addAll([
          ''
        ]);
      })
    );
   });