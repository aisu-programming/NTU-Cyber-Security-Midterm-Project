<?php

  $configs = include('config/config.php');

  $db = mysqli_connect($configs['host'],
                       $configs['username'],
                       $configs['password'],
                       $configs['dbname']);

  if (!$db) {
    header($_SERVER['SERVER_PROTOCOL'] . " 501");
    $aResult['error'] = "Connect Error ($db->connect_errno): $db->connect_error";
    exit;
  }

  $sql_command = "ALTER TABLE user ADD COLUMN login_turn INT(8) UNSIGNED NOT NULL DEFAULT 0 AFTER avatar;";
  $sql_result = $db->query($sql_command);

  // Query failed
  if ($sql_result === FALSE) {
    header($_SERVER['SERVER_PROTOCOL'] . " 501");
    echo "Query failed: $db->error<br/><br/>";
  }

  $sql_command = "CREATE TABLE number (
                    id INT(2) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                    name VARCHAR(20) NOT NULL UNIQUE,
                    value INT(6) UNSIGNED NOT NULL DEFAULT 0
                  )";
  $sql_result = $db->query($sql_command);

  // Query failed
  if ($sql_result === FALSE) {
    header($_SERVER['SERVER_PROTOCOL'] . " 501");
    echo "Query failed: $db->error<br/><br/>";
  }

  $sql_command = "INSERT INTO number (name) VALUES ('total_login_turn')";
  $sql_result = $db->query($sql_command);

  // Query failed
  if ($sql_result === FALSE) {
    header($_SERVER['SERVER_PROTOCOL'] . " 501");
    echo "Query failed: $db->error<br/><br/>";
  }

  echo "Succeed.";

  //Close the connection
  $db->close();

  exit;
?>