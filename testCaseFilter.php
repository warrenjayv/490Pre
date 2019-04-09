<?php

$testcase = "yesorno(a, b)     = yes";
$function = " "; 
$output = " "; 

$testcase2 = "areyouokay(a, b) = no";

echo "testcase: " . $testcase . "<br>"; 
echo "testcase2: " . $testcase2 . "<br>"; 

if(! $result =  getFunc($testcase)) {
     echo '$getFunc failed<br>';
} else {
     echo 'function = ' .  $result . '<br>' ;
}

if (! $output = getOut($testcase)){
     echo 'getOut() failed<br>';
} else {
     echo 'output = ' .  $output . '<br>'  ; 
}



if(! $result =  getFunc($testcase2)) {
     echo '$getFunc failed<br>';
} else {
     echo 'function = ' .  $result . '<br>' ;
}

if (! $output = getOut($testcase2)){
     echo 'getOut() failed<br>';
} else {
     echo 'output = ' .  $output . '<br>'  ; 
}

echo "<br><br>";
echo "now we will append to a python file and execute. <br><br>"; 

$testcase3 = "add(2, 3) = 5"; 
echo "testcase3 = " . $testcase3 . "<br><br>"; 

$function = getFunc($testcase3); 
echo "function = " . $function . "<br>";

$output = getOut($testcase3); 
echo "output = ". $output . "<br>"; 
echo "writing to ' firstpy.py ' "; 
$source = '/afs/cad/u/w/b/wbv4/public_html/Middle/firstpy.py';
$source2 =  '/afs/cad/u/w/b/wbv4/public_html/Middle/firstpy.txt';
if (clear($source)) { 
      echo "cleared the file to write to before appending<br>"; 
}       
if (clear($source2)) {
      //echo "cleared the file to write to before appending<br>"; 
}

echo "appending a function 'def add(a, b): return a+b' to first.py<br>";

append($source, 'def add(a, b): return a+b');
append($source2, 'add(a, b): return a+b');

echo "append 'function' to 'firstpy.py' <br>";
$printout = "print("; 
$printout .= $function;
$printout .=")";
if (! append($source, $printout))
        echo "append() failed! <br>";
if (! append($source2, $printout)) 
        echo "append() failed! <br>"; 

echo "check the input here: <br><br>";
echo '<a href="https://web.njit.edu/~wbv4/Middle/firstpy.txt"> firstpy.txt </a>';

echo "<br><br>";
echo "now I will use php exec to get the output <br>"; 

$test = 'python '; 
$test .= $source; 
echo "executing : " . $test . "<br>"; 
$exec = exec($test, $array, $status);
if (! $status) {
 foreach($array as $x)
        echo "output of exec = " . $x . "<br>"; 
} else {
        echo "exec failed!<br>"; 
        echo $status; 
}
/*********************************************************OPERATORS*******************/

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
  if (! $target = fopen($file, 'a' )) //CLEAR THE FILE. 
            echo "error: file failed to open file in append()<br>";
  else   {
            fwrite($target, PHP_EOL); 
            fwrite($target, $input); 
            return true;       
  }
}

?>
