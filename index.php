<?php include_once('modules/dbConfig.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeoJSON View</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha256-PI8n5gCcz9cQqQXm3PEtDuPG8qx9oFsFctPg0S5zb8g=" crossorigin="anonymous">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha256-CDOy6cOibCWEdsRiZuaHf8dSGGJRYuBGC+mjoJimHGw=" crossorigin="anonymous"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.6.0/mapbox-gl.js"></script>
    <div id="map"></div>
    <div class="card float-start mt-1 ms-1 z-3" id="mapOptions">
        <div class="card-header" id="srcData">
            <span class="">
                <h6 class="card-title">Map Data</h6>
            </span>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="srcSel" id="srcSel1" value="India_District">
                <label for="srcSel1">India-District</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="srcSel" id="srcSel2" value="R4G_State">
                <label for="srcSel2">R4G-State</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="srcSel" id="srcSel3" value="Village_Boundary">
                <label for="srcSel3">Village-Boundary</label>
            </div>
        </div>
        <div class="card-footer d-none" id="indDistLayerSel">
            <span class="d-flex">
                <h6 class="card-title">Choose Layer</h6>
            </span>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="indLayerSel" id="indLayerSel1" value="STATECODE">
                <label for="indLayerSel1">Circle</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="indLayerSel" id="indLayerSel2" value="DISTRICTCO">
                <label for="indLayerSel2">City</label>
            </div>
        </div>
        <div class="card-footer d-none" id="vilgLayerSel">
            <span class="d-flex">
                <h6 class="card-title">Choose Layer</h6>
            </span>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="vilgLayerSel" id="vilgLayerSel1" value="villagenam">
                <label for="vilgLayerSel1">Village</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="vilgLayerSel" id="vilgLayerSel2" value="talukname">
                <label for="vilgLayerSel2">Taluk</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="vilgLayerSel" id="vilgLayerSel3" value="districtna">
                <label for="vilgLayerSel3">District</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="vilgLayerSel" id="vilgLayerSel4" value="statename">
                <label for="vilgLayerSel4">Circle</label>
            </div>
        </div>
        <div class="card-footer d-none" id="vilgInfoSel">
            <span class="d-flex">
                <h6 class="card-title">Choose Requisite Data</h6>
            </span>
            <div class="border rounded" style="max-height:200px; overflow-y:scroll;">
                <?php
                $query = "SELECT ordinal_position, column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'village'";
                $result = pg_query($db, $query);
                while ($data = pg_fetch_array($result)) {
                    echo ('<div class="form-check ms-1">' .
                        '<input class="form-check-input" type="checkbox" name="infoSel" id="infoSel' . ($data[0]) . '"' .
                        'value="' . ($data[1]) . '">' .
                        '<label for="infoSel' . ($data[0]) . '">' . ($data[1]) . '</label>' .
                        '</div>');
                }
                ?>
            </div>
            <div class="form-floating my-1">
                <input type="text" class="form-control" id="vilgCount" placeholder="">
                <label for="vilgCount">Total Visible Villages</label>
            </div>
            <button class="btn btn-sm btn-primary d-flex" id="updVilgData">Update Village Data</button>
        </div>
    </div>
    <script type="text/javascript">
        mapboxgl.accessToken = 'pk.eyJ1IjoibmlsYXlyaWwiLCJhIjoiY2x3NmhieTZqMW9sYTJqcGQ3Y2o2Mmd0eCJ9.8cNx9NZ2B0gEMRZVkvuXUg';
        const map = new mapboxgl.Map({
            container: 'map',
            projection: 'mercator',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [79.9338403201581, 23.1680847713300], // [lng, lat Jabalpur]
            zoom: 4,
            cooperativeGestures: false,
            attributionControl: true,
            boxZoom: true,
            doubleClickZoom: true,
            dragPan: true,
            dragRotate: true,
            interactive: true,
            keyboard: true,
            logoPosition: 'bottom-left',
            scrollZoom: true,
            trackResize: true
        });

        document.addEventListener("DOMContentLoaded", () => map.resize());
        map.on('style.load', () => {
            map.setFog({});
        });

        map.addControl(new mapboxgl.GeolocateControl({
            positionOptions: {
                enableHighAccuracy: true
            },
            trackUserLocation: true,
            showUserHeading: true,
            showAccuracyCircle: true,
            showUserLocation: true
        })
        ).addControl(new mapboxgl.NavigationControl({
            showCompass: true,
            showZoom: true,
            visualizePitch: true
        })
        ).addControl(new mapboxgl.FullscreenControl()
        ).addControl(new mapboxgl.ScaleControl()
        );

        $('#srcData').click(function () {
            $('#srcSel1').prop('checked') ? $('#indDistLayerSel').removeClass('d-none') : $('#indDistLayerSel').addClass('d-none');
            $('#srcSel3').prop('checked') ? $('#vilgLayerSel, #vilgInfoSel').removeClass('d-none') : $('#vilgLayerSel, #vilgInfoSel').addClass('d-none');
        });

        let Village_Boundary = <?php
        // $query = "SELECT ST_AsGeoJSON(geom) FROM india_boundary.village ORDER BY gid ASC LIMIT 10";
        $query = "SELECT json_build_object(
        'type', 'FeatureCollection',
        'features', json_agg(features.feature)
        ) FROM (SELECT json_build_object(
        'type', 'Feature',
        'properties', to_jsonb(inputs) - 'geom', -- 'gid',
        'geometry', ST_AsGeoJSON(geom)::json
        ) AS feature FROM (
        SELECT villagenam, talukname, districtna, statename, geom FROM india_boundary.village ORDER BY gid ASC LIMIT 1000
        ) inputs) features";
        $result = pg_query($db, $query);
        // print_r(pg_fetch_all($result));
        while ($data = pg_fetch_array($result)) {
            print_r($data[0]);
        }
        ?>;

        let indLayerFilter = '';
        $('input[name="indLayerSel"]').click(function () {
            indLayerFilter = $('input[name="indLayerSel"]:checked').val();
        });
        let vilgLayerFilter = '';
        $('input[name="vilgLayerSel"]').click(function () {
            vilgLayerFilter = $('input[name="vilgLayerSel"]:checked').val();
        });

        let geojsonSrc = '';
        $('input[name="srcSel"]').click(function () {
            map.removeLayer(geojsonSrc).removeLayer('Circles-Highlighted').removeLayer('Village-Highlighted');
            geojsonSrc = $('input[name="srcSel"]:checked').val();
            map.addLayer({
                'id': geojsonSrc,
                'type': 'fill',
                'source': geojsonSrc,
                'paint': {
                    'fill-outline-color': 'rgba(0,0,0,0.1)',
                    'fill-color': 'rgba(0,0,0,0.1)'
                }
            }).addLayer({
                ...(geojsonSrc == 'India_District') && {
                    'id': 'Circles-Highlighted',
                    'source': geojsonSrc,
                    'filter': ['in', indLayerFilter, '']
                },
                ...(geojsonSrc == 'Village_Boundary') && {
                    'id': 'Village-Highlighted',
                    'source': geojsonSrc,
                    'filter': ['in', vilgLayerFilter, '']
                },
                'type': 'fill',
                'paint': {
                    'fill-outline-color': '#484896',
                    'fill-color': '#6e599f',
                    'fill-opacity': 0.75
                }
            })
            map.on('click', geojsonSrc, (e) => {
                let geoDataRow = '';
                for (var geoProp in e.features[0].properties) {
                    // if (e.features[0].properties.hasOwnProperty(x)) {
                    geoDataRow += '<tr><td>' + geoProp + '</td><td>' + e.features[0].properties[geoProp] + '</td></tr>';
                    // }
                }
                new mapboxgl.Popup({
                    closeButton: true,
                    closeOnClick: true,
                    closeOnMove: false,
                    maxWidth: '300px'
                }).setLngLat(e.lngLat)
                    .setHTML('<div class="card"><div class="card-header"><h5 class="card-title">Details</h5></div>' +
                        '<table class="card-body table table-sm table-bordered table-striped table-hover"><tbody style="max-height:300px; overflow-y:scroll; display:block">' + geoDataRow + '</tbody></table></div>')
                    .addTo(map);
            });
        });

        map.on('load', async () => {
            map.addSource('India_District', {
                type: 'geojson',
                data: 'GeoJSON/India_District.geojson' //URL, JSON, Var
            });
            map.addSource('R4G_State', {
                type: 'geojson',
                data: 'GeoJSON/R4G_State.geojson' //URL, JSON, Var
            });
            map.addSource('Village_Boundary', {
                type: 'geojson',
                // buffer: 512,
                data: Village_Boundary //URL, JSON, Var
            });
        });

        map.on('click', (e) => {
            // Set `bbox` as 5px reactangle area around clicked point.
            const bbox = [
                [e.point.x - 5, e.point.y - 5],
                [e.point.x + 5, e.point.y + 5]
            ];
            // Find features intersecting the bounding box.
            const selectedFeatures = map.queryRenderedFeatures(bbox, {
                layers: [geojsonSrc]
            });
            indDistObj = selectedFeatures.map((feature) => feature.properties[indLayerFilter]);
            villageObj = selectedFeatures.map((feature) => feature.properties[vilgLayerFilter]);

            map.setFilter('Circles-Highlighted', ['in', indLayerFilter, ...indDistObj]);
            map.setFilter('Village-Highlighted', ['in', vilgLayerFilter, ...villageObj]);
        });

        $('#updVilgData').click(function () {
            $.ajax({
                url: "index.php",
                method: "POST",
                data: { vilgCount: $('#vilgCount').val() },
                success: function (data) { $('#vilgCount').html(data); }
            });
            Village_Boundary = <?php
            // $query = "SELECT ST_AsGeoJSON(geom) FROM india_boundary.village ORDER BY gid ASC LIMIT 10";
            $query = "SELECT json_build_object(
            'type', 'FeatureCollection',
            'features', json_agg(features.feature)
            ) FROM (SELECT json_build_object(
            'type', 'Feature',
            'properties', to_jsonb(inputs) - 'geom', -- 'gid',
            'geometry', ST_AsGeoJSON(geom)::json
            ) AS feature FROM (
            SELECT villagenam, talukname, districtna, statename, geom FROM india_boundary.village ORDER BY gid ASC LIMIT $1
            ) inputs) features";
            $val = @$_POST['vilgCount'];
            $result = pg_query_params($db, $query, array(10000));
            // $result = pg_query($db, $query);
            // print_r(pg_fetch_all($result));
            while ($data = pg_fetch_array($result)) {
                print_r($data[0]);
            }
            ?>;
            map.getSource('Village_Boundary').setData(Village_Boundary);
        });
    </script>
</body>

</html>