<?php
	
	function authenticate($username, $password){
	
		
		$ch = curl_init();
		$ar = array(
		 	"ucid" => $username,
		 	"pass" => $password
		);
	
		curl_setopt_array($ch, array(
			CURLOPT_URL => "https://aevitepr2.njit.edu/myhousing/login.cfm",
			CURLOPT_POSTFIELDS => http_build_query($ar),
			CURLOPT_RETURNTRANSFER => 1
		));
		$result = curl_exec($ch);
		curl_close($ch);
		// Return validation bool
		return strpos($result, "Please login using your UCID") != true;
	}
	
	$json_data = file_get_contents("php://input");
	$data = json_decode($json_data, true);
	$username = $data['username'];
	$password = $data['password'];
	if(authenticate($username, $password))
		echo '{"valid":"valid"}';
	else{
		$ch = curl_init();
	
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => "",
			CURLOPT_USERAGENT => "POST Request to back",
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $json_data
		));
	 
		$response = curl_exec($ch);
		curl_close($ch);
		echo $response;
	}
	?>
