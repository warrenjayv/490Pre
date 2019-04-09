<?php
    $source = 'python /afs/cad/u/w/b/wbv4/public_html/Middle/firstpy.py'; 
    $output = exec($source); //output is an array for every output of $source  
    echo $output; 
    
?>
