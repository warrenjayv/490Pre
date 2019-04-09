<?php

$dbserver = "sql1.njit.edu";
$mySql_user = "rd248";
$mySql_password = "aZrVVjeCv";
$mySql_database = "rd248";

$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);


$conn =  mysqli_connect($dbserver, $mySql_user, $mySql_password, $mySql_database);
if (!$conn) {
       $report = "backend SQL: failed to connect "; 
       echo $report; 
} 

//test
$decoder = array("QIds" => array('2', '3', '4')); 
if (! $feedback = getAnswer($conn, $decoder)) {  
      $error = "backend getAnswer() failed."; 
      $report = array("Type" => "getAnswer", "Error" => $error); 
      echo json_encode ($report); 
  } else {
      echo $feedback; 
}

 
function getAnswers($conn, $decoder) {
      $arrayofTestCases = array();
      $arrayofAnswers = array();
      
      $questionIds = $decoder['QIds'];
      
      foreach($questionIds as $x) {
              $sql1 = " SELECT * FROM QuestionStudentRelation WHERE questionId = '$x' ";
              if ( ! $result1 = $conn->query($sql1)) { 
                       $sqlerror1 = $conn->error; 
                       $error .= "sql1: " . $sqlerror1 . " "; 
                } else {  
                       while($row1 = mysqli_fetch_assoc($result1)) { //obtain the questionIds
                               $temp = array('QId' => $x, 'Text' => $row1['userAnswer']); 
                               
                               //GET THE TESTCASES FOR THIS QID NOW!!! :
                               $sql2 =  " SELECT * FROM TestCases WHERE questionId = '$x' ";
                               if ( ! $result2 = $conn->query($sql2)) { 
                                       $sqlerror2 = $conn->error; 
                                       $error .= "sql2: " . $sqlerror2 . " "; 
                              } else {
                                   while($row2 = mysqli_fetch_assoc($result2)) {
                                       array_push($arrayofTestCases, $row2['testcases']); 

                                   }
                              } 
                              
                              $temp2 = array('Tests' => $arrayofTestCases);
                              $temp = array_merge($temp, $temp2);
                              array_push($arrayofAnswers, $temp);                             
                     }//while row1
    
                 }
        }
         
        if ($error === null) {
                    $error = 0; 
        }  
        $output = array('Type' => "GetAnswers", 'Error' => $error, "Answers" => $arrayofAnswers); 
               //create the json format. 
               $package = json_encode($output); 
               return $package; 
         
         
        
        return true; 
}//getAnswer() 

/*
1. get answers and testcases based on questionId
*/
?>