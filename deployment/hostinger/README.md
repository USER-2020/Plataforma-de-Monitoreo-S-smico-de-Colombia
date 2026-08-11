# Procesos en Hostinger

## Hosting compartido

Configure en hPanel > Avanzado > Tareas Cron una ejecución cada minuto. Reemplace la ruta:

```cron
* * * * * cd /home/USUARIO/domains/DOMINIO/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

El scheduler consulta EMSC FDSN, deduplica, enriquece con DANE y vacía los jobs pendientes cada minuto. Es la opción compatible cuando Hostinger no permite procesos permanentes. El frontend conserva polling como respaldo.

## Hostinger VPS

Un VPS sí permite WebSocket continuo. Copie `sismo-workers.conf` a `/etc/supervisor/conf.d/`, reemplace `USUARIO` y `DOMINIO`, y ejecute:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sismo-reverb sismo-emsc sismo-queue
```

Mantenga también el cron `schedule:run` para reconciliación. Configure un proxy HTTPS de Nginx hacia Reverb `127.0.0.1:8080` y use `REVERB_SCHEME=https` en producción.

## Despliegue

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
npm ci
npm run build
php artisan earthquakes:sync
```
