<?php


function curlop($ammo, $tgt) {
 	// $tgt = 'https://web.njit.edu/~wbv4/Middle/getTest2.php';
	$proj = curl_init();
	curl_setopt($proj , CURLOPT_URL, $tgt);
	curl_setopt($proj , CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($proj , CURLOPT_POSTFIELDS, json_encode($ammo));
	curl_setopt($proj , CURLOPT_FOLLOWLOCATION, true);  
	if ( ! $recoil = curl_exec($proj)) {
		//if (curl_exec($proj) === false) 
		echo "curop at " . $tgt . " curl_error:" . curl_error($proj) . "<br>";
	} else  {
		curl_close($proj); 
		return $recoil; 
	} 
}//getExam()

?> 
