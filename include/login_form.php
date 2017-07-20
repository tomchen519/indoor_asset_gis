<?php
// Written by Tom Chen
session_start();
include('./database.php');

$checkInput = array();

if (isset($_POST)) {
  if (strlen($input_username = validate_input($_POST['username'])) == 0) {
    $error['username'] = "Name is required.";
  } else {
    $checkInput['username'] = $input_username;
  }
  if (strlen($input_password = validate_input($_POST['password'])) == 0) {
    $error['password'] = "Password is required.";
  }else {
    $checkInput['password'] = $input_password;
  }
  if (count($checkInput) == 2) {
    $database = new DataBase();
    try {
      $database->query('SELECT username, password, role FROM users WHERE username= :username');
      $database->bind(':username', $input_username);
      if (count($row = $database->result_one()) > 0) {
        $pass_hash = $row['password'];
        if ($pass_hash == crypt($input_password, $pass_hash)) {
          $_SESSION['userName'] = $input_username;
          $_SESSION['userRole'] = $row['role'];
          $_SESSION['password'] = $row['password'];
          echo json_encode($_SESSION);
        } else {
          echo json_encode(array("error"=>"User Name and Password do not match"));
        }
      }
    } catch (Exception $e) {
      print_r($e);
    }
  }
}
exit();
?>
