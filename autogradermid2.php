<?php 
date_default_timezone_set("America/New_York"); 

include 'autolog.php'; 
include 'targets.php'; 

grade(); 

function grade() {
	/* task A : get all the tests with sub 1 */
	$target = targetIs('auto'); 
	$write = "[+] page accessed AUTOGRADER " . date("Y-m-d h:i:sa") . "\n"; 
	$write .= "********************************************************\n\n";
	$write .= "+ target file size of : " . $target . " = " . filesize($target) . "\n"; 
	autolog($write, $target); 
	if (filesize($target) >= 100000) {
		autoclear($target); 
		$write = "+ the log reached 10 mb; it has been cleared \n"; autolog($write, $target); 
	}
	$arrayofTests = array(); $submittedTests = array(); 
	$bullet = array("type" => "autograder", "rels" => array("0", "1")); 
	if (! $hole = getExam($bullet)) {
		$write = "error. failure to execute getexam\n"; 
		autolog($write, $target); 
	} else {
		$write = "+ getExam() obtained :\n"; 
		$write .= print_r($hole, true) + "\n";
		$write .=  "+ decoding the json file from getExam()\n"; 
		$hit = json_decode($hole, true); 
		$arrayofTests = $hit['tests'];  

		if (empty($hit)) { 
			$write = "+ getExam() returned null or empty.\n"; 
			autolog($write, $target); 
		}
	}

	$source = '/afs/cad/u/w/b/wbv4/public_html/Middle/firstpy.py'; 
	foreach ($arrayofTests as $key=>$x) {
		if($x['sub'] == 1) {
			$temp = array('id' => $x['id'], 'ques' => $x['ques']); 
			array_push($submittedTests, $temp); 
		}	
	}//foreach array as x 
	$write = "+ an array of submittedTests: \n"; 
	$write .= print_r($submittedTests, true) . "\n";
	$write .= "+ proceed with task B: obtain test cases\n"; 
	autolog($write, $target); 

	/*task B : get all the testcases for each question from each sub testId */ 
	$write = "+ obtaining the array of ques from subm. tests\n";
	autolog($write, $target); 

	foreach ($submittedTests as $y) {
		$ques = $y['ques']; $id = $y['id']; 
		$qIds=array(); 
		foreach ($ques as $z) { array_push($qIds, $z['id']); }//foreach ques as z
		$bullet2 = array("type" => "autograder", "qIds" => $qIds, "testId" => $id); 
		$write = "+ sending paramaters to getAnswers() :\n"; 
		$write .= print_r($bullet2, true) . "\n"; autolog($write, $target); 
		$arrayofAnswers = array(); 
		if (! $hole2 = getAnswers($bullet2)) {
			$write = "error; failure to execute getAnswer('bullet2')\n";
			autolog($write, $target); 
		} else { 
			$hit2 = json_decode($hole2, 'true'); 
			if (empty($hit2['answers'])) { 
				$write = "+ getAnswers(bullet2) answers array returned null or empty.\n";
				$write .= "+ continuing to the next test. \n";  
				autolog($write, $target); 
				continue; /* mv to next test */
			} else {
				$arrayofAnswers = $hit2['answers']; 
				$write = "+ getAnswers(bullet2) output and formed the arrayofAnswers: \n"; 
				$write .= print_r($hit2, true) . "\n"; 
				$write .= print_r($arrayofAnswers, true); autolog($write, $target); 
			}//if empty hit2 else 
		}//if hole2 getanswers 
		/*task C: insert function and output and text from each question for each test*/
		$counter=0;  
		foreach ($arrayofAnswers as $a) {
			$counter+=1; $write="counter is " . $counter . "\n"; autolog($write, $target); 
			$arrayofOuts = array(); 
			$qId = $a['qId']; $tests = $a['tests']; $text = $a['text']; 
			$text = str_ireplace("\x0D", "", $text); 
			$write =  "+ clearning the python file for " . $qId . "\n"; autolog($write, $target); 
			autolog($write, $target); clear($source); 
			$write = "+ writing answer to python file : " . $text . "\n";  
			append($source, $text); $write .= print_r($source, true) . "\n"; autolog($write, $target); 

			/* FUNCOM WILL BE IMPLEMENTED LATER. 
				 if ($fun = funcom($text, $tests)) {
				 $write = "+ replacing " . $text . " with " . $fun . "\n"; autolog($write, $target); 	
				 $text = $fun; 
				 $write = "+ writing answer to python file: " . $text . "\n"; autolog($write, $target); 
				 append($source, $text); 
				 } else {
				 $write = "+ writing answer to python file: " . $text . "\n"; autolog($write, $target);
				 append($source, $text); 
				 }//if fun funcom else 
			 */

			foreach($tests as $b) {
				$write = "+ current testcase :  " .  $b . "\n";  
				$function = getFunc($b); $write .= "+ current function : " . $function . "\n"; 
				$printout = "print(" . $function . ")"; $write .= "+ printout : " . $printout . "\n"; 
				$write .= "+ obtaining the output part to be compared later.  \n"; 
				$output = getOut($b); array_push($arrayofOuts, $output);
				$write .= "+ formed the arrayofOuts : \n" . print_r($arrayofOuts, true) . "\n"; 
				$write .= "+ now we are writing the testcase function " . $function . " on the python file : \n"; 			   
				 autolog($write, $target); append($source, $printout); 		 		
			}//foreach tests as b

			/* task D: run each testcase and compare (pass or fail) */ 
			if (! $ex = execom($source, $tests, $arrayofOuts, $id, $qId)) {
				$write = "+ execom failed. pls check logs. \n"; autolog($write, $target); 
				$write .= "+ calling updatePoints() to provide feedback\n"; 
				$feed = "b user function doesn't match test cases. python failed to execute. "; 
				$write .= "+ " . $feed . "\n"; autolog($write, $target); 
				$bullet3  = array('testId' => $id, 'qId' => $qId, 'feedback' => $feed, 'subpoints' => '.8'); 
        /*subpoints should be a percent*/
				if (! $hole3  = updatePoints($bullet3)) {
					$write = "+error; failure to execute updatePoints('bullet3') for fail execom()\n"; 
					autolog($write, $target); 
          continue; 
				}
				$hole3  = json_decode($hole3); 
				$write = "+ updatePoints() : \n"; $write .= print_r($hole3, true) . "\n";
				autolog($write, $target); 
			}
		}//foreach arrayofAnswers as a

	}//foreach submtests as y

}	//grade() 


