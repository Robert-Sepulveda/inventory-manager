<?php
// connects user to the database
function db_connect($db){
	$un="web_user";
	$pw="BMxptDhPcsOh6dN-";
	$hostname="localhost";
	$dblink = new mysqli($hostname,$un,$pw,$db);
	return $dblink;
}

function redirect( $uri )
{ ?>
	<script type="text/javascript">
	document.location.href="<?php echo $uri; ?>";
	</script>
<?php die;
}

// queries data and returns result
function queryData($dblink, $sql, $endPoint, $uri)
{
	try{
		$result=$dblink->query($sql);
		return $result;
	}catch (mysqli_sql_exception $e){
		$error = array($e->getMessage(),$e->getCode(),$e->getFile(),$e->getLine());
		log_error($endPoint, $uri,$error, "/var/log/db_errors.log");
		$output[]='Status: ERROR';
		$output[]='MSG: Query failed';
		$output[]='Action: list_devices';
		$responseData=json_encode($output);
		echo $responseData;
		return;
	}
}

// log errors to given log file, note: no sanitization applied
function log_error($endPoint, $remoteClient, $e, $logFile)
{
	$logData = [
		'timestamp' => date("Y-m-d H:i:s"),
		'error' => $e[0],
		'code' => $e[1],
		'file' => $e[2],
		'line' => $e[3],
		'ip' => $remoteClient,
		'uri' => $endPoint
	];
	$formattedString = "[".$logData['timestamp']."] ERROR ".$logData['code'].": ".$logData['error']." thrown in ".$logData['file']." on line ".$logData['line']." | IP: ".$logData['ip']." | ENDPOINT: ".$logData['uri']."\n";
	file_put_contents($logFile, $formattedString, FILE_APPEND);
	return $logData;
}

// log successful api requests, note: no sanitization applied
function log_activity($endPoint,$remoteClient,$parameters,$logFile)
{
	$logData = [
		'timestamp' => date("Y-m-d H:i:s"),
		'query' => $parameters[0],
		'time' => $parameters[1],
		'ip' => $remoteClient,
		'uri' => $endPoint
	];
	$formattedString = "[".$logData['timestamp']."] SUCCESS: ".$logData['query']." | Time:  ".$logData['time']."ms | IP: ".$logData['ip']." | ENDPOINT: ".$logData['uri']."\n";
	file_put_contents($logFile, $formattedString, FILE_APPEND);
	return $logData;
}

// returns true if given inputs are null
function isDidNull($did)
{
	$output=array();
	if ($did==NULL || !isset($_REQUEST['did']))//decive id is missing
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid or missing device id.';
		$output[]='Action: list_devices';
		$responseData=json_encode($output);
		echo $responseData;
		return true;
	}
	return false;
}
function isMidNull($mid)
{
	$output=array();
	if ($mid==NULL || !isset($_REQUEST['mid']))//missing manufacturer id
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid or missing manufacturer id.';
		$output[]='Action: list_manufacturers';
		$responseData=json_encode($output);
		echo $responseData;
		return true;
	}
	return false;
}
function isSnNull($sn)
{
	$output=array();
	if ($sn==NULL || !isset($_REQUEST['sn']))//missing serial number
	{
		$output[]='Status: ERROR';
		$output[]='MSG: Invalid or missing serial number.';
		$output[]='Action: list_devices';
		$responseData=json_encode($output);
		echo $responseData;
		return true;
	}
	return false;

}

// returns and array of statuses from a given table
function getStatus($field,$table,$dblink,$endPoint,$uri)
{
	$active=array();
	$sql = "Select `$field`,`status` from `$table`";
	$result = queryData($dblink, $sql, $endPoint, $uri);
	while($data = $result->fetch_array(MYSQLI_NUM))
		$active[$data[0]]=$data[1];
	return $active;
}
?>




































