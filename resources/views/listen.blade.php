<!DOCTYPE html>
<html>
<head>
    <title>Reverb Test - Event Listener</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Reverb Test - Event Listener</h1>
        <div class="mb-4">
            <button onclick="fetch('/broadcast').then(r=>r.json()).then(d=>console.log(d))" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Broadcast Event</button>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Received Events</h2>
            <div id="events" class="space-y-2">
                <p class="text-gray-500" id="no-events">Listening for events... Click "Broadcast Event" to send one.</p>
            </div>
        </div>
    </div>
    <script type="module">
        import Echo from 'laravel-echo';
        import Pusher from 'pusher-js';
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        window.Echo.channel('test-channel')
            .listen('TestBroadcast', (e) => {
                document.getElementById('no-events')?.remove();
                const div = document.createElement('div');
                div.className = 'border-b py-2';
                div.innerHTML = '<span class="text-green-600 font-mono">✓</span> ' + e.message + ' <span class="text-gray-400 text-sm">(' + e.timestamp + ')</span>';
                document.getElementById('events').prepend(div);
            });
    </script>
</body>
</html>