function execom($source, $tests, $arrayofOuts, $id, $qId) {
	/* takes in the python source file, gets each output, and compares to arrayofOuts */ 
	$target = targetIs('auto'); 
	$write = "+ running execom() with pars for id : " . $id . ", qId : " . $qId .  "\n";
	$test = "python " . $source . " 2>&1" ; 
	$write .= "+ execom command: " . $test . "\n"; autolog($write, $target); 
	$exec = exec($test, $array, $status); 
	if (! $status ) { 
		foreach($array as $key=>$c) {
			$write .= "+ comparing " . $tests[$key] . " with output : " . $arrayofOuts[$key] . "\n"; 
			$write .= "+ comparing c: " . $c . " with output : " . $arrayofOuts[$key] . "\n"; 
			autolog($write, $target); 
			if ($c == $arrayofOuts[$key]) {
				$write = "pass!\n"; autolog($write, $target); 
				$write = "+ calling updatePoints() to provide feedback\n"; 
				/* g = good b = bad n = neutral */
				$feed = "g testcase '". $tests[$key] . "' passed!"; 
				$write .= "+ " . $feed . "\n"; autolog($write, $target);   
				$bullet = array('testId' => $id, 'qId' => $qId, 'feedback' => $feed, 'subpoints' => '0'); 
				if (! $hole = updatePoints($bullet)) {
					$write = "+ error; failure to execute updatePoints('bullet')\n";
					autolog($write, $target); 
          continue; 
				} 
				$hole = json_decode($hole); 
				$write = "+ updatePoints() : \n"; $write .= print_r($hole, true) . "\n";
         autolog($write, $target); 				
			}//if c == arrayofOuts 
			else {
				$write = "fail!\n"; autolog($write, $target); 
				$write = "+ calling updatePoints() to provide feedback\n"; 
				$feed = "b testcase '" . $tests[$key] . "' failed!"; 
				$write .= "+ " . $feed . "\n"; autolog($write, $target); 
				$bullet = array('testId' => $id, 'qId' => $qId, 'feedback' => $feed, 'subpoints' => '.20'); 
				if (! $hole = updatePoints($bullet)) {
					$write = "+ error; failure to execute updatePoints('bullet')\n";
					autolog($write, $target); 
          continue; 
				} 
				$hole = json_decode($hole); 
				$write = "+ updatePoints() : \n"; $write .= print_r($hole, true) . "\n"; autolog($write, $target); 		
			}//if c == arrayofOuts else
		}//foreach array as c 
	}//if ! status
	else { 
		$write = "+ exec() failed. returning 0. function did not match testcase or program syntax errors 		\n"; autolog($write, $target); 
		return 0; 
	}//if ! status else
	$write = "+ execom() returned 1\n"; autolog($write, $target); 
	return true;  
}//execom 

