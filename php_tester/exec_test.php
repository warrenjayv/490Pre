<?php
   /* tests the php exec to print out error parameters */


$source = '/afs/cad/u/w/b/wbv4/public_html/Middle/pytest.py';
$test = "python " . $source . " 2>&1";
$err = "";
if (! $ex = execom($source, $test, $err)) {
    echo "execom failed." . PHP_EOL; 
} else {
   echo "execom success." . PHP_EOL; 
}

function execom($source, $test, $err) {
  $exec = exec($test, $array, $status); 
  if($status){
    echo "program fail. " . PHP_EOL; 
    foreach ($array as $key=>$c) {
      //   echo $c . PHP_EOL; 
    }
    echo "status: " . $status . PHP_EOL; 
    foreach($array as $key=>$n) {
        if ($key == 0) {
            $end = stripos($n, ',', 0);
            $n = substr_replace($n, "Code failed to execute:", 0, $end+1); 
        } 
        if ($key == 2) {
            continue; 
        }
        $err .= trim($n) . " " . PHP_EOL;
        echo "key: " .  $key . "val: " . $n . PHP_EOL; 
    }
    echo "error : " . $err . PHP_EOL; 
    return 0; 
  } else {
    echo "program success . output: "; 
    foreach ($array as $key=>$c) {
        echo $c . PHP_EOL;
    }
    echo "status: " . $status . PHP_EOL; 
    return 1;
  }
}//execom

echo "error is now " . $err . PHP_EOL; 

?>
