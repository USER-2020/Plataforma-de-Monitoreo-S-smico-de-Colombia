import {Link, usePage} from '@inertiajs/react';
import SystemToasts from './SystemToasts';

const Icon = ({children}) => <span className="text-xl leading-none">{children}</span>;

export default function Layout({children}) {
    const {url} = usePage();
    const isActive = path => path === '/' ? url === '/' : url.startsWith(path);

    return <div className="app-shell">
        <SystemToasts/>
        <a className="skip-link" href="#main-content">Saltar al contenido</a>
        <header className="app-header border-b border-purple-100/80 bg-white/85 sticky top-0 z-40 backdrop-blur-xl">
            <div className="max-w-[1500px] mx-auto px-4 md:px-7 h-[72px] flex items-center justify-between">
                <Link href="/" className="flex items-center gap-3">
                    <img src="/icons/app-icon.svg" alt="" className="w-11 h-11 rounded-2xl"/>
                    <span><b className="block tracking-tight text-purple-950">terracosismos</b><small className="text-purple-400">Monitoreo en tiempo real</small></span>
                </Link>
                <nav className="desktop-nav flex items-center gap-8 text-sm font-semibold text-purple-900" aria-label="Navegación principal">
                    <Link href="/">Monitor</Link><Link href="/sismos">Historial</Link><Link href="/estadisticas">Estadísticas</Link><a href="/#alertas" className="btn !py-2.5">Activar alertas</a>
                </nav>
            </div>
        </header>
        <main id="main-content" className="app-content max-w-[1500px] mx-auto p-4 md:p-7">{children}</main>
        <footer className="app-footer max-w-[1500px] mx-auto px-4 md:px-7 py-6 flex flex-wrap justify-center gap-4 text-xs text-purple-400">
            <Link href="/privacidad">Privacidad y cookies</Link>
            <button type="button" onClick={() => window.openPrivacySettings?.()} className="hover:text-purple-700">Configurar cookies</button>
            <span>© {new Date().getFullYear()} terracosismos</span>
        </footer>
        <nav className="mobile-nav" aria-label="Navegación móvil">
            <Link href="/" className={isActive('/') ? 'active' : ''} aria-current={isActive('/') ? 'page' : undefined}><Icon>⌁</Icon><span>Monitor</span></Link>
            <Link href="/sismos" className={isActive('/sismos') ? 'active' : ''} aria-current={isActive('/sismos') ? 'page' : undefined}><Icon>⌕</Icon><span>Sismos</span></Link>
            <Link href="/estadisticas" className={isActive('/estadisticas') ? 'active' : ''} aria-current={isActive('/estadisticas') ? 'page' : undefined}><Icon>⌁</Icon><span>Estadísticas</span></Link>
            <a href="/#alertas"><Icon>♢</Icon><span>Alertas</span></a>
        </nav>
    </div>;
}
