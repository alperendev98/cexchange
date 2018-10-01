<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/


/**
 * Run mysql query
 * @param	$sql		mysql query to run
 * @return	boolean		false if failed run mysql query
*/

function smart_mysql_query($sql)
{
	global $conn;
	// echo $sql;
	$res = mysqli_query($conn, $sql) or die("<p align='center'><span style='font-size:13px; font-family: tahoma, arial, helvetica, sans-serif; color: red;'>query failed: ".mysqli_error($conn)."</span></p>");
	if (!$res) { return false; }
	return $res;
}


/**
 * Retrieves parameter from POST array
 * @param	$name	parameter name
*/


function getPostParameter($name)
{
	$data = isset($_POST[$name]) ? $_POST[$name] : null;
	if(!is_null($data) && get_magic_quotes_gpc() && is_string($data))
	{
		$data = stripslashes($data);
	}
	$data = trim($data);
	$data = htmlentities($data, ENT_QUOTES, 'UTF-8');
	return $data;
}


/**
 * Retrieves parameter from GET array
 * @param	$name	parameter name
*/


function getGetParameter($name)
{
	return isset($_GET[$name]) ? $_GET[$name] : false;
}


/**
 * Returns random password
 * @param	$length		length of string
 * @return	string		random password
*/

if (!function_exists('generatePassword')) {
	function generatePassword($length = 8)
	{
		$password = "";
		$possible = "0123456789abcdefghijkmnpqrstvwxyzABCDEFGHJKLMNPQRTVWXYZ!(@)";
		$i = 0; 

		while ($i < $length)
		{ 
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

			if (!strstr($password, $char))
			{ 
				$password .= $char;
				$i++;
			}
		}
		return $password;
	}
}


/**
 * Returns random key
 * @param	$text		string
 * @return	string		random key for user verification
*/

if (!function_exists('GenerateKey')) {
	function GenerateKey($text)
	{
		$text = preg_replace("/[^0-9a-zA-Z]/", " ", $text);
		$text = substr(trim($text), 0, 50);
		$key = md5(time().$text.mt_rand(1000,9999));
		return $key;
	}
}


/**
 * Calculate percentage
 * @param	$amount				Amount
 * @param	$percent			Percent value
 * @return	string				returns formated money value
*/

if (!function_exists('CalculatePercentage')) {
	function CalculatePercentage($amount, $percent)
	{
		return number_format(($amount/100)*$percent,2,'.','');
	}
}


/**
 * Returns formated money value
 * @param	$amount				Amount
 * @param	$hide_currency		Hide or Show currency sign
 * @param	$hide_zeros			Show as $5.00 or $5
 * @return	string				returns formated money value
*/

if (!function_exists('DisplayMoney')) {
	function DisplayMoney($amount, $hide_currency = 0, $hide_zeros = 0)
	{
		$newamount = number_format($amount, 2, '.', '');

		if ($hide_zeros == 1)
		{
			$cents = substr($newamount, -2);
			if ($cents == "00") $newamount = substr($newamount, 0, -3);
		}

		if ($hide_currency != 1)
		{
			switch (SITE_CURRENCY_FORMAT)
			{
				case "1": $newamount = SITE_CURRENCY.$newamount; break;
				case "2": $newamount = SITE_CURRENCY." ".$newamount; break;
				case "3": $newamount = SITE_CURRENCY.number_format($amount, 2, ',', ''); break;
				case "4": $newamount = $newamount." ".SITE_CURRENCY; break;
				case "5": $newamount = $newamount.SITE_CURRENCY; break;
				default: $newamount = SITE_CURRENCY.$newamount; break;
			}	
		}

		return $newamount;
	}
}


/**
 * Returns time left
 * @return	string	time left
*/

if (!function_exists('GetTimeLeft')) {
	function GetTimeLeft($time_left)
	{
		$days		= floor($time_left / (60 * 60 * 24));
		$remainder	= $time_left % (60 * 60 * 24);
		$hours		= floor($remainder / (60 * 60));
		$remainder	= $remainder % (60 * 60);
		$minutes	= floor($remainder / 60);
		$seconds	= $remainder % 60;

		$days == 1 ? $dw = CBE1_TIMELEFT_DAY : $dw = CBE1_TIMELEFT_DAYS;
		$hours == 1 ? $hw = CBE1_TIMELEFT_HOUR : $hw = CBE1_TIMELEFT_HOURS;
		$minutes == 1 ? $mw = CBE1_TIMELEFT_MIN : $mw = CBE1_TIMELEFT_MINS;
		$seconds == 1 ? $sw = CBE1_TIMELEFT_SECOND : $sw = CBE1_TIMELEFT_SECONDS;

		if ($time_left > 0)
		{
			//$new_time_left = $days." $dw ".$hours." $hw ".$minutes." $mw";
			$new_time_left = $days." $dw ".$hours." $hw";
			return $new_time_left;
		}
		else
		{
			return "<span class='expired'>".CBE1_TIMELEFT_EXPIRED."</span>";
		}
	}
}


/**
 * Returns member's referrals total
 * @param	$userid		User's ID
 * @return	string		member's referrals total
*/

if (!function_exists('GetReferralsTotal')) {
	function GetReferralsTotal($userid)
	{
		$query = "SELECT COUNT(*) AS total FROM exchangerix_users WHERE ref_id='".(int)$userid."'";
		$result = smart_mysql_query($query);

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			return $row['total'];
		}
	}
}


