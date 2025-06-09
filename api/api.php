<?php
// Build header info for requesting service
header('Content-Type: application/json');
header('HTTP/1.1 200 OK');
//// Build payload array
//$output[]='Status: ERROR';
//$output[]='MSG: System Disabled';
//$output[]='Action: None';
//echo '<pre>';
//print_r($output);
//echo '</pre>';
//// Convert array to json
//$responseData=json_encode($output);
//echo $responseData;

//log_error($_SERVER['REMOTE_ADDR'],"SYSTEM DISABLED","SYSTEM DISABLED: $endPoint",$url,"api.php");
$url=$_SERVER['REQUEST_URI'];
$path = parse_url($url, PHP_URL_PATH);
$pathClean=trim($path,"/");
// use the / delimeter to create an array
$pathComponents = explode("/", $pathClean);
$endPoint=$pathComponents[1];
//$did=$_REQUEST['did']; // get dvice id from URL
//$something=$_REQUEST['something']; // get additional var from url
switch($endPoint)
{
    case "list_devices":
        include("list_devices.php");
        break;
	case "list_manufacturer":
		include("list_manufacturer.php");
		break;
	case "add_equipment":
		include("add_equipment.php");
		break;
	case "add_device_type":
		include("add_device_type.php");
		break;
	case "add_manu_type":
		include("add_manufacturer.php");
		break;
	case "search_device":
		include("search_device.php");
		break;
	case "search_manufacturer":
		include("search_manufacturer.php");
		break;
	case "search_sn":
		include("search_sn.php");
		break;
	case "search_all":
		include("search_all.php");
		break;
	case "view_equipment":
		include("view_equipment.php");
		break;
	case "modify_device":
		include("modify_device.php");
		break;
	case "modify_manufacturer":
		include("modify_manufacturer.php");
		break;
	case "modify_equipment":
		include("modify_equipment.php");
		break;
    default:
		// logging goes here -> send the data to a database
        $output[]='Status: ERROR';
        $output[]='MSG: Invalid or missing endpoint';
        $output[]='Action: None';
        $responseData=json_encode($output);
        echo $responseData;
        break;
}
?>