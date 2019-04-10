<?php

  include 'quesBuilder.php'; 

  /*tesbuilder builds the test object for the JSON file*/
  /*returns test : {id, desc, rel, sub, ques [], pts []}*/

  date_default_timezone_set("America/New_York"); 

  //test:
  //  $id = 1; 

/*
if ($testObj = testObject($conn, $id)){
    $tgt = targetIs('testB'); 
	$write = "test obj succesfully created!\n"; 
	$write .= print_r($testObj, true ) . "\n";  
    autolog($write, $tgt); 
}
*/

function testObject($conn, $id) { 

    $arrayofQues = array(); //array of questions 
	$ptsArray = array(); 
   
    $target = targetIs('testB'); 

    $write = "[ + ] page accessed testB " . date("Y-m-d h:i:sa") . "\n"; 
    autolog($write, $target);
	if (filesize($target) >= 100000) {
		autoclear($target); 
		$write = "+ the log reached 10 mb; it has been cleared \n"; autolog($write, $target); 
	}


    $write = "executing testObject() with testId = " . $id . "\n"; 
	autolog($write, $target); 

	$temp = array('id' => $id); 

	$sql1 = "SELECT * FROM Test WHERE Id = '$id' ";
	$write = $sql1 . "\n"; autolog($write, $target); 
	if ( ! $result1 = $conn->query($sql1)) {
		$sqlerror1 = $conn->error;
		$error .= "sql1: error " . $sqlerror1 . " "; 
		$write = $error . "\n"; autolog($write, $target); 
		return false; 
	} else {
		while($row1 = mysqli_fetch_assoc($result1)) {
			$desc = $row1['testName']; 
			$rel = $row1['released']; 
			$sub = $row1['sub']; 
            $temp1 = array('desc' => $desc, 'rel' => $rel, 'sub' => $sub); 
	                $write = "test being formed (temp1)...\n" . print_r($temp1, true ) . "\n"; autolog($write, $target); 
			$temp = array_merge($temp, $temp1); 
			$write = "test being formed (temp) ...\n" . print_r($temp, true ) . "\n";
			autolog($write, $target); 

		} //while row1 

		    $write = "building question object for test...\n"; autolog($write, $target); 

	  $sql2 = "SELECT * FROM QuestionStudentRelation WHERE testId = '$id'"; 
	   		if ( ! $result2 = $conn->query($sql2)) {
				$sqlerror2 = $conn->error; 
				$error = "sql2: " . $sqlerror2 . " "; 
				$write = $error . "\n"; autolog($write, $target); 
				return false; 
			} else {
			    while($row2 = mysqli_fetch_assoc($result2)) {
				  if (!	$questionObj = quesObject($conn, $row2['questionId'])) {
						$write = "quesObject() failed!..\n"; autolog($write, $target); 
						return false; 
				  } else {
				        $write = "question object for test built!\n"; 
						$write .= print_r($questionOb, true) . "\n"; autolog($write, $target);  
						array_push($arrayofQues, $questionObj); 
				  }//if quesObject() else 
				  
				  $write = "obtaining points from each question id: " . $row2['questionId'] .
				  " \n"; 
				  $write .= "pts for this question : " . $row2['maxpoints'] . " \n"; 
				  autolog($write, $target); 
				  array_push($ptsArray, $row2['maxpoints']); 
				  
				}//row2 msqi fetch 
			}//sql2 if else
	}//sql1 if else

	$temp3 = array("pts" => $ptsArray); 
    $temp2 = array("ques" => $arrayofQues); 
	$temp = array_merge($temp, $temp2); 
	$temp = array_merge($temp, $temp3); 
	return $temp; 
  }//testobject() 

?>
