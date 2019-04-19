<?php

date_default_timezone_set("America/New_York"); 
include 'getQuestion.php'; 
// include 'dblogin_interface.php'; 

function quesObject($conn, $qId) {
	$arrayCases = array(); 

	$tgt = targetIs('quesB'); 

	$write = "[ + ] page accessed quesB " . date("Y-m-d h:i:sa") . "\n";
	autolog($write, $tgt); 
	if (filesize($tgt) >= 100000) {
		autoclear($tgt); 
		$write = "+ the log reached 10 mb; it has been cleared \n"; autolog($write, $tgt); 
	}


	$write = "executing quesObject() with qId = " . $qId . "\n"; 
	autolog($write, $tgt); 

	$sql1 = "SELECT * FROM Question WHERE Id = '$qId' ";    
	if (! $result1  = $conn->query($sql1)) {
		$sqlerror1 = $conn->error; 
		$error .= "sql1: error " . $sqlerror1 . " "; 
		$write = $error . "\n"; autolog($write, $tgt); 
		return false; 
	} //if result1 conn query
	else { 
		$write = "obtaining testcases for qId = " . $qId . "\n"; 
		autolog($write, $tgt); 
                $cons = getCons($conn, $qId); 
		while($row1 = mysqli_fetch_assoc($result1)) {
			$desc = $row1['question']; 
			$topic = $row1['category']; 
			$diff = $row1['difficulty']; 
			$temp1 = array('id' => $qId, 'desc' => $desc, 'topic' => $topic, 'cons' => $cons,  'diff' => $diff);

			$write = "building the ques object (temp1)... \n";
			$write .= print_r($temp1, true) . "\n"; 
			autolog($write, $tgt); 

			$sql2 = "SELECT * FROM TestCases WHERE questionId = '$qId' "; 
			if (! $result2 = $conn->query($sql2)) {
				$errorsql2 = $conn->error; 
				$error = "sql2 : " . $errorsql2 . " "; 
			} else {
				while($row2 = mysqli_fetch_assoc($result2)) {
					array_push($arrayCases, $row2['testcases']); 
				}//row 2 mysqli fetch 

				$temp2 = array("tests" => $arrayCases); 
				$temp = array_merge($temp1, $temp2); 
			} //sql2 if else 
		}//while row1 mysqli fetch
	}//sql 1 if else 

	$write = "the ques object with array cases (temp2)... \n"; 
	$write .= print_r($temp, true); autolog($write, $tgt); 
	return $temp; 
} //quesObject(); 
?>
