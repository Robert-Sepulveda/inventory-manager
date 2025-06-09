<?php
$startTime = microtime(true);
include("../functions.php");
$endPoint = $_SERVER['REQUEST_URI'];
$uri = $_SERVER['REMOTE_ADDR'];
$db_errors='/var/log/db_errors.log';
$successLog='/var/log/api.log';
$dblink = db_connect("equipment");
$sql = "Select `auto_id`,`manufacturer` from `manufacturers` where `status`='active'";
try{
	$result=$dblink->query($sql);
}catch (mysqli_sql_exception $e){
	$error = array($e->getMessage(),$e->getCode(),$e->getFile(),$e->getLine());
	log_error($endPoint, $uri,$error, $db_errors);
	$output=array();
	$output[]='Status: Error';
	$output[]='MSG: Query failed.';
	$output[]='Action: Home';
	$responseData=json_encode($output);
	echo $responseData;
	die();
}
$devices=array();
while($data=$result->fetch_array(MYSQLI_ASSOC))
{
	$devices[$data['auto_id']]=$data['manufacturer']; // use the auto_id to bind to the corresponding name
}
// add the data for all devices
$devices[]="All Manufacturers";
$endTime=microtime(true);
$execTime = round(($endTime - $startTime) * 1000,2);
// log successful call to endpoint
log_activity($endPoint,$uri,array($sql,$execTime),$successLog);
// build the json payload
$output=array(); // clear output array from anay potential previous data
$output[]='Status: Success'; // status success and error are the two we are worried about
$output[]='MSG: '.json_encode($devices);
$output[]='Action: Proceed';
$responseData=json_encode($output);
echo $responseData;
die();
?>