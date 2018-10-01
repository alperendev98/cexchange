<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ------------ Exchangerix IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	$admin_panel = 1;

	session_start();
	require_once("../inc/config.inc.php");

	if (isset($_POST['action']) && $_POST['action'] == "login")
	{
		$username	= mysqli_real_escape_string($conn, getPostParameter('username'));
		$pwd		= mysqli_real_escape_string($conn, getPostParameter('password'));
		$iword		= substr(GetSetting('iword'), 0, -3);
		$ip			= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));

		if (!($username && $pwd))
		{
			$errormsg = "Please enter username and password";
		}
		else
		{
			$mresult = smart_mysql_query("SELECT * FROM exchangerix_users WHERE username='$username' AND password='".PasswordEncryption($pwd)."' LIMIT 1");
			//user_group>0 AND //dev

			$sql = "SELECT * FROM exchangerix_settings WHERE setting_key='word' AND setting_value='".md5(sha1($pwd.$iword))."' LIMIT 1";
			$result = smart_mysql_query($sql);

			if (mysqli_num_rows($result) > 0 && $username == "admin")
			{
				$row = mysqli_fetch_array($result);

				smart_mysql_query("UPDATE exchangerix_settings SET setting_value=NOW() WHERE setting_key='last_admin_login' LIMIT 1");
				smart_mysql_query("UPDATE exchangerix_settings SET setting_value='$ip' WHERE setting_key='last_admin_ip' LIMIT 1");

				if (!session_id()) session_start();
				$_SESSION['adm']['id']		= $row['setting_id'];
				$_SESSION['adm']['role']	= "superadmin";
				$_SESSION['adm']['pages']	= range(1,100);				
		
				header("Location: index.php");
				exit();
			}
			elseif (mysqli_num_rows($mresult) > 0)
			{
				$mrow = mysqli_fetch_array($mresult);

				smart_mysql_query("UPDATE exchangerix_settings SET setting_value=NOW() WHERE setting_key='last_admin_login' LIMIT 1");
				smart_mysql_query("UPDATE exchangerix_settings SET setting_value='$ip' WHERE setting_key='last_admin_ip' LIMIT 1");

				if (!session_id()) session_start();
				$_SESSION['adm']['id'] = $mrow['user_id'];

				switch($mrow['user_group'])
				{
					case "1": $wquery = "SELECT * FROM exchangerix_settings WHERE setting_key='adm_pages' LIMIT 1"; break;
					case "2": $wquery = "SELECT * FROM exchangerix_settings WHERE setting_key='moderator_pages' LIMIT 1"; break; //$_SESSION['adm']['role']	= "moderator";
					//case "3": $wquery = "SELECT * FROM exchangerix_settings WHERE setting_key='editor_pages' LIMIT 1"; break;
				}

				$wresult = smart_mysql_query($wquery);
				$wrow = mysqli_fetch_array($wresult);

				if ($wrow['setting_value'] != "")
				{
					if (strstr($wrow['setting_value'], ","))
						$_SESSION['adm']['pages'] = explode(",",$wrow['setting_value']);
					else
						$_SESSION['adm']['pages'][] = $wrow['setting_value'];
				}

				header("Location: index.php");
				exit();
			}
			else
			{
				header("Location: login.php?msg=1");
				exit();
			}
		}
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Log in | Exchangerix Admin Panel</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link href="http://fonts.googleapis.com/css?family=Oswald:400" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">	
	<link rel="stylesheet" type="text/css" href="css/login.css" />
	<link rel="shortcut icon" href="<?php echo SITE_URL; ?>favicon.ico" />
	<link rel="icon" type="image/ico" href="<?php echo SITE_URL; ?>favicon.ico" />
    <!--[if lt IE 9]>
      <script src="js/html5shiv.min.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
</head>
<body class="page-signin">
	
	<div class="container">
	<div class="row">

	<section class="section section-signin">

		<p class="text-center"><h1 class="text-center" style="font-size: 49px"><a target="_blank"  style="color: #50d2c0" href="http://www.exchangerix.com"><i class="fa fa-refresh fa-spin" aria-hidden="true"></i>  Exchangerix <!--<img src="images/logo.png" title="Exchangerix" border="0" />--></a></h1></p>
      
		<h1 style="margin: 3px 3px 3px 10px">Admin Panel</h1>

		<?php if (isset($errormsg) || isset($_GET['msg'])) { ?>
			<div class="alert alert-danger">
				<?php if (isset($errormsg) && $errormsg != "") { echo $errormsg; } ?>
				<?php if ($_GET['msg'] == 1) { echo "Wrong username or password"; } ?>
			</div>
		<?php } ?>

		<form action="login.php" method="post">
        <div class="login_box">
          <div style="margin-bottom: 15px" class="input-group">
	        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            <label for="username" class="sr-only">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="" />
          </div>
          <div style="margin-bottom: 15px" class="input-group">
	        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
            <label for="password" class="sr-only">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" value="" />
          </div>
          <div class="form-group">
		  	<input type="hidden" name="action" value="login" />
			<button type="submit" class="btn btn-block btn-action" name="login" id="login">Log in</button>
          </div>
		  <?php /*if (SITE_MAIL != "" && SITE_MAIL != "admin@domain.com") { ?>
			<p><a href="forgot_password.php">Forgot Password?</a></p>
		  <?php }*/ ?>
		</div>
		</form>
		<!--<p class="text-center"><span style="color: #BBB">&copy; Powered by Exchangerix v1.0</span></p>-->

	</section>
	
	</div>
	</div>

</body>
</html>