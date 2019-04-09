<?php

/*disclaimer, locked is called to run this program; comment it to disable the program*/
/*its just here so that when editing the file, no body can curl it when we are editing*/
/*
$lockmsg = array("type" => "addT", "error" => "addT is being edited and currently locked."); 
echo json_encode($lockmsg); 
*/

 locked();  /* ********************* program ******************** */ 

function locked() {
date_default_timezone_set("America/New_York");
$write = "page access " . date("Y-m-d h:i:sa") . "\n";
autolog($write); 
//lastupdate: 03/19/2019 8:13 PM

include 'dblogin_interface.php';

$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);
$write = "received data \n"; autolog($write); 
$write = print_r($decoder, true) . "\n"; autolog($write); 
/* testpoint 
$decoder = array("desc" => "testing addT false id", "rel" => "0", "ques" => array('0'
=> array('id' => '1001'), '1' => array('id' => '1002')));
$write = "sample input into addT\n"; autolog($write);
$write = print_r($decoder, true) . "\n"; autolog($write); 
*/
if ($decoder != null) { //ensures this page doesn't run a query  if no input is received  
  if (! $feedback = addExam($conn, $decoder)) { //calls the function getQUEST() ; 
    $error .= "backend getExam() failed."; 
    $report = array("type" => "addT", "error" => $error); 
    echo json_encode ($report); 
  } else {
    echo $feedback; 
  }  
} else {
  $error .= "backend received nothing.";
  $write = "backend received nothing.\n"; autolog($write);
  $report = array("type" => "addT", "error" => $error);
  echo json_encode ($report); 
  } //if decoder != null;   
} //locked

function addExam($conn, $decoder) {
  $release = $decoder['rel'];
  $testName = $decoder['desc'];
  $testName = addslashes($testName);
  $questions = $decoder['ques']; 

  $write = "checking each questionId to ensure they are valid\n"; autolog($write); 
  $errorcount = 0; 
  foreach ($questions as $a) {
       $write = "checking id: " . $a['id']  . "\n"; autolog($write); 
       $qIdnum = $a['id']; 
       if (! qIdcheck($conn, $qIdnum)) {
          $errorcount += 1; 
          $error .= "an invalid question Id was detected.";
          $write = $error . "\n"; autolog($write); 
      }
  }//foreach $questions as $a

  if ($errorcount > 0) {
       $write = "terminating program because of invalid question ids.\n"; autolog($write); 
       $error = "terminating program because of invalid question ids."; 
       $report = array("type" => "addT", "error" => $error); 
       return  json_encode ($report); 

   }//if $errorcount >0 
  
  /*
     foreach($questions as $q) {
     $arrayofIds = $q['Id'];    
     }
   */

  if (empty($testName)) {
    $error .= "TestName is NULL or Empty";
    $report = array("type" => "addT", "error" => $error);
    return json_encode($report); //terminate the program.
  }

  $sql1 = "SELECT * FROM Test"; 
  if ( ! $result1 = $conn->query($sql1)) { 
    $sqlerror = $conn->error; 
    $error .= "sql: " . $sqlerror . " "; 
  } else {   
    $id = $result1->num_rows; 
    $id += 1; //testid

  }//sql1 
  $sql2 = "INSERT INTO Test (Id, released, testName) VALUES ('$id', '$release', '$testName')"; 
  if ( ! $result1 = $conn->query($sql2)) { 
    $sqlerror2 = $conn->error; 
    $error .= "sql2: " . $sqlerror2 . " "; 
  } else { 
   foreach ($questions as $x) { 
      $qId = $x['id'];
      /* prints the ids here: */
      $write = $qId . "\n" ; autolog($write); 
      //ensure the qId is in the database, if not, get rid of it. 
      if (! qIdcheck($conn, $qId)) {
          $error .= "an invalid question Id was detected.";
          $write = $error . "\n"; autolog($write); 
	  break; 
      }
      $sql3 = "INSERT INTO QuestionStudentRelation (testId, questionId, testName) VALUES ('$id', '$qId', '$testName')"; 
      if ( ! $result3 = $conn->query($sql3)) { 
	$sqlerror3 = $conn->error; 
	$error .= "sql3: " . $sqlerror3 . " ";
      } else {
	//succesful insert into table 'QuestionStudentRelation'
      } 

    }//foreach questions  as $x

  }//sql2

  //obtain each question.
  $arrayofQuestions = array(); 
  foreach($questions as $y) { 
    $qId2 = $y['id'];
    $sql4 = " SELECT * FROM Question WHERE Id = '$qId2' ";      
    if ( ! $result4 = $conn->query($sql4)) { 
      $sqlerror4 = $conn->error; 
      $error .= "sql4: " . $sqlerror4 . " ";               
    } else {   
      while($row4 = mysqli_fetch_assoc($result4)) {
	$description = $row4['question'];
	$questionId = $row4['Id'];
	$difficulty = $row4['difficulty'];
	$topic = $row4['category']; 
	$temp = array("id" => $questionId, "desc" => $description, "topic" => $topic, "diff" => $difficulty); 
	array_push($arrayofQuestions, $temp); 
      }//while $row4 = msqyli($result4)
    }  
  }//foreach $question as $y

  if ($error === null) {
    $error = 0; 
  }
  $testName = stripslashes($testName);
  /* old format:  Mon Apr  1 15:53:03 EDT 2019 */ 
/*  $package = array("type" => "addT", "error" => $error, "id" => $id, "desc" => $testName,  "Rel" =>
  $release, "Sub" => "0", "ques" => $arrayofQuestions); */

  /* new format : Mon Apr  1 15:53:58 EDT 2019 */ 
  /* format */
  /* addT reply: {type, error, test} 
  test obj: {id, desc, rel, sub, ques[]} */ 

  $package = array('type'  => 'addT' , 'error'  => $error, "test" => array('id' => $id, 'desc' =>
  $testName, 'rel' => $release, 'sub' => '0', 'ques'  => $arrayofQuestions)/*test*/)/*type*/; 

  $write = "addT() function results: \n"; 
  $write .= print_r($package, true) . "\n"; autolog($write); 
  return json_encode($package); 

}//addexam ()

function qIdcheck($conn, $qId) {
    /*purpose: ensure that id is in the database, if not get rid of it.*/
    $write = "ensure that id is in the database. running qIdcheck()\n";
    autolog($write); 
    $write = "checking the database for " . $qId . "\n"; autolog($write); 
    $sql = "SELECT * FROM Question WHERE Id = '$qId' ";
    if (! $result = $conn->query($sql)) {
        $errorsql = $conn->error;
	$error .= "sql : " . $errorsql . " ";
    } else {
      
         $rowcount = $result->num_rows;
	 $write = "qIdcheck - number of rows " . $rowcount . "\n"; 
	 autolog($write); 
	 if ($rowcount < 1) {
             return 0; //invalid qid return false
	 } else {
             return 1; //valid id, return true 
	 }
    }
}//qIdcheck()

function autolog($input) {
  if (! $file = fopen('/afs/cad/u/w/b/wbv4/public_html/Middle/tracklogs/addT.txt', 'a')){
    echo ".txt failed to 'fopen' to write \n";
    return 0; 
  } else {

    if (! fwrite($file, $input)) {
      echo "autolog in MakeExam2.php  failed to write \n";
      return 0;
    }
    return 1;
  }
}//autolog()
?>
