import {Link} from '@inertiajs/react';
import SystemToasts from './SystemToasts';

const Icon = ({children}) => <span className="text-xl leading-none">{children}</span>;

export default function Layout({children}) {
    return <>
        <SystemToasts/>
        <header className="border-b border-purple-100/80 bg-white/85 sticky top-0 z-40 backdrop-blur-xl">
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
        <main className="max-w-[1500px] mx-auto p-4 md:p-7">{children}</main>
        <nav className="mobile-nav" aria-label="Navegación móvil"><Link href="/"><Icon>⌁</Icon><span>Monitor</span></Link><Link href="/sismos"><Icon>⌕</Icon><span>Sismos</span></Link><Link href="/estadisticas"><Icon>⌁</Icon><span>Estadísticas</span></Link><a href="/#alertas"><Icon>♢</Icon><span>Alertas</span></a></nav>
    </>;
}
