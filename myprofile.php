<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	session_start();
	require_once("inc/auth.inc.php");
	require_once("inc/config.inc.php");

	$query	= "SELECT * FROM exchangerix_users WHERE user_id='$userid' AND status='active' LIMIT 1";
	$result = smart_mysql_query($query);

	if (mysqli_num_rows($result) > 0)
	{
		$row = mysqli_fetch_array($result);
	}
	else
	{
		header ("Location: logout.php");
		exit();
	}

	
	if (isset($_POST['action']) && $_POST['action'] == "editprofile")
	{
		$fname			= mysqli_real_escape_string($conn, ucfirst(strtolower(getPostParameter('fname'))));
		$lname			= mysqli_real_escape_string($conn, ucfirst(strtolower(getPostParameter('lname'))));
		$email			= mysqli_real_escape_string($conn, strtolower(getPostParameter('email')));
		$address		= mysqli_real_escape_string($conn, getPostParameter('address'));
		$address2		= mysqli_real_escape_string($conn, getPostParameter('address2'));
		$city			= mysqli_real_escape_string($conn, getPostParameter('city'));
		$state			= mysqli_real_escape_string($conn, getPostParameter('state'));
		$zip			= mysqli_real_escape_string($conn, getPostParameter('zip'));
		$country		= (int)getPostParameter('country');
		$phone			= mysqli_real_escape_string($conn, getPostParameter('phone'));
		$newsletter		= (int)getPostParameter('newsletter');
		
		unset($errs);
		$errs = array();

		if(!($fname && $lname && $email))
		{
			$errs[] = CBE1_MYPROFILE_ERR;
		}

		if(isset($email) && $email !="" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
		{
			$errs[] = CBE1_MYPROFILE_ERR1;
		}

		if (count($errs) == 0)
		{
			// reset verification
			if (PHONE_VERIFICATION == 1 && $row['phone'] != "" && $phone != $row['phone']) $add_sql = ", verified_phone='0'"; else $add_sql = "";
			
			$up_query = "UPDATE exchangerix_users SET email='$email', fname='$fname', lname='$lname', address='$address', address2='$address2', city='$city', state='$state', zip='$zip', country='$country', phone='$phone', newsletter='$newsletter' $add_sql WHERE user_id='$userid' LIMIT 1";
		
			if (smart_mysql_query($up_query))
			{
				$_SESSION['FirstName'] = $fname;
				header("Location: myprofile.php?msg=1");
				exit();
			}
		}
	}


	if (isset($_POST['action']) && $_POST['action'] == "changepwd")
	{
		$pwd		= mysqli_real_escape_string($conn, getPostParameter('password'));
		$newpwd		= mysqli_real_escape_string($conn, getPostParameter('newpassword'));
		$newpwd2	= mysqli_real_escape_string($conn, getPostParameter('newpassword2'));

		$errs2 = array();

		if (!($pwd && $newpwd && $newpwd2))
		{
			$errs2[] = CBE1_MYPROFILE_ERR0;
		}
		else
		{
			if (PasswordEncryption($pwd) !== $row['password'])
			{
				$errs2[] = CBE1_MYPROFILE_ERR2;
			}

			if ($newpwd !== $newpwd2)
			{
				$errs2[] = CBE1_MYPROFILE_ERR3;
			}
			elseif ((strlen($newpwd)) < 6 || (strlen($newpwd2) < 6) || (strlen($newpwd)) > 20 || (strlen($newpwd2) > 20))
			{
				$errs2[] = CBE1_MYPROFILE_ERR4;
			}
			elseif (stristr($newpwd, ' '))
			{
				$errs2[] = CBE1_MYPROFILE_ERR5;
			}
		}

		if (count($errs2) == 0)
		{
			$upp_query = "UPDATE exchangerix_users SET password='".PasswordEncryption($newpwd)."' WHERE user_id='$userid' LIMIT 1";
		
			if (smart_mysql_query($upp_query))
			{
				header("Location: myprofile.php?msg=2");
				exit();
			}	
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = CBE1_MYPROFILE_TITLE;

	require_once ("inc/header.inc.php");

?>
		<div class="row">
			<div class="col-md-12 hidden-xs">
			<div id="acc_user_menu">
				<ul><?php require("inc/usermenu.inc.php"); ?></ul>
			</div>
		</div>


        <h1><i class="fa fa-address-card-o" aria-hidden="true"></i> <?php echo CBE1_MYPROFILE_TITLE; ?></h1>

		<?php if (isset($_GET['msg']) && is_numeric($_GET['msg']) && !$_POST['action']) { ?>
			<div class="alert alert-success">
				<?php

					switch ($_GET['msg'])
					{
						case "1": echo CBE1_MYPROFILE_MSG1; break;
						case "2": echo CBE1_MYPROFILE_MSG2; break;
					}
				?>
			</div>
		<?php } ?>

		<?php
				if (count($errs) > 0)
				{
					foreach ($errs as $errorname) { $allerrors .= $errorname."<br/>\n"; }
					echo "<div class='alert alert-danger'>".$allerrors."</div>";
				}
				
		?>

        <div class="row">
	    <div class="col-md-6">
		        
		<!--<span class="req pull-right">* <?php echo CBE1_LABEL_REQUIRED; ?></span>-->

		<div class="widget" style="background: #F9F9F9">
		<form action="" method="post">
		   <div class="form-group">
		    <label for="username"><?php echo CBE1_LABEL_USERNAME; ?></label>
		    <input type="text" class="form-control" id="username" value="<?php echo $row['username']; ?>" disabled="disabled">
		   </div>
		   <div class="form-group">
		    <label for="fname"><?php echo CBE1_LABEL_FNAME; ?> <span class="req">* </span></label>
		    <input type="text" class="form-control" name="fname" id="fname" value="<?php echo $row['fname']; ?>">
		   </div>
		   <div class="form-group">
		    <label for="lname"><?php echo CBE1_LABEL_LNAME; ?> <span class="req">* </span></label>
		    <input type="text" class="form-control" name="lname" id="lname" value="<?php echo $row['lname']; ?>">
		   </div>	
		   <div class="form-group">
		    <label for="email"><?php echo CBE1_LABEL_EMAIL; ?> <span class="req">* </span></label>
		    <input type="text" class="form-control" name="email" id="email" value="<?php echo $row['email']; ?>">
		   </div>
            <div class="form-group">
              <label for="address"><?php echo CBE1_LABEL_ADDRESS1; ?></label>
              <input type="text" class="form-control" name="address" id="address" value="<?php echo $row['address']; ?>">
            </div>
            <div class="form-group">
              <label for="address2"><?php echo CBE1_LABEL_ADDRESS2; ?></label>
              <input type="text" class="form-control" name="address2" id="address2" value="<?php echo $row['address2']; ?>">
            </div>
            <div class="form-group">
              <label for="city"><?php echo CBE1_LABEL_CITY; ?></label>
              <input type="text" class="form-control" name="city" id="city" value="<?php echo $row['city']; ?>">
            </div>
            <div class="form-group">
              <label for="state"><?php echo CBE1_LABEL_STATE; ?></label>
              <input type="text" class="form-control" name="state" id="state" value="<?php echo $row['state']; ?>">
            </div>
            <div class="form-group">
              <label for="zip"><?php echo CBE1_LABEL_ZIP; ?></label>
              <input type="text" class="form-control" name="zip" id="zip" value="<?php echo $row['zip']; ?>">
            </div>
            <div class="form-group">
              <label for="country"><?php echo CBE1_LABEL_COUNTRY; ?></label>
				<select name="country" class="form-control" id="country">
				<option value=""><?php echo CBE1_LABEL_COUNTRY_SELECT; ?></option>
				<?php

					$sql_country = "SELECT * FROM exchangerix_countries WHERE signup='1' AND status='active' ORDER BY sort_order, name";
					$rs_country = smart_mysql_query($sql_country);
					$total_country = mysqli_num_rows($rs_country);

					if ($total_country > 0)
					{
						while ($row_country = mysqli_fetch_array($rs_country))
						{
							if ($row['country'] == $row_country['country_id'])
								echo "<option value='".$row_country['country_id']."' selected>".$row_country['name']."</option>\n";
							else
								echo "<option value='".$row_country['country_id']."'>".$row_country['name']."</option>\n";
						}
					}

				?>
				</select>
			  </td>
            </div>
            <div class="form-group">
              <label for="phone"><?php echo CBE1_LABEL_PHONE; ?> <i class="fa fa-info-circle itooltip" title="format example: +447234567890"></i> <?php if (PHONE_VERIFICATION == 1 && $row['phone'] != "") { ?><a href="<?php echo SITE_URL; ?>myaccount.php#verification"><?php if ($row['verified_phone'] == 1) { ?><sup class="badge" style="background: #71ac14">verified</sup><?php }else{ ?><sup class="badge">not verified</sup><?php } ?></a><?php } ?></label>
              <input type="text" class="form-control" name="phone" id="phone" value="<?php echo $row['phone']; ?>">
            </div>
			<div class="checkbox">
				<label><input type="checkbox" name="newsletter" class="checkboxx" value="1" <?php echo (@$row['newsletter'] == 1) ? "checked" : "" ?>/> <?php echo CBE1_MYPROFILE_NEWSLETTER; ?></label>
			</div>
				<input type="hidden" name="action" value="editprofile" />
				<input type="submit" class="btn btn-success" name="Update" id="Update" value="<?php echo CBE1_MYPROFILE_UPBUTTON; ?>" />
				<input type="button" class="btn btn-default" name="cancel" value="<?php echo CBE1_CANCEL_BUTTON; ?>" onClick="javascript:document.location.href='myaccount.php'" />
        </form>
		<br/>
		</div>

	</div>
	<div class="col-md-4 col-md-offset-1">

		<div class="widget" style="background: #F9F9F9">
		<h3 class="text-center"><i class="fa fa-lock" aria-hidden="true"></i> <?php echo CBE1_MYPROFILE_PASSWORD; ?></h3>
		<br>

		<?php

				if (count($errs2) > 0)
				{
					foreach ($errs2 as $errorname) { $allerrors .= $errorname."<br/>\n"; }
					echo "<div class='alert alert-danger'>".$allerrors."</div>";
				}
		?>

		
		  <form action="" method="post">
		  <div class="form-group">
		    <label for="password"><?php echo CBE1_MYPROFILE_OPASSWORD; ?></label>
		    <input type="password" class="form-control" name="password" id="password" value="">
		  </div>
		  <div class="form-group">
		    <label for="newpassword"><?php echo CBE1_MYPROFILE_NPASSWORD; ?></label>
		    <input type="password" class="form-control" name="newpassword" id="newpassword" value="">
		  </div>
		  <div class="form-group">
		    <label for="newpassword2"><?php echo CBE1_MYPROFILE_CNPASSWORD; ?></label>
		    <input type="password" class="form-control" name="newpassword2" id="newpassword2" value="">
		  </div>		  
			  <input type="hidden" name="action" value="changepwd" />
		      <button type="submit" class="btn btn-success"><?php echo CBE1_MYPROFILE_PWD_BUTTON; ?></button>
		  </form>
		</div>

	</div>
	</div>


<?php require_once ("inc/footer.inc.php"); ?>