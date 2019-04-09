<?php

     if (! $file = fopen('/afs/cad/u/w/b/wbv4/public_html/Middle/log.txt', 'w' ))
            echo "page failed to open file"; 
     else
            $msg = 'eat me faggot'; 
     echo $msg; 
    //  file_put_contents($file, $msg, FILE_APPEND); 
    fwrite($file, $msg); 
    fclose($file); 
?>
