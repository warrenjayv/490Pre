<?php
date_default_timezone_set("America/New_York"); 

echo "HTTP 402: still working on it!";
return 0;

include 'dblogin_interface.php';
include 'autolog.php'; 
include 'targets.php'; 

/*
echo "http 402: page is currently being edited\n"; 
return 0; 
*/

// $target = '/afs/cad/u/w/b/wbv4/public_html/Middle/tracklogs/addA.txt'; 
$target = targetIs('addA'); 
$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);

$write = "[+] addA page accessed " . date("Y-m-d h:i:sa") . "\n";  
$write .= "+ page received data. processing the data below:\n";
$write .= "+ target file size of : " . $target . " = " . filesize($target) . "\n"; 
autolog($write, $target); 
if (filesize($target) >= 100000) {
	autoclear($target); 
	$write = "+ the log reached 10 mb; it has been cleared \n"; autolog($write, $target); 
}
/*testpoint*/ 
/*
$decoder = array('type' => 'addA', 'id' => '1234'); 
$write = print_r($decoder, true);
*/

if (empty($decoder)) {
    $write = "addA decoder is empty! \n"; autolog($write, $target); 
    $report = array('type' => 'addA', 'error' => 'addA decoder received no data'); 
    echo json_encode($report); 
    return 0; //program terminates and code stops here. 
} else {
    $write = "received data....\n";
    $write .= print_r($decoder, true) . "\n"; 
    autolog($write, $target); 

	$atest = $decoder['test']; $id = $atest['id']; 
	if (! $check = checktestId($conn, $id)) {
		$write = "invalid test id. terminating.\n"; autolog($write, $target); 
		$report = array('type' => 'addA', 'error' => "test id not found in database.");
		echo json_encode ($report);
		return 0; 
	}// if checktestId()  
}

if (! $feedback = submitExam($conn, $decoder)) { //calls the function getQUEST() ; 
    $error = "backend submitExam() failed."; 
    $write = $error . "\n"; autolog($write, $target); 
    $report = array("type" => "addA", "error" => $error); 
    echo json_encode ($report); //terminate 
} else {
    $write = "executing submitExam().. \n"; autolog($write, $target); 
    $write = print_r($feedback, $target) . "\n"; autolog($write, $target); 
    echo $feedback; 
}


function submitExam($conn, $decoder) {

 //   $target = '/afs/cad/u/w/b/wbv4/public_html/Middle/tracklogs/addA.txt'; 
    $target = targetIs('addA'); 
    $test = $decoder['test']; 
    $testId = $test['id']; 
    $answers = $decoder['answers']; /*array of answers*/
    $comment = $decoder['comment'];
    $remarks = $decoder['remarks'];   
   $qIds = getqIds($conn, $testId);   //array of qIds

    $comment = addslashes($comment);

    foreach($answers as $key=>$x) {
			
	      //$index = array_search($x, $answers);     
            $index = $key; 
	    $qId = $qIds[$index]; 
 	    $text = $x;
	    $text = addslashes($text);

	$write = "updating database with answer " . $text . " for testId " .
	$testId . " where qId = " . $qId . " \n"; autolog($write, $target); 

	// echo "<br<br>text = " . $text . "<br><br>";
	$sql1 = "UPDATE rd248.QuestionStudentRelation SET userAnswer = '$text',
	comment = '$comment' WHERE QuestionStudentRelation.questionId =
	'$qId' AND QuestionStudentRelation.testId = '$testId' ";
	if ( ! $result1 = $conn->query($sql1)) { 
	    $sqlerror1 = $conn->error; 
	    $error .= "sql1: " . $sqlerror1 . " "; 
	    $write = $error . "\n"; autolog($write);
	} else { 
	    /* succesfully updated table */
	    /* update sub to 1 */
	    $sql2 = "UPDATE rd248.Test SET sub = '1' WHERE
		Test.Id = '$testId' ";
	    if ( ! $result2 = $conn->query($sql2)) { 
		$sqlerror2 = $conn->error; 
		$error .= "sql2: " . $sqlerror2 . " "; 
	    } else {  
		/* succesfully updated table */
	    }//if sql2 
	}//if sql1
    }//foreach answers as x

    if ($error === null) {
	$error = 0; 
    }
    $comment = stripslashes($comment);
   // $package = array('type' => 'addA', 'error' => $error, 'id' => $testId, 'sub' => '1', 'comment' => $comment, 'answers' => $answers); 
    //$package = array('type' => 'addA', 'error' => 'INT', 'attempt' => ' ' 
//function attemptObj($conn, $test, $answers, $grades, $comment, $feedback, $remarks) {
    $temp = attemptObj($conn, $test, $answers, $grades, $comment, $feedback, $remarks);
	$package = array("type" => "addA", "error" => $error, "attempt" => $temp);  
    return json_encode ($package);
    //return 1; 
}//submitExam()

function checktestId($conn, $id) {
	$target = targetIs('addA'); 
	$write = "executing checktestId() for " . $id . "\n"; autolog($write, $target); 
	$sql = " SELECT Id FROM Test ";  
	if ( ! $result = $conn->query($sql)) { 
	    $sqlerror = $conn->error; 
	    $error .= "sql: " . $sqlerror . " "; 
	    $write = $error . "\n"; autolog($write, $target);
	} else { 
		while($row = mysqli_fetch_assoc($result)) {
			if ($id == $row['Id']) {
				$write = "id found in the database!\n"; autolog($write, $target); 
			 	return true;
			}
		}//while row mysqli fetch assoc
		$write = $id . " not found in the database!\n"; 
		return false; 
	}//sql 
}//checktestId(); 

function getqIds($conn, $id) {
	$qIds = array(); //array of qIds 
	$target = targetIs('addA');
	$write = "executing getqIds for testId " . $id . "\n"; autolog($write,
	$target); 
	
	$sql = "SELECT questionId FROM QuestionStudentRelation WHERE testId = '$id'"; 
	if ( ! $result = $conn->query($sql)) { 
	    $sqlerror = $conn->error; 
	    $error .= "sql: " . $sqlerror . " "; 
	    $write = $error . "\n"; autolog($write, $target);
		echo $error; 
		return 0; 
	} else {
		while($row = mysqli_fetch_assoc($result)) {
			array_push($qIds, $row['questionId']); 
		}//while row mysql	
		$write = "getqIds() formed an array of qIds\n"; 
		$write .= print_r($qIds, true) .  "\n";
		autolog($write, $target); 
		return $qIds; 
	}
}//getqIds

function attemptObj($conn, $test, $answers, $grades, $comment, $feedback, $remarks) {
	$target = targetIs('addA'); 
	$write = "+ executing attempt Obj\n"; 
	$write .= "+ test: " . print_r($test, true) . "\n"; 
	$write .= "+ comment: " . $comment . "\n"; 
	$write .= "+ answers: " . print_r($answers, true) . "\n"; 
	autolog($write, $target); 

	$package = array("test" => $test, "answers" => $answers, "grades" => $grades, "comment" => $comment, "feedback" => $feedback, "remarks" => $remarks); 
	return $package; 
}//attemptObj 


?>
