<?php
date_default_timezone_set("America/New_York"); 
include 'autolog.php'; 
include 'targets.php'; 
include 'testBuilder.php'; 
include 'dblogin_interface.php'; 

// $target = '/afs/cad/u/w/b/wbv4/public_html/Middle/tracklogs/getAt.txt';
$target = targetIs('getA'); 
$response = file_get_contents('php://input'); 
$decoder = json_decode($response, true); 
//testpoint:   $decoder = array('ids' => array('10'));
$write = "[ + ] page accessed getA " . date("Y-m-d h:i:sa") . "\n"; 
$write .= "+ target file size of : " . $target . " = " . filesize($target) . "\n"; 
autolog($write, $target); 
if (filesize($target) >= 100000) {
	autoclear($target); 
	$write = "+ the log reached 10 mb; it has been cleared \n"; autolog($write, $target); 
}

/*testpoint*/
/* $decoder = array("ids" => array("10"));*/

if (! empty($decoder)) {
	$write = "data received...\n"; 
	$write .= print_r($decoder, true) . "\n";  autolog($write, $target); 
  
	if ( ! $feedback = getAttempt($conn, $decoder)) {
		$error = "backend getAttempt() failed. please check logs! ";
		$write = $error . "\n"; autolog($write, $target); 	
	} else {
		$write = "executing getAttempt()...output:\n"; 
		$write .= print_r($feedback, true); autolog($write, $target); 
		echo $feedback; 
	}	
}//if empty decoder
else {
	 $error = "decoder at backend of getA received empty or null"; 
	 $write = $error . "\n"; autolog($write, $target); 
	 $report = array('type' => 'getA', 'error' => $error); 
	 echo json_encode($report); 
	 return 0; 
} 

function getAttempt($conn, $decoder) {
	$target = targetIs('getA'); 
    $ids = $decoder['ids']; //test ids
	/* if the array of ids is empty, get them */ 
		if (empty($ids)) {
			$write = "decoder returned an empty array of ids! go get em!\n"; autolog($write, $target); 
			$ids = getallIds($conn); 
		}	

	$write = "executing getAttempt() with testIds... " . print_r($ids, true) . "\n"; 
	
	$attemptArray  = array(); //array of attempt json objects. 
//	$testArray = array(); //test 

	foreach($ids as $x) {
		
		$ansObj = array(); 
		$write = "executing testObject() for test id : " . $x . " \n"; 
	        if ($testObj = testObject($conn, $x)) {
				$write .= print_r($testObj, true) . "\n"; 
				$write .= "pushed test "  . $testObj['id'] . " into testArray\n"; 
				autolog($write, $target); 
			//	array_push($testArray, $testObj); 
			} else {
				$write = "testObj() failed in getAttempt()! terminating.\n"; 
				autolog($write, $target); 
				return false;  
			}
		
		$write = "executing ansObject() for test id : " . $x . " \n"; 
		    if ($ansObj  = ansObject($conn, $x)) {
		    		$write .= "ansObject() was succesful...output: \n"; 
				$write .= print_r($ansObj, true) . "\n"; 
				$write .= "warning! ansObject is an array!\n"; 
				autolog($write, $target); 
				//array_push($ansArray, $array); 
			} else {
				$write = "critical: ansObject() failed!\n"; autolog($write, $target); 
				return false; 
			}
	
	$write =  "building the attempt object...\n";
	$temp = array('test' => $testObj);
//	$temp2 = array($ansObj); 
	$temp = array_merge($temp, (array)$ansObj); 
	$write .= print_r($temp, true);
	$write .= "pushing the attempt object into array of attempts\n";
	autolog($write, $target); 
	array_push($attemptArray, $temp); 

	}//foreach ids as x 
		
	if ($error == null) { $error = 0; } 
	$payload  = array('type' => 'getA', 'error' => $error, 'attempts' => $attemptArray); 
	$package = json_encode($payload); 
	return $package; 

}//getAttempt(); 

function ansObject($conn, $id) {
       $ansArray = array(); 
	     $userAnswers = array(); 
       $grades = array(); 
	     $feedbacks = array(); 
	     $comments = array(); 

    $target = targetIs('getA'); 
	$write = "executing ansObject() with testId : " . $id . "\n"; 
	$sql = "SELECT * FROM QuestionStudentRelation WHERE testId = '$id' "; 
		if (! $result = $conn->query($sql)) {
			$sqlerror = $conn->error; 
			$error = "sql: " . $sqlerror . " "; 
			$write = $error . "\n"; autolog($write, $target); 
			return false; 
		} else {
			while($row = mysqli_fetch_assoc($result)) {
				array_push($userAnswers, $row['userAnswer']); 
			//	array_push($feedbacks, $row['feedback']); 
        array_push($grades, $row['points']); 
				$afeedbacks = getFeedbacks($conn, $id, $row['questionId']); 
				array_push($feedbacks, $afeedbacks); 
				array_push($comments, $row['comment']); 
			}//while row mysqli 
			$ansArray = array('answers' => $userAnswers, 'grades' => $grades, 'feedback' => $feedbacks, 'remarks' => $comments); 

			$write = "ansObject() formed the ansArray\n"; 
			$write .= print_r($ansArray, true) . "\n"; 
			autolog($write, $target); 
			return $ansArray; 
		}//if result conn-query 
}//ansObject(); 

function getallIds($conn) {
	$arrayofIds = array(); 
	$target = targetIs('getA'); 
	$write = "getallIds() is called!\n"; autolog($write, $target); 
	$sql = "SELECT Id FROM Test WHERE sub = 1";
		if (!$result = $conn->query($sql)) {
			$sqlerror = $conn->error; $error = "sql: " . $sqlerror . " "; 
			$write = $error . "\n"; autolog($write, $target); 
		}//if result
		else {
			while($row = mysqli_fetch_assoc($result)) {
				array_push($arrayofIds, $row['Id']); 
			}
			$write = "getallIds() returned an array: \n"; 
			$write .= print_r($arrayofIds, true) . "\n"; 
			return $arrayofIds; 
		}//if sql else
}//getallids

function getFeedbacks($conn, $id, $qId) {
	$target = targetIs('getA'); 
	$feedbacks = array(); 
	$write = "executing getFeedbacks() for testId " . $id . " & qId " . $qId . "\n"; 
	autolog($write, $target); 
	$sql = "SELECT feedback FROM Feedback WHERE testId = '$id' AND questionId = '$qId' "; 
		if (! $result = $conn->query($sql)) {
			$sqlerror = $conn->error; 
			$error = "sql: " . $sqlerror . " "; 
			$write = $error . "\n"; autolog($write, $target); 
			return false; 
		} else {
			while($row = mysqli_fetch_assoc($result)) {
				array_push($feedbacks, $row['feedback']); 
			}
		}//result conn 
    $write = "getFeedback() formed an array of feedbacks:\n"; 
	  $write = print_r($feedbacks, true) . "\n"; autolog($write, $target); 
 	return $feedbacks;  
}//getFeedbacks(); 

?> 		
