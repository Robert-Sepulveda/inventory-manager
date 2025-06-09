<?php
$startTime = microtime(true);
include("../functions.php");
include("../datahandler.php");
//Needed: device type
$mid=$_REQUEST['mid'];
$endPoint = $_SERVER['REQUEST_URI'];
$uri = $_SERVER['REMOTE_ADDR'];
$dblink = db_connect("equipment");
$output=array();
$errorLog = "/var/log/api_errors.log";
$successLog = "/var/log/api.log";
$db_errors="/var/log/db_errors.log";
// Check for null values
if(isMidNull($mid))
{
	$e = ['Null manufacturer id','NV',__FILE__,__LINE__];
	log_error($endPoint,$uri,$e,$errorLog);
	die();
}
// Check for formatting
$mid=ucfirst(strtolower(trim($mid)));
if(!isValidManu($mid))
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing manufacturer id.';
	$output[]='Action: none';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Invalid manufacturer Formatting','IF',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
// Check if manu already exists
$sql = "Select `auto_id` from `manufacturers` where `manufacturer`='$mid'";
$m_result = queryData($dblink,$sql,$endPoint,$uri);
if($m_result->num_rows>0)
{
	$output[]='Status: ERROR';
	$output[]='MSG: Duplicate manufacturer.';
	$output[]='Action: none';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Manufacturer already exists','AE',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
// insert to database
$sql = "Insert into `manufacturers` (`manufacturer`,`status`) values ('$mid','active')";
queryData($dblink,$sql,$endPoint,$uri);
$output[]='Status: Success';
$output[]='MSG: New manufacturer successfuly added';
$output[]='Action: home';
$responseData=json_encode($output);
$endTime=microtime(true);
$execTime = round(($endTime - $startTime) * 1000,2);
log_activity($endPoint,$uri,array($sql,$execTime),$successLog);
echo $responseData;
die();
?>