if (!function_exists('GetUserDiscount')) {
	function GetUserDiscount($userid)
	{
		$query = "SELECT discount FROM exchangerix_users WHERE user_id='".(int)$userid."'";
		$result = smart_mysql_query($query);

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			return $row['discount'];
		}
		return "0";
	}
}



/**
 * Returns  member's current balance
 * @param	$userid					User's ID
 * @param	$hide_currency_option	Hide or show currency sign
 * @return	string					member's current balance
*/

if (!function_exists('GetUserBalance')) {
	function GetUserBalance($userid, $hide_currency_option = 0)
	{
		$query_total = "SELECT SUM(exchange_amount) as total FROM exchangerix_exchanges WHERE ref_id='$userid' AND status='confirmed'";
		$row = mysqli_fetch_array(smart_mysql_query($query_total));
	
		$yourearning = $row['total'] /100 * REFERRAL_COMMISSION;

		$query = "SELECT SUM(amount) AS total FROM exchangerix_transactions WHERE user_id='".(int)$userid."' AND status='confirmed'";
		$result = smart_mysql_query($query);

	
			$row_confirmed = mysqli_fetch_array($result);

		
			$row_paid = mysqli_fetch_array(smart_mysql_query("SELECT SUM(amount) AS total FROM exchangerix_transactions WHERE user_id='".(int)$userid."' AND ((status='paid' OR status='request') OR (payment_type='Withdrawal' AND status='declined'))"));

			$balance = $row_confirmed['total'] - $row_paid['total'] + $yourearning;

			return DisplayMoney($balance, $hide_currency_option);
		

	}
}


/**
 * Add/Deduct money from member's balance
 * @param	$userid		User's ID
 * @param	$amount		Amount
 * @param	$action		Action
*/

if (!function_exists('UpdateUserBalance')) {
	function UpdateUserBalance($userid, $amount, $action)
	{
		$userid = (int)$userid;

		if ($action == "add")
		{
			smart_mysql_query("INSERT INTO exchangerix_transactions SET user_id='$userid', amount='$amount', status='confirmed'");
		}
		elseif ($action == "deduct")
		{
			smart_mysql_query("INSERT INTO exchangerix_transactions SET user_id='$userid', amount='$amount', status='deducted'");
		}
	}
}


/**
 * Returns total of new member's messages from administrator
 * @return	integer		total of new messages for member from administrator
*/

if (!function_exists('GetMemberMessagesTotal')) {
	function GetMemberMessagesTotal()
	{
		$userid	= $_SESSION['userid'];
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_messages_answers WHERE user_id='".(int)$userid."' AND is_admin='1' AND viewed='0'");
		$row = mysqli_fetch_array($result);

		if ($row['total'] == 0)
		{
			$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_messages WHERE user_id='".(int)$userid."' AND is_admin='1' AND viewed='0'");
			$row = mysqli_fetch_array($result);
		}
		return (int)$row['total'];
	}
}


/**
 * Returns payment method name by payment method ID
 * @return	string	payment method name
*/

if (!function_exists('GetPaymentMethodByID')) {
	function GetPaymentMethodByID($pmethod_id)
	{
		$result = smart_mysql_query("SELECT pmethod_title FROM exchangerix_pmethods WHERE pmethod_id='".(int)$pmethod_id."' LIMIT 1");
		$total = mysqli_num_rows($result);

		if ($total > 0)
		{
			$row = mysqli_fetch_array($result);
			return $row['pmethod_title'];
		}
		else
		{
			return "Unknown";
		}
	}
}


/**
 * Returns random string
 * @param	$len	string length
 * @param	$chars	chars in the string
 * @return	string	random string
*/

