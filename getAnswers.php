<?php
date_default_timezone_set("America/New_York"); 
include 'dblogin_interface.php'; 
include 'autolog.php';
include 'targets.php'; 

$target = targetIs('getAns');
$write = "[ + ] page accessed getAnswer() " . date("Y-m-d h:i:sa") . "\n"; 
$write .= "***********************************************************\n"; 
$write .= "+ target file size of : " . $target . " = " . filesize($target) . "\n"; 
autolog($write, $target); 

$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);

$write = "+ data received from somewhere: \n"; 
$write .= print_r($decoder, true) . "\n"; autolog($write, $target); 

//test
// $decoder = array("QIds" => array('2', '3', '4')); 
if (! $feedback = getAnswer($conn, $decoder)) {  
	$error = "backend getAnswer() failed."; 
	$report = array("type" => "getAnswers", "Error" => $error); 
	$write = "+ " . $error . "\n"; autolog($write, $target); 
	echo json_encode ($report); 
} else {
	echo $feedback; 
}

function getAnswer($conn, $decoder) {
	$target = targetIs('getAns'); 
	$write = "+ executing getAnswer() root \n"; autolog($write, $target); 
	//$arrayofTestCases = array();
	$arrayofAnswers = array();
	$questionIds = $decoder['qIds'];
	$testId = $decoder['testId'];
	// echo "questionIds<br>";
	//  var_dump($questionIds);
	//  echo "<br> questionIds<br>"; 
	foreach($questionIds as $x) {
		$arrayofTestCases = array();  /*clears the array for each question*/
		$sql1 = " SELECT * FROM QuestionStudentRelation WHERE questionId = '$x' AND testId = '$testId' ";
		if ( ! $result1 = $conn->query($sql1)) { 
			$sqlerror1 = $conn->error; 
			$error .= "sql1: " . $sqlerror1 . " "; 
		} else {  
			while($row1 = mysqli_fetch_assoc($result1)) { //obtain the questionIds
				if (! empty($row1['feedback'])) {
					$error .= "+ a feedback was detected in qId: " . $x . ", it is graded already.\n";
					$write = "+ " . $error . "\n"; autolog($write, $target); 
					continue; 
				}
				$temp = array('qId' => $x, 'text' => $row1['userAnswer']); 
				$write = "+ getAnswer() formed the 'temp' object: \n"; 
				$write = print_r($temp, true); autolog($write, $target); 
				//GET THE TESTCASES FOR THIS QID NOW!!! :
				$sql2 =  " SELECT * FROM TestCases WHERE questionId = '$x' ";
				if ( ! $result2 = $conn->query($sql2)) { 
					$sqlerror2 = $conn->error; 
					$error .= "sql2: " . $sqlerror2 . " "; 
				} else {
					while($row2 = mysqli_fetch_assoc($result2)) {
						array_push($arrayofTestCases, $row2['testcases']); 

					}
				}//result2

				$temp2 = array('tests' => $arrayofTestCases);
				$temp = array_merge($temp, $temp2);
				array_push($arrayofAnswers, $temp);                             
			}//while row1

		}//if result1
	}//foreach questionIds as $x
	
	if (empty($arrayofAnswers)) {
		$error = "arrayofAnswers are empty. test " . $testId . "is graded already."; 
		$write = "+ " . $error . "\n"; autolog($write, $target); 
	}
	if ($error === null) {
		$error = 0; 
	}  
	$output = array('type' => "getAnswers", 'error' => $error, "answers" => $arrayofAnswers); 
	//create the json format. 
	$package = json_encode($output); 
	return $package; 

}//getAnswer() 

/*
   1. get answers and testcases based on questionId
 */
?>
