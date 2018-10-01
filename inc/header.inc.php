<!DOCTYPE html>
<html lang="en-us">
<head>
	<title><?php echo $PAGE_TITLE." | ".SITE_TITLE; ?></title>
	<?php if ($PAGE_DESCRIPTION != "") { ?><meta name="description" content="<?php echo $PAGE_DESCRIPTION; ?>" /><?php } ?>
	<?php if ($PAGE_KEYWORDS != "") { ?><meta name="keywords" content="<?php echo $PAGE_KEYWORDS; ?>" /><?php } ?>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="author" content="Exchangerix.com" />
	<meta name="robots" content="index, follow" />
	<link href="http://fonts.googleapis.com/css?family=Open+Sans+Condensed:400,700" rel="stylesheet" type="text/css" />
    <link href="<?php echo SITE_URL; ?>css/bootstrap.min.css" rel="stylesheet" />
	<!--[if lt IE 9]>
    <script src="<?php echo SITE_URL; ?>js/html5shiv.js"></script>
    <script src="<?php echo SITE_URL; ?>js/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="<?php echo SITE_URL; ?>css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_URL; ?>css/stylesheet.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_URL; ?>css/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_URL; ?>css/lightbox.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_URL; ?>css/bootstrap-select.min.css" />
	<link rel="shortcut icon" href="<?php echo SITE_URL; ?>favicon.ico" />
	<link rel="icon" type="image/ico" href="<?php echo SITE_URL; ?>favicon.ico" />
	<meta property="og:title" content="<?php echo $PAGE_TITLE; ?>" />
	<meta property="og:url" content="<?php echo SITE_URL; ?>" />
	<meta property="og:description" content="<?php echo $PAGE_DESCRIPTION; ?>" />
	<meta property="og:image" content="<?php echo SITE_URL; ?>images/logo.png" />
	<?php echo (GOOGLE_ANALYTICS != "") ? GOOGLE_ANALYTICS : ""; ?>
</head>
<body>
<a href="#" class="scrollup">Top</a>
		
