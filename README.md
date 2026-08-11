# terracosismos

terracosismos es un monolito Laravel 12 + Inertia + React para consultar, almacenar y visualizar actividad sísmica en Colombia. Conserva fechas en UTC y presenta horas en `America/Bogota`.

## Instalación

Requisitos: PHP 8.2+, Composer, Node 20+, MySQL 8 (SQLite sirve para desarrollo).

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Configure MySQL mediante `DB_CONNECTION=mysql`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`. El mapa utiliza Leaflet con mosaicos de OpenStreetMap y no necesita API key.

## Proveedores y sincronización

`EARTHQUAKE_PROVIDER=sgc` usa la capa pública oficial ArcGIS `catalogo_de_sismos_2/MapServer/0`, que declara geometría de puntos, consultas y salida GeoJSON. El contrato está encapsulado en `SgcEarthquakeProvider`; USGS queda disponible como alternativa con `EARTHQUAKE_PROVIDER=usgs`. La aplicación conserva siempre los últimos datos conocidos ante cualquier error externo.

```bash
php artisan earthquakes:sync
php artisan schedule:work
```

En producción, ejecute cada minuto: `* * * * * cd /ruta/app && php artisan schedule:run >> /dev/null 2>&1`. El scheduler usa `withoutOverlapping()`.

## Tiempo real: EMSC y Reverb

El comando persistente `earthquakes:listen-emsc` escucha el WebSocket oficial de SeismicPortal, filtra por el área de Colombia, normaliza, guarda con deduplicación y emite `earthquake.received` por el canal público `earthquakes`. React escucha ese canal con Laravel Echo y actualiza el mapa inmediatamente. La sincronización programada SGC/USGS permanece como reconciliación de respaldo.

Cada coordenada recibida se consulta espacialmente contra la capa oficial `Municipio (317)` de DIVIPOLA MGN 2025 del Geoportal DANE. Se guardan nombre y código DIVIPOLA del municipio y departamento. Los resultados se conservan 30 días en cache para reducir consultas al servicio público; si DANE está temporalmente fuera de línea, el evento sísmico se conserva sin ubicación administrativa.

En desarrollo abra tres procesos:

```bash
php artisan reverb:start
php artisan earthquakes:listen-emsc
php artisan queue:work
```

En producción administre esos tres procesos con Supervisor o systemd. Configure `REVERB_*`, `VITE_REVERB_*`, `BROADCAST_CONNECTION=reverb` y `EMSC_WEBSOCKET_URL`. Si cambia valores `VITE_*`, vuelva a ejecutar `npm run build`.

Las plantillas específicas para Hostinger están en `deployment/hostinger`: cron para hosting compartido y Supervisor para VPS.

## Funcionalidad

- Monitor público `/`, historial `/sismos`, detalle y estadísticas `/estadisticas`.
- API `GET /api/earthquakes` con `from`, `to`, `min_magnitude`, `max_magnitude`, `department`, `latitude`, `longitude` y `radius`.
- Proveedores desacoplados, DTO normalizado, prevención de duplicados y logs de sincronización.
- `/admin` protegido por middleware `auth`; el proyecto deja la zona preparada para conectarla al flujo de autenticación elegido.
- Polling del monitor cada 60 segundos, mapas y marcadores escalados por magnitud.
- Registro público de alertas por magnitud y departamento. Los eventos nuevos encolan correos mediante Laravel Notifications; configure `MAIL_*` y ejecute `php artisan queue:work` en producción.

## Pruebas

```bash
php artisan test
npm run build
```

Las pruebas usan SQLite y proveedores simulados; nunca requieren SGC ni USGS.

## Arquitectura

El dominio está en `app/Data`, `app/Models` y `app/Services/Earthquake`; los controladores sólo adaptan peticiones. Las vistas Inertia y componentes reutilizables viven en `resources/js`. Para agregar un proveedor, implemente `EarthquakeProviderInterface`, normalice a `EarthquakeData` y añádalo al binding de `AppServiceProvider`.
