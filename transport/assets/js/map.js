let mapInstance = null;
function initMap(lat=14.7167, lng=-17.4677, zoom=13) {
    if(mapInstance) return mapInstance;
    mapInstance = L.map('map').setView([lat, lng], zoom);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(mapInstance);
    return mapInstance;
}
