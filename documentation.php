<!DOCTYPE html>
<!-- // Written by Tom Chen -->
<html lang="en">
<head>
  <meta name="author" content="Tom Chen">
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
  <meta name="description" content="Indoor asset management and mapping application">
  <meta name="keywords" content="GIS, Indoor, Enterprise, Asset Management, Open Source">
  <link rel="shortcut icon" type="image/png" href="./images/asset_management.png">
  <title>About</title>

  <!-- css section -->
  <link href='https://fonts.googleapis.com/css?family=Ubuntu:400,700' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="./css/bootstrap.min.css">
  <link href='./css/documentation.css' rel='stylesheet' type='text/css'>
  <script src="./js/jquery-3.1.1.min.js"></script>
  <script src="./js/bootstrap.js"></script>

</head>
<body>
  <div class="container-fluid">
    <?php include_once("./include/navbar.php") ?>
    <!-- TITLE -->
    <div class="row">
      <div class="content">
      </div>
    </div>

    <!-- About -->
    <div class="row">
      <h4 class="section_title" id="about">About</h4>

      <div class="section_body">
        This web GIS (Geographic Information System) application is the result of a graudate project for the Master of Science Program in GIS for the Geography Department at California State University, Northridge. The intent of the project is to create an enterprise GIS for the purpose of indoor asset management and mapping using only open source software. The front-end consists of codes written in HTML/CSS/JavaScript, and the back-end is created from integrations of NGINX, PostgreSQL/PostGIS, GeoServer, and PHP.
      </div>
    </div>

    <!-- Documentation -->
    <div class="row">
      <h4 class="section_title" id="instructions">Documentation</h4>
      <div class="section_body">
        <div id="screenshot" class="carousel slide" data-ride="carousel">
          <!-- Indicators -->
          <ol class="carousel-indicators">
            <li data-target="#screenshot" data-slide-to="0" class="active"></li>
            <li data-target="#screenshot" data-slide-to="1"></li>
            <li data-target="#screenshot" data-slide-to="2"></li>
            <li data-target="#screenshot" data-slide-to="3"></li>
            <li data-target="#screenshot" data-slide-to="4"></li>
            <li data-target="#screenshot" data-slide-to="5"></li>
            <li data-target="#screenshot" data-slide-to="6"></li>
            <li data-target="#screenshot" data-slide-to="7"></li>
          </ol>
          <!-- Wrapper for slides -->
          <div class="carousel-inner">
            <div class="item active"> <!-- 0 -->
              <img src="./images/admin_panel.jpg" alt="Indoor Assets Mapping and Management Interface">
              <div class="carousel-caption">
                <h3>Indoor Asset Mapping and Management Interface</h3>
                <p>Mapping interface to explore buildings and select rooms to view the indoor assets</p>
              </div>
            </div>
            <div class="item"> <!-- 1 -->
              <img src="./images/layer_control.jpg" alt="Map Controls">
              <div class="carousel-caption">
                <h3>Map Zoom and Floor Layer Controls</h3>
                <p>Map can be zoomed in or out and floor views can be changed using the control buttons</p>
              </div>
            </div>
            <div class="item"> <!-- 2 -->
              <img src="./images/select_room_admin.jpg" alt="List of Assets for Selected Room">
              <div class="carousel-caption">
                <h3>List of Assets for Selected Room</h3>
                <p>Clicking on a room brings up a list of assets associated with that room</p>
              </div>
            </div>
            <div class="item"> <!-- 3 -->
              <img src="./images/query.jpg" alt="Query Assets Based on Attributes">
              <div class="carousel-caption">
                <h3>Querying for Assets Based on Attributes</h3>
                <p>Users can query for assets based on a combination of attributes. The rooms with matching assets will be highlighted</p>
              </div>
            </div>
            <div class="item"> <!-- 4 -->
              <img src="./images/admin_editor_select_diff.jpg" alt="Differences between administrative and non-administrative users">
              <div class="carousel-caption">
                <h3>Administrative and Non-Administrative Users</h3>
                <p>Administrative users are given more functions to manipulate data</p>
              </div>
            </div>
            <div class="item"> <!-- 5 -->
              <img src="./images/editing_interface.jpg" alt="Differences between administrative and non-administrative users">
              <div class="carousel-caption">
                <h3>Administrative and Non-Administrative Users</h3>
                <p>Administrative users are given more functions to manipulate data</p>
              </div>
            </div>
            <div class="item"> <!-- 6 -->
              <img src="./images/data_management.jpg" alt="Manage Preset Values">
              <div class="carousel-caption">
                <h3>Management of Preset Values for Attributes</h3>
                <p>Administrators can determine what preset values should be used for specific attributes</p>
              </div>
            </div>
            <div class="item"> <!-- 7 -->
              <img src="./images/user_management.jpg" alt="Manage Users">
              <div class="carousel-caption">
                <h3>Management of Authorized Users</h3>
                <p>Administrators can add or delete users</p>
              </div>
            </div>
          </div>
          <!-- Left and right controls -->
          <a class="left carousel-control" href="#screenshot" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left"></span>
            <span class="sr-only">Previous</span>
          </a>
          <a class="right carousel-control" href="#screenshot" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right"></span>
            <span class="sr-only">Next</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Contact -->
    <div class="row">
      <h4 class="section_title" id="contact">Contact</h4>
      <div class="section_body">
        <div class="contact_title">Author: </div><div class="contact_value">Tom Chen</div>
        <div class="contact_title">Email: </div><div class="contact_value">tom.chen.78@my.csun.edu</div>
        <div class="contact_title">Advisor: </div><div class="contact_value">Dr. Soheil Boroushaki</div>
        <div class="contact_title">Digital Portfolio: </div><div class="contact_value"><a href="https://cartographicmedia.ddns.net" target="_blank">CartoGraphicMedia</a></div>
      </div>
    </div>
  </div>
</body>
