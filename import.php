<?php
$process = $argv[1];
$key = "$argv[2]";
echo "process "$process-2" to process entries from file $key\n";
include 'datahandler.php';

$un="web_user";
$pw="BMxptDhPcsOh6dN-";
$db="equipment";
$host="localhost";

// attempt to connect to the database
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try{
	$dblink=new mysqli($host,$un,$pw,$db);
	echo "Connection to ".$db." successful.\n";
} catch (mysqli_sql_exception $e) {
	die('Connection error: '.mysqli_connect_error().".\n");
}

// open our current file
$fileName="/home/ubuntu/files/test/$key";
if(!file_exists($fileName))
	die("file not found.\n");
$fp=fopen("$fileName","r") ;
if(!$fp)
	die('failed to open file.\n');

$deviceArray=array("laptop"=>0,"television"=>1,"tablet"=>2,"mobile phone"=>3,"vehicle"=>4,"smart watch"=>5,"computer"=>6);
$manuArray=array("Huawei"=>0,"Vizio"=>1,"Samsung"=>2,"Nokia"=>3,"KIA"=>4,"Ford"=>5,"Google"=>6,"Nissan"=>7,"IBM"=>8,"Sony"=>9,"Dell"=>10,"Apple"=>11,"LG"=>12,"Microsoft"=>13,"Chevorlet"=>14,"TCL"=>16,"GM"=>17,"Toyota"=>18,"Panasonic"=>19,"Hyundai"=>20,"Hisense"=>21,"Motorola"=>22,"OnePlus"=>23,"HP"=>24);
$snArray=array();
$maxEntryLength=64;
$processNum = intval($process)-2;
$fileSize = 10000
$lineNum = $target + ($processNum * $fileSize);
timeLog="/var/log/test-results.log"

$processStartTime=microtime(true);
while (($line=fgetcsv($fp)) !== FALSE)
{
	$lineNum++;
	$error;
	// get our entry values
	$device=$line[0];
	$manu=$line[1];
	$sn=$line[2];
	$entryLine=array(&$device,&$manu,&$sn);
	
	// NOTE: no entries exceed length, removed for efficiency
	// check for right number of entries
	// checkEntryCount($entryLine,$countError,$entryErrors);
	// if($countError)
	// 	logErrors($dblink,$process,$line,$lineNum,$countError);
	
	// check for null values
	$error = checkForNull($entryLine);
	
	// continue to further error checking
	if(!$error)
	{
		$sn=substr($sn,3); // remove the redundant "SN-" from serial numbers to save space
		$error=checkForFormat($device);
		$error=checkForFormat($manu);
		$error=checkSNFormat($sn);
		if(!array_key_exists($device,$deviceArray))
			$error = wordMatcher($device,$deviceArray);
		if(!array_key_exists($manu,$manuArray))
			$error = wordMatcher($manu,$manuArray);
		// Note: already identified every valid type, removed for efficiency
		// if(addItemToArray($device,$deviceArray))
		// {
		// 	$sql = "Insert into `device_types` (`device_type`) values ('$device')";
		// 	queryEntry($dblink,$sql,$process);
		// }
		// if(addItemToArray($manu,$manuArray))
		// {
		// 	$sql = "Insert into `manufacturers` (`manufacturer`) values ('$manu')";
		// 	queryEntry($dblink,$sql,$process);
		// }
		// check for length
		if(strlen($line[2])!==64)
		{
			$error = "Incorrect entry length";
		}

		// check for duplicate serial numbers
		$error = checkForDuplicates($sn,$snArray,$lineNum);
	}
	if(!$error)
	{
		$sql="Insert into `devices` (`device_type`,`manufacturer`,`serial_number`) values ('$deviceArray[$device]','$manuArray[$manu]','$sn')";
		queryEntry($dblink,$sql,$process);
	}
	else
	{
		logErrors($dblink,$process,$line,$lineNum,$error);
	}
}
$endTime=microtime(true);
$totalTime=$endTime-$startTime;
$minutes=$totalTime / 60;
$avgentryTime = $totalTime / $lineNum
$total = "Total Time for process $processNum: $minutes minutes\n";
$avg = "\n Rows per second for process $processNum: ". ($avgentryTime)."\n";
file_put_contents($logFile, $total, FILE_APPEND);
file_put_contents($logFile, $avg, FILE_APPEND);
fclose($fp);
?>














