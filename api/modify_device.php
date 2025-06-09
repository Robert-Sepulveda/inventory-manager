<?php
$startTime = microtime(true);
include("../functions.php");
include("../datahandler.php");
//Needed: device type
$did=$_REQUEST['did'];
$ndid=$_REQUEST['ndid'];
$status=$_REQUEST['status'];
$endPoint = $_SERVER['REQUEST_URI'];
$uri = $_SERVER['REMOTE_ADDR'];
$dblink = db_connect("equipment");
$output=array();
$errorLog = "/var/log/api_errors.log";
$successLog = "/var/log/api.log";
$db_errors="/var/log/db_errors.log";
// Check for null values, ndid and status could be null
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
	$output[]='Action: none';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Invalid device formatting','IF',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
// Check that device exists
$sql = "Select `auto_id` from `device_types` where `device_type`='$did'";
$result = queryData($dblink,$sql,$endPoint,$uri);
if($result->num_rows<=0)
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing device id.';
	$output[]='Action: none';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Device does not exist','DE',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
// if ndid is not null, follow same steps as above
if($ndid!==NULL && isset($_REQUEST['ndid']))
{
	// check formatting
	$ndid=strtolower(trim($ndid));
	if(!isValidDevice($ndid))
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid modifier.';
		$output[]='Action: none';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Invalid device modifier formatting','IF',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// Check that new device does not exist
	$sql = "Select `auto_id` from `device_types` where `device_type`='$ndid'";
	$result = queryData($dblink,$sql,$endPoint,$uri);
	if($result->num_rows>0)
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid modifier.';
		$output[]='Action: none';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Device type already exists','AE',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// update database
	$sql = "Update `device_types` set `device_type`='$ndid' where `device_type`='$did'";
	queryData($dblink,$sql,$endPoint,$uri);
	$sql = "Update `devices` set `device_type`='$ndid' where `device_type`='$did'";
	queryData($dblink,$sql,$endPoint,$uri);
	$output[]='Status: Success';
	$output[]='MSG: Device modified.';
	$output[]='Action: home';
	$responseData=json_encode($output);
	$endTime=microtime(true);
	$execTime = round(($endTime - $startTime) * 1000,2);
	log_activity($endPoint,$uri,array($sql,$execTime),$successLog);
	echo $responseData;
	die();
}
if($status!==NULL && isset($_REQUEST['status']))
{
	if($status!=="active" && $status!=="inactive")
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid modifier.';
		$output[]='Action: home';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Invalid modifier','IF',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// update status
	$sql = "Update `device_types` set `status`='$status' where `device_type`='$did'";
	queryData($dblink,$sql,$endPoint,$uri);
	$output[]='Status: Success';
	$output[]='MSG: Device modified.';
	$output[]='Action: home';
	$responseData=json_encode($output);
	$endTime=microtime(true);
	$execTime = round(($endTime - $startTime) * 1000,2);
	log_activity($endPoint,$uri,array($sql,$execTime),$successLog);
	echo $responseData;
	die();
}
$output[]='Status: ERROR';
$output[]='MSG: Invalid or missing modifier.';
$output[]='Action: none';
$responseData=json_encode($output);
echo $responseData;
$e = ['Missing modifier','NV',__FILE__,__LINE__];
log_error($endPoint, $uri, $e, $errorLog);
die();
?>































