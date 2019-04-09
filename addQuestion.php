<?php 
date_default_timezone_set("America/New_York"); 
include 'dblogin_interface.php'; 
include 'autolog.php';
include 'targets.php'; 

$target = targetIs('addQ'); 
$write = "[ + ]  page accessd addQ " . date("Y-m-d h:i:sa") . "\n"; 
autolog($write, $target); 

$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);
$write .= "page received data isnt that cool!\n"; 
$write = print_r($decoder, true) . "\n"; autolog($write, $target);  

$question   = $decoder['desc'];
$question = addslashes($question); 
$difficulty = $decoder['diff'];
$cases      = $decoder['tests'];
// $keywords  = $decoder['Keys'];
$category = $decoder['topic'];
$consArray = $decoder['cons']; 
$category = addslashes($category);

/*testpoint*/

$conn       = mysqli_connect($dbserver, $mySql_user, $mySql_password, $mySql_database);
if (!$conn) {
       $error .= "failed to connect to database ";  
}

if(! empty($decoder))  {
     if(! $feedback = addQUEST($conn, $question, $difficulty, $cases, $category, $consArray))  {
         $error = "backend addQUEST() failed!";
     	 $report = array("type" => "addQ", "error" => $error);
		 $write = $error . "\n"; 
    	 echo json_encode($report); 
      }  else {
         echo $feedback; 
      }
} else {
  
      $error .= "empty desc. or topic"; 
      $report = array("type" => "addQ", "error" => $error); 
      echo json_encode($report);         

}

  function addQUEST($conn, $question, $difficulty , $cases, $category, $consArray) {
   	  $target = targetIs('addQ');   
      $sql1 = "SELECT * FROM Question"; 
       if ( ! $result1 = $conn->query($sql1)) { 
      //if (! $result1) { //NOTE: running another IF statement on queries RUNS its twice!! 
          $sqlerror = $conn->error; 
          //return "type: AddQ; SQL1 = " . $error; 
          $error .= "sql: " . $sqlerror . " "; 
       } else {   
          $id = $result1->num_rows; 
          $id += 1; //this counts the number of rows and add 1 for the next SQL statement.  
      } //if else
      
      $sql2 = "INSERT INTO Question (Id, question, difficulty, category) VALUES ('$id', '$question', '$difficulty',  '$category')";   
       if (! $result2 = $conn->query($sql2)) { 
      //if (! $result2) { ///NOTE: running another IF statement on queries RUNS its twice!!
        $sqlerror2 = $conn->error; 
        $error .= "sql2: " . $sqlerror2 . " "; 
       } else {
      
      ///*** adds the testcases to the '$id' that is certified above /// 
      ///*** '$cases' is already an array of testcases. loop through it. ///      
           foreach($cases as $v) {
               $v = addslashes($v);
               $sql3 = "INSERT INTO TestCases (questionID, testcases) VALUES  ('$id', '$v')"; 
	          if (! $result3 = $conn->query($sql3)) {
                    $sqlerror3 = $conn->error; 
                     $error .= "sql3: " . $sqlerror3 . " "; 
	          } //if result3
            }//foreach

 	  		foreach($consArray as $w) {
				$w = addslashes($w); 
				$sql4 = "INSERT INTO QuestionsConstraints (questionId, constraintext) VALUES ('$id', '$w')"; 
	          if (! $result4  = $conn->query($sql4)) {
                    $sqlerror4  = $conn->error; 
                    $error .= "sql4 : " . $sqlerror4  . " "; 
                  	$write = $error . "\n"; autolog($write, $target); 
	          } //if result4
		 	}//foreach consarray as w

      }//else

      //note: Id(in the database) is not included here as it autoincremenets per insert. 
      if ($error === null) {
           $error = 0; 
      } 
      
      $question = stripslashes($question);
      $category = stripslashes($category);
      $questionobj  = array("id" => $id, "desc" => $question, "topic" => $category, "cons" => $consArray, "diff" => $difficulty, "tests" => $cases); 
	  $write = "formed the questiobObj, here: \n"; 
	  $write .= print_r($questionobj, true) . "\n"; autolog($write, $target); 
      $feedback = array("type" => "addQ", "error" => $error, "que" => $questionobj); 
	 
      $recoil = json_encode($feedback); 
      if ($recoil == false) {
              return "backend tried to encode JSON and failed."; 
      } else {
         return $recoil; 
      }
      
  } //addQUEST(); 


mysqli_close($conn);
?>
