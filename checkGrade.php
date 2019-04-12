<?php

include 'dblogin_interface.php'; 
include 'autolog.php'; 
include 'targets.php'; 

$target = targetIs('auto'); 
$response = file_get_contents('php://input'); 
$decoder = json_decode($response, true); 

// $decoder= array('type' => 'check', 'testId' => '10'); 
if ($decoder['type'] == 'check') {
    $feedback = checkGrade($conn, $decoder); 
    echo $feedback;     
}
if ($decoder['type'] == 'set') {
    //execute gradeSet()
    $feedback = setGrade($conn, $decoder); 
    echo $feedback;  
}
function checkGrade($conn, $decoder) {
    $target = targetIs('auto'); 
    $id = $decoder['testId']; 
    $sql = "SELECT * FROM Test WHERE Id = '$id' "; 
    if (! $result = $conn->query($sql)) {
       $error = $conn->error; 
       $write = "+ sql: " . $error . "\n"; autolog($write, $target); 
       $report = array('grade' => 'null', 'error' => $error); 
       print_r($report); 
       return  json_encode($report); 
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
    	$grade = $row['grade']; 
        }
        $report = array('grade' => $grade, 'error' => $error); 
        return  json_encode($report); 
    }
} //checkGrade

function setGrade($conn, $decoder) { 
    $target = targetIs('auto'); 
	$id = $decoder['testId']; 
    $grade = $decoder['grade'];
    $sql = "UPDATE rd248.Test SET grade = '$grade' WHERE Test.Id = '$id'"; 
    if (! $result = $conn->query($sql)) {
       $error = $conn->error; 
       $write = "+ sql: " . $error . "\n"; autolog($write, $target); 
       $report = array('grade' => 'null', 'error' => $error); 
       return json_encode($report); 
    } else { 
       $write = "+ gradeSet() set grade for " . $id . "\n"; 
       $report = array('grade' => $grade, 'error' => $error); 
       return json_encode($report); 
    }
} //gradeSet

?> 
