<?php
$dbserver = "sql1.njit.edu";
$mySql_user = "rd248";
$mySql_password = "aZrVVjeCv";
$mySql_database = "rd248";

$conn =  mysqli_connect($dbserver, $mySql_user, $mySql_password, $mySql_database);
      if (!$conn) {
          $error .= "backend SQL: failed to connect"; 
          $report = Array("Type" => "Database", "Error" => $error);
          echo json_encode($report);   
      }

?>