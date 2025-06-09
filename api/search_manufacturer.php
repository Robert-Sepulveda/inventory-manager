<?php
$startTime = microtime(true);
include("../functions.php");
include("../datahandler.php");
//Needed: device type
$mid=$_REQUEST['mid'];
$did=$_REQUEST['did'];
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
if(isDidNull($did))
{
	$e = ['Null device id','NV',__FILE__,__LINE__];
	log_error($endPoint,$uri,$e,$errorLog);
	die();
}

// Check for formatting
$did=strtolower(trim($did));
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

$sql = "Select `status` from `manufacturers` where `manufacturer`='$mid'";
$m_result = queryData($dblink,$sql,$endPoint,$uri);
if($m_result->num_rows<=0)
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing manufacturer id.';
	$output[]='Action: none';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Manufacturer does not exist','DE',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}

if($did!=="all")
{
	// Check if device exists
	$sql = "Select `status` from `device_types` where `device_type`='$did'";
	$d_result = queryData($dblink,$sql,$endPoint,$uri);
	if($d_result->num_rows<=0)
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid or missing device id';
		$output[]='Action: none';
		$responseData=json_encode($output);
		echo $responseData;
		$e = ['Device type does not exist','DE',__FILE__,__LINE__];
		log_error($endPoint, $uri, $e, $errorLog);
		die();
	}
	$active = $d_result->fetch_row();
	$d_data = [$did=>$active[0]];
}
else
{
	$d_data = getStatus("device_type","device_types",$dblink,$endPoint,$uri);
}

// search database, return 1000 queries
$mid = "`manufacturer` = '$mid'";
if($did === "all")
	$did = "1=1";
else
	$did = "`device_type` = '$did'";

$sql = "Select * from `devices` where $mid and $did limit 1000";
$result = queryData($dblink,$sql,$endPoint,$uri);
if($result->num_rows<=0)
{
	$output[]='Status: Success';
	$output[]='MSG: No results found.';
	$output[]='Action: none';
	$responseData=json_encode($output);
	echo $responseData;
	$endTime=microtime(true);
	$execTime = round(($endTime - $startTime) * 1000,2);
	log_activity($endPoint,$uri,array($sql,$execTime),$successLog);
	die();
}
$devices=array();
$active = $m_result->fetch_row();
if($active[0] === "active")
	$status = "active";
else
	$status = "inactive";
while($data=$result->fetch_array(MYSQLI_ASSOC))
{
	$auto_id = $data['auto_id'];
	$serial = "SN-".$data['serial_number'];
	$sql = "Select 	`devices`.`auto_id` from `devices` inner join `inactive_devices` on `devices`.`auto_id` = `inactive_devices`.`device_id` where `devices`.`auto_id` = '$auto_id'";
	$s_result = queryData($dblink,$sql,$endPoint,$uri);
	if($status === "inactive" || $d_data[$data['device_type']] === "inactive" || $s_result->num_rows>0)
		$activity = "inactive";
	else
		$activity = "active";
	$devices[$data['auto_id']]=array($data['manufacturer'],$data['device_type'],$serial,$activity);
}
$output[]='Status: Success';
$output[]='MSG: '.json_encode($devices);
$output[]='Action: home';
$responseData=json_encode($output);
echo $responseData;
$endTime=microtime(true);
$execTime = round(($endTime - $startTime) * 1000,2);
log_activity($endPoint,$uri,array($sql,$execTime),$successLog);
die();
?>



























