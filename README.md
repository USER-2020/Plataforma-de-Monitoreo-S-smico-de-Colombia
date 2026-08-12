# terracosismos

terracosismos es una plataforma Laravel 12 + Inertia + React para consultar, consolidar y visualizar actividad sísmica de Colombia. Consume varias redes sismológicas, evita duplicados entre proveedores, conserva los reportes originales y permite crear alertas personalizadas por correo.

Las fechas se almacenan en UTC y se presentan usando `America/Bogota`.

## Requisitos e instalación

- PHP 8.2 o superior.
- Composer.
- Node.js 20 o superior.
- MySQL 8. SQLite también puede utilizarse en pruebas.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

Configure MySQL mediante `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`. El mapa usa Leaflet y OpenStreetMap, por lo que no requiere una clave de Google Maps.

## Fuentes de información sísmica

La configuración recomendada es:

```env
EARTHQUAKE_PROVIDER=multi
EARTHQUAKE_SYNC_DAYS=7

EARTHQUAKE_SGC_URL=https://apicatalogador.sgc.gov.co/api/events/search/
EARTHQUAKE_SGC_SYNC_HOURS=48

EARTHQUAKE_EMSC_URL=https://www.seismicportal.eu/fdsnws/event/1/query
EMSC_WEBSOCKET_URL=wss://www.seismicportal.eu/standing_order/websocket

EARTHQUAKE_USGS_URL=https://earthquake.usgs.gov/fdsnws/event/1/query

EARTHQUAKE_GEOFON_URL=https://geofon.gfz.de/fdsnws/event/1/query
EARTHQUAKE_GEOFON_INTERVAL=5

EARTHQUAKE_ALERT_MAX_AGE_MINUTES=30
```

| Fuente | Frecuencia | Función |
|---|---:|---|
| SGC | 1 minuto | Fuente preferente para eventos locales de Colombia, incluidos sismos pequeños. |
| EMSC | 1 minuto y WebSocket opcional | Detección rápida y respaldo internacional. |
| USGS | 1 minuto | Confirmación y respaldo internacional. |
| GEOFON | 5 minutos | Confirmación adicional mediante FDSN. |

SGC tiene la mayor prioridad para los parámetros consolidados. EMSC, USGS y GEOFON tienen coberturas y umbrales de publicación distintos, por lo que no necesariamente reportan los microsismos detectados por la red colombiana.

## Sincronización multifuente

Ejecute una sincronización manual con:

```bash
php artisan earthquakes:sync
```

El comando consulta las fuentes configuradas y muestra cuántos reportes entregó cada una. Si una API falla, las demás continúan funcionando. La sincronización sólo falla completamente cuando ningún proveedor responde.

El scheduler ejecuta cada minuto:

```php
Schedule::command('earthquakes:sync')
    ->everyMinute()
    ->withoutOverlapping(5);

Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=60')
    ->everyMinute()
    ->withoutOverlapping(5);
```

## Consolidación y prevención de duplicados

Cada proveedor usa identificadores propios. Para evitar cuatro registros y varios correos por un mismo evento, terracosismos compara:

- Tiempo de origen: diferencia máxima de 90 segundos.
- Distancia entre epicentros: máximo 50 km.
- Diferencia de magnitud: máximo 0.8.

El evento consolidado se guarda en `earthquakes`. Cada observación original se conserva en `earthquake_source_reports` con su fuente, identificador, magnitud, coordenadas, profundidad, fecha y respuesta original.

Prioridad de actualización:

```text
SGC → EMSC → USGS → GEOFON
```

Una actualización de otra fuente se asocia al evento existente y no genera una segunda alerta. Los eventos importados con más de `EARTHQUAKE_ALERT_MAX_AGE_MINUTES` minutos se almacenan para el mapa y el historial, pero no envían correos retrospectivos.

## Ubicación administrativa con DANE

Las coordenadas pueden consultarse espacialmente contra la capa DIVIPOLA del Geoportal DANE:

```env
DANE_MUNICIPALITIES_URL=https://geoportal.dane.gov.co/mparcgis/rest/services/Divipola/Serv_DIVIPOLA_MGN_2025/FeatureServer/317
```

