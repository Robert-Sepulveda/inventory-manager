<?php
$startTime = microtime(true);
include("../functions.php");
include("../datahandler.php");
//Needed: device type
$did=$_REQUEST['did'];
$mid=$_REQUEST['mid'];
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
// Check for formatting
$did=strtolower(trim($did));
$mid=ucfirst(strtolower(trim($mid)));
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
else if(!isValidManu($mid))
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

// Check if device exists
$sql = "Select `status` from `device_types` where `device_type`='$did'";
$d_result = queryData($dblink,$sql,$endPoint,$uri);
if($d_result->num_rows<=0)
{
	$output[]='Status: ERROR';
	$output[]='MSG: Invalid or missing device id.';
	$output[]='Action: none';
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Device type does not exist','DE',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
if($mid!=="All")
{
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
	$active = $m_result->fetch_row();
	$m_data = [$mid=>$active[0]];
}
else
{
	$m_data = getStatus("manufacturer","manufacturers",$dblink,$endPoint,$uri);
}
// search database, return 1000 queries
$did = "`device_type` = '$did'";
if($mid === "All")
	$mid = '1=1';
else
	$mid = "`manufacturer` = '$mid'";
$sql = "Select * from `devices` where $did and $mid limit 1000";
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
$active = $d_result->fetch_row();
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
	if($status === "inactive" || $m_data[$data['manufacturer']] === "inactive" || $s_result->num_rows>0)
		$activity = "inactive";
	else
		$activity = "active";
	$devices[$data['auto_id']]=array($data['device_type'],$data['manufacturer'],$serial,$activity);
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










































