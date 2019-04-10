<?php
date_default_timezone_set("America/New_York"); 

include 'dblogin_interface.php';
include 'autolog.php'; 
include 'targets.php'; 

//$target = '/afs/cad/u/w/b/wbv4/public_html/Middle/tracklogs/getT.txt'; 
$target = targetIs('getT'); 
$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);

$write  = "[ + ]  page accessed getT " . date("Y-m-d h:i:sa") . "\n"; 
$write .= "+ target file size of : " . $target . " = " . filesize($target) . "\n"; 
autolog($write, $target); 
if (filesize($target) >= 100000) {
	autoclear($target); 
	$write = "+ the log reached 10 mb; it has been cleared \n"; autolog($write, $target); 
}

if (! empty($decoder)) {
   $write = "data received...\n"; 
   $write .= print_r($decoder, true); autolog($write, $target); 

//$decoder = array("rels" => array("0", "1")); 
	if (! $feedback = getExam($conn, $decoder)) { //calls the function getQUEST() ; 
   		$error = "backend getQUEST() failed."; 
   		$report = array("type" => "getT", "error" => $error); 
  			 echo json_encode ($report); 
	} else {
   		$write = "running getExam()...output:\n"; 
   		$write .= print_r($feedback, true); autolog($write, $target); 
   		echo $feedback; 
 	}//if $feedback = getexam(); 	
} //if empty($decoder) 
else {
	$error = "decoder at backend of getT received empty or null"; 
	$write = $error . "\n"; autolog($write, $target); 
	$report = array('type' => 'getT', 'error' => $error); 
	echo json_encode($report); 
}

function getExam($conn, $decoder) { 
  
   $release = $decoder["rels"]; //array of rels
   $arrayofTests=array(); 

   foreach($release as $x) {
      //the sql statement below is necessary to filter those released/unreleased. 
      $sql= " SELECT * FROM Test WHERE released = '$x' ";
      if ( ! $result = $conn->query($sql)) { 
	 $sqlerror = $conn->error; 
	 $error .= "sql1 error : " . $sqlerror . " "; 
      } else {   //if theres nothing wrong, retrieve result1
	 while($row = mysqli_fetch_assoc($result)) {
	    $id = $row['Id'];
	    $sub = $row['sub'];
	    $aTest = testObject($conn, $id, $x, $sub);
	    array_push($arrayofTests, $aTest); 
	 }                      
      }

      /*
      //var_dump($arrayofTests); echo "<br><br>"; 
      foreach ($arrayofTests as $t) {
      var_dump($t);  echo "<br><br>";
      }
       */
   }//foreach release as x

   if($error == null)
   {
      $error = 0;
   }

   //payload
   $payload = array("type" => "getT", "error" => "$error", "tests" => $arrayofTests); 
   //echo "object to be encoded / the response: <br>";
   $package = json_encode($payload); 
   return $package;

}

/*********************************UTILITIES***********************************/

function testObject($conn, $testId, $rel, $sub) {
     $target = targetIs('getT'); 
  // $target = '/afs/cad/u/w/b/wbv4/public_html/Middle/tracklogs/getT.txt'; 
   //returns: "Id" => $y, "Desc" => $testName, "Rel" => $relstate, "Sub" => $substate          
   $temp = array('id' => $testId); 
   $arrayofPts = array();        
   //get the testname
   $arrayofQIds=array(); 
   $sql1 = " SELECT * FROM QuestionStudentRelation WHERE testId = '$testId' ";
   if ( ! $result1 = $conn->query($sql1)) {
      $sqlerror1 = $conn->error;
      $error .= "sql1: error " . $sqlerror1 . " ";
      echo $error; 
   } else {
      $write ="select * from questionstudentrelation where testid = " . $testId . "\n"; 
      autolog($write, $target); 
      while($row1 = mysqli_fetch_assoc($result1)) {
         $write = print_r($row1, true) . "\n"; autolog($write, $target); 
	 $testName = $row1['testName']; 
	 array_push($arrayofQIds, $row1['questionId']);
	 array_push($arrayofPts, $row1['maxpoints']); 
	 //var_dump($testName);  echo "<br><br>";
      }
      $atemp = array("desc" => $testName); 
      $temp = array_merge($temp, $atemp);
      $atemp = array("rel" => $rel);
      $temp = array_merge($temp, $atemp);
      $atemp = array("sub" => $sub);
      $temp = array_merge($temp, $atemp); 
      //return $temp; 
   }//if else sql1 

   //Questions [] : Question = { Desc, Topic, Id, Diff, [Tests] }
   //get the questions
   $arrayofQuestions=array(); 

   foreach($arrayofQIds as $q) {
      $sql2 = " SELECT * FROM Question WHERE Id = '$q' "; 
      if ( ! $result2 = $conn->query($sql2)) {
	 $sqlerror2 = $conn->error;
	 $error .= "sql1: error " . $sqlerror2 . " ";
	 echo $error; 
      } else {
	 while($row2 = mysqli_fetch_assoc($result2)) {
	    $Id = $row2['Id']; 
	    $Desc = $row2['question'];
	    $Topic = $row2['category'];
	    $Diff = $row2['difficulty'];

	    $temp1 = array('id' => $Id, 'desc' => $Desc, 'topic' => $Topic, 'diff' => $Diff); 
	    array_push($arrayofQuestions, $temp1); 
	    //var_dump($temp1); echo "<br><br>"; 
	 }  
      }
   } 
     $write = "check the contents of temp..\n"; 
     $write.= print_r($temp, true) . "\n"; autolog($write, $target); 
     $write = "check the contents of arrayofPts...\n"; 
     $write .= print_r($arrayofPts, true) . "\n"; autolog($write, $target); 
   $atemp1 = array("ques" => $arrayofQuestions);
       $write = "array of questions ... \n"; 
       $write .= print_r($atemp1, true) . "\n"; autolog($write, $target); 
   $temp = array_merge($temp, $atemp1); 
       $write = "contents of temp with questions...\n"; 
       $write .= print_r($temp, true) . "\n"; autolog($write, $target); 
   $atemp2 = array("pts" => $arrayofPts); 
   $temp = array_merge($temp, $atemp2); 
       $write = "contents of temp with arrayofPts...\n";
       $write .= print_r($temp, true) . "\n"; autolog($write, $target); 
       $write = "formed the test object...\n"; 
       $write .= print_r($temp, true); autolog($write, $target); 
   return $temp;      
}//testObject

?>
