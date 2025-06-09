<?php
$startTime = microtime(true);
include("../functions.php");
include("../datahandler.php");
//Needed: device type
$sn=$_REQUEST['sn'];
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
	$e = ['Null SN','NV',__FILE__,__LINE__];
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

// query
// SN
$sql="Select * from `devices` where `serial_number`='$sn'";
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
while($data=$result->fetch_array(MYSQLI_ASSOC))
{
	$auto_id = $data['auto_id'];
	$did = $data['device_type'];
	$mid = $data['manufacturer'];
	$sql = "Select * from `device_types` where `status` = 'inactive' and `device_type` = '$did'";
	$d_result = queryData($dblink,$sql,$endPoint,$uri);
	$sql = "Select * from `manufacturers` where `status` = 'inactive' and `manufacturer` = '$mid'";
	$m_result = queryData($dblink,$sql,$endPoint,$uri);
	$sql = "Select 	`devices`.`auto_id` from `devices` inner join `inactive_devices` on `devices`.`auto_id` = `inactive_devices`.`device_id` where `devices`.`auto_id` = '$auto_id'";
	$s_result = queryData($dblink,$sql,$endPoint,$uri);
	if($d_result->num_rows>0 || $m_result->num_rows>0 || $s_result->num_rows>0)
		$status = "inactive";
	else
		$status = "active";
	$serial = "SN-".$data['serial_number'];
	$devices[$data['auto_id']]=array($data['manufacturer'],$data['device_type'],$serial,$status);
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




