if (!function_exists('GenerateRandString')) {
	function GenerateRandString($len, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
	{
		$string = '';
		for ($i = 0; $i < $len; $i++)
		{
			$pos = rand(0, strlen($chars)-1);
			$string .= $chars{$pos};
		}
		return $string;
	}
}


/**
 * Returns payment reference ID
 * @return	string	Reference ID
*/

if (!function_exists('GenerateReferenceID')) {
	function GenerateReferenceID()
	{
		unset($num);

		$num = GenerateRandString(9,"0123456789");
    
		$check = smart_mysql_query("SELECT * FROM exchangerix_transactions WHERE reference_id='$num'");
    
		if (mysqli_num_rows($check) == 0 && $num[0] != '0')
		{
			return $num;
		}
		else
		{
			return GenerateReferenceID();
		}
	}
}


/**
 * Returns Encrypted password
 * @param	$password	User's ID
 * @return	string		encrypted password
*/

if (!function_exists('PasswordEncryption')) {
	function PasswordEncryption($password)
	{
		return md5(sha1($password));
	}
}


/**
 * Check user login
 * @return	boolen			false or true
*/

function CheckCookieLogin()
{
	global $conn;

    $uname = mysqli_real_escape_string($conn, $_COOKIE['usname']);

	if (!empty($uname))
	{
        $check_query = "SELECT * FROM exchangerix_users WHERE login_session='$uname' LIMIT 1";
		$check_result = smart_mysql_query($check_query);
		
		if (mysqli_num_rows($check_result) > 0)
		{
			$row = mysqli_fetch_array($check_result);
			
			$_SESSION['userid'] = $row['user_id'];
			$_SESSION['FirstName'] = $row['fname'];

			setcookie("usname", $uname, time()+3600*24*365, '/');

			return true;
		}
		else
		{
			return false;
		}
    }
	else
	{
		return false;
	}
}


/**
 * Saves referral's ID in cookies
 * @param	$ref_id		Referrals's ID
*/

if (!function_exists('setReferral')) {
	function setReferral($ref_id)
	{
		//set up cookie for one month period
		setcookie("referer_id", $ref_id, time()+(60*60*24*30), '/');
	}
}


/**
 * Check if user logged in
 * @return	boolen		false or true
*/

if (!function_exists('isLoggedIn')) {
	function isLoggedIn()
	{
		if (!(isset($_SESSION['userid']) && is_numeric($_SESSION['userid'])))
			return false;
		else
			return true;
	}
}


/**
 * Returns user's information
 * @param	$user_id	User ID
 * @return	string		user name, or "User not found"
*/

if (!function_exists('GetUsername')) {
	function GetUsername($user_id, $hide_lastname = 0)
	{
		if ($user_id == 0) return "Visitor";
		
		$result = smart_mysql_query("SELECT * FROM exchangerix_users WHERE user_id='".(int)$user_id."' LIMIT 1");
		
		if (mysqli_num_rows($result) != 0)
		{
			$row = mysqli_fetch_array($result);
			if ($hide_lastname == 1)
				return $row['fname']." ".substr($row['lname'], 0, 1).".";
			else
				return $row['fname']." ".$row['lname'];
		}
		else
		{
			return "User not found";
		}
	}
}

//$from USD
//$to BTC
//https://min-api.cryptocompare.com/data/price?fsym=BTC&tsyms=USD,EUR
//https://www.cryptocompare.com/api/#public-api-invocation
function exchagerix_update_rate ($from, $to, $margin=0, $exdirection_id=0)
{
		global $conn;
		
		$fsym = $from;
		$tsyms = $to;				
		
		$url = "https://min-api.cryptocompare.com/data/price?fsym=".$fsym."&tsyms=".$tsyms;
		$json = json_decode(file_get_contents($url), true);
						
		if ($json["Response"] != "Error")
		{
			$json[$tsyms] = strtolower($json[$tsyms]);
			if ($json[$tsyms] < 0.01) //if (strstr($json[$tsyms], "e")) die();
			{
				$json2 = json_decode(file_get_contents("https://min-api.cryptocompare.com/data/price?fsym=".$tsyms."&tsyms=".$fsym), true);
				$from_rate = $json2[$fsym];
				$to_rate = 1;
			}
			else
			{
				$from_rate = 1;
				$to_rate = floatval($json[$tsyms]);
			}
				
			if ($margin != "" && $margin != 0)
			{
				if (strstr($margin, "-"))
				{
					$percent = trim(str_replace("-","",$margin));
					$margin = CalculatePercentage($from_rate, $percent);
					$from_rate  -= $margin;
				}
				else
				{
					$percent = trim($margin);
					$margin = CalculatePercentage($from_rate, $percent);
					$from_rate  += $margin;
				}
			}
				
			$from_rate = mysqli_real_escape_string($conn, $from_rate);
			$to_rate = mysqli_real_escape_string($conn, $to_rate);
			$exchange_rate  = $to_rate/$from_rate;
				
			smart_mysql_query("UPDATE exchangerix_exdirections SET from_rate='$from_rate', to_rate='$to_rate', exchange_rate='$exchange_rate', updated=NOW() WHERE exdirection_id='".(int)$exdirection_id."' LIMIT 1");
							
		}
		else
		{
			return false;
		}				
}


/**
 * Returns setting value by setting's key
 * @param	$setting_key	Setting's Key
 * @return	string	setting's value
*/

if (!function_exists('GetSetting')) {
	function GetSetting($setting_key)
	{
		global $conn;
		$setting_key = mysqli_real_escape_string($conn, $setting_key);
		$setting_result = smart_mysql_query("SELECT setting_value FROM exchangerix_settings WHERE setting_key='".$setting_key."' LIMIT 1");
		if (mysqli_num_rows($setting_result) > 0)
		{
			$setting_row = mysqli_fetch_array($setting_result);
			$setting_value = $setting_row['setting_value'];
			return $setting_value;
		}
		else
		{
			die ("config settings not found");
		}
	}
}


/**
 * Returns top menu pages links
 * @return	string	top menu pages links
*/

if (!function_exists('ShowTopPages')) {
	function ShowTopPages()
	{
		global $conn;

		$language = mysqli_real_escape_string($conn, USER_LANGUAGE);
		$result = smart_mysql_query("SELECT * FROM exchangerix_content WHERE (language='' OR language='$language') AND (page_location='top' OR page_location='topfooter') AND status='active'");
		if (mysqli_num_rows($result) > 0)
		{
			while ($row = mysqli_fetch_array($result))
			{
				echo "<li><a href=\"".SITE_URL."content.php?id=".$row['content_id']."\">".$row['link_title']."</a></li> ";
			}
		}
	}
}


/**
 * Returns footer menu pages links
 * @return	string	footer menu pages links
*/

if (!function_exists('ShowFooterPages')) {
	function ShowFooterPages()
	{
		global $conn;

		$language = mysqli_real_escape_string($conn, USER_LANGUAGE);
		$result = smart_mysql_query("SELECT * FROM exchangerix_content WHERE (language='' OR language='$language') AND (page_location='footer' OR page_location='topfooter') AND status='active'");
		if (mysqli_num_rows($result) > 0)
		{
			while ($row = mysqli_fetch_array($result))
			{
				echo "<a href=\"".SITE_URL."content.php?id=".$row['content_id']."\">".$row['link_title']."</a> | ";
			}
		}
	}
}


/**
 * Returns content for static pages
 * @param	$content_name	Content's Name or Content ID
 * @return	array	(1) - Page Title, (2) - Page Text
*/

if (!function_exists('GetContent')) {
	function GetContent($content_name)
	{
		global $conn;

		$language = mysqli_real_escape_string($conn, USER_LANGUAGE);

		if (is_numeric($content_name))
		{
			$content_id = (int)$content_name;
			$content_result = smart_mysql_query("SELECT * FROM exchangerix_content WHERE (language='' OR language='$language') AND content_id='".$content_id."' LIMIT 1");
		}
		else
		{
			$content_result = smart_mysql_query("SELECT * FROM exchangerix_content WHERE (language='' OR language='$language') AND name='".$content_name."' LIMIT 1");
		}

		$content_total = mysqli_num_rows($content_result);

		if ($content_total > 0)
		{
			$content_row					= mysqli_fetch_array($content_result);
			$contents['link_title']			= stripslashes($content_row['link_title']);
			$contents['title']				= stripslashes($content_row['title']);
			$contents['text']				= stripslashes($content_row['description']);
			$contents['meta_description']	= stripslashes($content_row['meta_description']);
			$contents['meta_keywords']		= stripslashes($content_row['meta_keywords']);
		}
		else
		{
			$contents['title']	= CBE1_CONTENT_NO;
			$contents['text']	= "<p align='center'>".CBE1_CONTENT_NO_TEXT."<br/><br/><a class='goback' href='".SITE_URL."'>".CBE1_CONTENT_GOBACK."</a></p>";
		}

		return $contents;
	}
}


/**
 * Returns content for email template
 * @param	$email_name	Email Template Name
 * @return	array	(1) - Email Subject, (2) - Email Message
*/

if (!function_exists('GetEmailTemplate')) {
	function GetEmailTemplate($email_name)
	{
		global $conn;

		$language = mysqli_real_escape_string($conn, USER_LANGUAGE);
		
		$etemplate_result = smart_mysql_query("SELECT * FROM exchangerix_email_templates WHERE language='".$language."' AND email_name='".$email_name."' LIMIT 1");
		$etemplate_total = mysqli_num_rows($etemplate_result);

		if ($etemplate_total > 0)
		{
			$etemplate_row = mysqli_fetch_array($etemplate_result);
			$etemplate['email_subject'] = stripslashes($etemplate_row['email_subject']);
			$etemplate['email_message'] = stripslashes($etemplate_row['email_message']);

			$etemplate['email_message'] = "<html>
								<head>
									<title>".$etemplate['email_subject']."</title>
								</head>
								<body>
								<table width='80%' border='0' cellpadding='10'>
								<tr>
									<td align='left' valign='top'>".$etemplate['email_message']."</td>
								</tr>
								</table>
								</body>
							</html>";
		}
		else
		{
			//$etemplate['email_subject'] = CBE1_EMAIL_NO_SUBJECT;
			die (CBE1_EMAIL_NO_MESSAGE);
		}

		return $etemplate;
	}
}


/**
 * Sends email
 * @param	$recipient		Email Recipient John Doe <johndoe@email.com>
 * @param	$subject		Email Subject
 * @param	$message		Email Message
 * @param	$noreply_mail	No Reply Email flag
 * @param	$from			FROM headers
*/

if (!function_exists('SendEmail')) {
	function SendEmail($recipient, $subject, $message, $noreply_mail = 0, $from = "")
	{
		define('EMAIL_TYPE', 'html');			// html, text
		define('EMAIL_CHARSET', 'UTF-8');

		if ($noreply_mail == 1) $SITE_MAIL = NOREPLY_MAIL; else $SITE_MAIL = SITE_MAIL;

		if (SMTP_MAIL == 1)
		{
			require_once('phpmailer/PHPMailerAutoload.php');

			$mail = new PHPMailer();
			
			$mail->IsSMTP();
			$mail->CharSet = EMAIL_CHARSET;		// email charset
			$mail->SMTPDebug = 0;				// 0 = no output, 1 = errors and messages, 2 = messages only
			$mail->SMTPAuth = true;				// enable SMTP authentication
			$mail->SMTPSecure = SMTP_SSL;		// sets the prefix to the servier (ssl, tls)
			$mail->Host = SMTP_HOST;			// SMTP server
			$mail->Port = SMTP_PORT;			// SMTP port
			$mail->Username = SMTP_USERNAME;	// SMTP username
			$mail->Password = SMTP_PASSWORD;	// SMTP password

			if (EMAIL_TYPE == "text")
			{
				$mail->ContentType = 'text/plain';
				$mail->IsHTML(false);
			}
			else
			{
				$mail->IsHTML(true);
			}

			$mail->Subject = $subject;
			if ($from != "")
			{
				$afrom = str_replace('>', '', $from);
				$aafrom = explode("<", $afrom);
				$from_name = $aafrom[0];
				$from_email = $aafrom[1];
				$mail->SetFrom ($from_email, $from_name);
			}
			else
			{
				$mail->SetFrom ($SITE_MAIL, EMAIL_FROM_NAME);
			}
			$mail->Body = $message;	// $mail->Body = file_get_contents('mail_template.html');
			
			if (strstr($recipient, "<") && strstr($recipient, ">"))
			{
				$efrom = str_replace('>', '', $recipient);
				$eefrom = explode("<", $efrom); //DEV
				$recipient_name = $eefrom[0];
				$recipient_email = $eefrom[1];
			}
			else
			{
				$recipient_name = $recipient;
				$recipient_email = $recipient;				
			}

			$mail->AddAddress ($recipient_email, $recipient_name);
			//$mail->AddBCC ('sales@example.com', 'Example.com Sales Dep.');

			if(!$mail->Send())
				return false; // $error_message = "Mailer Error: " . $mail->ErrorInfo;
			else
				return true;
		}
		else
		{
			$headers = 'MIME-Version: 1.0' . "\r\n";
			
			if (EMAIL_TYPE == "text")
				$headers .= 'Content-type: text/plain; charset='.EMAIL_CHARSET.'' . "\r\n";
			else
				$headers .= 'Content-type: text/html; charset='.EMAIL_CHARSET.'' . "\r\n";
			
			if ($from != "")
				$headers .= $from. "\r\n";
			else
				$headers .= 'From: '.EMAIL_FROM_NAME.' <'.$SITE_MAIL.'>' . "\r\n";

			mail($recipient, $subject, $message, $headers);
		}
	}
}


/**
 * Returns trancated text
 * @param	$text		Text
 * @param	$limit		characters limit
 * @param	$more_link	Show/Hide 'read more' link
 * @return	string		text
*/

if (!function_exists('TruncateText')) {
	function TruncateText($text, $limit, $more_link = 0)
	{
		$limit = (int)$limit;

		if ($limit > 0 && strlen($text) > $limit)
		{
			$ntext = substr($text, 0, $limit);
			$ntext = substr($ntext, 0, strrpos($ntext, ' '));
			$ttext = $ntext;
			if ($more_link == 1)
			{
				$ttext .= ' <a id="next-button">'.CBE1_TRUNCATE_MORE.' &raquo;</a><span id="hide-text-block" style="display: none">'.@str_replace($ntext, '', $text, $count = 1).' <a id="prev-button" style="display: none">&laquo; '.CBE1_TRUNCATE_LESS.'</a></span>';
			}
			else
			{
				$ttext .= " ...";
			}
		}
		else
		{
			$ttext = $text;
		}
		return $ttext;
	}
}


if (!function_exists('GetDirectionName')) {
	function GetDirectionName($exdirection_id)
	{
		$result = smart_mysql_query("SELECT from_currency, to_currency FROM exchangerix_exdirections WHERE exdirection_id='".(int)$exdirection_id."' LIMIT 1");
		$row = mysqli_fetch_array($result);
		return GetCurrencyName($row['from_currency'])." <i class='fa fa-long-arrow-right'></i> ".GetCurrencyName($row['to_currency']);
	}
}

if (!function_exists('GetDirectionRate')) {
	function GetDirectionRate($exdirection_id)
	{
		$result = smart_mysql_query("SELECT from_rate, to_rate, exchange_rate FROM exchangerix_exdirections WHERE exdirection_id='".(int)$exdirection_id."' LIMIT 1");
		$row = mysqli_fetch_array($result);
		return $row['exchange_rate'];
	}
}



/**
 * Returns country name
 * @param	$country_id			Country ID
 * @param	$show_only_icon		Show/Hide country name
 * @return	string				country name
*/

if (!function_exists('GetCountry')) {
	function GetCountry($country_id, $show_only_icon = 0)
	{
		$result = smart_mysql_query("SELECT * FROM exchangerix_countries WHERE country_id='".(int)$country_id."' LIMIT 1");

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			
			if ($show_only_icon == 1)
				$country_name = "<img src='".SITE_URL."images/flags/".strtolower($row['code']).".png' alt='".$row['name']."' title='".$row['name']."' align='absmiddle'/>";
			else
				$country_name = "<img src='".SITE_URL."images/flags/".strtolower($row['code']).".png' alt='".$row['name']."' title='".$row['name']."' align='absmiddle' /> ".$row['name'];
		
			return $country_name;
		}
	}
}


