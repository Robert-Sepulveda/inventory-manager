<?php
$startTime = microtime(true);
include("../functions.php");
include("../datahandler.php");
//Needed: device type
$did=$_REQUEST['did'];
$endPoint = $_SERVER['REQUEST_URI'];
$uri = $_SERVER['REMOTE_ADDR'];
$dblink = db_connect("equipment");
$output=array();
$errorLog = "/var/log/api_errors.log";
$successLog = "/var/log/api.log";
$db_errors="/var/log/db_errors.log";
// Check for null values
if(isDidNull($did))
{
	$e = ['Null device id','NV',__FILE__,__LINE__];
	log_error($endPoint,$uri,$e,$errorLog);
	die();
}
// Check for formatting
$did=strtolower(trim($did));
if(!isValidDevice($did))
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing device id.';
	$output[]='Action: list_device';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Invalid device formatting','IF',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
// Check if device already exists
$sql = "Select `auto_id` from `device_types` where `device_type`='$did'";
$d_result = queryData($dblink,$sql,$endPoint,$uri);
if($d_result->num_rows>0)
{
	$output[]='Status: ERROR';
	$output[]='MSG: Duplicate device.';
	$output[]='Action: list_device';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Device type already exists','AE',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
// insert to database
$sql = "Insert into `device_types` (`device_type`,`status`) values ('$did','active')";
queryData($dblink,$sql,$endPoint,$uri);
$output[]='Status: Success';
$output[]='MSG: New device successfully added.';
$output[]='Action: home';
$responseData=json_encode($output);
$endTime=microtime(true);
$execTime = round(($endTime - $startTime) * 1000,2);
log_activity($endPoint,$uri,array($sql,$execTime),$successLog);
echo $responseData;
die();
?>