Cuando la consulta tiene respuesta se guardan municipio, departamento y códigos DIVIPOLA. Si DANE no responde, el evento sísmico se conserva sin perder sus datos principales.

## Desarrollo local

Una sola orden inicia Laravel, Vite, scheduler, cola, escucha EMSC y logs:

```bash
composer run dev
```

Procesos iniciados:

```text
server     php artisan serve
queue      php artisan queue:work
scheduler  php artisan schedule:work
emsc       php artisan earthquakes:listen-emsc
logs       php artisan pail
vite       npm run dev
```

Detenga todos los procesos con `Ctrl + C`. Después de modificar `.env` o configuración PHP, reinicie `composer run dev`.

## Actualización del mapa

EMSC puede recibirse mediante:

```bash
php artisan earthquakes:listen-emsc
```

El mensaje se envía a la cola y se procesa con `queue:work`. Cuando Laravel Reverb está configurado, `EarthquakeReceived` puede actualizar React mediante Echo casi inmediatamente.

En hosting compartido se recomienda:

```env
BROADCAST_CONNECTION=null
VITE_REVERB_ENABLED=false
```

Con Reverb desactivado, React consulta los datos actualizados cada 60 segundos. La latencia práctica será el tiempo de publicación del organismo más hasta un minuto de sincronización y hasta un minuto de refresco del navegador.

## Hostinger compartido

No ejecute `composer run dev`, `schedule:work`, `queue:work` permanente ni `reverb:start` en hosting compartido. Publique el código y cree un único cron cada minuto:

```bash
cd /home/USUARIO/domains/DOMINIO/public_html && /usr/bin/php artisan schedule:run >> storage/logs/cron.log 2>&1
```

Expresión cron:

```cron
* * * * *
```

Después de desplegar cambios:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan schedule:clear-cache
php artisan schedule:list
php artisan earthquakes:sync
php artisan earthquakes:status
```

Si `public/build` se compila en el servidor:

```bash
npm ci
npm run build
```

Si se sube ya compilado desde local, esos dos comandos pueden omitirse.

## Diagnóstico

Estado de actualización, último sismo y reportes por fuente:

```bash
php artisan earthquakes:status
```

Programación registrada:

```bash
php artisan schedule:list
php artisan schedule:run
```

Logs:

```bash
tail -n 100 storage/logs/cron.log
tail -n 100 storage/logs/laravel.log
```

Cola de notificaciones:

```bash
php artisan queue:failed
php artisan queue:retry all
```

## Funcionalidad

- Monitor público, mapa con marcadores pulsantes e historial sísmico.
- Detalle del evento y fuentes que lo reportaron.
- Estadísticas de actividad, alertas, correos y visitas consentidas.
- API `GET /api/earthquakes` con filtros de fecha, magnitud, departamento y radio.
- Alertas por correo para todas las magnitudes o una magnitud mínima y departamento.
- PWA instalable, caché sin conexión y banner de instalación compatible con el navegador.
- Consentimiento de privacidad mediante Klaro y auditoría de acciones consentidas.

## Pruebas

```bash
php artisan test
npm run build
```

Las pruebas utilizan SQLite y respuestas simuladas; no consumen los servicios públicos durante su ejecución.

## Arquitectura

- `app/Data`: DTO normalizado `EarthquakeData`.
- `app/Services/Earthquake`: proveedores, agregación, sincronización y deduplicación.
- `app/Models/Earthquake`: evento consolidado.
- `app/Models/EarthquakeSourceReport`: reportes originales por fuente.
- `app/Services/Geography`: resolución espacial mediante DANE.
- `resources/js`: páginas Inertia, mapa y componentes React.

Para agregar una fuente, implemente `EarthquakeProviderInterface`, normalice su respuesta a `EarthquakeData` y agréguela a `MultiEarthquakeProvider` en `AppServiceProvider`.

Los datos mostrados son preliminares e informativos. terracosismos no predice terremotos ni sustituye las comunicaciones oficiales del Servicio Geológico Colombiano o de las autoridades de gestión del riesgo.
