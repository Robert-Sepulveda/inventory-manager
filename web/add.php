<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Advanced Software Engineering</title>
<link href="../assets/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/font-awesome.min.css">
<link rel="stylesheet" href="../assets/css/owl.carousel.css">
<link rel="stylesheet" href="../assets/css/owl.theme.default.min.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="../assets/css/templatemo-style.css">
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
            <a href="#" class="navbar-brand">Add New Equipment</a>
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
<section id="home">
</section>
<!-- FEATURE -->
<section id="feature">
    <div class="container">
        <?php 
		include('../functions.php');
		include('../webfunctions.php');
		include('../datahandler.php');
		$endPoint = $_SERVER['REQUEST_URI'];
		$uri = $_SERVER['REMOTE_ADDR'];
		$db="equipment";
		$dblink=db_connect($db);
		if (!isset($_GET['type']))
		{
			echo '<a class="btn btn-primary" href="add.php?type=equipment">Add equipment</a>';
			echo '<a class="btn btn-primary" href="add.php?type=device">Add device type</a>';
			echo '<a class="btn btn-primary" href="add.php?type=manufacturer">Add manufacturer</a>';
		}
		else if ($_GET['type']=="equipment")
		{
			if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="snexists")
            {
                echo '<div class="alert alert-danger" role="alert">Serial number already registered.</div>';            
            }
			else if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="sn")
            {
                echo '<div class="alert alert-danger" role="alert">Formatting: must be letter a-f, numbers 0-9, and exactly 64 characters long</div>';          
            }
			else if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="device")
			{
				echo '<div class="alert alert-danger" role="alert">Formatting: only lowercase letters, name cannot be longer than 24 characters</div>';
			}
			else if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="manu")
			{
				echo '<div class="alert alert-danger" role="alert">Formatting: only letters, name cannot be longer than 24 characters/div>';
			}
		?>
		<form method="post" action="">
			<div class="form-group">
				<label for="exampleDevice">Device:</label>
				<select class="form-control" name="device">
					<?php
					$sql="Select `device_type` from `device_types` where `status`='active'";
					getSearchOptions($dblink,$sql,$endPoint,$uri);
					?>
				</select>
			</div>
			<div class="form-group">
				<label for="exampleManufacturer">Manufacturer:</label>
				<select class="form-control" name="manufacturer">
					<?php
						$sql="Select `manufacturer` from `manufacturers` where `status`='active'";
						getSearchOptions($dblink,$sql,$endPoint,$uri);
					?>
				</select>
			</div>
			<div class="form-group">
				<label for="exampleSerial">Serial Number:</label>
				<input type="text" class="form-control" id="serial" name="serial">
			</div>
			<button type="submit" class="btn btn-primary" name="submit" value="equipment">Add Equipment</button>
		</form>
		<?php
		}
		else if ($_GET['type']=="device")
		{
			if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="deviceexists")
            {
            	echo '<div class="alert alert-danger" role="alert">Device already exists.</div>';                        
            }
			else if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="device")
            {
            	echo '<div class="alert alert-danger" role="alert">Formatting: only lowercase letters, name cannot be longer than 24 characters</div>';            
            }
		?>
		<form method="post" action="">
			<div class="form-group">
				<label for="exampleDevice">Device:</label>
				<input type="text" class="form-control" name="device">
			</div>
			<button type="submit" class="btn btn-primary" name="submit" value="device">Add Device</button>
		</form>
		<?php
		}
		else if ($_GET['type']=="manufacturer")
		{
		if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="manuexists")
        {
            echo '<div class="alert alert-danger" role="alert">Manufacturer already exists.</div>';                
        }
		else if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="manu")
        {
        	echo '<div class="alert alert-danger" role="alert">Formatting: only letters, name cannot be longer than 24 characters</div>';
		}
		?>
		<form method="post" action="">
			<div class="form-group">
				<label for="exampleDevice">Manufacturer:</label>
				<input type="text" class="form-control" name="manufacturer">
			</div>
			<button type="submit" class="btn btn-primary" name="submit" value="manufacturer">Add Manufacturer</button>
		</form>
		<?php
		}
    	if (isset($_POST['submit']) and $_POST['submit'] == "equipment")
    	{
        	$device=trim(strtolower($_POST['device']));
        	$manufacturer=trim(ucwords($_POST['manufacturer']));
			$serialNumber=trim(str_replace("SN-"," ",$_POST['serial']));
			if(!isValidDevice($device))
				redirect("add.php?type=equipment&msg=device");
			if(!isValidManu($manufacturer))
				redirect("add.php?type=equipment&msg=manu");
			if(!isValidSN($serialNumber))
				redirect("add.php?type=equipment&msg=sn");
			$sql="Select `auto_id` from `devices` where `serial_number`='$serialNumber'";
			$result=queryWebData($dblink,$sql,$endPoint,$uri);
			if ($result->num_rows>0)//sn already exists
				redirect("add.php?type=equipment&msg=snexists");
			$sql="Insert into `devices` (`device_type`,`manufacturer`,`serial_number`) values ('$line_num','$device','$manufacturer','$serialNumber')";
			queryWebData($dblink,$sql,$endPoint,$uri);
			redirect("index.php?msg=EquipmentAdded");				
		}
		else if (isset($_POST['submit']) and $_POST['submit'] == "device")
		{
			$device=trim(strtolower($_POST['device']));
			if(!isValidDevice($device))
				redirect("add.php?type=device&msg=device");
			$sql="Select `auto_id` from `device_types` where `device_type`='$device'";
			$result=queryWebData($dblink,$sql,$endPoint,$uri);
			if ($result->num_rows>0)//device already exists
				redirect("add.php?type=device&msg=deviceexists");
			$sql="Insert into `device_types` (`device_type`,`status`) values ('$device','active')";
			queryWebData($dblink,$sql,$endPoint,$uri);
			redirect("index.php?msg=DeviceAdded");
							
		}
		else if (isset($_POST['submit']) and $_POST['submit'] == "manufacturer")
		{
			$manufacturer=trim(ucwords($_POST['manufacturer']));
			if(!isValidManu($manufacturer))
				redirect("add.php?type=manufacturer&msg=manu");
			$sql="Select `auto_id` from `manufacturers` where `manufacturer`='$manufacturer'";
			$result=queryWebData($dblink,$sql,$endPoint,$uri);
			if ($result->num_rows>0)//manu already exists
				redirect("add.php?type=manufacturer&msg=manuexists");			
			$sql="Insert into `manufacturers` (`manufacturer`,`status`) values ('$manufacturer','active')";
			queryWebData($dblink,$sql,$endPoint,$uri);
			redirect("index.php?msg=ManufacturerAdded");
		}
		?>
          </div>
     </section>
</body>
</html>
