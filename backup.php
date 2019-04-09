<?php 

echo "sending dud payload (username/pass) to NJIT login\n"; 
$username = "wbv4";
$password = "payload"; 

$ch = curl_init(); 

/* LEGACY
// curl_setopt($ch, CURLOPT_URL, "https://webauth.njit.edu/idp/profile/cas/login;jsessionid=2BBF65D836AE3D5FB62989535FDBAFDF?execution=els1"); 
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
          "username" => $username,
	  "password" => $password,
))); //its recommended to use CURL_POSTFIELDS, there is an easier method... 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
// $result = curl_exec($ch); 
echo $result; 
*/

$payload = array('username' => $username, 'password' => $password); 
echo "sending payload...\n" . http_build_query($payload) . "\n"; 
curl_close($ch); 

$test = curl_init('https://my.njit.edu');
curl_setopt($test, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($test, CURLOPT_FOLLOWLOCATION, true); 
$result = curl_exec($test); //html 
/*
I now have the HTML of the login page. the goal now is to obtain form action URL. 
*/
$formBEG = "<form action="; //form url BEG = thisPos +  (length)  
$formEN = "method="post">"; //form url END = thisPOs + (length) 
if (curl_exec($test) ===  false) 
{
   echo "Curl error: " . curl_error($test) . " \n"; 
}
else
{
   echo "\n"; 
   echo "success!\n";
   echo $result; 
  // $newURL = curl_getinfo($test, CURLINFO_EFFECTIVE_URL ); 
}

curl_close($test); 
/*
I now have the HTML of the login page. the goal now is to obtain form action URL. 
*/
$formBEG = "<form action="; //form url BEG = thisPos +  (length)  
$formEN = "method="; //form url END = thisPOs + (length) 
/*
now we get the url path; start at 'form url BEGpos' and end at 'form url ENDpos' 
and will be appended to: https://webauth.njit.edu 
*/
$formAP = "https://webauth.njit.edu";
$begPOS = strpos($result, $formBEG); 
$endPOS = strpos($result, $formEN); 
/* test */
echo "formBEG is found at " . $begPOS . "\n"; 
echo "formEN is found at " . $endPOS . "\n"; 
/* LEGACY
$sh = curl_init($newURL); 
curl_setopt($sh, CURLOPT_POSTFIELDS, http_build_query($payload));
curl_setopt($sh, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($sh, CURLOPT_FOLLOWLOCATION, true); 
$result = curl_exec($sh); 
if (curl_exec($sh) === false)
{
     echo "curl error: " . curl_error($sh) . "\n";
}
else
{
     echo "\n"; 
    // echo $result;  //I now have the HTML of login page. I need the form action. 
}

curl_close($sh); 
*/
?>
