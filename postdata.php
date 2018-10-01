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
	
	
		
	if (isset($_POST['action']) && $_POST['action'] == "reserve_request")
	{
		unset($errs);
		$errs = array();

		if (isset($_POST['amount'])) // && is_numeric($_POST['amount'])
			$amount = floatval($_POST['amount']);
		
		$email		 	= strtolower(mysqli_real_escape_string($conn, getPostParameter('email')));
		$exdirection_id	= (int)$_POST['exdirId'];
		$currency_id	= (int)$_POST['currId'];
		$comment		= mysqli_real_escape_string($conn, getPostParameter('comment'));
		if (isLoggedIn()) $user_id = (int)$_SESSION['userid'];
		$ip				= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));
		//captcha //dev

		if (!($email && $amount))
		{
			$errs[] = CBE1_SIGNUP_ERR;
		}
		
		if (isset($email) && $email != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
		{
			$errs[] = CBE1_SIGNUP_ERR4;
		}
		
		if (count($errs) == 0)
		{
			$result = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='$currency_id' LIMIT 1");
			if (mysqli_num_rows($result) > 0)
			{
				$row = mysqli_fetch_array($result);
				$currency_name = mysqli_real_escape_string($conn, $row['currency_name']);
				$currency_code = mysqli_real_escape_string($conn, $row['currency_code']);
				
				$check_result = smart_mysql_query("SELECT * FROM exchangerix_reserve_requests WHERE user_id='$user_id' AND currency_name='$currency_name' AND amount='$amount' AND email='$email' LIMIT 1");
				if (mysqli_num_rows($check_result) == 0)
				{
					// send sms to admin
					if (SMS_AMOUNT_REQUEST_ALERT == 1 && SMS_API_KEY != "" && SMS_API_SECRET != "")
					{
						if (CONTACT_PHONE3 != "" ) 	$sms_number = CONTACT_PHONE3;
						if (CONTACT_PHONE2 != "" ) 	$sms_number = CONTACT_PHONE2;
						if (CONTACT_PHONE != "" ) 	$sms_number = CONTACT_PHONE;
						
						if ($sms_number != "")
						{
							require_once("inc/sms/nexmo/NexmoMessage.php");
							$sms 			= new NexmoMessage(SMS_API_KEY, SMS_API_SECRET);
							$sms_message 	= 'New amount request: '.$amount." ".$currency_name;
							$sms->sendText($sms_number, 'MyApp', $sms_message);
						}				
					}
					
					smart_mysql_query("INSERT INTO exchangerix_reserve_requests SET user_id='$user_id', exdirection_id='$exdirection_id', currency_name='$currency_name', currency_code='$currency_code', currency_id='$currency_id', amount='$amount', email='$email', phone='', comment='$comment', is_viewed='0', is_notified='0', ip='$ip', status='pending', added=NOW()");
				
					// send email to admin //dev
					if (NEW_AMOUNT_REQUEST_ALERT == 1)
					{
						SendEmail(SITE_ALERTS_MAIL, "New Reserve Amoint Request - ".SITE_TITLE, "Hi,<br>You have received new reserve amount request from user.");
					}
				}
			}
		}
	}

?>