<?php
$startTime = microtime(true);
include("../functions.php");
include("../datahandler.php");
//Needed: device type
$status=$_REQUEST['status']; //can be active, inactive, or null
$endPoint = $_SERVER['REQUEST_URI'];
$uri = $_SERVER['REMOTE_ADDR'];
$dblink = db_connect("equipment");
$output=array();
$errorLog = "/var/log/api_errors.log";
$successLog = "/var/log/api.log";
$db_errors="/var/log/db_errors.log";
$devices=array();
$active_devices=array();
$active_manus=array();


if (!isset($_REQUEST['status']) || $status==NULL)
{
	$sql="Select * from `devices` limit 1000";
	$result=queryData($dblink,$sql,$endPoint,$uri);
	
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
		$devices[$data['auto_id']]=array($data['device_type'],$data['manufacturer'],$data['serial_number'],$status);
	}
}
elseif ($status=="active")
{
	$sql="Select `device_type` from `device_types` where `status` = 'active'";
	$result=queryData($dblink,$sql,$endPoint,$uri);
	while($data=$result->fetch_array(MYSQLI_ASSOC))
		$active_devices[]=$data['device_type'];
	$device=join("','",$active_devices);

	$sql="Select `manufacturer` from `manufacturers` where `status`='active'";
	$result=queryData($dblink,$sql,$endPoint,$uri);		  
	while($data=$result->fetch_array(MYSQLI_ASSOC))
		$active_manus[]=$data['manufacturer'];
	$manus=join("','",$active_manus);
	
	$sql="Select * from `devices` where `auto_id` not in (select `device_id` from `inactive_devices`) and `device_type` in ('$device') and `manufacturer` in ('$manus') limit 1000";
	$result=queryData($dblink,$sql,$endPoint,$uri);
	
	while($data=$result->fetch_array(MYSQLI_ASSOC))
		$devices[$data['auto_id']]=array($data['device_type'],$data['manufacturer'],$data['serial_number'],$status);
}
elseif ($status=="inactive")
{
	$sql="Select `device_type` from `device_types` where `status` = 'inactive'";
	$result=queryData($dblink,$sql,$endPoint,$uri);
	while($data=$result->fetch_array(MYSQLI_ASSOC))
		$active_devices[]=$data['device_type'];
	$device=join("','",$active_devices);
	
	$sql="Select `manufacturer` from `manufacturers` where `status`='inactive'";
	$result=queryData($dblink,$sql,$endPoint,$uri);		  
	while($data=$result->fetch_array(MYSQLI_ASSOC))
		$active_manus[]=$data['manufacturer'];
	$manus=join("','",$active_manus);
	
	$sql="Select * from `devices` where `auto_id` in (select `device_id` from `inactive_devices`) or `device_type` in ('$device') or `manufacturer` in ('$manus') limit 1000";
	$result=queryData($dblink,$sql,$endPoint,$uri);
	
	while($data=$result->fetch_array(MYSQLI_ASSOC))
		$devices[$data['auto_id']]=array($data['device_type'],$data['manufacturer'],$data['serial_number'],$status);
}
else // invalid status submitted
{
	$output[]="Status: ERROR";
	$output[]="MSG: Invalid or missing serial number.";
	$output[]="Action: None";
	$responseData=json_encode($output);
	echo $responseData;
	$e = ['Invalid status Formatting','IF',__FILE__,__LINE__];
	log_error($endPoint, $uri, $e, $errorLog);
	die();
}
// check if any results returned
if($result->num_rows<=0)
{
	$output[]="Status: Success";
	$output[]="MSG: No results found.";
	$output[]="Action: None";
	$responseData=json_encode($output);
	echo $responseData;
	$endTime=microtime(true);
	$execTime = round(($endTime - $startTime) * 1000,2);
	log_activity($endPoint,$uri,array($sql,$execTime),$successLog);
	die();
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






