<div id="wrapper">
<div class="header">
  <header id="header" class="header-in">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <div class="row">
            <div class="logo"><a href="<?php echo SITE_URL; ?>"><img src="<?php echo SITE_URL; ?>images/logo.png" alt="<?php echo SITE_TITLE; ?>"></a></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="row">
	          
	          <div class="operator_box">
	          <i class="fa fa-user-circle-o fa-2x" aria-hidden="true" style="color: <?php echo (OPERATOR_STATUS == "online") ? "#79b45b" : "#777777"; ?>; vertical-align: middle;"></i> Operator: <?php if (OPERATOR_STATUS == "online") { ?><span class="label label-success"><i id="operator_live" class="fa fa-circle" aria-hidden="true" style="color: #8bf24a"></i> online</span><?php }else{ ?> <span class="label label-default"> offline</span><?php } ?>
	          <?php if (SHOW_OPERATOR_HOURS == 1) { ?><br>&nbsp;&nbsp; <i class="fa fa-clock-o fa-lg" style="color: #79b45b"></i> working time: <?php echo OPERATOR_HOURS; ?> <?php echo OPERATOR_TIMEZONE; ?><?php } ?>
	          <?php if (CONTACT_PHONE != "") { ?><br><b><i class="fa fa-phone-square fa-lg" aria-hidden="true" style="color: #92b453"></i> <?php echo CONTACT_PHONE; ?></b><?php } ?>
	          <?php if (CONTACT_PHONE2 != "") { ?><b><i class="fa fa-phone-square fa-lg" aria-hidden="true" style="color: #92b453; margin-left: 7px;"></i> <?php echo CONTACT_PHONE2; ?></b><?php } ?>
	          <?php if (CONTACT_PHONE3 != "") { ?><b><i class="fa fa-phone-square fa-lg" aria-hidden="true" style="color: #92b453; margin-left: 7px;"></i> <?php echo CONTACT_PHONE3; ?></b><?php } ?>
	          </div>
	      </div>
	          
	    </div>
	    <div class="col-md-4">
		    <div class="row">
	    
            <div class="header-top">
				<?php if (MULTILINGUAL == 1 && count($languages) > 0) { ?>
					<div id="languages">
					<?php foreach ($languages as $language_code => $language) { ?>
						<a href="<?php echo SITE_URL; ?>?lang=<?php echo $language; ?>"><img src="<?php echo SITE_URL; ?>images/flags/<?php echo $language_code; ?>.png" alt="<?php echo $language; ?>" border="0" /></a> &nbsp;
					<?php } ?>
					</div>
				<?php } ?>
             <div class="top2">
       
             <div class="login-reg">	             
			 <?php if (isLoggedIn()) { ?>
					<a href="<?php echo SITE_URL; ?>myaccount.php"><i class="fa fa-user-circle fa-lg"></i></a> <?php echo CBE_WELCOME; ?>, <a href="<?php echo SITE_URL; ?>myaccount.php"><span class="member"><b><?php echo $_SESSION['FirstName']; ?></b></span></a> | <?php echo CBE_BALANCE; ?>: <a href="<?php echo SITE_URL; ?>mybalance.php"><span class="label label-success"><?php echo GetUserBalance($_SESSION['userid']); ?></span></a><span class="hidden-xs"> | <i class="fa fa-users"></i>  <?php echo CBE_REFERRALS; ?>: <a href="<?php echo SITE_URL; ?>invite.php" style="color: #000"><span class="referrals"><?php echo GetReferralsTotal($_SESSION['userid']); ?></span></a></span><!-- | <a class="logout" href="<?php echo SITE_URL; ?>logout.php"><?php echo CBE_LOGOUT; ?></a>-->				
					<div class="dropdown" id="account_nav" style="margin-top: 10px">
					  <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-user" aria-hidden="true"></i> <?php echo CBE1_BOX_ACCOUNT; ?> <span class="caret"></span></button>
					  <ul class="dropdown-menu">
					    <?php require_once("inc/usermenu.inc.php"); ?>
					  </ul>
					</div>									
			<?php }else{ ?>
				<a class="signup" href="<?php echo SITE_URL; ?>signup.php"><i class="fa fa-user"></i> <?php echo CBE_SIGNUP; ?></a>
				<a href="#signin" data-toggle="modal" class="login"><i class="fa fa-sign-in"></i> <?php echo CBE_LOGIN; ?></a>
			<?php } ?>	                          
             </div>
             
             </div>

             
             </div>
            </div>
              
            </div>
           
          </div>
        </div>
      </div>
  </header>		

<div class="menu-ctnr">
<div class="container">
<div class="row">

  <nav class="navbar navbar-default" style="background-color: transparent; border: none;">
    <div class="navbar-header">
      <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".js-navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#"></a>
    </div>
    <div class="collapse navbar-collapse js-navbar-collapse">
      <ul class="nav navbar-nav">
 			<li><a href="<?php echo SITE_URL; ?>"><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> <?php echo CBE_MENU_HOME; ?></a></li> <!--class="home"-->
			<li><a href="<?php echo SITE_URL; ?>rates.php"><i class="fa fa-area-chart" aria-hidden="true"></i> Rates</a></li>
			<li><a href="<?php echo SITE_URL; ?>affiliate.php">Affiliates</a></li>
			<li><a href="<?php echo SITE_URL; ?>testimonials.php"><i class="fa fa-comments-o"></i> Testimonials</a></li>
			<li><a href="<?php echo SITE_URL; ?>news.php"><?php echo CBE1_FMENU_NEWS; ?></a></li>
			<!--<li><a href="<?php echo SITE_URL; ?>myaccount.php" rel="nofollow"><?php echo CBE_MENU_ACCOUNT; ?></a></li>-->
			<li><a href="<?php echo SITE_URL; ?>help.php"><i class="fa fa-question-circle"></i> <?php echo CBE_MENU_HELP; ?></a></li>
			<li><a href="<?php echo SITE_URL; ?>contact.php"><?php echo CBE1_FMENU_CONTACT; ?></a></li>
			<! -- track order -->
			<?php echo ShowTopPages(); ?>
      </ul>
    </div>
  </nav>
  
</div>
</div>
</div>

</div>


<div id="body-ctnr" <?php if (@$bg_dark == 1) echo "class='dark'"; ?>>
<div class="container">
	
	
