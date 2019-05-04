<?php  

include 'dblogin_interface.php';
include 'autolog.php';
include 'targets.php'; 
include 'sqlCheck.php'; 

$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);

$target = targetIs('auto'); 

$write = "+ updatePoints() 2 was called. Received:  \n"; 
$write .= print_r($decoder, true) . "\n" ; autolog($write, $target); 


// $decoder = array('testId' => '1', 'qId' => '4', 'feedback' => 'bp  testcase add(2,3)=5 failed', 'subpoints' => '.5', 'max'=> '0.5');
if (! $feedback = updatePoints($conn, $decoder)) { //calls the function getQUEST() ; 
		$error = "backend updatePoints() failed."; 
		$report = array("type" => "updatePoints", "error" => $error); 
		echo json_encode ($report); //terminate 
} else {
		echo $feedback; 
}

function updatePoints($conn, $decoder) {
    $target = targetIs('auto'); 
		$testId = $decoder['testId'];
		$qId = $decoder['qId'];
		$feedback = $decoder['feedback']; 
//		$feedback = addslashes($feedback); 
		$subpoints = $decoder['subpoints'];
    $max = $decoder['max']; //it turns out MAX is in percent; we don't want percent. 
		/*warning, subpoints is a percent*/


		/* task A : get the points for this current test and question */ 

    $sql2 = "SELECT * FROM QuestionStudentRelation WHERE testId = '$testId' AND questionId = '$qId'"; 	  
    if (! $result2 = $conn->query($sql2)) {
				$sqlerror2 = $conn->error; 
				$error .= "sql2 " . $sqlerror2 . " "; 
			 $write = "updatePoints SQL : " . $error . "\n"; autolog($write, $target);  
		} else { 
			    while($row2  = mysqli_fetch_assoc($result2)) {
              $ded = $row2['maxpoints'] * $subpoints;
              $ded = round($ded); 
    	    	  $points = $row2['points'] - $ded; 
              $points = round($points); 			  
					}
    }

    $sql4 = "SELECT * FROM QuestionStudentRelation WHERE testId = '$testId' AND questionId = '$qId'";
    if (! $result4 = $conn->query($sql4)) {
        $sqlerror4 = $conn->error;
        $error .= "update SQL: " . $error . "\n"; autolog($write, $target); 
    } else {
        while($row4 = mysqli_fetch_assoc($result4)) {
            $maxpoints = $row4['maxpoints']; 
            $maxpoints = $maxpoints * $max; 
            $maxpoints = round($maxpoints); 
        }
    }

	  /* task B : submit the new points to the database */ 
	
	  $sql3 = "UPDATE rd248.QuestionStudentRelation SET points = '$points' WHERE QuestionStudentRelation.questionId = '$qId' AND QuestionStudentRelation.testId = '$testId' "; 
		if (! $result3 = $conn->query($sql3)) {
				$sqlerror3 = $conn->error; 
	   $error3 .= "sql3 " . $sqlerror3 . " "; 
			 $write = "updatePoints SQL : " . $error . "\n"; autolog($write, $target);  
	  }
    //$pos = strpos($hay, $needle) 
    //$newstr = substr_replace($oldstr, $str_ins, $pos, 0) 
   /* task C : submit feedback into the database */ 
   // $newfeedback = $feedback + " -" + $ded; 
    
    // $ded = round($ded); 
    if (($pos = stripos($feedback, "gp", 0)) === false) {
        $ded*=-1; 
	     	$feedback = substr_replace($feedback , $ded, $pos+1, 0);
     } else {
        $feedback = substr_replace($feedback, $maxpoints, $pos+1, 0);   
     }

    /* task A : a new feature; add index according to # of rows, index = rows - 1.*/ 
    $sql4 = "SELECT * FROM Feedback WHERE testId = '$testId' AND questionId = '$qId'"; 
    if(! $result4 = sqlCheck($sql4, $conn)) {
        $error = "sql4 in modA, selecting feedbacks failed."; 
        $write = "+ error: " . $error . "\n"; autolog($write, $target); 
    } else {
        $write = "+ modA was able to retrieve all rows from feedback for testId = " . $testId .
            " and qId = " . $qId . "\n"; 
        $write .= "+ obtained " . $result4->num_rows . " rows\n"; autolog($write, $target); 
        $index = $result4->num_rows;     
        $index +=1;    
    }//if sql4 else

    $newfeedback = addslashes($feedback);     
    $sql = "INSERT INTO Feedback (arrayindex, testId, questionId, feedback) VALUES ('$index', '$testId', '$qId','$newfeedback')";
		if (! $result = $conn->query($sql)) {
				$sqlerror = $conn->error; 
				$error .= "sql " . $sqlerror . " "; 
			 $write = "updatePoints SQL : " . $error . "\n"; autolog($write, $target);  
		}//if result conn query	
	 
		if(empty($error)) {
				$error = 0; 
		}
	
  $write = "+ updatePoints()2 returned error: \n"; $write = "error: " . $error . "\n"; 
	autolog($write, $target); 
  $newfeedback = stripslashes($newfeedback); 
    
     
		$package = array("type" => "updatePoints", "error" => $error, "testId" => $testId, "qId" => $qId, "feedback" => $newfeedback, 'points' => $points, 'max' => $maxpoints);
    $write = "+ updatePoints() in backend returning:\n" . print_r($package, true) . "\n"; 
    autolog($write, $target); 
		return json_encode($package);

} //updatePoints

/* worklog 4 19 2017 changes
     implementing a new change in which an index will now be update for each testId and questionId
*/
?>
