<?php
/* format
GetTests:
Request { Type:GetTests, Rels:[int] }
Reply	{ Type:GetTests, Error:Str, Tests:[{Test}] }
Tests [] : { "Id" => $y, "Desc" => $testName, "Rel" => $relstate, "Sub" => $substate "Questions" => $questions[]) }
Questions [] : Question = { Desc, Topic, Id, Diff, [Tests] }
*/

include 'dblogin_interface.php';

$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);

$decoder = array("rels" => array("0", "1")); 
if (! $feedback = getExam($conn, $decoder)) { //calls the function getQUEST() ; 
      $error = "backend getQUEST() failed."; 
      $report = array("type" => "getTests", "error" => $error); 
      echo json_encode ($report); 
  } else {
        echo $feedback; 
  }
  
function getExam($conn, $decoder) { 
          $release = $decoder["rels"]; //array of rels
          
          $arrayofTests=array(); 
          
          foreach($release as $x) {
          //the sql statement below is necessary to filter those released/unreleased. 
              $sql= " SELECT * FROM Test WHERE released = '$x' ";
                if ( ! $result = $conn->query($sql)) { 
                    $sqlerror = $conn->error; 
                    $error .= "sql1 error : " . $sqlerror . " "; 
                } else {   //if theres nothing wrong, retrieve result1
                       while($row = mysqli_fetch_assoc($result)) {
                            $id = $row['Id'];
                            $sub = $row['sub'];
                            $aTest = testObject($conn, $id, $x, $sub);
                            array_push($arrayofTests, $aTest); 
 
		                   }                      
                }
                 
                 /*
                 //var_dump($arrayofTests); echo "<br><br>"; 
                 foreach ($arrayofTests as $t) {
                          var_dump($t);  echo "<br><br>";
                 }
                 */
          }
          
           if($error == null)
                 {
                   $error = 0;
                 }
                 
                 //payload
                 $payload = array("type" => "getTests", "error" => "$error", "tests" => $arrayofTests); 
                 //echo "object to be encoded / the response: <br>";
                 $package = json_encode($payload); 
                 return $package;
 
}

/*********************************UTILITIES***********************************/

function testObject($conn, $testId, $rel, $sub) {
//returns: "Id" => $y, "Desc" => $testName, "Rel" => $relstate, "Sub" => $substate          
        $temp = array('id' => $testId); 
        
        //get the testname
        $arrayofQIds=array(); 
        $sql1 = " SELECT * FROM QuestionStudentRelation WHERE testId = '$testId' ";
            if ( ! $result1 = $conn->query($sql1)) {
                    $sqlerror1 = $conn->error;
                    $error .= "sql1: error " . $sqlerror1 . " ";
                    echo $error; 
            } else {
                  while($row1 = mysqli_fetch_assoc($result1)) {
                         $testName = $row1['testName']; 
                         array_push($arrayofQIds, $row1['questionId']);
                         //var_dump($testName);  echo "<br><br>";
                  }
                 $atemp = array("desc" => $testName); 
                 $temp = array_merge($temp, $atemp);
                 $atemp = array("rel" => $rel);
                 $temp = array_merge($temp, $atemp);
                 $atemp = array("sub" => $sub);
                 $temp = array_merge($temp, $atemp); 
                 //return $temp; 
               
            }
            
            //Questions [] : Question = { Desc, Topic, Id, Diff, [Tests] }
            //get the questions
            $arrayofQuestions=array(); 
            
            foreach($arrayofQIds as $q) {
                 $sql2 = " SELECT * FROM Question WHERE Id = '$q' "; 
                 if ( ! $result2 = $conn->query($sql2)) {
                    $sqlerror2 = $conn->error;
                    $error .= "sql1: error " . $sqlerror2 . " ";
                    echo $error; 
                } else {
                     while($row2 = mysqli_fetch_assoc($result2)) {
                           $Id = $row2['Id']; 
                           $Desc = $row2['question'];
                           $Topic = $row2['category'];
                           $Diff = $row2['difficulty'];
                           
                           $temp1 = array('id' => $Id, 'desc' => $Desc, 'topic' => $Topic, 'diff' => $Diff); 
                           array_push($arrayofQuestions, $temp1); 
                           //var_dump($temp1); echo "<br><br>"; 
                     }  
                }
            } 
            
             $atemp1 = array("ques" => $arrayofQuestions);
             $temp = array_merge($temp, $atemp1); 
             return $temp;      
        
}//testObject

?>