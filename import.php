<?php
echo "process $argv[1] to process entries from file $argv[2]\n";
$process = $argv[1];
$key = "$argv[2]";
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
//$fileName="/home/ubuntu/equipment-part2.txt";
$fileName="/home/ubuntu/files/$key";
if(!file_exists($fileName))
	die("file not found.\n");
$fp=fopen("$fileName","r") ;
if(!$fp)
	die('failed to open file.\n');

$entryErrors=array();
$spellingErrors=array();
$countError=array();
$deviceArray=array("laptop","television","tablet","mobile phone","vehicle","smart watch","computer");
$manuArray=array("Huawei","Vizio","Samsung","Nokia","KIA","Ford","Google","Nissan","IBM","Sony","Dell","Apple","LG","Microsoft","Chevorlet","TCL","GM","Toyota","Panasonic","Hyundai","Hisense","Motorola","OnePlus","HP");
$snArray=array();
$maxEntryLengths=array(24,24,64);
$processNum = intval($process)-2;
$target=0;
$lineNum = $target + ($processNum * 100000);
$queryattempt=0;
$maxqueries=3;
$startTime=microtime(true);
cycleLines($fp,$target);
while (($line=fgetcsv($fp)) !== FALSE)
{
	$lineNum++;
	$spellingErrors=[];
	$entryErrors = [];
	$countErrors=[];
	// get our entry values
	$device=$line[0];
	$manu=$line[1];
	$sn=$line[2];
	$entryLine=array(&$device,&$manu,&$sn);
	
	// check for right number of entries
	checkEntryCount($entryLine,$countError,$entryErrors);
	if($countError)
		logErrors($dblink,$process,$line,$lineNum,$countError);
	
	// check for null values
	checkForNull($entryLine,$entryErrors);
	
	// remove the redundant "SN-" from serial numbers to save space
	if(!$entryErrors)
	{
		$sn=substr($sn,3);
		$syntaxErrors=checkForFormat($device);
		$syntaxErrors=array_merge(checkForFormat($manu),$syntaxErrors);
		$syntaxErrors=array_merge(checkForSNFormat($sn),$syntaxErrors);
		if($syntaxErrors)
			logErrors($dblink,$process,$line,$lineNum,$syntaxErrors);
		if(!in_array($device, $deviceArray))
			wordMatcher($device,$deviceArray,$entryErrors,$spellingErrors);
		if(!in_array($manu,$manuArray))
			wordMatcher($manu,$manuArray,$entryErrors,$spellingErrors);
	}
	if($entryErrors)
	{
		logErrors($dblink,$process,$line,$lineNum,$entryErrors);
		continue;
	}
	if($spellingErrors)
		logErrors($dblink,$process,$line,$lineNum,$spellingErrors);
	if(addItemToArray($device,$deviceArray))
	{
		$sql = "Insert into `device_types` (`device_type`) values ('$device')";
		queryEntry($dblink,$sql,$process);
	}
	if(addItemToArray($manu,$manuArray))
	{
		$sql = "Insert into `manufacturers` (`manufacturer`) values ('$manu')";
		queryEntry($dblink,$sql,$process);
	}
	
	// check for length
	lengthCheck($entryLine,$maxEntryLengths,$entryErrors);

	// check for duplicate serial numbers
	checkForDuplicates($sn,$snArray,$lineNum,$entryErrors);
	
	if(!$entryErrors)
	{
		$sql="Insert into `devices` (`line_num`,`device_type`,`manufacturer`,`serial_number`) values ('$lineNum','$device','$manu','$sn')";
		queryEntry($dblink,$sql,$process);
	}
	else
	{
		logErrors($dblink,$process,$line,$lineNum,$entryErrors);
	}
}
$endTime=microtime(true);
$totalTime=$endTime-$startTime;
$minutes=$totalTime / 60;
echo "\n Total Time: $minutes minutes\n";
echo "\n Rows per second: ". ($lineNum/$totalTime)."\n";
fclose($fp);
?>