function funcom($text , $tests) {
	$target = targetIs('auto'); 
	/* go through each test and obtain the function, string search the text for the function */
	// if (!  $eqpos = strPos($str, '=')) 
	$size = sizeof($tests);  
	$miss = 0; 
	$write = "+ operating funcom for userAnswer: " . $text . "\n"; autolog($write, $target); 
	foreach($tests as $x) {
		$function = getFunc($x); 
		$write = "+ obtained function " . $function . " with funcom()\n"; autolog($write, $target); 
		$write = "+ finding if " . $function . " is in user answer " . $text  . "\n"; autolog($write, $target); 
		if ( ! $pos = strPos($text, $function)) {
			$miss += 1; 	
		} 
	}//foreach tests as x
	if ($miss >= $size) {
		$write = "+ function was not found at all in the user answer\n"; 
		$write .= "+ replacing user function with " . $function . "\n"; autolog($write, $target); 
		return $function; 
	} else {
		$write = "+ the function was found in the user answer\n"; 
		return 0; 
	}
}//funcom

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
	$target = targetIs('auto'); 
	$tgt = 'https://web.njit.edu/~wbv4/Middle/updatePoints2.php';
	$proj = curl_init();
	curl_setopt($proj , CURLOPT_URL, $tgt);
	curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
	curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
  curl_setopt($proj , CURLOPT_HTTPHEADER, array('Accept: application/json'));
	curl_setopt($proj , CURLOPT_FAILONERROR, true); 
  curl_setopt($proj , CURLOPT_SSL_VERIFYPEER, FALSE); 
  curl_setopt($proj , CURLOPT_SSL_VERIFYHOST, FALSE); 
  curl_setopt($proj, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)'); 
	if ( ! $recoil = curl_exec($proj)) {
		//if (curl_exec($proj) === false) 
		echo "type: updatePoints;  curl_error:" . curl_error($proj) . "<br>";
		$write  = "type: updatePoints; curl_error: " . curl_error($proj) . "\n"; 
		autolog($write, $target); 
	} else  {
	//	curl_close($proj);
		return $recoil; 
	}
}//updatepoints

function getFunc($str) 
{
	$target = targetIs('auto'); 
	if (!  $eqpos = strPos($str, '=')) {
		$write = "getFunc() failed;  eqpos was not found. \n";
		autolog($write, $target); 
	} else {
		if(! $func = substr($str,0,$eqpos)) {
			$write =  "substr failed in getFunc()  \n";
			autolog($write, $target); 
			return false;
		}
		trim($func);
		return $func; 
	}
}

function getOut($str) {
	$target = targetIs('auto'); 
	if(! $eqpos = strPos($str, '=')) {
		$write = "eqpos was not found in getOut(). \n"; 
		autolog($write); 
	} else {
		if (! $out  = substr($str, $eqpos+1, strlen($str))) {
			$write =  "substr failed at getOut()";
			autolog($write, $target); 
			return false;
		}
		trim($out); 
		return $out; 
	}
}

function clear($file) {
	$target = targetIs('auto'); 
	if (! $clean = fopen($file, 'w' )) {  //CLEAR THE FILE. 
		$write = "error: file failed to open file in clear()\n";
		autolog($write, $target);
	} else  {
		fwrite ($clean, "");
		fclose($clean); 
		return true; 
	}
}//clear()

function append($file, $input) {
	$target = targetIs('auto'); 
	if (! $target = fopen($file, 'a' )) {  
		$write =  "error: file failed to open file in append()\n";
		autolog($write, $target); 
	} else   {
		fwrite($target, PHP_EOL);
		fwrite($target, $input); 
		return true;
	}
}//append()
?>
