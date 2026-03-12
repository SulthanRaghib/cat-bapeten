import './bootstrap';
import { registerSW } from 'virtual:pwa-register';

// Register service worker with auto-update on new content
const updateSW = registerSW({
    // Periodically check for updates (every 60 minutes)
    onRegisteredSW(swUrl, r) {
        r && setInterval(async () => {
            if (!(!r.installing && navigator)) return;
            if (('connection' in navigator) && !navigator.onLine) return;
            try {
                const resp = await fetch(swUrl, { cache: 'no-store', headers: { 'cache': 'no-store', 'cache-control': 'no-cache' } });
                if (resp?.status === 200) await r.update();
            } catch { /* network unavailable */ }
        }, 60 * 60 * 1000);
    },
    // Show reload prompt when new SW is waiting
    onNeedRefresh() {
        if (confirm('Versi baru CAT BAPETEN tersedia. Muat ulang sekarang?')) {
            updateSW(true);
        }
    },
    onOfflineReady() {
        console.log('[CAT BAPETEN] Aplikasi siap digunakan secara offline.');
    },
});
