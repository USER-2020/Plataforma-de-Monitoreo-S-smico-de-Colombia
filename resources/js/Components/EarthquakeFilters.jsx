import {router} from '@inertiajs/react';
import {useState} from 'react';
import {magnitudeOptions} from '../lib/magnitudeOptions';

export default function EarthquakeFilters({departments=[],initial={}}){
    const [filters,setFilters]=useState(initial);
    const change=event=>setFilters({...filters,[event.target.name]:event.target.value});
    return <form onSubmit={event=>{event.preventDefault();router.get(location.pathname,filters,{preserveState:true})}} className="panel p-4 flex flex-wrap gap-3 items-end">
        <label><span className="label block mb-1">Buscar</span><input className="input" name="search" value={filters.search||''} onChange={change} placeholder="Municipio o lugar"/></label>
        <label><span className="label block mb-1">Magnitud</span><select className="input" name="min_magnitude" value={filters.min_magnitude||''} onChange={change}>{magnitudeOptions.map(option=><option key={option.label} value={option.value}>{option.label}</option>)}</select></label>
        <label><span className="label block mb-1">Departamento</span><select className="input" name="department" value={filters.department||''} onChange={change}><option value="">Todos los departamentos</option>{departments.map(department=><option key={department}>{department}</option>)}</select></label>
        <button className="btn">Aplicar filtros</button>
    </form>
}
