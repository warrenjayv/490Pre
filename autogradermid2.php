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
		$write .= print_r($hole) + "\n";
		$write .=  "+ decoding the json file from getExam()\n"; 
		$hit = json_decode($hole, true); 
		$arrayofTests = $hit['tests'];  
	}
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
		$arrayofAnswers = $hit2['answers']; 
	}
 }//foreach submtests as y

}	//grade() 

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
}//updatepoints

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
}//clear()

function append($file, $input) {
  if (! $target = fopen($file, 'a' )) {  
    $write =  "error: file failed to open file in append()\n";
    autolog($write); 
  } else   {
    fwrite($target, PHP_EOL);
    fwrite($target, $input); 
    return true;
  }
}//append()
?>
