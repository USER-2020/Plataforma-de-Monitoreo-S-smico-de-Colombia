export const getMagnitudeStyle=m=>m>=6?{color:'#5b0a76',size:54}:m>=5?{color:'#7e22ce',size:46}:m>=4?{color:'#9333ea',size:40}:m>=3?{color:'#a855f7',size:34}:m>=2?{color:'#c061e8',size:29}:{color:'#d8a2ed',size:24};
export const dateCO=d=>new Intl.DateTimeFormat('es-CO',{dateStyle:'medium',timeStyle:'short',timeZone:'America/Bogota'}).format(new Date(d));
export const ago=d=>{const min=Math.max(0,Math.floor((Date.now()-new Date(d))/60000));return min<1?'Ahora':min<60?`Hace ${min} min`:min<1440?`Hace ${Math.floor(min/60)} h`:`Hace ${Math.floor(min/1440)} d`;};