/**
 * Returns user's exchanges total
 * @param	$user_id		User ID
 * @return	integer			user's clicks total
*/

if (!function_exists('GetUserExchangesTotal')) {
	function GetUserExchangesTotal($user_id, $direction_id = 0)
	{
		//if ($direction_id > 0) $sql = " AND exdirections_id='".(int)$direction_id."'";
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE user_id='".(int)$user_id."'".$sql);
		$row = mysqli_fetch_array($result);
		return number_format($row['total']);
	}
}

if (!function_exists('GetVerificationProgress')) {
	function GetVerificationProgress($userid)
	{
		$result = smart_mysql_query("SELECT verification_progress FROM exchangerix_users WHERE user_id='".(int)$userid."' LIMIT 1");
		$row = mysqli_fetch_array($result);
		return $row['verification_progress'];
	}
}	


/**
 * Returns user's reviews total
 * @param	$user_id	User ID
 * @return	integer		user's reviews total
*/

if (!function_exists('GetUserReviewsTotal')) {
	function GetUserReviewsTotal($user_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_reviews WHERE user_id='".(int)$user_id."' AND status='active'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}


/**
 * Returns stores total
 * @return	integer		stores total
*/

if (!function_exists('GetStoresTotal')) {
	function GetStoresTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_retailers WHERE (end_date='0000-00-00 00:00:00' OR end_date > NOW()) AND status='active'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}


/**
 * Returns coupons total
 * @return	integer		coupons total
*/

if (!function_exists('GetCouponsTotal')) {
	function GetCouponsTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_coupons WHERE status='active'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}


/**
 * Returns paid money total
 * @return	string		paid money total
*/
if (!function_exists('GetMoneyTotal')) {
	function GetMoneyTotal()
	{
		$result = smart_mysql_query("SELECT SUM(amount) AS total FROM exchangerix_transactions WHERE status='confirmed'");
		$row = mysqli_fetch_array($result);
		$total_money = DisplayMoney($row['total']);
		return $total_money;
	}
}

/**
 * Returns referral money total
 * @return	string		paid referral money total
*/
if (!function_exists('GetReferralEarningTotal')) {
	function GetReferralEarningTotal($userid)
	{
		$query_total = "SELECT SUM(exchange_amount) as total FROM exchangerix_exchanges WHERE ref_id='$userid' AND status='confirmed'";
		$row = mysqli_fetch_array(smart_mysql_query($query_total));
	
		return $row['total'];
	}
}

/**
 * Returns users total
 * @return	integer		users total
*/

if (!function_exists('GetUsersTotal')) {
	function GetUsersTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_users WHERE status='active'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}


/**
 * Returns formatted sctring
 * @param	$str		string
 * @return	string		formatted sctring
*/

if (!function_exists('well_formed')) {
	function well_formed($str) {
		$str = strip_tags($str);
		$str = preg_replace("/[^a-zA-Z0-9_ (\n|\r\n)]+/", "", $str);
		$str = str_replace("&nbsp;", "", $str);
		$str = str_replace("&", "&amp;", $str);
		return $str;
	}
}


/**
 * Returns page's link
 * @param	$page_title		Page Title
 * @return	string			Returns page's link
*/

if (!function_exists('GetPageLink')) {
	function GetPageLink($retailer_id, $retailer_title = "") {
		$retailer_id = (int)$retailer_id;
		$retailer_link = SITE_URL."view_page.php?id=".$retailer_id;
		return $retailer_link;
	}
}

	function country_ip($ip, $show_flag = 0)
	{
		$ip = substr($ip, 0, 15);
		require_once (PUBLIC_HTML_PATH."/inc/ip/geoip.inc");
		
		$gi	= geoip_open(PUBLIC_HTML_PATH."/inc/ip/GeoIP.dat",GEOIP_MEMORY_CACHE);
		$country = geoip_country_code_by_addr($gi, $ip); //us
		geoip_close($gi);

		$country = strtolower($country);

		//return "<img src='/images/flags/".$country.".png' align='absmiddle' />";
		return $country;	
	}
	
	function currencyConvertor($amount,$from_Currency,$to_Currency) {
	 $amount = urlencode($amount);
	  $from_Currency = urlencode($from_Currency);
	  $to_Currency = urlencode($to_Currency);
	  $get = "https://finance.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
	  $ch = curl_init();
	$url = $get;
		// Disable SSL verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// Will return the response, if false it print the response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$url);
		$result=curl_exec($ch);
		curl_close($ch);
	  $get = explode("<span class=bld>",$result);
	  $get = explode("</span>",$get[1]);  
	  $converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
	  return number_format($converted_amount, 2, '.', '');
}

function currencyConvertor2($from_Currency,$to_Currency) {
	 $amount = urlencode($amount);
	  $from_Currency = urlencode($from_Currency);
	  $to_Currency = urlencode($to_Currency);
	  $get = "https://wex.nz/api/3/ticker/".$from_Currency."_".$to_Currency;
	  $ch = curl_init();
										$url = $get;
										// Disable SSL verification
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
										// Will return the response, if false it print the response
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
										// Set the url
										curl_setopt($ch, CURLOPT_URL,$url);
										// Execute
										$result=curl_exec($ch);
										// Closing
										curl_close($ch);
	  $get = json_decode($result, true);
	  print_r($get);
	  
	  //check when empty
}


/**
 * Returns currency's name
 * @param	$id	Currency ID
 * @return	string			currency name
*/

if (!function_exists('GetCurrencyName')) {
	function GetCurrencyName($id)
	{
		$result = smart_mysql_query("SELECT currency_name FROM exchangerix_currencies WHERE currency_id='".(int)$id."' LIMIT 1");
		$row = mysqli_fetch_array($result);
		return $row['currency_name'];
	}
}

if (!function_exists('GetCurrencyFName')) {
	function GetCurrencyFName($id)
	{
		$result = smart_mysql_query("SELECT currency_name, currency_code FROM exchangerix_currencies WHERE currency_id='".(int)$id."' LIMIT 1");
		$row = mysqli_fetch_array($result);
		return $row['currency_name']." ".$row['currency_code'];
	}
}

if (!function_exists('GetCurrencyCode')) {
	function GetCurrencyCode($id)
	{
		$result = smart_mysql_query("SELECT currency_code FROM exchangerix_currencies WHERE currency_id='".(int)$id."' LIMIT 1");
		$row = mysqli_fetch_array($result);
		return $row['currency_code'];
	}
}

if (!function_exists('GetCurrencyImg')) {
	function GetCurrencyImg($id, $size = 33)
	{
		$result = smart_mysql_query("SELECT image FROM exchangerix_currencies WHERE currency_id='".(int)$id."' LIMIT 1");
		if (mysqli_num_rows($result) == 0) {
			return "";
		}
		$row = mysqli_fetch_array($result);
		return "<img src='".SITE_URL."images/currencies/".$row['image']."' width='".(int)$size."' height='".(int)$size."' style='border-radius: 50%'> ";
	}
}

if (!function_exists('GetCurCode')) {
	function GetCurCode($string)
	{
		if (@strstr($string, ' '))
		{
			$pieces = explode(' ', $string);
			$currency_code = array_pop($pieces);
			return $currency_code;
		}
	}
}


if (!function_exists('GetCurrencyTotalTransactions')) {
	function GetCurrencyTotalTransactions($id, $period = 1)
	{
		$today = date("Y-m-d");
		
		if ($period == "today")
			$result = smart_mysql_query("SELECT COUNT(*) as total FROM exchangerix_exchanges WHERE (from_currency_id='".(int)$id."' OR to_currency_id='".(int)$id."') AND DATE(created)='$today'"); //AND status='confirmed'
		else
			$result = smart_mysql_query("SELECT COUNT(*) as total FROM exchangerix_exchanges WHERE from_currency_id='".(int)$id."' OR to_currency_id='".(int)$id."'"); //AND status='confirmed'
			
		$row = mysqli_fetch_array($result);
		
		if ($period == "today" && $row['total'] > 0)
			return "+".$row['total'];
		else
		 	return $row['total'];
	}
}


if (!function_exists('GetCurrencySends')) {
	function GetCurrencySends($id)
	{
		global $conn;
		$id = mysqli_real_escape_string($conn, $id);
		
		$result = smart_mysql_query("SELECT COALESCE(sum(exchange_amount), 0) AS total FROM exchangerix_exchanges WHERE from_currency='".$id."' AND status='confirmed'");
		$row = mysqli_fetch_array($result);
		$total = floatval($row['total']);
		return $total;
	}
}


if (!function_exists('GetCurrencyReceives')) {
	function GetCurrencyReceives($id)
	{
		global $conn;
		$id = mysqli_real_escape_string($conn, $id);
		
		$result = smart_mysql_query("SELECT COALESCE(sum(receive_amount), 0) AS total FROM exchangerix_exchanges WHERE to_currency='".$id."' AND status='confirmed'");
		$row = mysqli_fetch_array($result);
		$total = floatval($row['total']);
		return $total;
	}
}

if (!function_exists('GetCurrencyReserve')) {
	function GetCurrencyReserve($id)
	{	
		$rr = mysqli_fetch_array(smart_mysql_query("SELECT SUM(receive_amount) AS total FROM exchangerix_exchanges WHERE to_currency='".$id."' AND status='waiting' AND created >= NOW() - INTERVAL ".RESERVE_MINUTES." MINUTE"));
		$result = smart_mysql_query("SELECT reserve FROM exchangerix_currencies WHERE currency_id='".(int)$id."' LIMIT 1");
		$row = mysqli_fetch_array($result);
		if ($row['reserve'] == "") return "unlimited";
		$reserve = $row['reserve'] - $rr['total'];
		//$reserve = floatval($reserve);
		$reserve = number_format($reserve, 2, '.', '');
		return $reserve;
	}
}


if (!function_exists('GetTestimonialsTotal')) {
	function GetTestimonialsTotal($status = 'active')
	{
		if ($status == "all")
			$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_reviews");
		else
			$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_reviews WHERE status='active'");
		
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];
	}
}



