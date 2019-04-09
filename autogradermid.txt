<?php /* THE ALMIGHTY AUTOGRADER SCRIPT */ 

date_default_timezone_set("America/New_York"); 
autoclear(); 
$write = "page accessed AUTOGRADER" . date("Y-m-d h:i:sa") . "\n";
autolog($write);

$submittedTests = array(); 
$arrayofAnswers = array(); 

$bullet = array("type" => "Autograde", "rels" => array("0", "1"));  
if (! $hole = getExam($bullet)) {
  $write = "error; failure to execute getExam('bullet')\n"; 
  autolog($write);
} else {
  /*
    echo "<br>getExam output: <br>";
    var_dump($hole);
     */
  $source = '/afs/cad/u/w/b/wbv4/public_html/Middle/firstpy.py';
  $source2 =  '/afs/cad/u/w/b/wbv4/public_html/Middle/firstpy.txt';
  $sourceA =  '/afs/cad/u/w/b/wbv4/public_html/Middle/autograderlogs/firstpy1.txt';
  $sourceB =  '/afs/cad/u/w/b/wbv4/public_html/Middle/autograderlogs/firstpy2.txt';

  $hit = json_decode($hole, true);
  $arrayofTests = $hit["tests"];
  //echo "<br> array of tests <br>"; 
  $write .=  " array of tests \n";
  //var_dump($arrayofTests);  
  //  $write .= print_r($arrayofTests, true) . "\n";
  //we now have qIds, and Subs
  //get the questions with Sub: 1 only
  foreach ($arrayofTests as $x) {
    if($x['sub'] == 1) {
      $temp = array("id" => $x['id'], "ques" => $x['ques']); 
      array_push($submittedTests, $temp); 
    }
  }

  //echo "<br><br> array of submitted Tests <br>";
  $write = "array of submitted tests \n";
  autolog($write);
  // var_dump($submittedTests); //composed of test id with an array of Questions
  $write = print_r($submittedTests, true) . "\n";
  autolog($write);
  // echo "<br><br>";
  $arrayofCases = array(); 
  $arrayofAnswers = array(); 
  $arrayofQuestions = array(); 
  $arrayofqIds = array(); 

  //echo "<br> cleaning the python file ... <br>"; 
  $write = "cleaning the python file ... \n";
  autolog($write);

  clear($source);  clear($source2);   

  foreach($submittedTests as $y) {
    $Questions = $y['ques']; 
    $testId = $y['id'];
    //echo "<br><br> array of questions for each test <br>" ;
    $write = "array of questions for each test \n";
    autolog($write);
    //var_dump($Questions);
    $write = print_r($Questions, true);
    autolog($write);

    foreach($Questions as $z) {
      array_push($arrayofqIds, $z['id']); 
    }

    $bullet2 = array("type" => "Autograde", "qIds" => $arrayofqIds, 'testId' => $testId); 
    if (! $hole2 = getAnswers($bullet2)) {
      $write = "error; failure to execute getAnswer('bullet2')\n";  
      autolog($write);
    } else {
      //echo "<br>getAnswers: <br>"; 
      $write = "results of getAnswers \n";
      autolog($write);
      //var_dump($hole2); 
      $write = print_r($hole2, true) . "\n";
      autolog($write);
      $hole2 = json_decode($hole2, 'true'); 
      $arrayofAnswers = $hole2['answers']; //testcases
    } 

    foreach ($arrayofAnswers as $a) {
      
      $qId = $a['qId'];
      $Tests = $a['tests']; //array of testcases 
      $Text = $a['text']; 
 
      $write = "clear the python file for " . $qId ."\n"; autolog($write); 
      clear($source); clear($source2); 

      //echo "<br> writing this answer to python file : " . $Text . "<br>";
      $write .=  "writing this answer to python file : " . print_r($Text, true) . "\n"; 
      autolog($write); 
      append($source, $Text);  
      append($source2, $Text);  

      //echo "<br>array of testcases <br>";
      $write = "array of testcases \n";
      $write .= "question Id: " . $qId . "\n"; 
      autolog($write);
      //var_dump($Tests);
      $write = print_r($Tests, true) . "\n";
      autolog($write);

      //echo "<br>"; 
      /* 
              $list = array(0=>'string1', 'foo'=>'string2', 42=>'string3');
              $index = array_search('string2', array_values($list));
              print "$index\n";
             */

      $arrayofOutputs = array();

      foreach($Tests as $b) {
        //$index = array_search($b, array_values($Tests)); 
        $write = "current testcase: \n";
        autolog($write);
        //echo "<br>" . $b . "<br>"; 
        $write = $b . "\n";
        autolog($write);

        $function = getFunc($b);
       // echo "<br> function = " . $function . "<br>"; 
        $write = "current function : " . $function . "\n";
        autolog($write);
        //print();
        $printout = "print("; 
        $printout .= $function;
        $printout .=")";
        $output = getOut($b);
        array_push($arrayofOutputs, $output);  
        //echo "output = " . $output . "<br>"; 
        $write = "current output = " . $output . "\n";
        autolog($write);

        //echo "<br> writing the function to python file <br>"; 
        $write = "writing the function to python file\n";
        autolog($write);
        append($source, $printout);  append($source2, $printout);
      }//foreach tests as b

      append($source, PHP_EOL); 
      //echo "check the input here: <br><br>";
      $write = "check the input here: \n";
      //echo '<a href="https://web.njit.edu/~wbv4/Middle/firstpy.txt"> firstpy.txt </a>';
      $write .= "https://web.njit.edu/~wbv4/Middle/firstpy.txt \n ";
      autolog($write);

      //lets grade it now. 
      $test = 'python '; 
      $test .= $source; 
      //echo "<br> executing : " . $test . "<br>"; 
      $write = "executing : " . $test . "\n";
      autolog($write);
      $exec = exec($test, $array, $status);
      if (! $status) {
        foreach($array as $c) {
          $index = array_search($c, array_values($array)); 
          //echo "qId = " . $qId . " Testcase : " . $index . ", " . $Tests[$index] . "<br>"; 
          //echo "output of exec = " . $c . "<br>";
          $write = "qId = " . $qId . " Testcase : " . $index . ", " . $Tests[$index] . "\n";
          $write .= "output of exec = " . $c . "\n";
          autolog($write);
          
          //echo "compare exec with testcase output : <br>"; 
          //echo "comparing " . $c . " = " . $arrayofOutputs[$index] . "<br>";  
          $write = "compare exec with testcase output : \n";
          $write .= "comparing " . $c . " = " . $arrayofOutputs[$index] . "\n"; 
          autolog($write);
          
          //compare each exec output with output.   
          if ($c == $arrayofOutputs[$index]) {
            //echo "pass!<br>"; 
            $write = "pass!\n";
            autolog($write);
          } else { 
            //echo "fail!<br>";
            $write = "fail!\n";
            //echo "updatePoints<br>"; 
            $write .= "results from updatePoints()\n";
            autolog($write);
            $points = '0'; 
            $bullet3 = array('type' => 'updatePoints', 'testId' => $testId, 'qId' => $qId, 'feedback' => $Tests[$index] . " failed!", 'points' => $points);  
            if (! $hole3 = updatePoints($bullet3)) {
              $write .= "error; failure to execute getAnswer('bullet3')\n";  
              //echo "<br>updatePoints failed!<br>";
              $write .= "updatePoints() fialed!\n";
              autolog($write);
            } else {
              //echo "<br> updatePoints was succesful! <br>";
              //echo "<br> printing decoded hit3 <br>"; 
              $write = "updatePoints() was succesful! \n";
              $write .= "printing updatePoints() output \n";
              //var_dump($hole3); 
              $write .= print_r($hole3, true) . "\n";
              $hit3 = json_decode($hole3); 
              $write .= "printing decoded updatePonts() output \n";
              //var_dump($hit3);
              $write .= print_r($hit3, true) . "\n";
              autolog($write);
              //echo "<br>";
            } //if hole3 is succesful.      
          }
        } //foreach array as c
      }/*status*/ else {
        //echo "exec failed!<br>"; 
        //echo $status; 
        $write = "exec failed!\n";
        $write .= "status " . $status . "\n"; 
	$write .= "updating points. calling updatePoints()\n";
        autolog($write);
	$points = '0';
        $bullet4 = array('type' => 'updatePoints', 'testId' => $testId, 'qId' => $qId, 'feedback' => $Text . " failed to match any testcases",
	'points' => $points); 
	    if (! $hole4 = updatePoints($bullet4)) {
                $write = "error; failure to execute getAnswer('bullet4') \n";
		$write .= "updatePoints() failed!\n";
		autolog($write); 
	    } else {
                $write = "updatePoints('bullet4') was succesful.\n"; 
		$write = "printing updatePoints('bullet4') output. \n"; 
		$hit4 = json_decode($hole4); 
		$write .= print_r($hit4, true) . "\n";
		autolog($write); 
	    } //if hole4 is succesful
      }
      
    } //foreach arrayofanswers as $a   

  }//foreach submittedtests as $y 
} //else, if (! $hole = getExam() ) 

