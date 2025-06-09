<?php
header('Content-Type: application/json');
header('HTTP/1.1 200 OK');

$output[]='Status: Success';
$output[]='MSG: Main Endpoint Reached';
$output[]='Action: None';

echo '<pre>';
print_r($output);
echo '</pre>';

$responseData=json_encode($output);
echo $responseData;

$url=$_SERVER['REQUEST_URI'	];
$path=parse_url($url, PHP_URL_PATH);
$pathClean=trim($path,"/");
$pathComponents=explode("/",$pathClean);
$endPoint=$pathComponents[1];

?>