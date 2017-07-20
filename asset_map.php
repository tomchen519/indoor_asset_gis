<?php
// Written by Tom Chen
session_start();
if (!isset($_SESSION['userRole'])) {
  header('Location: https://cartographicmedia.ddns.net/indoor_assets/');
  exit();
} else {
  $userRole = $_SESSION['userRole'];
  $userName = $_SESSION['userName'];
}
$pageTitle = "Indoor Asset Mapping";
$display = 'xs';
include_once('./include/header.php');
include_once('./include/map_sidebar.php');
?>
<script type="text/javascript">
var userRole = "<?php echo $userRole; ?>";
</script>
<!-- begin footer section -->
<?php
  include_once('./include/footer.php');
?>
