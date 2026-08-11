import {getMagnitudeStyle} from '../lib/earthquakes';
export default function MagnitudeBadge({value,large=false}){const s=getMagnitudeStyle(Number(value));return <span className={`inline-grid place-items-center rounded-xl font-black text-white ${large?'w-24 h-24 text-4xl':'w-12 h-12 text-lg'}`} style={{background:s.color}}>{Number(value).toFixed(1)}</span>}
