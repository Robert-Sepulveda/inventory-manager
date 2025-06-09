<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>ASE - AWS Server</title>
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
     <section id="home">
          </div>
     </section>
     <!-- FEATURE -->
     <section id="feature">
          <div class="container">
               <div class="row">
                   <?php
                    if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="EquipmentAdded")
                    {
                        echo '<div class="alert alert-success" role="alert">Equipment successfully added.</div>';
                        
                    }
				    else if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="DeviceAdded")
                    {
                        echo '<div class="alert alert-success" role="alert">Device successfully added.</div>';
                        
                    }
				    else if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="ManufacturerAdded")
                    {
                        echo '<div class="alert alert-success" role="alert">Manufacturer successfully added.</div>';
                        
                    }
                    ?>
                        
                    <div class="col-md-4 col-sm-4">
                         <div class="feature-thumb">
                              <h3>Search Equipment</h3>
                              <p>Click here to search the equipment database.</p>
                              <a href="#feature" class="btn btn-default smoothScroll">Discover more</a>
                         </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                         <div class="feature-thumb">
                              <h3>Add Equipment</h3>
                              <p>Click here to add new equipment</p>
                             <a href="#feature" class="btn btn-default smoothScroll">Discover more</a>
                         </div>
                    </div>

                    

               </div>
          </div>
     </section>
</body>
</html>