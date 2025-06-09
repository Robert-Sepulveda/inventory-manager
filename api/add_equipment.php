<?php
$startTime = microtime(true);
include("../functions.php");
include("../datahandler.php");
//Needed: device type, manufacturer type, serial number
$did=$_REQUEST['did'];
$mid=$_REQUEST['mid'];
$sn=$_REQUEST['sn'];
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
else if(isMidNull($mid))
{
	$e = ['Null manufacturer id','NV',__FILE__,__LINE__];
	log_error($endPoint,$uri,$e,$errorLog);
	die();
}
else if(isSnNull($sn))
{
	$e = ['Null SN','NV',__FILE__,__LINE__];
	log_error($endPoint,$uri,$e,$errorLog);
	die();
}

// Check for formatting
$did=strtolower(trim($did));
$mid=ucfirst(strtolower(trim($mid)));
$sn=str_replace("SN-"," ",$sn);
$sn=trim($sn);
if(!isValidDevice($did))
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing device id.';
	$output[]='Action: list_devices';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Invalid device Formatting','IF',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
else if(!isValidManu($mid))
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
else if(!isValidSN($sn))
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing serial number.';
	$output[]='Action: list_devices';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Invalid sn Formatting','IF',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}

// DID
$sql = "Select `status` from `device_types` where `device_type`='$did'";
$d_result = queryData($dblink,$sql,$endPoint,$uri);
if($d_result->num_rows<=0)
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing device id.';
	$output[]='Action: list_devices';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Device type does not exist','DE',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}

// MID
$sql = "Select `status` from `manufacturers` where `manufacturer`='$mid'";
$m_result = queryData($dblink,$sql,$endPoint,$uri);
if($m_result->num_rows<=0)
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing manufacturer id.';
	$output[]='Action: list_manufacturer';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Manufacturer does not exist','DE',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}

// check if did and mid is active
$d_active = $d_result->fetch_row();
if($d_active[0] !== "active")
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing device id.';
	$output[]='Action: list_device';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Device not active','NA',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
$m_active = $m_result->fetch_row();
if($m_active[0] !== "active")
{
	$output[]='Status: ERROR';
	$output[]='MSG: MSG: Invalid or missing manufacturer id.';
	$output[]='Action: list_manufacturer';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Manufacturer not active','NA',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}

// Check if each element already exists
// SN
$sql="Select `auto_id` from `devices` where `serial_number`='$sn'";
$result = queryData($dblink,$sql,$endPoint,$uri);
if($result->num_rows>0)
{
	$output[]='Status: ERROR';
	$output[]='MSG: Duplicate serial number.';
	$output[]='Action: none';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['sn already exists','AE',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
// insert to database
$sql = "Insert into `devices` (`device_type`,`manufacturer`,`serial_number`) values ('$did','$mid','$sn')";
queryData($dblink,$sql,$endPoint,$uri);
$output[]='Status: Success';
$output[]='MSG: New equipement successfully added.';
$output[]='Action: home';
$responseData=json_encode($output);
$endTime=microtime(true);
$execTime = round(($endTime - $startTime) * 1000,2);
log_activity($endPoint,$uri,array($sql,$execTime),$successLog);
echo $responseData;
die();
?>














































