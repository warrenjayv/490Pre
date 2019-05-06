<?php
/* recalculate the size of pts */

/*
 * 1. get the actual size of arrayofOuts by looping through it and output   
 *
 *
 */

$arrayofOuts = array('1', '2', 'None', 'None', 'None');
$outcount = 0; 
echo "size of arrayofOuts: " . sizeof($arrayofOuts) . PHP_EOL; 

foreach ($arrayofOuts as $key=>$k) {
    if((! isset($k)) || ($k == "None")) {
        continue; 
    } else {
       $outcount +=1; 
    }
}
echo "actual size: " . $outcount . PHP_EOL; 
?>
