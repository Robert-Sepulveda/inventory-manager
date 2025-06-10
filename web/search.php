<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Advanced Software Engineering</title>
<link href="../assets/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/font-awesome.min.css">
<link rel="stylesheet" href="../assets/css/owl.carousel.css">
<link rel="stylesheet" href="../assets/css/owl.theme.default.min.css">
<link rel="stylesheet" href="../assets/css/dataTables.dataTables.css">
	
<!-- MAIN CSS -->
<link rel="stylesheet" href="../assets/css/templatemo-style.css">
<script src="../assets/js/jquery-3.5.1.js"></script>
<script src="../assets/js/dataTables.js"></script>
</head>
<body id="top" data-spy="scroll" data-target=".navbar-collapse" data-offset="50">
    <!-- MENU -->
    <section class="navbar custom-navbar navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="icon icon-bar"></span>
                    <span class="icon icon-bar"></span>
                    <span class="icon icon-bar"></span>
                </button>
	            <!-- lOGO TEXT HERE -->
                <a href="#" class="navbar-brand">Search Equipment Database</a>
            </div>
            <!-- MENU LINKS -->
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-nav-first">
                    <li><a href="index.php" class="smoothScroll">Home</a></li>
                    <li><a href="search.php" class="smoothScroll">Search Equipment</a></li>
                    <li><a href="add.php" class="smoothScroll">Add Equipment</a></li>
                </ul>
            </div>
        </div>
    </section>
