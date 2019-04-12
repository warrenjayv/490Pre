<?php
date_default_timezone_set("America/New_York"); 


include 'dblogin_interface.php';
include 'autolog.php'; 
include 'targets.php'; 

$target = targetIs('getQ'); 
$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);
$write = "[ + ] page accessed getQ " . date("Y-m-d h:i:sa") . "\n"; 
$write .= "+ target file size of : " . $target . " = " . filesize($target) . "\n"; 
autolog($write, $target); 
if (filesize($target) >= 100000) {
	autoclear($target); 
	$write = "+ log reached max; it is cleared \n"; autolog($write, $target); 
}
/*testpoint*/
//$difficulty = array("2", "4"); ///test purpose.

///note, $decoder is an array of difficulties. 

if (! $feedback = getQUEST($conn, $decoder)) { //calls the function getQUEST() ; 
  $error = "backend getQUEST() failed. please check logs! "; 
  $report = array("type" => "getQ", "error" => $error); 
  echo json_encode ($report); 
} else {
  echo $feedback; 
}


function getQUEST($conn, $decoder) { 

  $target = targetIs('getQ'); 
  $array = array();  //array of questions
  $arrayID = array(); //array of ids
  $arrayCASES = array(); //array of testcases
  $arrayDIF = array(); //array of difficulty levels
  $arrayofRows = array(); //array of rows from SQL 
  $cons = array(); //array of constraints 

  foreach ($decoder as $v) { //runs a SELECT query for each difficulty and store it. 
    $sql= " SELECT * FROM Question WHERE difficulty = '$v' ";
    if ( ! $result = $conn->query($sql)) { //runs the select query
      $errorsql = $conn->error;
      $error .= "sql1 :"  . $errorsql . " "; 
      //return "type: getQ; sql: " . $error; 
    } else {
      while($row = mysqli_fetch_assoc($result)) { 
	//array_push($arrayofRows, $row); 
	$Id = $row['Id']; 

	$sql2 =  " SELECT * FROM TestCases WHERE questionId = '$Id' "; 
	if ( ! $result2 = $conn->query($sql2)) {
	  $errorsql2 = $conn->error; 
	  $error .= "sql2 : " . $errorsql2 . " " ; 
	  //return "type: getQ; sq2: " . $error;
	} else {
	  while($row2 = mysqli_fetch_assoc($result2)) {      
	    array_push($arrayCASES, $row2['testcases']); 
	  }

	  $temp = array("tests" => $arrayCASES); 
	  //array_push($arrayofRows, $arrayCASES);          
	  // $row = array_merge($row, $temp);
	  $question = $row['question'];
	  $diff = $row['difficulty'];
	  $category = $row['category']; 
          $cons = getCons($conn, $qId);
	  $temp2 = array("id" => $Id, "desc" => $question, "topic" => $category, "cons" => (array)$cons, "diff" => $diff);
	  $temp2 = array_merge($temp2, $temp);
	  array_push($arrayofRows, $temp2);      
	  $temp = array(); 
	  $arrayCASES = array(); //empty for every loop.    
	}
	//array_push($arrayofRows, $row);      
	//array_push($array, $row['question']);  //store the questions into the array. 
	//array_push($arrayID, $row['Id']); //store the id's into the array 
	// array_push($arrayDIF, $row['difficulty']); //store the difficulties                                    
      }//while
    } //else
  }///foreach!


  if ($error === null) {
    $error = 0; 
  }
  $output = array('type' => "getQ", 'error' => $error, 'ques' => $arrayofRows); 
  $write = "+ getQuest() produced the object:\n"; 
  $write .= print_r($output, true) . "\n"; autolog($write, $target); 
  //create the json format. 
  $package = json_encode($output); 
  return $package; 

}//getQUEST()

function getCons($conn, $qId) {
    $target = targetIs('getQ'); 
    $write = "+ calling getCons() ... \n"; autolog($write, $target); 
	/* return an array of cons for qId */ 
    $cons = array(); 
    $sql = " SELECT * FROM QuestionsConstraints WHERE questionId = '$qId' "; 
	if ( ! $result = $conn->query($sql)) {
		$errorsql = $conn->error; 
		$error = "sql :" . $errorsql . " "; 
		return  $error; 
        }

    while($row = mysqli_fetch_assoc($result)) {
        $write = "+ pushing into cons array : \n" . $row['constraintext'] . "\n"; 
        autolog($write, $target); 
	array_push($cons, $row['constraintext']); 
     }//while row mysqli fetch
	
     return $cons; 
}//getCons() 
mysqli_close($conn);

?>
