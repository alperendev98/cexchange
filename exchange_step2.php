<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	session_start();
	require_once("inc/config.inc.php");
	require_once("inc/pagination.inc.php");


	function dtruncate($val, $f="0")
	{
	    if(($p = strpos($val, '.')) !== false) {
	        $val = floatval(substr($val, 0, $p + 1 + $f));
	    }
	    return $val;
	}


	if (REQUIRE_LOGIN == 1 && !isLoggedIn())
	{
		header ("Location: login.php?login");
		exit();
	}


	if (!$_SESSION['rid'])
	{
		header ("Location: index.php");
		exit();		
	}
	
	
	if (isset($_POST['currency_send']) && isset($_POST['currency_receive']) && $_POST['currency_send'] != $_POST['currency_receive'])
	{
		$from_id 		= (int)$_POST['currency_send'];
		$to_id 			= (int)$_POST['currency_receive'];

		$from_amount = sprintf('%f', $_POST['from_amount']); //number_format($_POST['from_amount'], 5);
		if (!(isset($from_amount) && is_numeric($from_amount) && $from_amount > 0))
		{
			header("Location: index.php?currency_send=$from_id&currency_receive=$to_id&err=wrong_amount1");
			exit();	
		}
		
		if (!(isset($_POST['action']) && $_POST['action'] == "proceed"))
		{
			$to_amount = sprintf('%f', $_POST['to_amount']);
			if (!(isset($to_amount) && is_numeric($to_amount) && $to_amount > 0))
			{
				header("Location: index.php?currency_send=$from_id&currency_receive=$to_id&err=wrong_amount2");
				exit();	
			}
		}
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}
	

	$query = "SELECT *, TIMESTAMPDIFF(MINUTE,updated,NOW()) AS last_update_mins FROM exchangerix_exdirections WHERE from_currency='$from_id' AND to_currency='$to_id' AND from_currency IN (SELECT currency_id FROM exchangerix_currencies WHERE allow_send='1' AND (reserve>0 || reserve='') AND status='active') AND to_currency IN (SELECT currency_id FROM exchangerix_currencies WHERE allow_receive='1' AND (reserve>0 || reserve='') AND status='active') AND status='active' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);
	

	if ($total > 0)
	{
		$row = mysqli_fetch_array($result);
		
		$send_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='$from_id' LIMIT 1"));
		$receive_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='$to_id' LIMIT 1"));
		
		$gateway = strtolower($receive_row['currency_name']);
	
		$ip	= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));
		if (filter_var($ip, FILTER_VALIDATE_IP))
			$user_ip = $ip;


		//////////////////////// update rate ///////////////////////
		if ($row['auto_rate'] == 1 && $row['last_update_mins'] > UPDATE_RATES_MINUTES)
		{		
			$from 	= GetCurrencyCode($row['from_currency']);
			$to 	= GetCurrencyCode($row['to_currency']);
										
			exchagerix_update_rate($from, $to, $row['fee'], $row['exdirection_id']);			
		}
		////////////////////////////////////////////////////////////
	
	
		$from_amount 	= substr(floatval($_POST['from_amount']), 0, 20);
		$to_amount		= $from_amount*$row['exchange_rate']; //fee //dev
		$to_amount 		= dtruncate($to_amount, 4);
		//$to_amount	= floatval($from_amount*$row['exchange_rate']);
		//if (strstr($to_amount, ".")) $to_amount = number_format($to_amount, 4, '.', '');
		//$to_amount = round($to_amount, 4);

		if (!($from_amount > 0 && $to_amount > 0))
		{
			header("Location: index.php?currency_send=$from_id&currency_receive=$to_id");
			exit();
		}
		
		if ($row['min_amount'] != "" && $from_amount < $row['min_amount'])
		{
			header("Location: index.php?currency_send=$from_id&currency_receive=$to_id&err=min_amount");
			exit();
		}
		
		if ($row['max_amount'] != "" && $from_amount > $row['max_amount'])
		{
			header("Location: index.php?currency_send=$from_id&currency_receive=$to_id&err=max_amount");
			exit();
		}
		
		if ($receive_row['reserve'] > 0 && $to_amount > $receive_row['reserve'])
		{
			header("Location: index.php?currency_send=$from_id&currency_receive=$to_id&err=max_amount");
			exit();
		}
		
		if (GetCurrencyReserve($to_id) < $to_amount && GetCurrencyReserve($to_id) != "unlimited")
		{
			header("Location: index.php?currency_send=$from_id&currency_receive=$to_id&err=low_reserve");
			exit();		
		}
		
		// load user info
		if (isLoggedIn() && $_POST['action'] != "proceed")
		{
			$uquery	= "SELECT * FROM exchangerix_users WHERE user_id='".(int)$_SESSION['userid']."' AND status='active' LIMIT 1";
			$uresult = smart_mysql_query($uquery);
			if (mysqli_num_rows($uresult) > 0)
			{
				$urow = mysqli_fetch_array($uresult);
				$fullname = $urow['fname']." ".$urow['lname'];
				$email = $urow['email'];	
			}
			else
			{
				header("Location: logout.php");
				exit();					
			}
		}
		
		// setup exchange amounts
		$_SESSION['from_amount'] 	= $from_amount;
		$_SESSION['to_amount'] 		= $to_amount;
						
				
		$ptitle	= "Exchange ".GetCurrencyFName($row['from_currency'])." to ".GetCurrencyFName($row['to_currency']);

		
		if (isset($_POST['action']) && $_POST['action'] == "proceed")
		{
			unset($errs);
			$errs = array();
			
			if (isLoggedIn()) $user_id = (int)$_SESSION['userid']; else $user_id = 0;
			
			$fullname		= mysqli_real_escape_string($conn, getPostParameter('fullname'));
			$email			= mysqli_real_escape_string($conn, strtolower(getPostParameter('email')));
			$phone			= mysqli_real_escape_string($conn, getPostParameter('phone')); //check format //dev
			$account		= mysqli_real_escape_string($conn, getPostParameter('a_field_1'));
			$tos			= (int)getPostParameter('tos');
			$new_account 	= (int)getPostParameter('new_account'); //dev

			
			if (isset($_COOKIE['referer_id']) && is_numeric($_COOKIE['referer_id']))
				$ref_id	= (int)$_COOKIE['referer_id'];
			else 
				$ref_id = 0;

			
			$ip			= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));
			//$captcha	= mysqli_real_escape_string($conn, getPostParameter('captcha'));
	
			if (!($fullname && $email && $account))
			{
				$errs[] = CBE1_SIGNUP_ERR;
			}
			
			if (!$account)
			{	
				if ($gateway == "paypal")
				{
					$errs[] = "Please enter a valid Paypal Account (ex: example@domain.com)"; 
				
				}elseif ($gateway == "payeer")
				{
					$errs[] = "Please enter a valid Payeer Account (ex: P1000000)";
					
				}elseif ($gateway == "perfect_money")
				{
					$errs[] = "Please enter a valid PerfectMoney Account (ex: Uxxxxxx)";
					
				}elseif ($gateway == "advcash")
				{
					$errs[] = "Please enter a valid AdvCash Account (ex: example@gmail.com)";
					
				}elseif ($gateway == "bitcoin")
				{
					$errs[] = "Please enter a valid Bitcoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)";
					
				}elseif ($gateway == "litecoin")
				{
					$errs[] = "Please enter a valid Litecoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)";
					
				}elseif ($gateway == "dogecoin")
				{
					$errs[] = "Please enter a valid Dogecoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)";
					
				}elseif ($gateway == "dash")
				{
					$errs[] = "Please enter a valid Dash Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)";
					
				}elseif ($gateway == "peercoin")
				{
					$errs[] = "Please enter a valid Peercoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)";
					
				}elseif ($gateway == "ethereum")
				{
					$errs[] = "Please enter a valid Ethereum Address (ex: 0xaax00110aax00110aax00110aax00110aax00110)";
					
				}elseif ($gateway == "bitcoincash")
				{
					$errs[] = "Please enter a valid Bitcoin Cash Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)";
					
				}elseif ($gateway == "monero")
				{
					$errs[] = "Please enter a valid Monero Address (ex: 4XXXXxxXXx1XXx2xxX3XX456xXx...)";
					
				}elseif ($gateway == "ripple")
				{
					$errs[] = "Please enter a valid Ripple Address (ex: rXxxXxxXX15xXxXXxXx3XxxX1XxxXXxX6X)";
					
				}elseif ($gateway == "zcash")
				{
					$errs[] = "Please enter a valid Zcash Address (ex: t1XXXXxxXXx1XXx2xxX3XX456xXx)";
					
				}elseif ($gateway == "ethereumclassic")
				{
					$errs[] = "Please enter a valid Ethereum Address (ex: 0xaax00110aax00110aax00110aax00110aax00110)";
					
				}elseif ($gateway == "augur")
				{
					$errs[] = "Please enter a valid Augur Address (ex: 0xaax00110aax00110aax00110aax00110aax00110)";
					
				}elseif ($gateway == "golem")
				{
					$errs[] = "Please enter a valid Golem Address (ex: 0xaax00110aax00110aax00110aax00110aax00110)";
					
				}elseif ($gateway == "gnosis")
				{
					$errs[] = "Please enter a valid Gnosis Address (ex: 0xaax00110aax00110aax00110aax00110aax00110)";
					
				}elseif ($gateway == "lisk")
				{
					$errs[] = "Please enter a valid Lisk Address (ex: AABBCCDDEEFF0011A)";
					
				}elseif ($gateway == "clams")
				{
					$errs[] = "Please enter a valid Clams Address (ex: xXxXX1xxXxxXx1xX1xXxx1xXXxXxXXxXxx)";
					
				}elseif ($gateway == "namecoin")
				{
					$errs[] = "Please enter a valid Namecoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)";
					
				}else
				{
					//$errs[] = "Please enter a valid account for receiving"; 
				}
			}
			
			if ($gateway == "bitcoin" && $account != "" && !checkBitcoinAddress($account))
			{
				$errs[] = "Please enter a valid Bitcoin Address<br> (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)";
			}
			
			if (isset($email) && $email != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
			{
				$errs[] = CBE1_SIGNUP_ERR4;
			}
			
			if (!(isset($tos) && $tos == 1))
			{
				$errs[] = CBE1_SIGNUP_ERR9;
			}
			
				/*
			if (count($errs) == 0)
				$check_query = "SELECT * FROM exchangerix_transactions WHERE amount='$amount' OR email='$email' AND ip='' AND created IN 10 minutes LIMIT 1";
				$check_result = smart_mysql_query($check_query);
				if (mysqli_num_rows($check_result) != 0)
				{
					$errs[] = "Transaction is exists". You can make transaction in next 5 minutes.";
				}
			}
				*/
			
			
			if (count($errs) == 0)
			{
					$exdirection_id 	= (int)$row['exdirection_id'];
					$reference_id 		= GenerateReferenceID();
					$country			= @country_ip($ip);
					$country			= mysqli_real_escape_string($conn, $country);
					$country 	= 0;
					$exchange_amount 	= floatval($_SESSION['from_amount']);
					$receive_amount 	= floatval($_SESSION['to_amount']);
					
					$from_currency 		= mysqli_real_escape_string($conn, $send_row['currency_name']." ".$send_row['currency_code']);
					$to_currency 		= mysqli_real_escape_string($conn, $receive_row['currency_name']." ".$receive_row['currency_code']);
					$ex_from_rate		= floatval($row['from_rate']);
					$ex_to_rate 		= floatval($row['to_rate']);
					
					/////////////// create new account ///////////////
					if ($new_account == 1)
					{	
						$pwd 				= mysqli_real_escape_string($conn, generatePassword(10));
						$fullname_arr		= explode(" ", $fullname);
						$fname 				= ucfirst(trim($fullname_arr[0]));
						$lname 				= ucfirst(trim($fullname_arr[1]));
						//$activation_key 	= GenerateKey($email);
						$unsubscribe_key 	= GenerateKey($email);						
						
						$ucheck_result = smart_mysql_query("SELECT username FROM exchangerix_users WHERE username='$email' OR email='$email' LIMIT 1");
						
						if (mysqli_num_rows($ucheck_result) == 0)
						{						
							smart_mysql_query("INSERT INTO exchangerix_users SET username='$email', password='".PasswordEncryption($pwd)."', email='$email', fname='$fname', lname='$lname', country='$country', phone='$phone', ref_id='$ref_id', newsletter='1', ip='$ip', status='active', activation_key='$activation_key', unsubscribe_key='$unsubscribe_key', created=NOW()");
							$user_id = mysqli_insert_id($conn);
							
							//////  Send welcome message  /////
							$etemplate = GetEmailTemplate('signup');
							$esubject = $etemplate['email_subject'];
							$emessage = $etemplate['email_message'];
		
							$emessage = str_replace("{first_name}", $fname, $emessage);
							$emessage = str_replace("{username}", $email, $emessage);
							$emessage = str_replace("{password}", $pwd, $emessage);
							$emessage = str_replace("{login_url}", SITE_URL."login.php", $emessage);
							$to_email = $fname.' '.$lname.' <'.$email.'>';
		
							SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
							///////////////////////////////////
						}					
					}
					///////////////////////////////////////////////////
					
					
					if ($row['fee'] != "")
					{
						if (strstr($row['fee'],"%"))
							$exchange_fee = CalculatePercentage($exchange_amount, str_replace("%","",$row['fee']));
						else
							$exchange_fee = $row['fee'];
					}else {
						$exchange_fee = 0;
					}	
					$exchange_fee 		= floatval($exchange_fee);
					
					
					$client_details		= $fullname;
					if ($phone != "") 	$client_details .= "<br>".$phone;
					
					
					if (isset($_SESSION['transaction_id']) && $_SESSION['transaction_id'] > 0)
					{
						smart_mysql_query("UPDATE exchangerix_exchanges SET exdirection_id='$exdirection_id', user_id='$user_id', reference_id='$reference_id', to_currency_id='$to_id', from_currency_id='$from_id', from_currency='$from_currency', to_currency='$to_currency', ex_from_rate='$ex_from_rate', ex_to_rate='$ex_to_rate', exchange_rate='', exchange_amount='$exchange_amount', receive_amount='$receive_amount', exchange_fee='$exchange_fee', from_account='$from_account', to_account='$account', client_email='$email', country_code='$country', client_details='$client_details', ref_id='$ref_id', status='waiting', notification_sent='0' WHERE exchange_id='".(int)$_SESSION['transaction_id']."' LIMIT 1");
					}
					else
					{
						smart_mysql_query("INSERT INTO exchangerix_exchanges SET exdirection_id='$exdirection_id', user_id='$user_id', reference_id='$reference_id', to_currency_id='$to_id', from_currency_id='$from_id', from_currency='$from_currency', to_currency='$to_currency', ex_from_rate='$ex_from_rate', ex_to_rate='$ex_to_rate', exchange_rate='', exchange_amount='$exchange_amount', receive_amount='$receive_amount', exchange_fee='$exchange_fee', from_account='', to_account='$account', client_email='$email', country_code='$country', client_details='$client_details', ref_id='$ref_id', status='waiting', notification_sent='0', created=NOW()"); //$ip
						
						$new_id = mysqli_insert_id($conn);
						$_SESSION['transaction_id'] = $new_id;						
					}
					
					header("Location: exchange_step3.php");
					exit();
			}
			else
			{
				$allerrors = "";
				foreach ($errs as $errorname)
					$allerrors .= $errorname."<br/>";
			}				
		
		}
	}
	else
	{
		$ptitle = "Exchange";
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE			= $ptitle;

	$bg_dark = 1;
	require_once ("inc/header.inc.php");

?>	

	<?php

		if ($total > 0) {

	?>

	
	<div class="row">
		<div class="col-md-8">

			<div class="widget">
			
				<h1 class="text-center">
					<span class="hidden-xs">Exchange</span> 
					<?php if ($send_row['image'] != "no_image.png") { ?>
						<img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $send_row['image']; ?>" width="35" height="35" class="imgrs" />
					<?php } ?> 
					<?php echo $send_row['currency_name']." ".$send_row['currency_code']; ?>
					 <i class="fa fa-long-arrow-right" aria-hidden="true"></i> 
					<?php if ($receive_row['image'] != "no_image.png") { ?>
					 	<img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $receive_row['image']; ?>" width="35" height="35" class="imgrs" />
					<?php } ?> 
					 <?php echo $receive_row['currency_name']." ".$receive_row['currency_code']; ?>
				</h1>

				<div class="wrap">
				  <div class="links">
				    <div class="dot done">STEP 1</div>
				    <div class="dot current">STEP 2</div>
				    <div class="dot disabled">STEP 3</div>
				  </div>
				</div>
				
				<h2 class="lined text-center">Your Details</h2>
				
				<div class="row">
				<div class="col-md-8 col-md-offset-2">
					
				<?php if (isset($allerrors)) { ?>
					<div class="alert alert-danger">
						<?php if (isset($_GET['msg']) && $_GET['msg'] == "exists") { ?>
							<?php ?><br/>
						<?php }elseif (isset($allerrors)) { ?>
							<?php echo $allerrors; ?>
						<?php }	?>
					</div>
				<?php } ?>

				
				<form action="" method="post">
						
						<div class="form-group">
							<label>Your Name <span class="req">*</span></label>
							<input type="text" class="form-control" name="fullname" value="<?php echo @$fullname; ?>" placeholder="Enter your name" required>
						</div>
						<div class="form-group">
							<label>Your Email <span class="req">*</span></label>
							<input type="email" class="form-control" name="email" value="<?php echo @$email; ?>" placeholder="Enter your email" required>
						</div>
						<div class="form-group">
							<label>Phone</label>
							<input type="text" class="form-control" name="phone" value="<?php echo getPostParameter('phone'); ?>" placeholder="">
						</div>
				
				<?php

				if ($gateway == "paypal") {
						?>
						<div class="form-group">
							<label>Your PayPal account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" placeholder="Enter your paypal acccount" required>
						</div>
						<?php
					} elseif($gateway == "skrill") {
						?>
						<div class="form-group">
							<label>Your Skrill account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required>
						</div>
						<?php
					} elseif($gateway == "webmoney") {
						?>
						<div class="form-group">
							<label>Your WebMoney account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "payeer") {
						?>
						<div class="form-group">
							<label>Your Payeer account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "perfect money") {
						?>
						<div class="form-group">
							<label>Your Perfect Money account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "advcash") {
						?>
						<div class="form-group">
							<label>Your AdvCash account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "okpay") {
						?>
						<div class="form-group">
							<label>Your OKPay account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "entromoney") { 
						?>
						<div class="form-group">
							<label>Your Entromoney account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" placeholder="(Example: U12345678)" required>
						</div>
						<?php
					} elseif($gateway == "solidtrust pay") {
						?>
						<div class="form-group">
							<label>Your SolidTrust Pay account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "neteller") {
						?>
						<div class="form-group">
							<label>Your Neteller account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "uquid") {
						?>
						<div class="form-group">
							<label>Your UQUID account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "wex") {
						?>
						<div class="form-group">
							<label>Your Wex.nz account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "yandex money") {
						?>
						<div class="form-group">
							<label>Your Yandex Money account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "qiwi") {
						?>
						<div class="form-group">
							<label>Your QIWI account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "payza") {
						?>
						<div class="form-group">
							<label>Your Payza account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "bitcoin") {
						?>
						<div class="form-group">
							<label>Your Bitcoin address <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "litecoin") {
						?>
						<div class="form-group">
							<label>Your Litecoin address <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "dogecoin") {
						?>
						<div class="form-group">
							<label>Your Dogecoin address <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "dash") {
						?>
						<div class="form-group">
							<label>Your Dash address <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "peercoin") {
						?>
						<div class="form-group">
							<label>Your Peercoin address <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "ethereum") {
						?>
						<div class="form-group">
							<label>Your Ethereum address <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<?php
					} elseif($gateway == "bank transfer") {
						?>
						<div class="form-group">
							<label>Bank Account Holder's Name <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<div class="form-group">
							<label>Bank Account Number/IBAN <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo getPostParameter('a_field_2'); ?>">
						</div>
						<div class="form-group">
							<label>SWIFT Code <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_3" value="<?php echo getPostParameter('a_field_3'); ?>">
						</div>
						<div class="form-group">
							<label>Bank Name in Full <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_4" value="<?php echo getPostParameter('a_field_4'); ?>">
						</div>
						<div class="form-group">
							<label>Bank Branch Country, City, Address <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_5" value="<?php echo getPostParameter('a_field_5'); ?>">
						</div>
						<?php
					} elseif($gateway == "western union") {
						?>
						<div class="form-group">
							<label>Recipient Name <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<div class="form-group">
							<label>Recipient Address <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo getPostParameter('a_field_2'); ?>" required>
						</div>
						<?php
					} elseif($gateway == "moneygram") {
						?>
						<div class="form-group">
							<label>Recipient Name <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>
						<div class="form-group">
							<label>Recipient Address <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo getPostParameter('a_field_2'); ?>" required>
						</div>
						<?php
					} else { ?>
						<div class="form-group">
							<label>Your <?php echo substr($receive_row['currency_name'], 0,50); ?> Account <span class="req">*</span></label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo getPostParameter('a_field_1'); ?>" required> 
						</div>					
					<?php } ?>
				
						<!--<input type="hidden" name="exdirection" value="" />-->	
						<input type="hidden" name="from_amount" value="<?php echo @$from_amount; ?>" />
						<input type="hidden" name="currency_send" value="<?php echo @$from_id; ?>" />
						<input type="hidden" name="currency_receive" value="<?php echo @$to_id; ?>" />	
						<input type="hidden" name="action" value="proceed" />								

					<div class="checkbox">
						<label><input type="checkbox" name="tos" class="checkboxx" value="1" <?php echo (!$_POST['action'] || @$tos == 1) ? "checked" : "" ?>/> <?php echo CBE1_SIGNUP_AGREE; ?> <a href="<?php echo SITE_URL; ?>terms.php" target="_blank"><?php echo CBE1_SIGNUP_TERMS; ?></a></label>
	        		</div>
	        		<?php if (!isLoggedIn()) { ?>
					<div class="checkbox">
						<label><input type="checkbox" name="new_account" class="checkboxx" value="1" <?php echo (!$_POST['action'] || @$new_account == 1) ? "checked" : "" ?>/> I want to create <?php //echo SITE_TITLE; ?> account and earn discount</label>
	        		</div>
	        		<?php } ?>
	        		<hr>
	        		<p class="text-center">
		        		<a class="btn btn-default btn-lg pull-left" href="<?php echo SITE_URL; ?>index.php?currency_send=<?php echo @$from_id; ?>&currency_receive=<?php echo @$to_id; ?>"><i class="fa fa-angle-left" aria-hidden="true"></i> Go Back</a>
						<button type="submit" name="proceed" class="btn btn-success btn-lg pull-right">Next Step <i class="fa fa-angle-right" aria-hidden="true"></i></button>
						<br><br><br>
					</p>
					
				</form>
				</div>
				</div>			
			
			</div>
		

		</div>
		<div class="col-md-4">
			
			<div class="widget">
				<a href="<?php echo SITE_URL; ?>index.php?currency_send=<?php echo @$from_id; ?>&currency_receive=<?php echo @$to_id; ?>" class="pull-right" style="padding-top: 15px"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> change</a>
				<h1><i class="fa fa-refresh" aria-hidden="true"></i> Your Exchange</h1>
				
				<h3>Amount Send <i class="fa fa-arrow-up" aria-hidden="true" style="color: #8dc6fb"></i></h3>
				<h3 style="color: #54b1f8"> &nbsp; <?php echo $_SESSION['from_amount']; ?> <?php echo $send_row['currency_code']; ?></h3>
				
				<h3>Amount Receive <i class="fa fa-arrow-down" aria-hidden="true" style="color: #5cb85c"></i></h3>
				<h3 style="color: #5bbc2e"> &nbsp; <?php echo $_SESSION['to_amount']; ?> <?php echo $receive_row['currency_code']; ?></h3>
				
				<h3>Exchange Rate</h3>
				<h3 style="color: #777"> &nbsp; <?php echo $row['from_rate']; ?> <?php echo $send_row['currency_code']; ?> = <?php echo $row['to_rate']; ?> <?php echo $receive_row['currency_code']; ?></h3>
				 &nbsp;&nbsp; <small><i class="fa fa-clock-o" aria-hidden="true"></i> last updated: <?php $timestamp = time(); $date_time = date("j M Y H:i A T", $timestamp); echo $date_time; ?></small>
			</div>
			
			<div class="widget">
				
				<b><i class="fa fa-lock" aria-hidden="true"></i> Secure Exchange</b><br>
				Your exchange is always safe and secure.
				
				<?php if (RESERVE_MINUTES > 0) { ?>
					<br><br>Exchange amount (<b><span style="color: #000"><?php echo $_SESSION['to_amount']; ?> <?php echo $receive_row['currency_code']; ?></span></b>) will be reserved for <b><?php echo (int)RESERVE_MINUTES; ?> minutes</b>.
				<?php } ?>	
			</div>	

			<p><span style="color: #999"><small>Note: for security reasons, your IP (<?php echo @$user_ip; ?>) was recorded by our system.</small></span></p>

		</div>
	</div>


	<?php }else{ ?>
		<h1>Exchange</h1>
		<div class="alert alert-info"><?php echo CBE1_NOT_FOUND2; ?></div>
		<p align="center"><a class="btn btn-default" href="#" onclick="history.go(-1);return false;"><?php echo CBE1_GO_BACK; ?></a></p>
	<?php } ?>		  	
	
	

<?php require_once ("inc/footer.inc.php"); ?>