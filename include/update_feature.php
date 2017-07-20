<?php
// Written by Tom Chen

if (isset($_POST)) {

  // Initiate cURL session
  //////////////////////   DO NOT MODIFY THIS SECTION //////////////////////////////
  $service = "https://cartographicmedia.ddns.net/geoserver/";
  $request = "wfs"; // to add a new workspace
  $url = $service . $request;
  $ch = curl_init($url);
  //////////////////////////////////////////////////////////////////////////////////

  // Optional settings for debugging
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //option to return string
  curl_setopt($ch, CURLOPT_VERBOSE, true);

  //Required POST request settings
  curl_setopt($ch, CURLOPT_POST, true);
//  $passwordStr = "admin01:indoor123";
//  curl_setopt($ch, CURLOPT_USERPWD, $passwordStr);

  //POST data
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/xml", "23cb8xMEUHYV: admin"));

  // UPDATE FUNCTION
  if ($_POST["action"]=="update") {
    $xmlStr = '<wfs:Transaction service="WFS" version="1.0.0" xmlns:indoor_assets="http://cartographicmedia.ddns.net/indoor_assets" xmlns:ogc="http://www.opengis.net/ogc" xmlns:wfs="http://www.opengis.net/wfs" xmlns:gml="http://www.opengis.net/gml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.opengis.net/wfs http://schemas.opengis.net/wfs/1.0.0/WFS-transaction.xsd"><wfs:Update typeName="indoor_assets:assets">';
    unset($_POST["action"]);
    // Loop through feature properties
    foreach ($_POST as $key=>$value) {
      if($key == 'fid') {
        $fid = $_POST[$key];
      } else {
        $xmlStr .= '<wfs:Property><wfs:Name>' . $key . '</wfs:Name>';
        $xmlStr .= '<wfs:Value>' . $value . '</wfs:Value></wfs:Property>';
      }
    // End loop
    }

  // Add to string to filter which asset to update
    $xmlStr .= '<ogc:Filter>'
    .'<ogc:FeatureId fid="' . $fid . '"/>'
    .'</ogc:Filter>'
    .'</wfs:Update>'
    .'</wfs:Transaction>';
  // END UPDATE FUNCTION

  // INSERT FUNCTION
  } elseif ($_POST["action"]=="insert") {
    unset($_POST["action"]);
    $xmlStr = '<wfs:Transaction service="WFS" version="1.0.0"
      xmlns:wfs="http://www.opengis.net/wfs"
      xmlns:indoor_assets="http://cartographicmedia.ddns.net/indoor_assets"
      xmlns:gml="http://www.opengis.net/gml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://www.opengis.net/wfs http://schemas.opengis.net/wfs/1.0.0/WFS-transaction.xsd http://cartographicmedia.ddns.net/indoor_assets /geoserver/wfs/DescribeFeatureType?typename=indoor_assets:assets">';
    $xmlStr .= '<wfs:Insert>'
      .'<indoor_assets:assets>';

    // Loop through feature properties
    foreach ($_POST as $key=>$value) {
      $xmlStr .= '<indoor_assets:' . $key . '>' . $value . '</indoor_assets:' . $key . '>';
    }
    // End loop

    $xmlStr .= '</indoor_assets:assets>'
      .'</wfs:Insert></wfs:Transaction>';
  // END INSERT FUNCTION

  // DELETE FUNCTION
} elseif ($_POST["action"]=="delete" && $_POST["fid"]) {
    unset($_POST["action"]);
    $xmlStr = '<wfs:Transaction service="WFS" version="1.0.0"
      xmlns:ogc="http://www.opengis.net/ogc"
      xmlns:wfs="http://www.opengis.net/wfs"
	    xmlns:gml="http://www.opengis.net/gml"
      xmlns:indoor_assets="http://cartographicmedia.ddns.net/indoor_assets">
      <wfs:Delete typeName="indoor_assets:assets">
      <ogc:Filter>
      <ogc:FeatureId fid="' . $_POST["fid"] . '"/>
      </ogc:Filter>
      </wfs:Delete>
      </wfs:Transaction>';

} else {
  print "No actions were designated";
}

  curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlStr);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch); // Execute the curl request
  $info = curl_getinfo($ch);
  print_r($result);

  //POST return code
  if ($info["http_code"]==200) {
    echo "Update completed";
  } else {
    echo "Update failed";
  }
  curl_close($ch);
}
exit();
?>
