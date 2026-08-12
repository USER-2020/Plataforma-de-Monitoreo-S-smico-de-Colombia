<?php

return [
    'provider' => env('EARTHQUAKE_PROVIDER', 'multi'), 'sync_days' => (int) env('EARTHQUAKE_SYNC_DAYS', 7), 'nearby_radius_km' => (int) env('EARTHQUAKE_NEARBY_RADIUS', 150),
    'alert_max_age_minutes' => (int) env('EARTHQUAKE_ALERT_MAX_AGE_MINUTES', 30),
    'coverage' => ['min_lat' => -5, 'max_lat' => 15, 'min_lon' => -84, 'max_lon' => -66],
    'deduplication' => ['seconds' => 90, 'distance_km' => 50, 'magnitude_delta' => 0.8],
    'source_priority' => ['sgc' => 1, 'emsc' => 2, 'usgs' => 3, 'geofon' => 4],
    'emsc_websocket_url' => env('EMSC_WEBSOCKET_URL', 'wss://www.seismicportal.eu/standing_order/websocket'),
    'dane_municipalities_url' => env('DANE_MUNICIPALITIES_URL', 'https://geoportal.dane.gov.co/mparcgis/rest/services/Divipola/Serv_DIVIPOLA_MGN_2025/FeatureServer/317'),
    'providers' => ['emsc' => ['url' => env('EARTHQUAKE_EMSC_URL', 'https://www.seismicportal.eu/fdsnws/event/1/query')], 'usgs' => ['url' => env('EARTHQUAKE_USGS_URL', 'https://earthquake.usgs.gov/fdsnws/event/1/query')], 'sgc' => ['url' => env('EARTHQUAKE_SGC_URL', 'https://apicatalogador.sgc.gov.co/api/events/search/'), 'catalog_url' => 'https://www.sgc.gov.co/sismos', 'sync_hours' => (int) env('EARTHQUAKE_SGC_SYNC_HOURS', 48)], 'geofon' => ['url' => env('EARTHQUAKE_GEOFON_URL', 'https://geofon.gfz.de/fdsnws/event/1/query'), 'interval_minutes' => (int) env('EARTHQUAKE_GEOFON_INTERVAL', 5)]],
    'depth' => ['shallow' => [0, 70], 'intermediate' => [70, 300], 'deep' => [300, null]],
    'departments' => ['Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bolívar', 'Boyacá', 'Caldas', 'Caquetá', 'Casanare', 'Cauca', 'Cesar', 'Chocó', 'Córdoba', 'Cundinamarca', 'Guainía', 'Guaviare', 'Huila', 'La Guajira', 'Magdalena', 'Meta', 'Nariño', 'Norte de Santander', 'Putumayo', 'Quindío', 'Risaralda', 'San Andrés y Providencia', 'Santander', 'Sucre', 'Tolima', 'Valle del Cauca', 'Vaupés', 'Vichada'],
];
