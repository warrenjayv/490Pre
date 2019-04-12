<?php
date_default_timezone_set("America/New_York"); 
include 'dblogin_interface.php';
include 'autolog.php'; 
include 'targets.php'; 

$target = targetIs('auto'); 

$response = file_get_contents('php://input'); 
$decoder = json_decode($response, true); 

/*test*/ 

$write =  "+  page accessed by getCons " . date("Y-m-d h:i:sa") . "\n"; 
$write .= "+ received data : \n" . print_r($decoder, true) . "\n"; 
autolog($write, $target); 

if (! $feedback = getCons($conn, $decoder['qId'])) {
	$error = "backend getCons failed. pls check logs."; 
	$write = $error . " " . $feedback .  "\n"; autolog($write, $target); 	
	echo $feedback ; 
} else {
	$write = "+ getCons() in backend  was succesful. data: \n"; 
	$write .= print_r($feedback, true) . "\n"; autolog($write, $target); 
	echo $feedback; 
}

function getCons($conn, $qId) {
	/* return an array of cons for qId */ 
    $cons = array(); 
    $sql = " SELECT * FROM QuestionsConstraints WHERE questionId = '$qId' "; 
	if ( ! $result = $conn->query($sql)) {
		$errorsql = $conn->error; 
		$error = "sql :" . $errorsql . " "; 
		return $error; 
    }

    while($row = mysqli_fetch_assoc($result)) {
       	array_push($cons, $row['constraintext']); 
     }//while row mysqli fetch
    
    $conson = array('cons' => $cons); 
    return json_encode($conson); 
}//getCons() 

?>
