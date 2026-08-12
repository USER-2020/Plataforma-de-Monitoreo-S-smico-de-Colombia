import {useEffect, useState} from 'react';

const DISMISSED_KEY = 'terracosismos-pwa-install-dismissed';

const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
const isIos = () => /iphone|ipad|ipod/i.test(window.navigator.userAgent);

export default function PwaInstallBanner() {
    const [installPrompt, setInstallPrompt] = useState(null);
    const [visible, setVisible] = useState(false);
    const [ios] = useState(() => typeof window !== 'undefined' && isIos());

    useEffect(() => {
        if (isStandalone() || window.localStorage.getItem(DISMISSED_KEY) === 'true') return;

        if (isIos()) setVisible(true);

        const handlePrompt = event => {
            event.preventDefault();
            setInstallPrompt(event);
            setVisible(true);
        };
        const handleInstalled = () => {
            setInstallPrompt(null);
            setVisible(false);
        };

        window.addEventListener('beforeinstallprompt', handlePrompt);
        window.addEventListener('appinstalled', handleInstalled);

        return () => {
            window.removeEventListener('beforeinstallprompt', handlePrompt);
            window.removeEventListener('appinstalled', handleInstalled);
        };
    }, []);

    const dismiss = () => {
        window.localStorage.setItem(DISMISSED_KEY, 'true');
        setVisible(false);
    };

    const install = async () => {
        if (!installPrompt) return;
        await installPrompt.prompt();
        const choice = await installPrompt.userChoice;
        if (choice.outcome === 'accepted') setVisible(false);
        setInstallPrompt(null);
    };

    if (!visible) return null;

    return <aside className="pwa-install-banner" role="status" aria-label="Instalar terracosismos">
        <img src="/icons/app-icon.svg" alt="" className="pwa-install-icon"/>
        <div className="pwa-install-copy">
            <strong>Instala terracosismos en tu teléfono</strong>
            <span>{ios ? 'Toca Compartir y luego “Añadir a pantalla de inicio”.' : 'Accede más rápido y recibe una experiencia de aplicación.'}</span>
        </div>
        {!ios && installPrompt && <button type="button" className="pwa-install-action" onClick={install}>Instalar</button>}
        <button type="button" className="pwa-install-close" onClick={dismiss} aria-label="Cerrar invitación de instalación">×</button>
    </aside>;
}
