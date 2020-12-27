window.ZxMap = function(componentElement) {
    var id;
    var data;
    var init = function() {
        id = componentElement.dataset.id;
        if (data = zxMapLogics.getData(id)) {
            initLeafLet();
        }
    };
    var initLeafLet = function() {
        var map = L.map(componentElement);
        var markers = L.markerClusterGroup({
            spiderfyOnMaxZoom: false,
            showCoverageOnHover: false,
            iconCreateFunction: function(cluster) {
                var amount = 0;
                if (markers = cluster.getAllChildMarkers()) {
                    for (var i = 0; i < markers.length; i++) {
                        amount += markers[i].zxAmount;
                    }
                }
                return L.divIcon({html: '<div class="map_icon_cluster"><div class="map_icon_cluster_inner">' + amount + '</div></div>'});
            },
        });
        // Add OSM tile leayer to the Leaflet map.
        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);
        for (var i = 0; i < data.markers.length; i++) {
            var myIcon = L.divIcon({
                className: 'map_icon',
                iconSize: [30, 38],
                iconAnchor: [15, 19],
                html: '<div class="map_icon_amount">' + data.markers[i].amount + '</div>',
            });
            var marker = L.marker([data.markers[i].latitude, data.markers[i].longitude], {icon: myIcon});
            marker.zxAmount = data.markers[i].amount;
            marker.bindPopup(data.markers[i].title).openPopup();
            marker.on('mouseover', function(e) {
                this.openPopup();
            });
            marker.on('mouseout', function(e) {
                this.closePopup();
            });
            var url = data.markers[i].url;
            var clickHandler = function(url) {
                return function(e) {
                    document.location.href = url;
                };
            }(url);
            marker.on('click', clickHandler);
            markers.addLayer(marker);
        }
        map.addLayer(markers);

        map.setView([data.startLatitude, data.startLongitude], data.zoom);

    };
    init();
};