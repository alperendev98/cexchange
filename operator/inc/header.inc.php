<?php

	if (isset($_GET['action']) && $_GET['action'] == "change_status")
	{	
		$ostatuses 	= array("online", "offline");
		$ostatus	= mysqli_real_escape_string($conn, trim(getGetParameter('status')));
		
		if (isset($ostatus) && @in_array($ostatus, $ostatuses))
		{
			smart_mysql_query("UPDATE exchangerix_settings SET setting_value='$ostatus' WHERE setting_key='operator_status' LIMIT 1");
			
			header("Location: index.php?msg=status_updated");
			exit();
		}		
	}
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?> | Exchangerix Operator Panel</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700|Oswald" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="css/bootstrap-select.min.css" />
	<link rel="stylesheet" type="text/css" href="css/bootstrap-datetimepicker.min.css" />
	<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="css/exchangerix.css" />
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
</head>
<body>

	<br/><br/><br/>

	<div class="container" style="background: #FFF">

	<div class="navbar navbar-fixed-top ontop-now">
	<div class="container">
			<div class="navbar-header">

				<div class="pull-left" style="width: 30%; float: left;">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
					<span class="navbar-toggle-icon"></span>
					<i class="fa fa-bars fa-lg"></i> <b>MENU</b>
					</button>
					<a class="navbar-brand hidden-xs" href="index.php">OPERATOR PANEL</a>
				</div>		
			
				<div class="pull-right" style="width: 55%" id="rightmenu">Welcome<?php if (isSuperAdmin()) { ?>, <b>Operator</b><?php } ?>! <a href="<?php echo SITE_URL; ?>" target="_blank">View Site</a><br><i class="fa fa-user-circle-o" aria-hidden="true"></i> Operator status: <?php if (OPERATOR_STATUS == "online") { ?><a href="index.php?action=change_status&status=offline"><span id="itooltip" title="change status" class="label label-success"><i id="operator_live" class="fa fa-circle" aria-hidden="true" style="color: #8bf24a"></i> online</span></a><?php }else{ ?> <a href="index.php?action=change_status&status=online"><span id="itooltip" title="change status" class="label label-default"><i class="fa fa-circle" aria-hidden="true" style="color: #cccccc"></i> offline</span></a><?php } ?>
				</div>
			</div>	
				
				<div class="visible-xs visible-sm">
				<div id="navbar" class="navbar-collapse collapse">
              <ul class="nav navbar-nav" style="background: #EEE">
			<?php if (PageAllowed(1)) { ?><li <?php if (@$cpage==1) echo "class='selected'"; ?>><a href="index.php">Dashboard</a></li><?php } ?>
			<?php if (PageAllowed(2)) { ?><li <?php if (@$cpage==2) echo "class='selected'"; ?>><a href="users.php">Users</a></li><?php } ?>
			<?php if (PageAllowed(9)) { ?><li <?php if (@$cpage==9) echo "class='selected'"; ?>><a href="exchanges.php">Exchanges <?php if (GetPendingETotal() > 0) { ?><span class="badge" style="background: #f0ad4e"><?php echo GetPendingETotal(); ?></span><?php } ?></a></li><?php } ?>
			<?php if (PageAllowed(10) && GetRequestsTotal() > 0) { ?>
				<li <?php if (@$cpage==10) echo "class='selected'"; ?>><a href="cashout_requests.php">Withdrawal Requests <span class="badge"><?php echo GetRequestsTotal(); ?></span></a></li>
			<?php } ?>
			<?php if (PageAllowed(99)) { ?>
				<li <?php if (@$cpage==99) echo "class='selected'"; ?>><a href="reserve_requests.php">Reserve Requests <?php if (GetReserveRequestsTotal() > 0) { ?><span class="badge" style="background: #f0ad4e"><?php echo GetReserveRequestsTotal(); ?></span><?php } ?></a></li>
			<?php } ?>
			
			<?php if (PageAllowed(19)) { ?><li <?php if (@$cpage==19) echo "class='selected'"; ?>><a href="currencies.php">Currencies</a></li><?php } ?>
			<?php if (PageAllowed(12)) { ?><li <?php if (@$cpage==12) echo "class='selected'"; ?>><a href="exdirections.php">Exchange Directions</a></li><?php } ?>
			<?php if (PageAllowed(14)) { ?><li <?php if (@$cpage==14) echo "class='selected'"; ?>><a href="reviews.php">Testimonials</a></li><?php } ?>		
			<?php if (PageAllowed(24)) { ?><li <?php if (@$cpage==24) echo "class='selected'"; ?>><a href="email2users.php">Email Members</a></li><?php } ?>

			<li><a class="last" href="logout.php">Logout</a></li>
		</ul>
		
            </div>
				</div>

		</div>
	</div>

<div class="container">
	<div class="row row-offcanvas row-offcanvas-left">

		<div class="col-xs-6 col-sm-2 sidebar-offcanvas" id="sidebar" style="margin-top: 30px;">
		<ul>
			<?php if (PageAllowed(1)) { ?><li <?php if (@$cpage==1) echo "class='selected'"; ?>><a href="index.php">Dashboard</a></li><?php } ?>
			<?php if (PageAllowed(2)) { ?><li <?php if (@$cpage==2) echo "class='selected'"; ?>><a href="users.php">Users</a></li><?php } ?>
			<?php if (PageAllowed(9)) { ?><li <?php if (@$cpage==9) echo "class='selected'"; ?>><a href="exchanges.php">Exchanges <?php if (GetPendingETotal() > 0) { ?><span class="badge" style="background: #f0ad4e"><?php echo GetPendingETotal(); ?></span><?php } ?></a></li><?php } ?>
			<?php if (PageAllowed(10) && GetRequestsTotal() > 0) { ?>
				<li <?php if (@$cpage==10) echo "class='selected'"; ?>><a href="cashout_requests.php">Withdrawal Requests <span class="badge"><?php echo GetRequestsTotal(); ?></span></a></li>
			<?php } ?>
			<?php if (PageAllowed(19)) { ?><li <?php if (@$cpage==19) echo "class='selected'"; ?>><a href="currencies.php">Currencies</a></li><?php } ?>
			<?php if (PageAllowed(12)) { ?><li <?php if (@$cpage==12) echo "class='selected'"; ?>><a href="exdirections.php">Exchange Directions</a></li><?php } ?>
			
				<li <?php if (@$cpage==99) echo "class='selected'"; ?>><a href="reserve_requests.php">Reserve Requests <?php if (GetReserveRequestsTotal() > 0) { ?><span class="badge" style="background: #f0ad4e"><?php echo GetReserveRequestsTotal(); ?></span><?php } ?></a></li>			
			<?php if (PageAllowed(14)) { ?><li <?php if (@$cpage==14) echo "class='selected'"; ?>><a href="reviews.php">Testimonials</a></li><?php } ?>
			<?php if (PageAllowed(24)) { ?><li <?php if (@$cpage==24) echo "class='selected'"; ?>><a href="email2users.php">Email Members</a></li><?php } ?>

			<li><a class="last" href="logout.php">Logout</a></li>
		</ul>
		</div>

		<div class="col-xs-12 col-sm-10 row row-offcanvas row-offcanvas-left" style="margin-top: 30px;">

