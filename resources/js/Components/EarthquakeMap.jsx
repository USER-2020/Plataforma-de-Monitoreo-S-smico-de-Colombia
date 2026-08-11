import {Link} from '@inertiajs/react';
import L from 'leaflet';
import {MapContainer,Marker,Popup,TileLayer} from 'react-leaflet';
import {dateCO,getMagnitudeStyle} from '../lib/earthquakes';

function earthquakeIcon(event){
    const magnitude=Number(event.magnitude);
    const style=getMagnitudeStyle(magnitude);
    const size=Math.max(34,style.size);
    const speed=magnitude>=6?1.15:magnitude>=4?1.55:2.1;
    return L.divIcon({className:'earthquake-div-icon',iconSize:[size,size],iconAnchor:[size/2,size/2],popupAnchor:[0,-size/2],html:`<div class="quake-marker" style="--quake-color:${style.color};--quake-size:${size}px;--pulse-speed:${speed}s"><i class="quake-wave quake-wave-one"></i><i class="quake-wave quake-wave-two"></i><span>${magnitude.toFixed(1)}</span></div>`});
}

export default function EarthquakeMap({earthquakes=[],height=600}){
    return <MapContainer center={[4.5709,-74.2973]} zoom={6} scrollWheelZoom style={{height,width:'100%'}}>
        <TileLayer attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>' url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"/>
        {earthquakes.map(event=><Marker key={event.id} position={[Number(event.latitude),Number(event.longitude)]} icon={earthquakeIcon(event)} riseOnHover><Popup><div className="quake-popup"><strong>M {Number(event.magnitude).toFixed(1)}</strong><p>{event.municipality||event.place}</p><small>{event.department||'Colombia'} · {dateCO(event.occurred_at)} · {event.depth_km} km</small><Link href={`/sismos/${event.id}`}>Ver detalle →</Link></div></Popup></Marker>)}
    </MapContainer>
}
