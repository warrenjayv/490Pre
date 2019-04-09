<?php
/* the purpose of this file is to write to a file, keeping logs of php programs for troubleshooting */
/* include in the program to utilize */ 
/* utilization: $input is the string to write, $target is the file to write to. */ 

function autolog($input, $target) {
    if (! $file = fopen($target, 'a')) { 
	echo "failed to open " . $target . "\n"; 
	return 0 ; 
    } else {
	if (! fwrite($file, $input)) {
	    echo "autolog writer for " . $target . " failed. \n"; 
	    return 0; 
	}
	return 1; //succesful write. 
    }

}//autolog() 

function autoclear($target) {
  if (! $file = fopen($target, 'w')){
    echo "'autolog.txt' failed to 'fopen' to clear \n";
    return 0; 
  } else {

    if (! fwrite($file, "\n")) {
      echo "clearing 'autolog.txt' failed \n";
      return 0;
    }  
  }
}//autolog()

?>
