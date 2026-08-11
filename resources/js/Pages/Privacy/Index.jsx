import {Head} from '@inertiajs/react';
import Layout from '../../Components/Layout';

export default function Index({cookies = []}) {
    return <Layout>
        <Head title="Privacidad y cookies"><meta name="description" content="Política de privacidad, cookies y auditoría de terracosismos."/></Head>
        <article className="panel max-w-4xl mx-auto p-6 md:p-10 prose-purple">
            <p className="label">TRANSPARENCIA</p><h1 className="text-3xl md:text-4xl font-black mt-2">Privacidad y cookies</h1>
            <p className="mt-4 text-purple-700">terracosismos utiliza cookies esenciales para seguridad y funcionamiento. Las métricas, auditoría de acciones y servicios externos permanecen desactivados hasta que otorgues consentimiento.</p>
            <h2 className="text-xl font-black mt-7">Datos de analítica y auditoría</h2>
            <p className="mt-2 text-purple-700">Con autorización podemos registrar páginas visitadas, clics en enlaces o botones, fecha, ruta, identificadores anónimos de navegador y sesión, y dirección IP cifrada. Nunca guardamos el contenido escrito en formularios, contraseñas ni valores sensibles.</p>
            <h2 className="text-xl font-black mt-7">Finalidad y conservación</h2>
            <p className="mt-2 text-purple-700">Los datos se usan para medir el funcionamiento, detectar fallos, prevenir abuso y mantener trazabilidad técnica. Debes establecer y aplicar una política interna de conservación acorde con la legislación aplicable.</p>
            <h2 className="text-xl font-black mt-7">Servicios externos</h2>
            <p className="mt-2 text-purple-700">El botón de Buy Me a Coffee se muestra como enlace de apoyo al proyecto y no depende de la preferencia de analítica. Las decisiones sobre medición y auditoría pueden modificarse en cualquier momento.</p>
            <h2 className="text-xl font-black mt-7">Inventario de cookies</h2>
            <div className="overflow-x-auto mt-3"><table className="w-full text-left text-sm"><thead><tr className="border-b border-purple-100"><th className="py-3 pr-4">Cookie</th><th className="py-3 pr-4">Finalidad</th><th className="py-3">Tipo</th></tr></thead><tbody>{cookies.map(cookie=><tr className="border-b border-purple-50" key={cookie.name}><td className="py-3 pr-4 font-bold">{cookie.name}</td><td className="py-3 pr-4">{cookie.description}</td><td className="py-3">{cookie.required?'Esencial':'Opcional'}</td></tr>)}</tbody></table></div>
            <button type="button" className="btn mt-7" onClick={() => window.openPrivacySettings?.()}>Configurar mis cookies</button>
        </article>
    </Layout>;
}