/***********************************UTILITIES***********************************************************/
function autolog($input) {
  if (! $file = fopen('/afs/cad/u/w/b/wbv4/public_html/Middle/autolog.txt', 'a')){
    echo "autolog.txt failed to 'fopen' to write \n";
    return 0; 
  } else {

    if (! fwrite($file, $input)) {
      echo "autolog failed to write \n";
      return 0;
    }
    return 1;
  }
}//autolog()
function autoclear() {
  if (! $file = fopen('/afs/cad/u/w/b/wbv4/public_html/Middle/autolog.txt', 'w')){
    echo "'autolog.txt' failed to 'fopen' to clear \n";
    return 0; 
  } else {

    if (! fwrite($file, "\n")) {
      echo "clearing 'autolog.txt' failed \n";
      return 0;
    }  
  }
}//autolog()

function getExam($ammo) {
  $tgt = 'https://web.njit.edu/~wbv4/Middle/getTest2.php';
  $proj = curl_init();
  curl_setopt($proj , CURLOPT_URL, $tgt);
  curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
  curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
  if ( ! $recoil = curl_exec($proj)) {
    //if (curl_exec($proj) === false) 
    echo "type: getT;  curl_error:" . curl_error($proj) . "<br>";
    $write  = "type: getT; curl_error: " . curl_error($proj) . "\n"; 
    autolog($write); 
  } else  {
    curl_close($proj); 
    return $recoil; 
  } 

}//getExam()

