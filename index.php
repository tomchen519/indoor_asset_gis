<?php
// Written by Tom Chen
session_start();
$pageTitle = "Indoor Asset Mapping Login"; $display = 'xs';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="author" content="Tom Chen">
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
  <meta name="description" content="Indoor asset management and mapping application">
  <meta name="keywords" content="GIS, Indoor, Enterprise, Asset Management, Open Source">
  <link rel="shortcut icon" type="image/png" href="./images/asset_management.png">
  <title> <?php echo isset($pageTitle) ? $pageTitle : 'Indoor Asset Mapping'?></title>

  <!-- css section -->
  <link rel="stylesheet" href="./css/bootstrap.min.css">
  <link href='https://fonts.googleapis.com/css?family=Ubuntu:400,700' rel='stylesheet' type='text/css'>
  <link href='./css/index.css' rel='stylesheet' type='text/css'>

  <!-- script section -->
  <script src="./js/jquery-3.1.1.min.js"></script>
  <script src="./js/bootstrap.js"></script>
  <script src="./js/index.js"></script>
</head>
<body>
  <div class="container-fluid">
    <?php include_once("./include/navbar.php");?>
    <div class="row"><br/><br/></div>   <!-- empty row -->
    <!-- being login section -->
    <div class="row">
      <div id="login_box">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">User Login</h3>
          </div>
          <div class="panel-body">
            <div>
              <!-- begin login form -->
              <form id="login_form" name="login_form" autocomplete="off" method="post">
                <div class="form-group">
                  <label for="username">User Name<span id="username_error" class="error">
                  </span></label>
                  <input type="text" id="username" class="form-control" name="username" placeholder="e.g. Joe Smith (Case sensitive)">
                </div>
                <div class="form-group">
                  <label for="password">Password<span id="password_error" class="error">
                  </span></label>
                  <input type="password" id="password" class="form-control" name="password" placeholder="Password">
                </div>
              </form>
              <button type="button" class="btn btn-default" form="login_form" id="submit_btn">Submit</button>
              <!-- end of login form -->
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- end login section -->
  </div>
<?php
  // footer section
  include_once('./include/footer.php');
?>
