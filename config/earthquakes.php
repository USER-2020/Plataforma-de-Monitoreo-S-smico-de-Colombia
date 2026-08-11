<?php

return [
    'provider' => env('EARTHQUAKE_PROVIDER', 'emsc'), 'sync_days' => (int) env('EARTHQUAKE_SYNC_DAYS', 7), 'nearby_radius_km' => (int) env('EARTHQUAKE_NEARBY_RADIUS', 150),
    'emsc_websocket_url' => env('EMSC_WEBSOCKET_URL', 'wss://www.seismicportal.eu/standing_order/websocket'),
    'dane_municipalities_url' => env('DANE_MUNICIPALITIES_URL', 'https://geoportal.dane.gov.co/mparcgis/rest/services/Divipola/Serv_DIVIPOLA_MGN_2025/FeatureServer/317'),
    'providers' => ['emsc' => ['url' => env('EARTHQUAKE_EMSC_URL', 'https://www.seismicportal.eu/fdsnws/event/1/query')], 'usgs' => ['url' => env('EARTHQUAKE_USGS_URL', 'https://earthquake.usgs.gov/fdsnws/event/1/query')], 'sgc' => ['url' => env('EARTHQUAKE_SGC_URL', 'https://geoportal.sgc.gov.co/arcgis/rest/services/catalogo_sismos/catalogo_de_sismos_2/MapServer/0/query'), 'catalog_url' => 'https://geoportal.sgc.gov.co/arcgis/rest/services/catalogo_sismos/catalogo_de_sismos_2/MapServer/0']],
    'depth' => ['shallow' => [0, 70], 'intermediate' => [70, 300], 'deep' => [300, null]],
    'departments' => ['Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bolívar', 'Boyacá', 'Caldas', 'Caquetá', 'Casanare', 'Cauca', 'Cesar', 'Chocó', 'Córdoba', 'Cundinamarca', 'Guainía', 'Guaviare', 'Huila', 'La Guajira', 'Magdalena', 'Meta', 'Nariño', 'Norte de Santander', 'Putumayo', 'Quindío', 'Risaralda', 'San Andrés y Providencia', 'Santander', 'Sucre', 'Tolima', 'Valle del Cauca', 'Vaupés', 'Vichada'],
];
