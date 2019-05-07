
<?php
$text = $text = "def addThree(a,b,c)
return a + b + c";

echo hiddencharkiller($text); 

function hiddencharkiller($text) {
	/* sanitize the moth f*** */
 if (preg_match('/[[:cntrl:]]/', $text ))  
    	$newstring = preg_replace( '/[[:cntrl:]]/', PHP_EOL, $text);
      echo $newstring;
	
}
