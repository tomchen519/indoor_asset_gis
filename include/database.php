<?php
// Written by Tom Chen
define("db_driver", 'pgsql');
define("db_host", 'localhost');
define("db_port", '5432');
define("db_name", 'XXXXXX'); // Replace XXXXXX with database name
define("db_user", 'XXXXXX'); // Replace XXXXXX with name of database owner/administrator
define("db_password", 'XXXXXX'); // Replace XXXXXX with password of owner/administrator
class DataBase {
  private $driver = db_driver;
  private $host = db_host;
  private $port = db_port;
  private $user = db_user;
  private $password = db_password;
  private $database = db_name;
  private $dbh;
  private $error;
  private $stmt;

  // database object constructor
  public function __construct() {
    $dsn = $this->driver . ':host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->database;
    $option = array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    );
    try{
      $this->dbh = new PDO($dsn, $this->user, $this->password, $option);
    }
    catch(PDOException $e){
      $this->error = $e->getMessage();
    }
  }

  // return status of database connection
  public function getStatus() {
    return $this->dbh->getAttribute(PDO::ATTR_CONNECTION_STATUS);
  }

  // prepare query statement
  public function query($query){
    $this->stmt = $this->dbh->prepare($query);
  }

  // bind value to query statement
  public function bind($param, $value, $type = null){
    if (is_null($type)){
          $type = PDO::PARAM_STR;
      }
    $this->stmt->bindValue($param, $value, $type);
  }

  // execute query statement
  public function execute(){
    return $this->stmt->execute();
  }

  // return all results from query
  public function result_all(){
    $this->execute();
    return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // return first result from query
  public function result_one(){
    $this->execute();
    return $this->stmt->fetch(PDO::FETCH_ASSOC);
  }

  // return row count of query response
  public function row_count(){
    return $this->stmt->rowCount();
  }
}

// checks user login username and password
function checkUserPassword($userName, $userPassword) {
  $database = new DataBase();
  $database->query('SELECT username, password, role FROM users WHERE username= :username');
  $database->bind(':username', $userName);
  if (count($row = $database->result_one()) > 0) {
    $pass_hash = $row['password'];
    if ($pass_hash == crypt($userPassword, $pass_hash)) {
      return 1;
    } else {
      return 0;
    }
  }
}

if (isset($_POST['user_password'])) {
  $response = checkUserPassword($_POST['username'], $_POST['user_password']);
  if ($response==1) {
    echo json_encode(array('response'=>1));
  } else {
    echo json_encode(array('response'=>'Password is incorrect'));
  }
}

// query for distinct values from preset categories
function getDistinctAssetValues() {
  $tableName = array('asset_type'=>'asset_type', 'bldg'=>'bldg_name' , 'status'=>'status');
  $database = new DataBase();
  foreach($tableName as $table => $field) {
    $$field = null;
    $row = null;
    $query = 'SELECT DISTINCT ' . $field . ' FROM ' . $table . ' ORDER BY ' . $field;
    $database->query($query);
    $row = $database->result_all();
    $count = $database->row_count();
    foreach($row as $key=>$value) {
      foreach($value as $x=>$y) {
        $categories[] = array($x, $y);
      }
    }
  }
  return $categories;
}

