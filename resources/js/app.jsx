import 'leaflet/dist/leaflet.css';
import '../css/app.css';
import {createInertiaApp, router} from '@inertiajs/react';
import {createRoot} from 'react-dom/client';
import {toast} from 'react-hot-toast';
import {registerSW} from 'virtual:pwa-register';
import {echo} from './echo';

registerSW({
    immediate: true,
    onOfflineReady: () => toast.success('terracosismos está lista para funcionar sin conexión.', {id: 'pwa-offline-ready'}),
    onRegistered: registration => registration?.update(),
    onRegisterError: () => toast.error('No fue posible activar el modo sin conexión.', {id: 'pwa-error'}),
});

echo.channel('earthquakes').listen('.earthquake.received', () => router.reload({only: ['earthquakes', 'statistics']}));

createInertiaApp({
    title: title => title ? `${title} · terracosismos` : 'terracosismos · Monitoreo Sísmico de Colombia',
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.jsx', {eager: true});
        return pages[`./Pages/${name}.jsx`];
    },
    setup({el, App, props}) {
        createRoot(el).render(<App {...props}/>);
    },
    progress: {color: '#8c1cc4'},
});
