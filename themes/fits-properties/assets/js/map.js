(function () {
    var mapEl = document.getElementById('fp-property-map');

    if (!mapEl || typeof L === 'undefined') {
        return;
    }

    var lat = parseFloat(mapEl.getAttribute('data-lat'));
    var lng = parseFloat(mapEl.getAttribute('data-lng'));

    if (isNaN(lat) || isNaN(lng)) {
        return;
    }

    var map = L.map(mapEl, { scrollWheelZoom: false }).setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    L.marker([lat, lng]).addTo(map);
})();
