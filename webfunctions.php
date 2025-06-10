<?php
// queries data and returns result, will log an error and return false if the query fails
function queryWebData($dblink, $sql, $endPoint, $uri)
{
	try{
		$result=$dblink->query($sql);
		return $result;
	}catch (mysqli_sql_exception $e){
		$error = array($e->getMessage(),$e->getCode(),$e->getFile(),$e->getLine());
		log_error($endPoint, $uri,$error, "/var/log/db_errors.log");
		return false;
	}
}

// display select options
function getSearchOptions($dblink,$sql,$endPoint,$uri)
{
    $result=queryWebData($dblink,$sql,$endPoint,$uri);
	if($result==false)
		redirect();
	while ($data=$result->fetch_row())
	{
		$value=str_replace(" ","_",$data[0]);
		echo '<option value="'.$value.'">'.$data[0].'</option>';
	}
}

?>