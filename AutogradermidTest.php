<?php /* THE ALMIGHTY AUTOGRADER SCRIPT */ 

 /*curl a request to backend to retrieve the following: based on sub:1, retrieve userAnswer, testcases */
 $log = fopen('/afs/cad/u/w/b/wbv4/public_html/Middle/log.txt', 'a'); 
 $write .= "page accessed AUTOGRADER" . date("Y-m-d h:i:sa") . "\n"; 
 
 $submittedTests = array(); 
 $arrayofAnswers = array(); 
 
 $bullet = array("Type" => "Autograde", "Rels" => array("0", "1"));  
 if (! $hole = getExam($bullet)) {
           $write.= "error; failure to execute getExam('bullet')\n"; 
 } else {
    
   fclose($log); 
   $source = '/afs/cad/u/w/b/wbv4/public_html/Middle/firstpy.py';
   $source2 =  '/afs/cad/u/w/b/wbv4/public_html/Middle/firstpy.txt';
   
   $hit = json_decode($hole, true);
   $arrayofTests = $hit["Tests"];
   var_dump($arrayofTests);  
   //we now have QIds, and Subs
   //get the questions with Sub: 1 only
    foreach ($arrayofTests as $x) {
            if($x['Sub'] == 1) {
                  $temp = array("Id" => $x['Id'], "Questions" => $x['Questions']); 
                  array_push($submittedTests, $temp); 
            }
    }

    echo "<br><br> aray of submitted Tests <br>";
    var_dump($submittedTests); //composed of test id with an array of Questions
    echo "<br><br>";
    $arrayofCases = array(); 
    $arrayofAnswers = array(); 
    $arrayofQuestions = array(); 
    $arrayofQIds = array(); 
    
    echo "<br> cleaning the python file ... <br>"; 
    clear($source);  clear($source2);   
    
    foreach($submittedTests as $y) {
           $Questions = $y['Questions']; 
           echo "<br><br> array of questions for each test <br>" ;
	         var_dump($Questions); 
                   
           foreach($Questions as $z) {
                 array_push($arrayofQIds, $z['Id']); 
           }
           $bullet2 = array("Type" => "Autograde", "QIds" => $arrayofQIds); 
           if (! $hole2 = getAnswers($bullet2)) {
                 $write.= "error; failure to execute getAnswer('bullet2')\n";  
            } else {
                 echo "<br>getAnswers: <br>"; 
		             var_dump($hole2); 
	         $hole2 = json_decode($hole2, 'true'); 
                 $arrayofAnswers = $hole2['Answers']; //testcases
            } 
      
          
            
       foreach ($arrayofAnswers as $a) {
              $QId = $a['QId'];
              $Tests = $a['Tests']; //array of testcases 
              $Text = $a['Text']; 
             
             echo "<br> writing this answer to python file : " . $Text . "<br>"; 
             append($source, $Text);  
             append($source2, $Text);  
             
             echo "<br>array of testcases <br>";
             var_dump($Tests); 
             echo "<br>"; 
             
             
             /* 
              $list = array(0=>'string1', 'foo'=>'string2', 42=>'string3');
              $index = array_search('string2', array_values($list));
              print "$index\n";
             */
             
             $arrayofOutputs = array();
             
             foreach($Tests as $b) {
                   //$index = array_search($b, array_values($Tests)); 
                   echo "<br>" . $b . "<br>"; 
                   
                   $function = getFunc($b);
                   echo "<br> function = " . $function . "<br>"; 
                   //print();
                        $printout = "print("; 
                        $printout .= $function;
                        $printout .=")";
                   $output = getOut($b);
                   array_push($arrayofOutputs, $output);  
                   echo "output = " . $output . "<br>"; 
                   
                   echo "<br> writing the function to python file <br>"; 
                   append($source, $printout);  append($source2, $printout);
             }//foreach tests as b
             
             append($source, PHP_EOL); 
             echo "check the input here: <br><br>";
             echo '<a href="https://web.njit.edu/~wbv4/Middle/firstpy.txt"> firstpy.txt </a>';
             
             //lets grade it now. 
             
             $test = 'python '; 
             $test .= $source; 
             echo "<br> executing : " . $test . "<br>"; 
             $exec = exec($test, $array, $status);
             if (! $status) {
                     foreach($array as $c) {
                           $index = array_search($c, array_values($array)); 
                           echo "QId = " . $QId . " Testcase : " . $index . ", " . $Tests[$index] . "<br>"; 
                           echo "output of exec = " . $c . "<br>";
                           
                           echo "compare exec with testcase output : <br>"; 
                           echo "comparing " . $c . " = " . $arrayofOutputs[$index] . "<br>";  
                           //compare each exec output with output.   
                           if ($c == $arrayofOutputs[$index]) {
                                  echo "pass!<br>"; 
                           } else { 
                                  echo "fail!<br>";   
                           }
                     } //foreach array as c
             } else {
                 echo "exec failed!<br>"; 
                 echo $status; 
             }
             
       } //foreach arrayofanswers as a   
  
    }//foreach submittedtests as y s
 } //else, if (! $hole = getExam() ) 



 
 /***********************************UTILITIES***********************************************************/

 function getExam($ammo) {
      $tgt = 'https://web.njit.edu/~wbv4/Middle/getTest2.php';
      $proj = curl_init();
      curl_setopt($proj , CURLOPT_URL, $tgt);
      curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
      curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
      if ( ! $recoil = curl_exec($proj)) {
      //if (curl_exec($proj) === false) 
         echo "type: GetTest;  curl_error:" . curl_error($proj) . "<br>";
	       $_GLOBALS['write'] .= "type: GetExam; curl_error: " . curl_error($proj) . "\n"; 
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
	       $_GLOBALS['write'] .= "type: getAnswer; curl_error: " . curl_error($proj) . "\n"; 
      } else  {
        curl_close($proj); 
        return $recoil; 
      }
}//getAnswer()

function updatePoints($ammo) {
      $tgt = 'https://web.njit.edu/~wbv4/Middle/getAnswers.php';
      $proj = curl_init();
      curl_setopt($proj , CURLOPT_URL, $tgt);
      curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
      curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
      if ( ! $recoil = curl_exec($proj)) {
      //if (curl_exec($proj) === false) 
         echo "type: updatePoints;  curl_error:" . curl_error($proj) . "<br>";
	       $_GLOBALS['write'] .= "type: updatePoints; curl_error: " . curl_error($proj) . "\n"; 
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
          echo "eqpos was not found.<br> ";
   } else {
          if(! $func = substr($str,0,$eqpos)) {
              echo "substr failed. <br>";
	      return false;
	  }
	  trim($func);
	  return $func; 
   }

}

function getOut($str) 
{
   if(! $eqpos = strPos($str, '=')) {
        echo "eqpos was not found in getOut(). <br> "; 
   } else {
        if (! $out  = substr($str, $eqpos+1, strlen($str))) {
	     echo "substr failed at getOut()";
	     return false;
        }
	trim($out); 
	return $out; 
	
   }
}

function clear($file) {
 if (! $clean = fopen($file, 'w' )) //CLEAR THE FILE. 
            echo "error: file failed to open file in clear()<br>";
 else   {
            fwrite ($clean, "");
            fclose($clean); 
            return true; 
 }
}

function append($file, $input) {
  if (! $target = fopen($file, 'a' ))  
            echo "error: file failed to open file in append()<br>";
  else   {
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
