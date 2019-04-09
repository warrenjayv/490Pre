<?php
//include 'Autograder.php';
date_default_timezone_set("America/New_York"); 
/* stores activities via a log */ 

$log = fopen('/afs/cad/u/w/b/wbv4/public_html/Middle/log.txt', 'a'); 
$write .= "page accessed " . date("Y-m-d h:i:sa") . "\n"; 
// echo $write; 
/*get the user name and password */
$mux = file_get_contents("php://input"); 
$demux = json_decode($mux, true); 
//var_dump($demux); 

if (isset($demux['username'])) 
   $user = $demux['username']; 
if (isset($demux['password'])) {
   $pass = $demux['password']; 
   echo loginVERIFY($user, $pass);  
   }


/*legacy/deprecated
if (isset($demux['question'])) {
   //print question with answers. 
   // tracer($demux); 
   $question = $demux['question']; 
   echo $question . "<br>" ; 
   $answers = $demux['answers']; 
   $answer1 = $answers[0];
   $answer2 = $answers[1];   
   echo "answer 1: " . $answer1 . '<br>'; 
   echo "answer 2: " . $answer2 . '<br>'; 
   }
*/

if (isset($demux['qnum'])) {
   $qnum = $demux['qnum']; 
   $cart  = getQUEST($qnum); 
   echo $cart; 
   } 


if(isset($demux['type']) && ($demux['type'] == 'addQ')) {
   $note =  "running addQUEST() \n"; 
   //$description = $demux['Desc'];
   //$description = "testing questions";  
  // $testcases = $demux['Tests']; 
   //$difficulty = $demux['Diff']; 
   $write .= trace($note); 
  // $ammo = array('Desc' => $description, 'Diff' => $difficulty, 'Tests' => $testcases); 
   $ammo = $demux; 
   $testout = addQUEST($ammo);
   echo $testout;  
  
} //if addq

if(isset($demux['type']) && ($demux['type'] == 'getQ')) {
   $note = "running getQUEST() \n"; 
   $difficulty = $demux['diffs']; 
   $write .= trace($note); 
   echo getQUEST($difficulty); 
   
} //if search Q

if (isset($demux['type']) && ($demux['type'] == 'addT')) {
                      /* TESTPOINT */
                   
   $note = "running addExam() \n";
   $write .= trace($note); 
   echo addExam($demux);
}//if add exam

//$demux = array("Type" => "GetTest"); 
if (isset($demux['type'])  && ($demux['type'] == 'getT')) {
                        /* TESTPOINT */
                       // $test = array("Release" => "0", "TestIds" => array("2")); 
   $note = "running getExam() \n";
   $write .= trace($note); 
   echo getExam($demux); 
}// if get exam

if (! empty($demux['type']) && ($demux['type'] == 'addA')) {
    $note = "running submitExam() \n";
    $write .= trace($note);
    echo submitExam($demux); 
}//if submit exam

if (! empty($demux['type']) && ($demux['type'] == 'getAnswers')) {
    $note = "running getAnswer() \n";
    $write .= trace($note);
    //echo getAnswers($demux); 
}//if submit exam

if (! empty($demux['type']) && ($demux['type'] == 'getA')) {
    $note = "running getAttempt() \n"; 
    $write .= trace($note); 
    echo getAttempt($demux); 
}

/*
send a payload to backend.
echo "<br> sent a payload to back. <br>"; 
$url = "https://web.njit.edu/~wbv4/Middle/backend.php";
*/
//************************console and log******************************
function trace($note) {
       $global = $note; 
       $global .= date("Y-m-d h:i:sa") . "\n";
       //$global .= $trail . "\n"; 
       return $global; 
} //trace(); 
//************************login****************************************