function getAnswers($ammo) { 
  $tgt = 'https://web.njit.edu/~wbv4/Middle/getAnswers.php';
  $proj = curl_init();
  curl_setopt($proj , CURLOPT_URL, $tgt);
  curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
  curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
  if ( ! $recoil = curl_exec($proj)) {
    //if (curl_exec($proj) === false) 
    echo "type: getAnswer;  curl_error:" . curl_error($proj) . "<br>";
    $write  .= "type: getAnswer; curl_error: " . curl_error($proj) . "\n"; 
    autolog($write); 
  } else  {
    curl_close($proj); 
    return $recoil; 
  }
}//getAnswer()

function updatePoints($ammo) {
  $tgt = 'https://web.njit.edu/~wbv4/Middle/updatePoints.php';
  $proj = curl_init();
  curl_setopt($proj , CURLOPT_URL, $tgt);
  curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
  curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
  if ( ! $recoil = curl_exec($proj)) {
    //if (curl_exec($proj) === false) 
    echo "type: updatePoints;  curl_error:" . curl_error($proj) . "<br>";
    $write  = "type: updatePoints; curl_error: " . curl_error($proj) . "\n"; 
    autolog($write); 
  } else  {
    curl_close($proj); 
    return $recoil; 
  }
}//updatePoints 

/*********************************UTILITIES************************************************************/

/***************************************** PHP EXEC OPERATORS*******************/

//functions:
function getFunc($str) 
{
  if (!  $eqpos = strPos($str, '=')) {
     $write = "getFunc() failed;  eqpos was not found. \n";
     autolog($write); 
  } else {
    if(! $func = substr($str,0,$eqpos)) {
      $write =  "substr failed in getFunc()  \n";
      autolog($write); 
      return false;
    }
    trim($func);
    return $func; 
  }

}

function getOut($str) {
  if(! $eqpos = strPos($str, '=')) {
    $write = "eqpos was not found in getOut(). \n"; 
    autolog($write); 
  } else {
    if (! $out  = substr($str, $eqpos+1, strlen($str))) {
      $write =  "substr failed at getOut()";
      autolog($write); 
      return false;
    }
    trim($out); 
    return $out; 

  }
}

function clear($file) {
  if (! $clean = fopen($file, 'w' )) {  //CLEAR THE FILE. 
    $write = "error: file failed to open file in clear()\n";
    autolog($write);
  } else  {
    fwrite ($clean, "");
    fclose($clean); 
    return true; 
  }
}

function append($file, $input) {
  if (! $target = fopen($file, 'a' )) {  
    $write =  "error: file failed to open file in append()\n";
    autolog($write); 
  } else   {
    fwrite($target, PHP_EOL); 
    fwrite($target, $input); 
    return true;       
  }
}

//******************************************************************************


/*worklog 03/142019
0. purpose: this script will autograde every single question with sub: 1. 
1. grab all tests with sub: 1 with their userAnswers and testCases
*/
?>
