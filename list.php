<?php
$directory="/home/ubuntu/files/";
$scanned_dir=array_diff(scandir($directory),array('..','.'));		// filter out .. and . from our directory array by getting the difference
$count = 0;
$batch = 0;
$timeLog="/var/log/test-results.log";
$startTime=microtime(true);
if($argv[1] == 1)
{
	foreach($scanned_dir as $key=>$value)
	{
		$count ++;
		echo "beginning process ". ($key-2) ."\n";
		shell_exec("/usr/bin/php /var/www/html/import.php $key $value > /home/ubuntu/import.log 2>/home/ubuntu/import.log &");
		if($count == 5)
			sleep(3*60);
		
	}
}
echo "all processes running\n";
?>