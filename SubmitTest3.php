<?php
date_default_timezone_set("America/New_York"); 

include 'dblogin_interface.php';
include 'autolog.php'; 
include 'targets.php'; 
include 'testBuilder.php'; 

// $target = '/afs/cad/u/w/b/wbv4/public_html/Middle/tracklogs/addA.txt'; 
$target = targetIs('addA'); 
$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);

$write = "[+] addA page accessed " . date("Y-m-d h:i:sa") . "\n";  
$write .= "page received data. processing the data below:\n"; autolog($write, $target); 
$write = print_r($decoder, true);
if (empty($decoder)) {
    $write = "addA decoder is empty! \n"; autolog($write, $target); 
    $report = array('type' => 'addA', 'error' => 'addA decoder received no data'); 
    echo json_encode($report); 
    return 0; //program terminates and code stops here. 
} else {
    $write = "received data....\n";
    $write .= print_r($decoder, true) . "\n"; 
    autolog($write, $target);
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
    $attempt  = $decoder['answers']; /*array of attempt objects*/
    $comment = $decoder['comment'];

    $comment = addslashes($comment);

    foreach($answers as $x) {	
    	$id = $x['id']; $test = $x['test']; $grades = $x['grades']; 
	$comment =$x['comment']; $feedback = $x['feedback']; $remarks = $x['remarks']; 

	
    }//for each answers as x 

    /*

    foreach($answers as $x) {
		$qId = $x['id'];
		$text = $x['text'];
		$text = addslashes($text);

	$write = "updating database with answer " . $text . "for question " . $qId . "\n"; 
	autolog($write, $target); 

	// echo "<br<br>text = " . $text . "<br><br>";
	$sql1 = "UPDATE rd248.QuestionStudentRelation SET userAnswer = '$text',	comment = '$comment' WHERE QuestionStudentRelation.questionId = '$qId' AND QuestionStudentRelation.testId = '$testId' ";
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
    $package = array('type' => 'addA', 'error' => $error, 'id' => $testId, 'sub' => '1', 'comment' => $comment, 'answers' => $answers); 
    return json_encode ($package);
    //return 1; 
}//submitExam()
?>
