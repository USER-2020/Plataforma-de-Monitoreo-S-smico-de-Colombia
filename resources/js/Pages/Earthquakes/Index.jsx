import {Head, Link} from '@inertiajs/react';
import Layout from '../../Components/Layout';
import EarthquakeFilters from '../../Components/EarthquakeFilters';
import MagnitudeBadge from '../../Components/MagnitudeBadge';
import {dateCO} from '../../lib/earthquakes';

function Pagination({links}) {
    return (
        <nav className="mt-6 flex flex-wrap items-center justify-center gap-2" aria-label="Paginación del historial sísmico">
            {links.map((link, index) => {
                const isPrevious = index === 0;
                const isNext = index === links.length - 1;
                const label = isPrevious ? 'Anterior' : isNext ? 'Siguiente' : link.label;
                const content = (
                    <>
                        {isPrevious && <span aria-hidden="true">‹</span>}
                        <span className={(isPrevious || isNext) ? 'hidden sm:inline' : ''}>{label}</span>
                        {isNext && <span aria-hidden="true">›</span>}
                    </>
                );
                const classes = [
                    'inline-flex min-h-11 min-w-11 items-center justify-center gap-2 rounded-xl border px-4 py-2.5',
                    'text-base font-bold transition duration-200 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-purple-300',
                    link.active
                        ? 'border-purple-700 bg-gradient-to-br from-fuchsia-500 to-purple-700 text-white shadow-lg shadow-purple-200'
                        : 'border-purple-100 bg-white text-purple-950 shadow-sm hover:-translate-y-0.5 hover:border-purple-300 hover:bg-purple-50 hover:shadow-md',
                    !link.url ? 'cursor-not-allowed opacity-40 shadow-none hover:translate-y-0' : '',
                ].join(' ');

                if (!link.url) {
                    return <span key={`${label}-${index}`} className={classes} aria-disabled="true">{content}</span>;
                }

                return (
                    <Link
                        key={`${label}-${index}`}
                        href={link.url}
                        className={classes}
                        aria-current={link.active ? 'page' : undefined}
                        preserveScroll
                    >
                        {content}
                    </Link>
                );
            })}
        </nav>
    );
}

export default function Index({earthquakes, filters, departments}) {
    return (
        <Layout>
            <Head title="Historial"/>
            <h1 className="mb-2 text-4xl font-black">Historial sísmico</h1>
            <p className="mb-6 text-slate-400">Eventos consolidados y contrastados entre redes sismológicas.</p>
            <EarthquakeFilters departments={departments} initial={filters}/>
            <div className="panel mt-5 overflow-x-auto">
                <table className="w-full text-left">
                    <thead className="label border-b border-slate-700">
                        <tr>{['Magnitud', 'Fecha y hora', 'Ubicación', 'Departamento', 'Profundidad', 'Fuentes', ''].map(label => <th className="p-4" key={label}>{label}</th>)}</tr>
                    </thead>
                    <tbody>
                        {earthquakes.data.map(earthquake => (
                            <tr className="border-b border-slate-800" key={earthquake.id}>
                                <td className="p-4"><MagnitudeBadge value={earthquake.magnitude}/></td>
                                <td className="whitespace-nowrap p-4">{dateCO(earthquake.occurred_at)}</td>
                                <td className="p-4">{earthquake.place}</td>
                                <td className="p-4">{earthquake.department || '—'}</td>
                                <td className="p-4">{earthquake.depth_km} km</td>
                                <td className="p-4">
                                    <span className="font-bold uppercase">{earthquake.source}</span>
                                    <small className="block text-purple-400">{earthquake.source_reports_count || 1} reporte(s)</small>
                                </td>
                                <td className="p-4"><Link className="font-bold text-purple-600" href={`/sismos/${earthquake.id}`}>Ver →</Link></td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination links={earthquakes.links}/>
        </Layout>
    );
}
