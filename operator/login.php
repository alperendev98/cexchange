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
		$ip			= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));

		if (!($username && $pwd))
		{
			$errormsg = "Please enter username and password";
		}
		else
		{
			$mresult = smart_mysql_query("SELECT * FROM exchangerix_users WHERE user_group='2' AND username='$username' AND password='".PasswordEncryption($pwd)."' LIMIT 1");

			if (mysqli_num_rows($mresult) > 0)
			{
				$mrow = mysqli_fetch_array($mresult);

				if (!session_id()) session_start();
				$_SESSION['operator']['id'] = $mrow['user_id'];

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
	<title>Log in | Exchangerix Operator Panel</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link href="http://fonts.googleapis.com/css?family=Oswald:400" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="css/login.css" />
	<link rel="shortcut icon" href="<?php echo SITE_URL; ?>favicon.ico" />
	<link rel="icon" type="image/ico" href="<?php echo SITE_URL; ?>favicon.ico" />
    <!--[if lt IE 9]>
      <script src="/js/html5shiv.min.js"></script>
      <script src="/js/respond.min.js"></script>
    <![endif]-->
</head>
<body class="page-signin">

	<section class="section section-signin">
      
		<h1 class="text-center" style="color: #000; font-size: 33px; margin-bottom: 3px;"><span class="glyphicon glyphicon-headphones" style="margin-right: 7px;"></span> Operator Panel</h1>
		<br>

		<?php if (isset($errormsg) || isset($_GET['msg'])) { ?>
			<div class="alert alert-danger">
				<?php if (isset($errormsg) && $errormsg != "") { echo $errormsg; } ?>
				<?php if ($_GET['msg'] == 1) { echo "Wrong username or password"; } ?>
			</div>
		<?php } ?>

		<form action="login.php" method="post">
        <div class="login_box">
          <div class="form-group">
            <label for="username" class="sr-only">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="" />
          </div>
          <div class="form-group">
            <label for="password" class="sr-only">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" value="" />
          </div>
          <div class="form-group">
		  	<input type="hidden" name="action" value="login" />
			<input type="submit" class="btn btn-block btn-action" name="login" id="login" value="Log in" />
          </div>
		</div>
		</form>
		<!--<p class="text-center"><span style="color: #BBB">&copy; Powered by Exchangerix v1.1</span></p>-->

	</section>

</body>
</html>