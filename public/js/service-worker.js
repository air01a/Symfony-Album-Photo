self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request, {
            mode: 'cors',
            credentials: 'include',
            headers: {
                'test': 'Bearer mF_9.B5f-4.1JqM' // you would, of course, not hard-code this here...
            }
        })
    )
});