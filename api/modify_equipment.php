<?php
$startTime = microtime(true);
include("../functions.php");
include("../datahandler.php");
//Needed: device type
$sn=$_REQUEST['sn'];
$ndid=$_REQUEST['ndid'];
$nmid=$_REQUEST['nmid'];
$nsn=$_REQUEST['nsn'];
$status=$_REQUEST['status'];
$endPoint = $_SERVER['REQUEST_URI'];
$uri = $_SERVER['REMOTE_ADDR'];
$dblink = db_connect("equipment");
$output=array();
$errorLog = "/var/log/api_errors.log";
$successLog = "/var/log/api.log";
$db_errors="/var/log/db_errors.log";
// Check for null values
if(isSnNull($sn))
{
	$e = ['Null serial number','NV',__FILE__,__LINE__];
	log_error($endPoint,$uri,$e,$errorLog);
	die();
}
// Check for formatting
$sn=str_replace("SN-"," ",$sn);
$sn=trim($sn);
if(!isValidSN($sn))
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
// check sn exists
$sql="Select `auto_id` from `devices` where `serial_number`='$sn'";
$s_result = queryData($dblink,$sql,$endPoint,$uri);
if($s_result->num_rows<=0)
{
	$output[]='Status: ERROR';
	$output[]='MSG: Serial number does not exist';
	$output[]='Action: none';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Serial number does not exist','DE',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}

// check for modifier
// ndid
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
	// Check that new device is from the existing set
	$sql = "Select `auto_id` from `device_types` where `device_type`='$ndid'";
	$result = queryData($dblink,$sql,$endPoint,$uri);
	if($result->num_rows<=0)
	{
		$output[]='Status: ERROR';
		$output[]='MSG: invalid modifier';
		$output[]='Action: none';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Device type modifier does not exists','DE',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// update database
	$sql = "Update `devices` set `device_type`='$ndid' where `serial_number`='$sn'";
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
//nmid
if($nmid!==NULL && isset($_REQUEST['nmid']))
{
	// check formatting
	$nmid=ucfirst(strtolower(trim($nmid)));
	if(!isValidManu($nmid))
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid modifier.';
		$output[]='Action: list_manufacturer';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Invalid manufacturer modifier formatting','IF',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// Check that new manufacturer is from the existing set
	$sql = "Select `auto_id` from `manufacturers` where `manufacturer`='$nmid'";
	$result = queryData($dblink,$sql,$endPoint,$uri);
	if($result->num_rows<=0)
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid modifier.';
		$output[]='Action: none';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Manufacturer modifier does not exist.','DE',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// update database
	$sql = "Update `devices` set `manufacturer`='$nmid' where `serial_number`='$sn'";
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
//nsn
if($nsn!==NULL && isset($_REQUEST['nsn']))
{
	// check formatting
	$nsn=str_replace("SN-"," ",$nsn);
	$nsn=trim($nsn);
	if(!isValidSN($nsn))
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid modifier.';
		$output[]='Action: list_devices';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Invalid SN modifier formatting','IF',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// check that sn doesn't exist
	$sql="Select `auto_id` from `devices` where `serial_number`='$nsn'";
	$result = queryData($dblink,$sql,$endPoint,$uri);
	if($result->num_rows>0)
	{
		$output[]='Status: ERROR';
		$output[]='MSG: New serial number already exists.';
		$output[]='Action: none';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['SN modifier already exists','AE',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// update database
	$sql = "Update `devices` set `serial_number`='$nsn' where `serial_number`='$sn'";
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
//status
if($status!==NULL && isset($_REQUEST['status']))
{
	if($status!=="active" && $status!=="inactive")
	{
		$output[]='Status: ERROR';
		$output[]='MSG: invalid modifier';
		$output[]='Action: none';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Invalid status modifier','IF',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	// update status
	$data = $s_result->fetch_row();
	$auto_id=$data[0];
	if($status==="active")
		$sql = "Delete from `inactive_devices` where `device_id` = '$auto_id'";
	else
		$sql = "Insert into `inactive_devices` (`device_id`) values ('$auto_id')";
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
$output[]='MSG: Missing modifier';
$output[]='Action: none';
$responseData=json_encode($output);
echo $responseData;
$e = ['Null modifier','NV',__FILE__,__LINE__];
log_error($endPoint, $uri, $e, $errorLog);
die();
?>






























