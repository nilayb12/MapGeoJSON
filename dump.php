<?php include_once('modules/dbConfig.php');
// $query = "SELECT ST_AsGeoJSON(geom) FROM india_boundary.village ORDER BY gid ASC LIMIT 10";
$query = "SELECT json_build_object(
        'type', 'FeatureCollection',
        'features', json_agg(features.feature)
        ) FROM (SELECT json_build_object(
        'type', 'Feature',
        'properties', to_jsonb(inputs) - 'geom', -- 'gid',
        'geometry', ST_AsGeoJSON(geom)::json
        ) AS feature FROM (
        SELECT villagenam, talukname, districtna, statename, geom FROM india_boundary.village ORDER BY gid ASC LIMIT 10000
        ) inputs) features";
$result = pg_query($db, $query);
// print_r(pg_fetch_all($result));
while ($data = pg_fetch_array($result)) {
    print_r($data[0]);
}
?>