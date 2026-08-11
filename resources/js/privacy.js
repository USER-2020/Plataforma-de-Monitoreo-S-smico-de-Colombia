import axios from 'axios';
import 'klaro/dist/klaro.css';

const CONFIG_VERSION = '2026-08-11.1';
let actionTrackingEnabled = false;

const post = (url, data) => axios.post(url, data, {headers: {'X-Requested-With': 'XMLHttpRequest'}}).catch(() => {});

function trackClick(event) {
    const target = event.target.closest('a, button');
    if (!target) return;
    post('/privacidad/eventos', {
        action: 'click',
        path: window.location.pathname,
        metadata: {
            element: target.tagName.toLowerCase(),
            label: (target.getAttribute('aria-label') || target.textContent || '').trim().slice(0, 80),
            destination: target instanceof HTMLAnchorElement ? target.pathname : null,
        },
    });
}

function setActionTracking(enabled) {
    if (enabled === actionTrackingEnabled) return;
    actionTrackingEnabled = enabled;
    document[enabled ? 'addEventListener' : 'removeEventListener']('click', trackClick, {capture: true});
    if (enabled) post('/privacidad/eventos', {action: 'page_view', path: window.location.pathname, metadata: {source: 'consent'}});
}

const config = {
    version: 1,
    elementID: 'terracosismos-klaro',
    noAutoLoad: true,
    storageMethod: 'cookie',
    storageName: 'terracosismos_consent',
    cookieExpiresAfterDays: 365,
    cookiePath: '/',
    default: false,
    mustConsent: false,
    acceptAll: true,
    hideDeclineAll: false,
    groupByPurpose: true,
    lang: 'es',
    translations: {
        es: {
            privacyPolicyUrl: '/privacidad',
            consentNotice: {
                description: 'Usamos cookies esenciales y, con tu permiso, analítica de visitas y servicios externos. Puedes aceptar, rechazar o personalizar.',
                learnMore: 'Configurar',
            },
            consentModal: {
                title: 'Privacidad y cookies en terracosismos',
                description: 'Elige qué datos opcionales permites. Puedes cambiar tu decisión en cualquier momento.',
            },
            purposes: {functional: 'Funcionamiento esencial', analytics: 'Analítica y auditoría'},
            essential: {title: 'Cookies esenciales', description: 'Sesión, seguridad CSRF y funcionamiento de la PWA.'},
            analyticsAudit: {title: 'Analítica y auditoría', description: 'Registra visitas, acciones, IP cifrada y métricas para mejorar y auditar la plataforma.'},
        },
    },
    services: [
        {name: 'essential', required: true, purposes: ['functional'], cookies: ['laravel_session', 'XSRF-TOKEN']},
        {name: 'analyticsAudit', default: false, purposes: ['analytics'], cookies: ['terracosismos_visitor'], onAccept: () => setActionTracking(true), onDecline: () => setActionTracking(false)},
    ],
};

export async function initializePrivacy() {
    window.klaroConfig = config;
    const klaro = await import('klaro');
    const hasSavedDecision = document.cookie.split('; ').some(cookie => cookie.startsWith(`${config.storageName}=`));
    klaro.render(config, {show: !hasSavedDecision, modal: false});
    const manager = klaro.getManager(config);
    manager.watch({
        update: (_manager, eventType, data) => {
            if (eventType !== 'saveConsents') return;
            const type = ['acceptAll', 'declineAll'].includes(data.type) ? data.type.replace('All', '_all').toLowerCase() : 'save';
            post('/privacidad/consentimiento', {config_version: CONFIG_VERSION, consents: data.consents, action: type});
        },
    });
    window.openPrivacySettings = () => klaro.show(config, true);
}

export function trackPageView(path) {
    if (actionTrackingEnabled) post('/privacidad/eventos', {action: 'page_view', path, metadata: {source: 'inertia'}});
}
