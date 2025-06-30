<?php
$directory="/home/ubuntu/files/test";
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
		echo "beginning process "$key-2"\n";
		shell_exec("/usr/bin/php /var/www/html/import.php $key $value > /home/ubuntu/import.log 2>/home/ubuntu/import.log &");
		if($count === 5)
		{
			if($batch < 6)
				sleep(1200);
			else
				sleep(2400);
			
			$batch++;
			$count=0;
		}
	}
}
echo "all processes running\n";
//else if($argv[1]==2)
//{
////	foreach($scanned_dir as $key=>$value)
////	{
////		if($key==$argv[2])
////		{
////			echo "beginning process $key\n";
////			shell_exec("/usr/bin/php /var/www/html/import.php $key $value");
////			echo "process $key finished\n";
////		}
////	}
//	shell_exec("/usr/bin/php /var/www/html/import.php 2 /home/ubuntu/aag217.csv");
//}
$endTime=microtime(true);
$totalTime=$endTime-$startTime;
$minutes=$totalTime / 60;
$average=$minutes / 10;
$total = "Total time for all processes: $minutes minutes\n";
$avg = "Average time for process: $average minutes\n";
file_put_contents($logFile, $total, FILE_APPEND);
file_put_contents($logFile, $avg, FILE_APPEND);
?>