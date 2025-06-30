<?php

function addItemToArray($item,&$itemArray)
{
	if(!in_array($item, $itemArray))			// check to see if current device is not in devices array
	{
		$itemArray[]=$item;						// add item to array
		return true;
	}
	return false;
}

function isValidDevice($string) {
    return preg_match('/^[a-z ]{1,24}$/', $string) === 1;
}

function isValidManu($string) {
    return preg_match('/^[a-zA-Z ]{1,24}$/', $string) === 1;
}

function isValidSN($string) {
    return preg_match('/^[a-f0-9]{64}$/', $string) === 1;
}

// returns any syntax error found, sanitizes string
function checkForFormat(&$string)
{
	$errorMessage=null;
	$result = "";
	for ($i = 0; $i < strlen($string);$i++)
	{
		if (ctype_alpha($string[$i]))
			$result .= $string[$i];
		// special case of whitespace being allowed
		else if($string[$i]===" ")
			$result .= $string[$i];
		else
		{
			$errorMessage = "Syntax: " . addslashes($string[$i]);
		}
	}
	$string = $result;
	return $errorMessage;
}

// returns any serial number syntax errors found, sanitizes string
function checkSNFormat(&$string)
{
	$errorMessage=null;
	$result = "";
	for ($i = 0; $i < strlen($string);$i++)
	{
		if (ctype_alnum($string[$i]) and $string[$i] !== ')')
			$result .= $string[$i];
		else
		{
			$errorMessage = "Syntax: " . addslashes($string[$i]);
		}
	}
	$string = $result;
	return $errorMessage;
}


function checkForDuplicates(&$sn,&$snArray,$lineNum)
{
	if(!in_array($sn,$snArray))
	{
		addItemToArray($sn,$snArray);
		return;
	}
	return "Duplicate entry found";
}

function checkValidEntryCount(&$line,&$countError)
{
	if (count($line) > 3)
	{
		for($i=0;$i<count($line);$i++)
		{
			if($line[$i]===""||$line[$i]===" ")
			{
				
			}
		}
	}
	return;
}

// returns too many entries if line has more than 3 elements
function checkEntryCount(&$line,&$countError,&$entryErrors)
{
	$i=0;
	while(count($line) > 3 && $i < count($line))
	{
		if($line[$i]===""||$line[$i]===" ")
		{
			array_splice($line,$i,1);
			$countError[]="Too many entries";	
		}
		else
			$i++;
	}
	if(count($line) > 3)
	{
		$countError=[];
		$entryErrors[]="Too many entries";
	}
	return;
}

// returns an error string indicating an column is null
function checkForNull(&$line)
{
	for($i=0;$i<count($line);$i++)
	{
		if($line[$i]===""||$line[$i]===" ")
		{
			$line[$i]="Null"; // prevents an sql error when logging the line
			if($i===0)
				return "Null device";
			else if($i===1)
				return "Null manu";
			else if($i===2)
				return "Null sn";
		}
	}
	return;
}

// tries to find a valid type that matches the mispelled string
function wordMatcher(&$string,$stringArray)
{
	$stringKey=null;
	foreach($stringArray as $key => $value)
	{
		if(str_contains($key,$string))
		{
			if($stringKey)
			{
				return "Cannot identify string"; // multiple entries match the string
			}
			$stringKey = $key;
		}
	}
	if($stringKey)
	{
		$string = $stringKey;
		return "Mispelled string";
	}
	return "Cannot identify string";	// no matching strings found
}

// function lengthCheck(&$line,$valueLen)
// {
// 	for($i=0;$i<count($line);$i++)
// 	{
// 		if(strlen($line[$i])>$valueLen[$i])
// 		{
// 			$line[$i]=substr($line[$i],0,$valueLen[$i]);
// 			if($i===0)
// 				$entryErrors[]="device exceeds len";
// 			else if($i===1)
// 				$entryErrors[]="manu exceeds len";
// 			else if($i===2)
// 				$entryErrors[]="sn exceeds len";
// 			else
// 				$entryErrors[]="entry exceeds len";
// 		}
// 	}
// 	if(strlen($line[2])!==$valueLen[2])
// 		$entryErrors[]="sn not long enough";
// 	return;
// }

function cycleLines(&$fp,$target)
{
	if($target==0)
		return;
	for($i=0;$i<=$target;$i++)
		$foo = $line=fgetcsv($fp);
}

function queryEntry($dblink,$sql,$processNum,$lineNum)
{
	try{
		$result = $dblink->query($sql);
		if($result)
			echo"successful insert of entry ".$lineNum."\n";
	}catch (mysqli_sql_exception $e){
		switch($e->getCode()){
			case 1062:
				error_log("Duplicate entry detected in devices, query failed. Did you forget to truncate your table?\n");
				break;
			case 2002:
				error_log("Cannot connect to the database server. Query to devices failed.\n");
				break;
			case 2003:
				error_log("Connection to the host failed. Query to devices failed.\n");
				break;
			case 1045:
				error_log("Access to devices denied. Check your username and password.\n");
				break;
			default:
				error_log("Database error in devices: ".$e->getMessage());
		}
		error_log("An SQL error has occured in process: $processNum\n");
		die("Query to devices failed.". $e->getMessage() ."\n");
	}
}

// logs an error to the database
function logErrors($dblink,$key,$line,$lineNum,$errorMessage)
{
	$record=addslashes($line[0]).','.addslashes($line[1]).','.addslashes($line[2]);
	$sql="Insert into `import_log` (`line_number`,`error_type`,`raw_data`) values ('$lineNum','$errorMessage','$record')";
	try{
		$result = $dblink->query($sql);
		if($result)
			echo"successful error log of entry ".$lineNum."\n";
	}catch (mysqli_sql_exception $e){
		switch($e->getCode()){
			case 1062:
				error_log("Duplicate entry detected in import_log, query failed. Did you forget to truncate your table?\n");
				break;
			case 2002:
				error_log("Cannot connect to the database server. Query to import_log failed.\n");
				break;
			case 2003:
				error_log("Connection to the host failed. Query to import_log failed.\n");
				break;
			case 1045:
				error_log("Access to import_log denied. Check your username and password.\n");
				break;
			default:
				error_log("Database error in import_log: ".$e->getMessage());

		}
		error_log("process $key failed at line number: $lineNum\n");
		die("Query to import_log failed.\n");
	}
	return;
}
?>