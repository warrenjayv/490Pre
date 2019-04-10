<?php

include 'dblogin_interface.php';

$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);

//$decoder = array('qId' => '1', 'feedback' => 'testcase add(2,3)=5 failed', 'points' => '0');
if (! $feedback = updatePoints($conn, $decoder)) { //calls the function getQUEST() ; 
      $error = "backend updatePoints() failed."; 
      $report = array("type" => "updatePoints", "error" => $error); 
      echo json_encode ($report); //terminate 
  } else {
        echo $feedback; 
  }

function updatePoints($conn, $decoder) {
      $testId = $decoder['testId'];
      $qId = $decoder['qId'];
//$testCase = $decoder['testCase'];
      $feedback = $decoder['feedback'];
      $points = $decoder['points'];
      $feedback .= ", ";
//we need to get the last feedback from the prior loop.
      $sql1 = "SELECT * FROM QuestionStudentRelation WHERE questionID = '$qId' AND testId = '$testId' "; 
      if ( ! $result1 = $conn->query($sql1)) {
          $sqlerror1 = $conn->error;
          $error .= "sql1: " . $sqlerror1 . " ";
      } else {
          while($row1 = mysqli_fetch_assoc($result1)) {
           //obtain the last feedback from prior loop.
           $lastfeedBack = $row1['feedback'];
           //update 
           $feedback .= $lastfeedBack; 
          }  
      } //result1
      
//UPDATE `rd248`.`QuestionStudentRelation` SET `feedback` = 'ok', `points` = '5' WHERE `QuestionStudentRelation`.`Id` = 1;
      $sql2 = "UPDATE rd248.QuestionStudentRelation SET feedback = '$feedback', points = '$points' WHERE QuestionStudentRelation.questionId = '$qId' AND QuestionStudentRelation.testId = '$testId' ";
      if ( ! $result2 = $conn->query($sql2)) { 
             $sqlerror2 = $conn->error; 
             $error .= "sql: " . $sqlerror2 . " "; 
      } else { 
          
      }//result2
     if ($error === null) {
                 $error = 0; 
     }
    
     $package = array("type" => "updatePoints", "error" => $error, "testId" => $testId, "qId" => $qId, "feedback" => $feedback, 'points' => $points);
     return json_encode($package);
     
} //updatePoints
?>