if (!function_exists('findTimeAgo')) {
function findTimeAgo($past, $now = "now") {
    // sets the default timezone if required 
    // list of supported timezone identifiers 
    // http://php.net/manual/en/timezones.php
    // date_default_timezone_set("Asia/Calcutta"); 
    $secondsPerMinute = 60;
    $secondsPerHour = 3600;
    $secondsPerDay = 86400;
    $secondsPerMonth = 2592000;
    $secondsPerYear = 31104000;
    // finds the past in datetime
    $past = strtotime($past);
    // finds the current datetime
    $now = strtotime($now);
    
    // creates the "time ago" string. This always starts with an "about..."
    $timeAgo = "";
    
    // finds the time difference
    $timeDifference = $now - $past;
    
    // less than 29secs
    if($timeDifference <= 29) {
      $timeAgo = "less than a minute";
    }
    // more than 29secs and less than 1min29secss
    else if($timeDifference > 29 && $timeDifference <= 89) {
      $timeAgo = "1 minute";
    }
    // between 1min30secs and 44mins29secs
    else if($timeDifference > 89 &&
      $timeDifference <= (($secondsPerMinute * 44) + 29)
    ) {
      $minutes = floor($timeDifference / $secondsPerMinute);
      $timeAgo = $minutes." minutes";
    }
    // between 44mins30secs and 1hour29mins29secs
    else if(
      $timeDifference > (($secondsPerMinute * 44) + 29)
      &&
      $timeDifference < (($secondsPerMinute * 89) + 29)
    ) {
      $timeAgo = "about 1 hour";
    }
    // between 1hour29mins30secs and 23hours59mins29secs
    else if(
      $timeDifference > (
        ($secondsPerMinute * 89) +
        29
      )
      &&
      $timeDifference <= (
        ($secondsPerHour * 23) +
        ($secondsPerMinute * 59) +
        29
      )
    ) {
      $hours = floor($timeDifference / $secondsPerHour);
      $timeAgo = $hours." hours";
    }
    // between 23hours59mins30secs and 47hours59mins29secs
    else if(
      $timeDifference > (
        ($secondsPerHour * 23) +
        ($secondsPerMinute * 59) +
        29
      )
      &&
      $timeDifference <= (
        ($secondsPerHour * 47) +
        ($secondsPerMinute * 59) +
        29
      )
    ) {
      $timeAgo = "1 day";
    }
    // between 47hours59mins30secs and 29days23hours59mins29secs
    else if(
      $timeDifference > (
        ($secondsPerHour * 47) +
        ($secondsPerMinute * 59) +
        29
      )
      &&
      $timeDifference <= (
        ($secondsPerDay * 29) +
        ($secondsPerHour * 23) +
        ($secondsPerMinute * 59) +
        29
      )
    ) {
      $days = floor($timeDifference / $secondsPerDay);
      $timeAgo = $days." days";
    }
    // between 29days23hours59mins30secs and 59days23hours59mins29secs
    else if(
      $timeDifference > (
        ($secondsPerDay * 29) +
        ($secondsPerHour * 23) +
        ($secondsPerMinute * 59) +
        29
      )
      &&
      $timeDifference <= (
        ($secondsPerDay * 59) +
        ($secondsPerHour * 23) +
        ($secondsPerMinute * 59) +
        29
      )
    ) {
      $timeAgo = "about 1 month";
    }
    // between 59days23hours59mins30secs and 1year (minus 1sec)
    else if(
      $timeDifference > (
        ($secondsPerDay * 59) + 
        ($secondsPerHour * 23) +
        ($secondsPerMinute * 59) +
        29
      )
      &&
      $timeDifference < $secondsPerYear
    ) {
      $months = round($timeDifference / $secondsPerMonth);
      // if months is 1, then set it to 2, because we are "past" 1 month
      if($months == 1) {
        $months = 2;
      }
      
      $timeAgo = $months." months";
    }
    // between 1year and 2years (minus 1sec)
    else if(
      $timeDifference >= $secondsPerYear
      &&
      $timeDifference < ($secondsPerYear * 2)
    ) {
      $timeAgo = "about 1 year";
    }
    // 2years or more
    else {
      $years = floor($timeDifference / $secondsPerYear);
      $timeAgo = "over ".$years." years";
    }
    
    return $timeAgo." ago";
  }
  }
  
 
	