<!-- HOME -->
<section id="home"></section>
<!-- FEATURE -->
<section id="feature">
    <div class="container">
		<?php
		include("../functions.php");
		include("../webfunctions.php");
		$active_manus=[];
		$active_devices=[];
		$endPoint = $_SERVER['REQUEST_URI'];
		$uri = $_SERVER['REMOTE_ADDR'];
		$db="equipment";
		$dblink = db_connect($db);
		$line = 1;
		if (!isset($_GET['type']))
		{
			echo '<a class="btn btn-primary" href="search.php?type=device">Search by Device</a>';
			echo '<a class="btn btn-primary" href="search.php?type=manufacturer">Search by Manufacturer</a>';
			echo '<a class="btn btn-primary" href="search.php?type=serialNum">Search by Serial Number</a>';
			echo '<a class="btn btn-primary" href="search.php?type=all">View All</a>';
		}
		else if ($_GET['type']=="device")
		{
		?>
		<form method="post" action="">
			<div class="form-group">
				<label for="exampleDevice">Device:</label>
				<select class="form-control" name="device">
					<?php
					$sql="Select `device_type` from `device_types`";
					getSearchOptions($dblink,$sql,$endPoint,$uri);
					?>
					<option value="all">All Devices</option>
				</select>
				<label for="exampleDevice">Manufacturer:</label>
				<select class="form-control" name="manufacturer">';
					<?php
					$sql="Select `manufacturer` from `manufacturers`";
					getSearchOptions($dblink,$sql,$endPoint,$uri);
					?>
					<option value="all">All Manufacturers</option>
				</select>
			</div>
			<button type="submit" class="btn btn-success" value="device_search" name="submit">Search</button>
		</form>
		<?php
		}
		else if($_GET['type']=="manufacturer")
		{
		?>
		<form method="post" action="">
			<div class="form-group">
				<label for="exampleDevice">Manufacturer:</label>
				<select class="form-control" name="manufacturer">
					<?php
				  	$sql="Select `manufacturer` from `manufacturers`";
					getSearchOptions($dblink,$sql,$endPoint,$uri);				  
					?>
					<option value="all">All Manufacturers</option>
				</select>
				<label for="exampleDevice">Device:</label>
				<select class="form-control" name="device">
					<?php
					$sql="Select `device_type` from `device_types`";
					getSearchOptions($dblink,$sql,$endPoint,$uri);
					?>
					<option value="all">All Devices</option>
				</select>
			</div>
		<button type="submit" class="btn btn-success" value="manu_search" name="submit">Search</button>
		</form>
		<?php
		}
		else if ($_GET['type']=="serialNum")
		{
			echo '<form method="post" action="">';
			echo '<div class="form-group">';
			echo '<label for="exampleDevice">Serial Number:</label>';
			echo '<input type="text" class="form-control" name="serial" size="100">';
			echo '</div>';
			echo '<button type="submit" class="btn btn-success" value="sn_search" name="submit">Search</button>';
			echo '</form>';
		}
		else
		{
			echo '<form method="post" action="">';
			echo '<div class="form-group">';
			echo '<label for="exampleDevice">View All:</label>';
			echo '<select class="form-control" name="equipment">';
			echo '<option value="active">active</option>';
			echo '<option value="inactive">inactive</option>';
			echo '<option value="all">all</option>';
			echo '</select>';
			echo '</div>';
			echo '<button type="submit" class="btn btn-success" value="all_search" name="submit">Search</button>';
			echo '</form>';
		}
		

		if (isset($_POST['submit']))
		{
			echo '<table class="display" style="width:100%">';
			echo '<thead>';
			echo '<tr><th>Number</th><th>Device Type</th><th>Manufacturer</th><th>Serial Number</th>';
			echo '<th>Action</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			if($_POST['submit']=="device_search" || $_POST['submit']=="manu_search")
			{
				$type=str_replace("_"," ",$_POST['device']);
				$manu=str_replace("_"," ",$_POST['manufacturer']);
				if ($manu=="all")
					$manuStr="`manufacturer` like '%'";
				else
					$manuStr="`manufacturer`='$manu'";
				if ($type=="all")
					$typeStr="`device_type` like '%'";
				else
					$typeStr="`device_type`='$type'";
				$sql="Select * from `devices` where $typeStr and $manuStr limit 1000";	  
			}
			else if ($_POST['submit']=="sn_search")
			{
				$serial=str_replace("SN-"," ",$_POST['serial']);
				$serial = trim($serial);
				$serialStr="`serial_number`='$serial'";
				$sql="Select * from `devices` where $serialStr limit 1000";
			}
			else if ($_POST['submit']=="all_search")
			{
				if($_POST['equipment']=="all")
					$sql="Select * from `devices` limit 1000";
				else
				{
					$sql="Select `manufacturer` from `manufacturers` where `status` = 'active'";
					$result=queryWebData($dblink,$sql,$endPoint,$uri);
					while ($data=$result->fetch_array(MYSQLI_ASSOC))
					{			  
						$active_manus[]=$data['manufacturer'];
					}
					$manus = join("','",$active_manus);
					$sql="Select `device_type` from `device_types` where `status` = 'active'";
					$result=queryWebData($dblink,$sql,$endPoint,$uri);
					while ($data=$result->fetch_array(MYSQLI_ASSOC))
					{		  
						$active_device[]=$data['device_type'];
					}
					$devices = join("','",$active_device);
					if($_POST['equipment']=="active")
					{
						$manuStr="`manufacturer` in ('$manus')";
						$deviceStr="`device_type` in ('$devices')";
						$sql="Select * from `devices` join `inactive_devices` on `devices`.`auto_id`!=`device_id` where $manuStr and $deviceStr  limit 1000";
					}
					else if($_POST['equipment']=="inactive")
					{
						$manuStr="`manufacturer` not in ('$manus')";
						$deviceStr="`device_type` not in ('$devices')";
						$sql="Select * from `devices` join `inactive_devices` on `devices`.`auto_id`=`device_id` where $manuStr or $deviceStr limit 1000";
					}
			  	}
			}
			$result=queryWebData($dblink,$sql,$endPoint,$uri);
			while ($data=$result->fetch_array(MYSQLI_ASSOC))
			{
				echo '<tr>';
			    echo '<td>'.$line.'</td><td>'.$data['device_type'].'</td><td>'.$data['manufacturer'].'</td><td>SN-'.$data['serial_number'].'</td>';
				echo '<td><a class="btn btn-success" href="view.php?eid='.$data['auto_id'].'">View</a></td>';
				echo '</tr>';
				$line++;
			}
			echo '</tbody>';
		}
		?>
    </div>
</section>
</body>
</html>