// user management
function getUsers($userManageArray) {
  $user_array = null;
  $row = null;
  $database = new DataBase();
  // list all users
  if ($userManageArray['action'] == "getUsers") {
    $query = 'SELECT id, username, role from users ORDER BY role';
    $database->query($query);
    $row = $database->result_all();
    $count = $database->row_count();
    for ($i = 0; $i < $count; $i++) {
      $list_userID = $row[$i]['id'];
      $list_username = $row[$i]['username'];
      $list_userrole = $row[$i]['role'];
      $user_array[] = array($list_userID, $list_username, $list_userrole);
    }
    return $user_array;

  // delete user
  } elseif ($userManageArray['action'] == "deleteUser") {
    $del_id = $userManageArray['delete_user_id'];
    try {
      $database->query('DELETE from users WHERE id = :id');
      $database->bind(':id', $del_id);
      $database->result_one();
      $row = $database->row_count();
      if ($row > 0) {
        return 1;
      } else {
        return 0;
      }
    } catch (Exception $e) {
      return 0;
    }

  // add new user
  } elseif ($userManageArray['action'] == "addUser") {
    $add_username = $userManageArray['add_user_name'];
    $add_userrole = $userManageArray['add_user_role'];
    $add_password = ($add_userrole == 'admin' ? 'admin' : 'editor');
    $add_password = hashNewPassword($add_password);
    try {
      $database->query('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
      $database->bind(':username', $add_username);
      $database->bind(':password', $add_password);
      $database->bind(':role', $add_userrole);
      $database->result_one();
      $row = $database->row_count();
      if ($row > 0) {
        return 1;
      } else {
        return 0;
      }
    } catch (Exception $e) {
      return 0;
    }
  } elseif ($userManageArray['action'] == "updateUser") {
    $update_username = $userManageArray['username'];
    $update_password = $userManageArray['new_password'];
    try {
      $database->query('UPDATE users SET password = :password WHERE username = :username');
      $database->bind(':username', $update_username);
      $database->bind(':password', $update_password);
      $database->result_one();
      $row = $database->row_count();
      if ($row > 0) {
        return 1;
      } else {
        return 0;
      }
    } catch (Exception $e) {
      return 0;
    }
  }
}

// flow control for user management
if (isset($_POST['manageUsers'])) {
  if ($_POST['manageUsers']=='getUsers') {
    $userManageArray = array('action'=>'getUsers');
    $response = getUsers($userManageArray);
    if ($response) {
      echo json_encode(array($response));
    } else {
      echo json_encode(array('response'=>'error'));
    }
  } elseif ($_POST['manageUsers']=='deleteUser') {
    $delete_user_id = $_POST['deleteUser'];
    $userManageArray = array('action'=>'deleteUser', 'delete_user_id'=>$delete_user_id);
    $response = getUsers($userManageArray);
    echo json_encode(array('response'=>$response));

  } elseif ($_POST['manageUsers']=='addUser') {
    $new_username = $_POST['new_username'];
    $new_userrole = $_POST['new_userrole'];
    $userManageArray = array('action'=>'addUser', 'add_user_name'=>$new_username, 'add_user_role'=>$new_userrole);
    $response = getUsers($userManageArray);
    echo json_encode(array('insert'=>$response));
  }
}

// flow control for data management
if (isset($_POST['manageData'])) {
  $response = addCategory($_POST);
  echo json_encode(array('category_update'=>$response));
}

// add new or modify preset values
function addCategory($addCategoryArray) {
  $category_action = $addCategoryArray['category_action'];
  $category = $addCategoryArray['manageData'];
  $selected_category = $category_action . "_" . $category;
  if ($category == 'asset_type') {
    $table = 'asset_type';
    $field = 'asset_type';
  } elseif ($category == 'bldg_name') {
    $table = 'bldg';
    $field = 'bldg_name';
  } elseif ($category == 'status_type') {
    $table = 'status';
    $field = 'status';
  }
  $database = new DataBase();
  $row = null;
  $new_category_value = $addCategoryArray['category_value'];
  try {
    if ($category_action=="add") {
      $database->query('INSERT INTO ' . $table . '(' . $field . ') VALUES (:newValue)');
      $database->bind(':newValue', $new_category_value);
      $database->result_one();
      $row = $database->row_count();
    } elseif ($category_action=="edit") {
      $old_value = $addCategoryArray['old_value'];
      $database->query('UPDATE ' . $table . ' SET ' . $field . ' = :newValue WHERE ' . $field . ' = :oldValue');
      $database->bind(':newValue', $new_category_value);
      $database->bind(':oldValue', $old_value);
      $database->result_one();
      $row = $database->row_count();
    }
    if ($row > 0) {
      return 1;
    } else {
      return 0;
    }
  } catch (Exception $e) {
    print_r($e);
    return 0;
  }
}

// sanitize user inputs
function validate_input($input) {
  $input = trim($input);
  $input = stripslashes($input);
  $input = htmlspecialchars($input);
  return $input;
}

// hashing function for user passwords
function hashNewPassword($newPassword) {
  $salt = openssl_random_pseudo_bytes(22);
  $salt = bin2hex($salt);
  $salt = '$2y$10$' . strtr(base64_encode($salt), array('_' => '.', '~' => '/'));
  $pass_hash = crypt($newPassword, $salt);
  return $pass_hash;
}

if (isset($_POST['new_password'])) {
  $newPassword = $_POST['new_password'];
  $pass_hash = hashNewPassword($newPassword);
  $username = $_POST['username'];
  $userManageArray = array('action'=>'updateUser', 'new_password'=>$pass_hash, 'username'=>$username);
  $response = getUsers($userManageArray);
  echo json_encode(array('response'=>$response));
}
?>