function checkBitcoinAddress($address){
        $decoded = decodeBase58($address);
 
        $d1 = hash("sha256", substr($decoded,0,21), true);
        $d2 = hash("sha256", $d1, true);
 
        if(substr_compare($decoded, $d2, 21, 4)){
                return false; //throw new \Exception("bad digest");
        }
        return true;
}
function decodeBase58($input) {
        $alphabet = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
 
        $out = array_fill(0, 25, 0);
        for($i=0;$i<strlen($input);$i++){
                if(($p=strpos($alphabet, $input[$i]))===false){
                        throw new \Exception("invalid character found");
                }
                $c = $p;
                for ($j = 25; $j--; ) {
                        $c += (int)(58 * $out[$j]);
                        $out[$j] = (int)($c % 256);
                        $c /= 256;
                        $c = (int)$c;
                }
                if($c != 0){
                    return false; //throw new \Exception("address too long");
                }
        }
 
        $result = "";
        foreach($out as $val){
                $result .= chr($val);
        }
 
        return $result;
}

/*
function main () {
  $s = array(
                "1Q1pE5vPGEEMqRcVRMbtBK842Y6Pzo6nK9",
                "1AGNa15ZQXAZUgFiqJ2i7Z2DPU2J6hW62i",
                "1Q1pE5vPGEEMqRcVRMbtBK842Y6Pzo6nJ9",
                "1AGNa15ZQXAZUgFiqJ2i7Z2DPU2J6hW62I",
        );
  foreach($s as $btc){
    $message = "OK";
    try{
        validate($btc);
    }catch(\Exception $e){ $message = $e->getMessage(); }
    echo "$btc: $message\n";
  }
}
*/ 
//main();	
	