function loginVERIFY($user, $pass) {
// $url = "https://web.njit.edu/~rd248/download/backend.php";
    $url = "https://web.njit.edu/~rd248/download/Student&Teacher.php"; 
    $payload = array("username" => $user, "password" => $pass, "njit" => $njit);
// echo http_build_query($payload) . "<br>"; 
    $fac2 = curl_init(); 
    curl_setopt($fac2, CURLOPT_URL, $url);
    curl_setopt($fac2, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($fac2, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($fac2, CURLOPT_FOLLOWLOCATION, true);  
    if (curl_exec($fac2) === false) {
         echo "curl_error:" . curl_error($fac2) . "<br>"; 
    } else {
    $result2 = curl_exec($fac2); 
    curl_close($fac2); 
    return $result2; 
    
    }
} //loginVERIFY()

//************************get question**********************************

function getQUEST($ammo) {
    $tgt  = 'https://web.njit.edu/~wbv4/Middle/getQuestion.php'; 
    //$tgt  = 'https://web.njit.edu/~rd248/download/beta/getQuestion.php' ; 
    $proj = curl_init(); 
    curl_setopt($proj , CURLOPT_URL, $tgt); 
    curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
    curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
    if (! $recoil = curl_exec($proj)) { 
         echo "type: getQ;  curl_error:" . curl_error($proj) . "<br>"; 
	       $_GLOBALS['write'] .= "type: getQ; curl_error: " . curl_error($proj) . "\n"; 
    //$recoil = curl_exec($proj); 
    } else {    
    curl_close($proj); 
    return  $recoil; 
    }
}//getQUEST(); 

//*************************add question**********************************

function addQUEST($ammo) {
    //$tgt = 'https://web.njit.edu/~rd248/download/beta/InsertQuestion.php'; 
    $tgt  = 'https://web.njit.edu/~wbv4/Middle/addQuestion.php'; 
    $proj  = curl_init(); 
    curl_setopt($proj , CURLOPT_URL, $tgt);
    curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
    curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
    if ( ! $recoil = curl_exec($proj)) {
    //if (curl_exec($proj) === false) 
         echo "type: addQ;  curl_error:" . curl_error($proj) . "<br>";
	       $_GLOBALS['write'] .= "type: addQ; curl_error: " . curl_error($proj) . "\n"; 
    } else  {
     curl_close($proj); 
     return $recoil; 
    } 
    //$recoil = curl_exec($proj); 
   
}//addQUEST();  

// fwrite($log, $write); 

if ($error = error_get_last()) {
   $_GLOBALS['write'] .= $error['message'] . "\n" ;    
}

//*************************add exam***********************************

function addExam($ammo) {
      //$tgt = 'https://web.njit.edu/~rd248/download/beta/MakeTest.php';
      $tgt = 'https://web.njit.edu/~wbv4/Middle/MakeTest2.php';
      $proj = curl_init();
      curl_setopt($proj , CURLOPT_URL, $tgt);
      curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
      curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
      if ( ! $recoil = curl_exec($proj)) {
      //if (curl_exec($proj) === false) 
         echo "type: addT;  curl_error:" . curl_error($proj) . "<br>";
	       $_GLOBALS['write'] .= "type: addT; curl_error: " . curl_error($proj) . "\n"; 
      } else  {
        curl_close($proj); 
        return $recoil; 
      } 
      
}//addExam()
// file_put_contents($log, $write, FILE_APPEND); 
fwrite($log, $write); 
fclose($log); 
//****************************get exam**********************************

function getExam($ammo) {
      //$tgt = 'https://web.njit.edu/~rd248/download/beta/getTest2.php';
      $tgt = 'https://web.njit.edu/~wbv4/Middle/getTest2.php';
      $proj = curl_init();
      curl_setopt($proj , CURLOPT_URL, $tgt);
      curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
      curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
      if ( ! $recoil = curl_exec($proj)) {
      //if (curl_exec($proj) === false) 
         echo "type: getT;  curl_error:" . curl_error($proj) . "<br>";
	       $_GLOBALS['write'] .= "type: getT; curl_error: " . curl_error($proj) . "\n"; 
      } else  {
        curl_close($proj); 
        return $recoil; 
      } 
      
}//getExam()

//****************************get exam**********************************

function submitExam($ammo) {
      //$tgt = 'https://web.njit.edu/~rd248/download/beta/SubmitTest.php';
      $tgt = 'https://web.njit.edu/~wbv4/Middle/SubmitTest2.php';
      $proj = curl_init();
      curl_setopt($proj , CURLOPT_URL, $tgt);
      curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
      curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
      if ( ! $recoil = curl_exec($proj)) {
      //if (curl_exec($proj) === false) 
         echo "type: addA;  curl_error:" . curl_error($proj) . "<br>";
	       $_GLOBALS['write'] .= "type: addA; curl_error: " . curl_error($proj) . "\n"; 
      } else  {
        curl_close($proj); 
        return $recoil; 
      } 
}//getExam()

//****************************get answers**********************************

function getAnswers($ammo) {
      $tgt = 'https://web.njit.edu/~rd248/download/beta/getAnswers.php'; 
      //$tgt = 'https://web.njit.edu/~wbv4/Middle/getAnswers.php';
      $proj = curl_init();
      curl_setopt($proj , CURLOPT_URL, $tgt);
      curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
      curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
      if ( ! $recoil = curl_exec($proj)) {
      //if (curl_exec($proj) === false) 
         echo "type: getAnswers;  curl_error:" . curl_error($proj) . "<br>";
	       $_GLOBALS['write'] .= "type: getAnswers; curl_error: " . curl_error($proj) . "\n"; 
      } else  {
        curl_close($proj); 
        return $recoil; 
      } 
}//getExam()


//****************************get attempt **********************************

function getAttempt($ammo) {
      $tgt = 'https://web.njit.edu/~wbv4/Middle/getAttempt.php'; 
      $proj = curl_init();
      curl_setopt($proj , CURLOPT_URL, $tgt);
      curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
      curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
      if ( ! $recoil = curl_exec($proj)) {
      //if (curl_exec($proj) === false) 
         echo "type: getA;  curl_error:" . curl_error($proj) . "<br>";
	       $_GLOBALS['write'] .= "type: getA; curl_error: " . curl_error($proj) . "\n"; 
      } else  {
        curl_close($proj); 
        return $recoil; 
      } 
}//getAttempt()

?>
