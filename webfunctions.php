<?php
// queries data and returns result
function queryWebData($dblink, $sql, $endPoint, $uri)
{
	try{
		$result=$dblink->query($sql);
		return $result;
	}catch (mysqli_sql_exception $e){
		$error = array($e->getMessage(),$e->getCode(),$e->getFile(),$e->getLine());
		log_error($endPoint, $uri,$error, "/var/log/db_errors.log");
        // maybe redirect? figure out how to return an error to result
		return;
	}
}

// display select options
function getSearchOptions($dblink,$sql,$endPoint,$uri)
{
    $result=queryWebData($dblink,$sql,$endPoint,$uri);
	while ($data=$result->fetch_array(MYSQLI_ASSOC))
	{
		$value=str_replace(" ","_",$data['device_type']);
		echo '<option value="'.$value.'">'.$data['device_type'].'</option>';
	}
}

?>