function random_filename($length, $directory = '', $extension = '')
{
    // default to this files directory if empty...
    $dir = !empty($directory) && is_dir($directory) ? $directory : dirname(__FILE__);

    do {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
    } while (file_exists($dir . '/' . $key . (!empty($extension) ? '.' . $extension : '')));

    return $key . (!empty($extension) ? '.' . $extension : '');
}

// Checks in the directory of where this file is located.
//echo random_filename(50);

// Checks in a user-supplied directory...
//echo random_filename(50, '/ServerRoot/mysite/myfiles');

// Checks in current directory of php file, with zip extension...
//echo random_filename(50, '', 'zip');

 

function resize_image($file, $w, $h, $crop=FALSE) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    
    /*
      $image_info = getimagesize($file);
      $image_type = $image_info[2];
      if($image_type == IMAGETYPE_JPEG ) {
         $src = imagecreatefromjpeg($file);
      } elseif($image_type == IMAGETYPE_GIF ) {
         $src = imagecreatefromgif($file);
      } elseif($image_type == IMAGETYPE_PNG ) {
         $src = imagecreatefrompng($file);
      }
    */  
  
	//Get file extension
    $exploding = explode(".",$file);
    $ext = end($exploding);
    
    switch($ext){
        case "png":
            $src = imagecreatefrompng($file);
        break;
        case "jpeg":
        case "jpg":
            $src = imagecreatefromjpeg($file);
        break;
        case "gif":
            $src = imagecreatefromgif($file);
        break;
        default:
            $src = imagecreatefromjpeg($file);
        break;
    }  
    
    $dst = imagecreatetruecolor($newwidth, $newheight);
  
	// preserve transparency
	if ($ext == "gif" or $ext == "png"){
	    imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
	    imagealphablending($dst, false);
	    imagesavealpha($dst, true);
	}    
    
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}



  

?>