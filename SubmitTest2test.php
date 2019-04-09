<?php

include 'dblogin_interface.php';
include 'autolog.php'; 

$target = 'afs/cad/u/w/b/wbv4/public_html/Middle/tracklogs/addA.txt'; 
$response   = file_get_contents('php://input');
$decoder    = json_decode($response, true);

/*
UPDATE: 
addA:
request { type:addA, id:INT, answer:[{ANSWER}] }
ANSWER:
{ id:INT, text:STR }
*/

 //testpont: 
 //$decoder = array('type' => 'addA', 'id' => '2', 'answers' => array('0' => array('id' => '1', 'text' => "def add(a, b): return a+b")/*answer*/)/*answers*/)/*addA*/;
 
if (! $feedback = submitExam($conn, $decoder)) { //calls the function getQUEST() ; 
      $error = "backend submitExam() failed."; 
      $report = array("type" => "addA", "error" => $error); 
      echo json_encode ($report); //terminate 
  } else {
        echo $feedback; 
  }
      
 function submitExam($conn, $decoder) {
      $testId = $decoder['id'];
      $answers = $decoder['answers'];
      $comment = $decoder['comment'];
      $comment = addslashes($comment);
  
    foreach($answers as $x) {
        $QId = $x['id'];
        $text = $x['text'];
        $text = addslashes($text);
       // echo "<br<br>text = " . $text . "<br><br>";
        $sql1 = "UPDATE rd248.QuestionStudentRelation SET userAnswer = '$text' WHERE QuestionStudentRelation.questionId = '$QId' AND 
        QuestionStudentRelation.testId = '$testId' ";
        if ( ! $result1 = $conn->query($sql1)) { 
             $sqlerror1 = $conn->error; 
             $error .= "sql1: " . $sqlerror1 . " "; 
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
