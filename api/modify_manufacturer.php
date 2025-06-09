<?php
$startTime = microtime(true);
include("../functions.php");
include("../datahandler.php");
//Needed: device type
$mid=$_REQUEST['mid'];
$nmid=$_REQUEST['nmid'];
$status=$_REQUEST['status'];
$endPoint = $_SERVER['REQUEST_URI'];
$uri = $_SERVER['REMOTE_ADDR'];
$dblink = db_connect("equipment");
$output=array();
$errorLog = "/var/log/api_errors.log";
$successLog = "/var/log/api.log";
$db_errors="/var/log/db_errors.log";
// Check for null values, ndid and status could be null
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
	$output[]='Action: list_manufacturer';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Invalid manufacturer Formatting','IF',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
// Check that device exists
$sql = "Select `auto_id` from `manufacturers` where `manufacturer`='$mid'";
$result = queryData($dblink,$sql,$endPoint,$uri);
if($result->num_rows<=0)
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing manufacturer id.';
	$output[]='Action: none';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Manufacturer does not exist','AE',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
// if ndid is not null, follow same steps as above
if($nmid!==NULL && isset($_REQUEST['nmid']))
{
	// check formatting
	$nmid=ucfirst(strtolower(trim($nmid)));
	if(!isValidManu($nmid))
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid modifier';
		$output[]='Action: list_manufacturer';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Invalid manufacturer modifier formatting','IF',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// Check that new device does not exist
	$sql = "Select `auto_id` from `manufacturers` where `manufacturer`='$nmid'";
	$result = queryData($dblink,$sql,$endPoint,$uri);
	if($result->num_rows>0)
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid modifier.';
		$output[]='Action: none';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Manufacturer modifier already exists','AE',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// update database
	$sql = "Update `manufacturers` set `manufacturer`='$nmid' where `manufacturer`='$mid'";
	queryData($dblink,$sql,$endPoint,$uri);
	$sql = "Update `devices` set `manufacturer`='$nmid' where `manufacturer`='$mid'";
	queryData($dblink,$sql,$endPoint,$uri);
	$output[]='Status: Success';
	$output[]='MSG: Manufacturer modified.';
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
		$output[]='Action: none';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Invalid modifier','IF',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// update status
	$sql = "Update `manufacturers` set `status`='$status' where `manufacturer`='$mid'";
	queryData($dblink,$sql,$endPoint,$uri);
	$output[]='Status: Success';
	$output[]='MSG: Manufacturer modified.';
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































