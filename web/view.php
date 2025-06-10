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
				$auto_id = $info['auto_id'];
				$manu = $info['manufacturer'];
                    $device = $info['device_type'];
				$sql="Select * from `devices` join `inactive_devices` where $auto_id = `device_id` limit 1000";
				$result=queryWebData($dblink,$sql,$endPoint,$uri);
				if ($result->num_rows<=0)
					$active = "active";
				else
					$active = "inactive";
				$sql="Select * from `manufacturers` where `manufacturer`='$manu' and `status`='active'";
				$result=queryWebData($dblink,$sql,$endPoint,$uri);
				if ($result->num_rows>=0)
					$mactive = "active";
				else
					$mactive = "inactive";
                    $sql="Select * from `device_types` where `device_type`='$device' and `status`='active'";
				$result=queryWebData($dblink,$sql,$endPoint,$uri);
				if ($result->num_rows>=0)
					$dactive = "active";
				else
					$dactive = "inactive";
				echo '<h2>Device Info:</h2>';
				echo '<p>Device ID: <b>'.$info['auto_id'].'</b></p>';
				echo '<p>Device Type: <b>'.$info['device_type'].'</b></p>';
				echo '<p>Device Manufacturer: <b>'.$info['manufacturer'].'</b></p>';
				echo '<p>Device SN: <b>'.$info['serial_number'].'</b></p>';
				echo '<p>Device Type Active: <b>'.$dactive.'</b></p>';
				echo '<p>Manufacturer Active: <b>'.$mactive.'</b></p>';
                    echo '<p>Device Active: <b>'.$active.'</b></p>';
				echo '<p><a class="btn btn-success" href="modify.php?eid='.$info['auto_id'].'">Modify</a></p>';
				?>
               </div>
          </div>
     </div>
</section>
</body>
</html>