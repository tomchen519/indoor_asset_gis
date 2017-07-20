<?php
// Written by Tom Chen
// Initiate cURL session
$host = "https://cartographicmedia.ddns.net/geoserver/";
//$host = "localhost/geoserver/";
$service = "wfs?service=WFS&version=2.0.0";
$request = "request=GetFeature";
$outputformat = "outputformat=json";
$datastore = "typenames=indoor_assets:";
$cql = "CQL_FILTER=";
$queryAssetURL = $host . $service . "&" . $request . "&" . $outputformat . "&sortBy=asset_type" . "&" . $datastore . "assets" . "&" . $cql;
if ($_POST["request"]=="fetch_data_layers") {
  $url = $host . "/rest/workspaces/indoor_assets/featuretypes.json";
} elseif ($_POST["request"]=="fetch_floor_layers") {
  $floorNumber = $_POST["floor"];
  $url = $host . $service . "&" . $request . "&" . $outputformat . "&" . $datastore . $floorNumber;
} elseif ($_POST["request"]=="get_assets_in_room" || $_POST["request"]=="query_rooms") {
  $url = $queryAssetURL . $_POST["url"];
} elseif ($_POST["request"]=="fetch_queried_rooms") {
  $floor = $_POST["floor"];
  $room = $_POST["room"];
  $url = $host . $service . "&" . $request . "&" . $outputformat . "&" . $datastore . $floor . "&" . $cql . $room;
}

$ch = curl_init();

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //option to return string
curl_setopt($ch, CURLOPT_VERBOSE, true);

$header = array();
$header[] = "XXXXXX: YYYYYY"; // replace XXXXXX with proxy header attribute and YYYYYY with user with privilege to perform OGC services
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_URL, $url);

$result = curl_exec($ch); // Execute the curl request
$info = curl_getinfo($ch);
curl_close($ch);
echo ($result);
?>
