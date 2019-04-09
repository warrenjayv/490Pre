<?php

$dbserver = "sql1.njit.edu";
$mySql_user = "rd248";
$mySql_password = "aZrVVjeCv";
$mySql_database = "rd248";



$response = file_get_contents('php://input');
$decoder = json_decode($response, true);
// $username= $decoder ['username'];
// $userPassword = $decoder['password'];
$id = $decoder['Id'];
$qnum = $decoder['qnum']; 

//creates connection

$conn =  mysqli_connect($dbserver, $mySql_user, $mySql_password, $mySql_database);

if(!$conn){
    die("connection Failure" .mysqli_connect_error());
}

mysqli_select_db($conn,$mySql_database);
/*
$sql = "SELECT Id FROM Users WHERE username ='$username' AND password = '$userPassword'";
$getId = mysqli_fetch_assoc(mysqli_query($conn,$sql));
$userId = $getID['Id'];
echo "backe"
if($userId== 1)
{
    session_start();
    $_SESSION['student'] = $username;
    $json = array('Output' => 'Login Success'=>,'Auth'=> 'student');
}
else{
    if ($userId == 2)
    {
        session_start();
        $_SESSION['teacher'] = $username;
        $json = array('Output' => 'Login Success','Auth'=> 'teacher');
    }
    else{
        session_start();
        $json = array('Output' => 'Login Failed','Auth'=> 'none');
   }

}
*/
//rerietrieve the question please! 
//test; 
// $qnum = 1; 
$sql = "SELECT * FROM Question WHERE `Id` = '$qnum' "; 
$get = mysqli_fetch_assoc(mysqli_query($conn, $sql)); 
$question = $get['question']; 
$json = array('question' => $question); 
mysqli_close(mysqli_connect());
echo json_encode($json);

?>
