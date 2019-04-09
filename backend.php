<?php 
echo "backend page <br>"; 
$mux = file_get_contents('php://input'); 
$demux = json_decode($mux, true); 

// $user="";
// $pass=""; 
if (isset($demux['username']))
        $user = $demux['username'];
if (isset($demux['password']))
        $pass = $demux['password'];

echo $user . "<br>" ;
echo $pass . "<br>" ;


?>
