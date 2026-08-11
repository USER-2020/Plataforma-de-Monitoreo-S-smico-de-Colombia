import {Head} from '@inertiajs/react';
import {Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis} from 'recharts';
import Layout from '../../Components/Layout';

const metric = value => new Intl.NumberFormat('es-CO').format(value ?? 0);

export default function Index({statistics, daily, magnitudes, system, trafficDaily, notificationsDaily}) {
    const seismicCards = [
        ['Magnitud promedio', statistics.averageMagnitude],
        ['Profundidad promedio', `${statistics.averageDepth} km`],
        ['Mayor magnitud · 7 días', statistics.max7d],
    ];
    const productCards = [
        ['Visitantes únicos', metric(system.uniqueVisitors), 'Navegadores anónimos registrados'],
        ['Visitantes · 7 días', metric(system.visitors7d), 'Alcance reciente de la plataforma'],
        ['Páginas vistas', metric(system.pageViews), 'Interacciones acumuladas'],
        ['Usuarios con alertas', metric(system.subscribers), 'Correos únicos suscritos'],
        ['Alertas configuradas', metric(system.activeAlerts), 'Reglas actualmente activas'],
        ['Correos entregados', metric(system.emailsSent), 'Confirmados por el canal de correo'],
        ['Alertas sísmicas enviadas', metric(system.earthquakeAlertsSent), 'Eventos que notificaron usuarios'],
        ['Bienvenidas enviadas', metric(system.welcomeEmailsSent), 'Registros confirmados'],
        ['Envíos fallidos', metric(system.failedEmails), 'Requieren revisión operativa'],
        ['Decisiones de privacidad', metric(system.consentDecisions), 'Consentimientos versionados'],
        ['Acciones auditadas', metric(system.auditedActions), 'Solo con autorización del visitante'],
    ];

    return <Layout>
        <Head title="Estadísticas"><meta name="description" content="Estadísticas de actividad sísmica, alcance y alertas de terracosismos."/></Head>
        <h1 className="text-3xl md:text-4xl font-black">Estadísticas de terracosismos</h1>
        <p className="text-purple-400 mt-2 mb-6">Actividad sísmica, crecimiento y efectividad de las alertas.</p>

        <section aria-labelledby="platform-title">
            <div className="flex items-end justify-between gap-4 mb-4"><div><p className="label">PLATAFORMA</p><h2 id="platform-title" className="text-2xl font-black mt-1">Alcance y notificaciones</h2></div><span className="text-xs text-purple-400">Datos acumulados</span></div>
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">{productCards.map(([label,value,help])=><article className="panel p-5" key={label}><div className="label">{label}</div><div className="value mt-2">{value}</div><p className="text-xs text-purple-400 mt-2">{help}</p></article>)}</div>
        </section>

        <div className="grid lg:grid-cols-2 gap-5 mt-5">
            <Chart title="Tráfico de los últimos 30 días" data={trafficDaily} x="date" bars={[['views','#8b1fc1','Páginas vistas'],['visitors','#d06bec','Visitantes']]}/>
            <Chart title="Correos enviados por día" data={notificationsDaily} x="date" bars={[['total','#8b1fc1','Correos']]}/>
        </div>

        <section className="mt-9" aria-labelledby="seismic-title">
            <p className="label">ACTIVIDAD SÍSMICA</p><h2 id="seismic-title" className="text-2xl font-black mt-1 mb-4">Radiografía de los eventos</h2>
            <div className="grid md:grid-cols-3 gap-4">{seismicCards.map(([label,value])=><article className="panel p-5" key={label}><div className="label">{label}</div><div className="value mt-2">{value}</div></article>)}</div>
            <div className="grid lg:grid-cols-2 gap-5 mt-5"><Chart title="Sismos por día" data={daily} x="date" bars={[['total','#8b1fc1','Sismos']]}/><Chart title="Sismos por magnitud" data={magnitudes} x="bucket" bars={[['total','#ad46d7','Sismos']]}/></div>
        </section>

        <section className="panel p-6 mt-5"><h2 className="text-xl font-bold mb-5">Departamentos con mayor actividad sísmica</h2>{statistics.departments.map((department,index)=><div className="flex items-center gap-4 py-3 border-t border-purple-100" key={department.department}><span className="text-purple-300 w-5">{index+1}</span><b className="grow">{department.department}</b><span className="text-purple-600 font-bold">{department.total}</span></div>)}</section>
    </Layout>;
}

function Chart({title, data, x, bars}) {
    return <section className="panel p-4 md:p-6"><h2 className="font-bold mb-5">{title}</h2><ResponsiveContainer width="100%" height={280}><BarChart data={data}><CartesianGrid vertical={false} stroke="#eee3f4"/><XAxis dataKey={x} stroke="#987ca8" fontSize={11}/><YAxis stroke="#987ca8" fontSize={11}/><Tooltip contentStyle={{borderRadius:14,borderColor:'#eadff5'}}/>{bars.map(([key,color,name])=><Bar key={key} dataKey={key} name={name} fill={color} radius={[6,6,0,0]}/>)}</BarChart></ResponsiveContainer></section>;
}
