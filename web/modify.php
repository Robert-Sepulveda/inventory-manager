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
            <a href="#" class="navbar-brand">AES Inventory Database</a>
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
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <?php
                include('../functions.php');
                include('../webfunctions.php');
                $endPoint = $_SERVER['REQUEST_URI'];
		        $uri = $_SERVER['REMOTE_ADDR'];
			  	$db="equipment";
			  	$dblink=db_connect($db);
				$eid=$_GET['eid'];
				$sql="Select * from `devices` where `auto_id`='$eid'";
				$result=queryWebData($dblink,$sql,$endPoint,$uri);
				$info=$result->fetch_array(MYSQLI_ASSOC);
				echo '<h2>Modify Device Info:</h2>';
				echo '<form method="post" action="">';
				echo '<p>Device ID: <b>'.$info['auto_id'].'</b></p>';
				echo '<div class="form-group">';
				echo '<label for="exampleDevice">Device Type:</label>';
				echo '<select class="form-control" name="type">';
				$sql="Select distinct(`device_type`) from `device_types`";
				$result=queryWebData($dblink,$sql,$endPoint,$uri);  
				while ($data=$result->fetch_array(MYSQLI_ASSOC))
				{
					$value=str_replace(" ","_",$data['device_type']);
					if ($data['device_type']==$info['device_type'])
						echo '<option value="'.$value.'"selected>'.$data['device_type'].'</option>';
					else
						echo '<option value="'.$value.'">'.$data['device_type'].'</option>';
				}
				echo '</select>';
				echo '</div>';
				echo '<div class="form-group">';
				echo '<label for="exampleDevice">Device Manufacturer:</label>';
				echo '<select class="form-control" name="manufacturer">';
				$sql="Select distinct(`manufacturer`) from `manufacturers`";
				$result=queryWebData($dblink,$sql,$endPoint,$uri); 
				while ($data=$result->fetch_array(MYSQLI_ASSOC))
				{
					$value=str_replace(" ","_",$data['manufacturer']);
					if ($data['manufacturer']==$info['manufacturer'])
						echo '<option value="'.$value.'"selected>'.$data['manufacturer'].'</option>';
					else
						echo '<option value="'.$value.'">'.$data['manufacturer'].'</option>';
				}
				echo '</select>';
				echo '</div>';
				echo '<div class="form-group">';
				echo '<label for="exampleDevice">Device Serial Number:</label>';
				echo '<input type="text" value="'.$info['serial_number'].'" name="serial" size="100">';
				echo '</div>';
				echo '<div class="form-group">';
				echo '<label for="exampleDevice">Status:</label>';
				$sql="Select * from `inactive_devices` where `device_id`='$info[auto_id]'";
				$device_status=queryWebData($dblink,$sql,$endPoint,$uri);
				$sql="Select * from `device_types` where `device_type`='$info[device_type]' and `status`='inactive'";
				$device_type_status=queryWebData($dblink,$sql,$endPoint,$uri);
				$sql="Select * from `manufacturers` where `manufacturer`='$info[manufacturer]' and `status`='inactive'";
				$manu_type_status=queryWebData($dblink,$sql,$endPoint,$uri);
				echo '<select class="form-control" name="status">';
				if ($device_status->num_rows>0 || $device_type_status->num_rows>0 || $manu_type_status->num_rows>0)
				{
					echo '<option value="active">Active</option>';
					echo '<option value="inactive" selected>Inactive</option>';
				}
				else
				{
					echo '<option value="active" selected>Active</option>';
					echo '<option value="inactive">Inactive</option>';
				}
				echo '</select>';
				echo '</div>';
				?>
            </div>
        </div>
    </div>
</section>
</body>
</html>