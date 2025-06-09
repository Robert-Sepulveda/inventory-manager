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
<body>
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
          </div>
     </section>
     <!-- FEATURE -->
     <section id="feature">
          <div class="container">
               <div class="row">
                   

                   <?php 
				    include '../datahandler.php';
                  	$un="web_user";
			  		$pw="BMxptDhPcsOh6dN-";
			  		$db="equipment";
			  		$host="localhost";
			  		$dblink=new mysqli($host,$un,$pw,$db);
				   	if (!isset($_GET['type']))
			  		{
				  		echo '<a class="btn btn-primary" href="add.php?type=equipment">Add equipment</a>';
				  		echo '<a class="btn btn-primary" href="add.php?type=device">Add device type</a>';
				  		echo '<a class="btn btn-primary" href="add.php?type=manufacturer">Add manufacturer</a>';
			  		}
				   	else if ($_GET['type']=="equipment")
					{
					   if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="SerialExists")
                       {
                         echo '<div class="alert alert-danger" role="alert">Serial number already registered.</div>';
                        
                       }
					   else if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="error")
                       {
                         echo '<div class="alert alert-danger" role="alert">Formatting: must be letter a-f, numbers 0-9, and exactly 64 characters long</div>';
                        
                       }
						echo '<form method="post" action="">';
						echo '<div class="form-group">';
							echo '<label for="exampleDevice">Device:</label>';
							echo '<select class="form-control" name="device">';
									$sql="Select `device_type` from `device_types`";

									$result=$dblink->query($sql) or
										die("<h2>Something went wrong with $sql<br>".$dblink->error."</h2>");
									while ($data=$result->fetch_array(MYSQLI_ASSOC))
									{
										$value=str_replace(" ","_",$data['device_type']);
										echo '<option value="'.$value.'">'.$data['device_type'].'</option>';
									}
							echo '</select>';
						echo '</div>';
						echo '<div class="form-group">';
							echo '<label for="exampleManufacturer">Manufacturer:</label>';
							echo '<select class="form-control" name="manufacturer">';
									$sql="Select `manufacturer` from `manufacturers` join `manufacturer_status_inactive` on `manufacturers`.`auto_id` != `manufacturer_id`";

									$result=$dblink->query($sql) or
										die("<h2>Something went wrong with $sql<br>".$dblink->error."</h2>");
									while ($data=$result->fetch_array(MYSQLI_ASSOC))
									{
										$value=str_replace(" ","_",$data['manufacturer']);
										echo '<option value="'.$value.'">'.$data['manufacturer'].'</option>';
									}
							echo '</select>';
						echo '</div>';
						echo '<div class="form-group">';
							echo '<label for="exampleSerial">Serial Number:</label>';
							echo '<input type="text" class="form-control" id="serial" name="serial">';
						echo '</div>';
							echo '<button type="submit" class="btn btn-primary" name="submit" value="equipment">Add Equipment</button>';
					   echo '</form>';
				   }
				   else if ($_GET['type']=="device")
				   {
					  if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="DeviceExists")
                      {
                        echo '<div class="alert alert-danger" role="alert">Device already exists.</div>';
                        
                      }
					  else if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="error")
                      {
                        echo '<div class="alert alert-danger" role="alert">Formatting: only lowercase letters, name cannot be longer than 24 characters</div>';
                        
                      }
					  echo '<form method="post" action="">';
					  	echo '<div class="form-group">';
							echo '<label for="exampleDevice">Device:</label>';
					   		echo '<input type="text" class="form-control" name="device">';
					   	echo '</div>';
					   	echo '<button type="submit" class="btn btn-primary" name="submit" value="device">Add Device</button>';
					   echo '</form>';
				   }
				   else if ($_GET['type']=="manufacturer")
				   {
					  if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="ManufacturerExists")
                      {
                        echo '<div class="alert alert-danger" role="alert">Manufacturer already exists.</div>';
                        
                      }
					  else if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="error")
                      {
                        echo '<div class="alert alert-danger" role="alert">Formatting: only letters, name cannot be longer than 24 characters</div>';
                        
                      }
					  echo '<form method="post" action="">';
					  	echo '<div class="form-group">';
							echo '<label for="exampleDevice">Manufacturer:</label>';
					   		echo '<input type="text" class="form-control" name="manufacturer">';
					   	echo '</div>';
					   	echo '<button type="submit" class="btn btn-primary" name="submit" value="manufacturer">Add Manufacturer</button>';
					   echo '</form>';
				   }
    				if (isset($_POST['submit']) and $_POST['submit'] == "equipment")
    				{
						$line_num=0;
        				$device=$_POST['device'];
        				$manufacturer=$_POST['manufacturer'];
						$serialNumber=str_replace("SN-"," ",$_POST['serial']);
						$serialNumber=trim($serialNumber);
						if(!isValidSN($serialNumber))
							redirect("add.php?type=equipment&msg=error");
						$sql="Select `auto_id` from `devices` where `serial_number`='$serialNumber'";
						$rst=$dblink->query($sql) or
							 die("<p>Something went wrong with $sql<br>".$dblink->error);
						if ($rst->num_rows<=0)//sn not previously found
						{
							$sql="Insert into `devices` (`device_type`,`manufacturer`,`serial_number`) values ('$line_num','$device','$manufacturer','$serialNumber')";
							$dblink->query($sql) or
								 die("<p>Something went wrong with $sql<br>".$dblink->error);
							redirect("index.php?msg=EquipmentAdded");
						}
						else
							redirect("add.php?type=equipment&msg=SerialExists");
					}
				    else if (isset($_POST['submit']) and $_POST['submit'] == "device")
					{
						$device=$_POST['device'];
						if(!isValidDevice($device))
							redirect("add.php?type=device&msg=error");
						$sql="Select `auto_id` from `device_types` where `device_type`='$device'";
						$rst=$dblink->query($sql) or
							 die("<p>Something went wrong with $sql<br>".$dblink->error);
						if ($rst->num_rows<=0)//sn not previously found
						{
							$sql="Insert into `device_types` (`device_type`) values ('$device')";
							$dblink->query($sql) or
								 die("<p>Something went wrong with $sql<br>".$dblink->error);
							redirect("index.php?msg=DeviceAdded");
						}
						else
							redirect("add.php?type=device&msg=DeviceExists");
					}
				    else if (isset($_POST['submit']) and $_POST['submit'] == "manufacturer")
					{
						$manufacturer=$_POST['manufacturer'];
						if(!isValidManu($manufacturer))
							redirect("add.php?type=manufacturer&msg=error");
						$sql="Select `auto_id` from `manufacturers` where `manufacturer`='$manufacturer'";
						$rst=$dblink->query($sql) or
							 die("<p>Something went wrong with $sql<br>".$dblink->error);
						if ($rst->num_rows<=0)//sn not previously found
						{
							$sql="Insert into `manufacturers` (`manufacturer`) values ('$manufacturer')";
							$dblink->query($sql) or
								 die("<p>Something went wrong with $sql<br>".$dblink->error);
							redirect("index.php?msg=ManufacturerAdded");
						}
						else
							redirect("add.php?type=manufacturer&msg=ManufacturerExists");
					}
		  			?>
          </div>
     </section>
</body>
</html>
