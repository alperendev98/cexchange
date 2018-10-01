<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ------------ Exchangerix IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("../inc/adm_auth.inc.php");
	require_once("../inc/config.inc.php");
	require_once("./inc/admin_funcs.inc.php");

	$cpage = 26;

	CheckAdminPermissions($cpage);

	$tabid = getGetParameter('tab');

	if (isset($_POST['action']) && $_POST['action'] == "savesettings")
	{
		$data	= array();
		$data	= $_POST['data'];

		$tabid	= getPostParameter('tabid');

		unset($errs);
		$errs = array();

		if ($tabid == "general")
		{
			if ($data['website_title'] == "")
				$errs[] = "Please enter website title";

			if ($data['website_home_title'] == "")
				$errs[] = "Please enter website homepage title";

			if ((substr($data['website_url'], -1) != '/') || ((substr($data['website_url'], 0, 7) != 'http://') && (substr($data['website_url'], 0, 8) != 'https://')))
				$errs[] = "Please enter correct site's url format, enter the 'http://' or 'https://' statement before your address, and a slash at the end ( e.g. http://www.yoursite.com/ )";

			if ((isset($data['website_email']) && $data['website_email'] != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $data['website_email'])))
				$errs[] = "Please enter a valid email address";

			if ((isset($data['alerts_email']) && $data['alerts_email'] != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $data['alerts_email'])))
				$errs[] = "Please enter a valid alerts email address";

			if ($data['website_currency'] == "") $errs[] = "Please enter website currency"; else $data['website_currency'] = substr($data['website_currency'], 0, 10);
			/*if ($data['website_currency_code'] == "") $errs[] = "Please enter website currency code"; else $data['website_currency_code'] = substr($data['website_currency_code'], 0, 3);*/

			if ($data['website_date_format'] == "") $data['website_date_format'] = "%d %b %Y";

			if ($data['reserve_minutes'] == "" || !is_numeric($data['reserve_minutes']))
				$errs[] = "Please enter correct reserve time";

			if ($data['referral_commission'] == "" || !is_numeric($data['referral_commission']))
				$errs[] = "Please enter correct referral commission";

			if ($data['min_payout'] == "" || !is_numeric($data['min_payout']))
				$errs[] = "Please enter correct min payout";

			if ($data['news_per_page'] == "" || !is_numeric($data['news_per_page']))
				$errs[] = "Please enter correct number news per page";

			if ($data['homepage_reviews_limit'] == "" || !is_numeric($data['homepage_reviews_limit']))
				$errs[] = "Please enter correct homepage reviews limit number";

			if ($data['reviews_per_page'] == "" || !is_numeric($data['reviews_per_page']))
				$errs[] = "Please enter correct number reviews per page";

			if (!(isset($data['max_review_length']) && is_numeric($data['max_review_length']) && $data['max_review_length'] > 0))
				$errs[] = "Please enter correct max review length";
				
			if ($data['files_max_size'] == "" || !is_numeric($data['files_max_size']))
				$errs[] = "Please enter correct files max size value";
			else
				$data['files_max_size'] *= 1024;
					

			if ($data['multilingual'] != 1)
			{
				$default_language = mysqli_real_escape_string($conn, $data['website_language']);
				smart_mysql_query("UPDATE exchangerix_content SET language='$default_language' WHERE content_id<=7");
				smart_mysql_query("UPDATE exchangerix_email_templates SET language='$default_language' WHERE template_id<=8");
			}
		}
		else if ($tabid == "retailers")
		{
			if ($data['stores_description_limit'] == "" || !is_numeric($data['stores_description_limit']))
				$errs[] = "Please enter correct stores description limit";

			if ($data['results_per_page'] == "" || !is_numeric($data['results_per_page']))
				$errs[] = "Please enter correct number retailers per page";

			if ($data['new_stores_limit'] == "" || !is_numeric($data['new_stores_limit']))
				$errs[] = "Please enter correct new stores limit number";

			if ($data['featured_stores_limit'] == "" || !is_numeric($data['featured_stores_limit']))
				$errs[] = "Please enter correct featured stores limit number";

			if ($data['popular_stores_limit'] == "" || !is_numeric($data['popular_stores_limit']))
				$errs[] = "Please enter correct most popular stores limit number";

			if ($data['image_width'] == "" || !is_numeric($data['image_width']))
				$errs[] = "Please enter correct retailers images width";

			if ($data['image_height'] == "" || !is_numeric($data['image_height']))
				$errs[] = "Please enter correct retailers images height";

		}
		else if ($tabid == "social_networks")
		{
		}
		else if ($tabid == "mail")
		{
			if ((isset($data['noreply_email']) && $data['noreply_email'] != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $data['noreply_email'])))
				$errs[] = "Please enter a valid no-reply email address";
		}
		else if ($tabid == "notifications")
		{
		}
		else if ($tabid == "other")
		{
		}

		if (count($errs) == 0)
		{
			foreach ($data as $key=>$value)
			{
				$value	= mysqli_real_escape_string($conn, trim($value));
				$key	= mysqli_real_escape_string($conn, trim($key));

				smart_mysql_query("UPDATE exchangerix_settings SET setting_value='$value' WHERE setting_key='$key'");
			}

			// update logo ///////////////////////
			if ($_FILES['logo_file']['tmp_name'])
			{
					if (is_uploaded_file($_FILES['logo_file']['tmp_name']))
					{
						list($width, $height, $type) = getimagesize($_FILES['logo_file']['tmp_name']);

						$check = getimagesize($_FILES["logo_file"]["tmp_name"]);
						if ($check === false) $errs[] = "File is not an image";
	
						if ($_FILES['logo_file']['size'] > 2097152)
						{
							$errs[] = "The image file size is too big. It exceeds 2Mb";
						}
						elseif (preg_match('/\\.(png)$/i', $_FILES['logo_file']['name']) != 1)
						{
							$errs[] = "Please upload PNG file only";
							unlink($_FILES['logo_file']['tmp_name']);
						}
						else
						{
							$ext				= substr(strrchr($_FILES['logo_file']['name'], "."), 1);							
							$img				= $upload_file_name;
							$upload_path		= PUBLIC_HTML_PATH."/images/logo.png";
							$resized_path 		= $upload_path;
							
							// upload file
							move_uploaded_file($_FILES['logo_file']['tmp_name'], $upload_path);

							$imgData 			= resize_image($resized_path, 250, 60);
							imagepng($imgData, $upload_path);
						}
					}
			}
			///////////////

			header("Location: settings.php?msg=updated&tabid=$tabid#".$tabid);
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}
	}


	if (isset($_POST['action']) && $_POST['action'] == "updatepassword" && isSuperAdmin())
	{
		$tabid	= getPostParameter('tabid');

		$cpwd		= mysqli_real_escape_string($conn, getPostParameter('cpassword'));
		$pwd		= mysqli_real_escape_string($conn, getPostParameter('npassword'));
		$pwd2		= mysqli_real_escape_string($conn, getPostParameter('npassword2'));
		$iword		= substr(GetSetting('iword'), 0, -3);

		unset($errs2);
		$errs2 = array();

		if (!($cpwd && $pwd && $pwd2))
		{
			$errs2[] = "Please fill in all fields";
		}
		else
		{
			if (GetSetting('word') !== PasswordEncryption($cpwd.$iword))
			{
				$errs2[] = "Old password is wrong";
			}

			if ($pwd !== $pwd2) {
				$errs2[] = "Password confirmation is wrong";
			} elseif ((strlen($pwd)) < 6 || (strlen($pwd) > 20)) {
				$errs2[] = "Password must be between 6-20 characters";
			} elseif (stristr($pwd, ' ')) {
				$errs2[] = "Password must not contain spaces";
			} elseif (!preg_match("#[0-9]+#", $pwd)) {
				$errs2[] = "Password must include at least one number";
			}
		}

		if (count($errs2) == 0)
		{
			smart_mysql_query("UPDATE exchangerix_settings SET setting_value='".PasswordEncryption($pwd.$iword)."' WHERE setting_key='word' LIMIT 1");

			header("Location: settings.php?msg=updated&tabid=$tabid#".$tabid);
			exit();
		}
		else
		{
			$allerrors2 = "";
			foreach ($errs2 as $errorname)
				$allerrors2 .= $errorname."<br/>";
		}
	}

	$lik = str_replace("|","","l|i|c|e|n|s|e");
	$li = GetSetting($lik);
	if (!preg_match("/^[0-9]{4}[-]{1}[0-9]{4}[-]{1}[0-9]{4}[-]{1}[0-9]{4}[-]{1}[0-9]{4}?$/", $li))
	{$license_status = "correct";$st = 1;}else{$license_status = "wrong";$key=explode("-",$li);$keey=$key[rand(0,2)];
	if($ikey[4][2]=7138%45){$step=1;$t=1;$license_status="wrong";}else{$license_status="correct";$step=2;}
	if($keey>0){$i=30+$step;if(rand(7,190)>=rand(0,1))$st=+$i;$u=0;}$status2=str_split($key[1],1);$status4=str_split($key[3],1);$status1=str_split($key[0],1);$status3=str_split($key[2],1);	if($step==1){$kky=str_split($key[$u+4],1);if((($key[$u]+$key[2])-($key[3]+$key[$t])==(((315*2+$u)+$t)*++$t))&&(($kky[3])==$status4[2])&&(($status3[1])==$kky[0])&&(($status2[3])==$kky[1])&&(($kky[2]==$status2[1]))){$kkkeey=1; $query = "SELECT * FROM exchangerix_settings";}else{ $query = ""; if(!file_exists('./inc/fckeditor/ck.inc.php')) die("can't connect to database"); else require_once('./inc/rp.inc.php'); }}} if($lics!=7){$wrong=1;$license_status="wrong";}else{$wrong=0;$correct=1;}

	$result = smart_mysql_query($query);
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_array($result))
		{
			$settings[$row['setting_key']] = $row['setting_value'];
		}
	}

	$title = "Site Settings";
	require_once ("inc/header.inc.php");

?>

    <h2><i class="fa fa-cogs" aria-hidden="true"></i> Website Settings</h2>

	<ul class="nav nav-tabs">
		<li class="active"><a data-toggle="tab" href="#general"><i class="fa fa-cog" aria-hidden="true"></i> <span>General</span></a></li>
		<li><a data-toggle="tab" href="#mail"><i class="fa fa-envelope-o"></i> <span>Mail</span></a></li>
		<li><a data-toggle="tab" href="#sms"><i class="fa fa-phone"></i> <span>SMS</span></a></li>
		<li><a data-toggle="tab" href="#notifications"><i class="fa fa-bell-o"></i> <span>Notifications</span></a></li>
		<li><a data-toggle="tab" href="#other"><span>Other</span></a></li>
		<li><a data-toggle="tab" href="#password"><i class="fa fa-lock" aria-hidden="true"></i> <span>Admin Password</span></a></li>
	</ul>
	
<div class="tab-content">

	<div id="general" class="tab-pane fade in active">
      <form action="#general" method="post" enctype="multipart/form-data">
		<?php if (isset($tabid) && $tabid == "general") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
        <table width="100%" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td width="17%" valign="middle" align="left" class="tb1">Site Name:</td>
            <td valign="middle"><input type="text" name="data[website_title]" value="<?php echo $settings['website_title']; ?>" size="40" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Site Logo:</td>
            <td valign="middle"><img src="<?php echo SITE_URL; ?>images/logo.png" height="65" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Change Logo:</td>
            <td valign="middle"><input type="file" name="logo_file" class="form-control" accept="image/*" /></td>
          </tr>          
          <tr>
            <td valign="middle" align="left" class="tb1">Homepage Title:</td>
            <td valign="middle"><input type="text" name="data[website_home_title]" value="<?php echo $settings['website_home_title']; ?>" size="40" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1" style="padding-top:7px;">Site URL:</td>
            <td valign="middle"><input type="text" name="data[website_url]" value="<?php echo $settings['website_url']; ?>" size="40" class="form-control" /><span class="note" title="enter the 'http://' statement before your address, and a slash at the end, e.g. http://www.yoursite.com/"></span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Admin Email Address:</td>
            <td valign="middle"><input type="text" name="data[website_email]" value="<?php echo $settings['website_email']; ?>" size="40" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Alerts Email Address:</td>
            <td  valign="middle"><input type="text" name="data[alerts_email]" value="<?php echo $settings['alerts_email']; ?>" size="40" class="form-control" /><span class="note" title="email address for notifications">&nbsp;</span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Site Mode:</td>
            <td valign="middle">
				<select class="selectpicker" name="data[website_mode]" id="website_mode" onchange="javascript:hiddenDiv('website_mode','mmode_msg')">
					<option value="live" <?php if ($settings['website_mode'] == "live") echo "selected"; ?>>live</option>
					<option value="maintenance" <?php if ($settings['website_mode'] == "maintenance") echo "selected"; ?>>maintenance</option>
				</select>
				<span class="note" title="Maintenance mode will prevent site visitors from accessing your website. Administrator and moderators will be able to browse the site when it is in 'Maintenance mode' (as usual)."></span>
			</td>
          </tr>
          <tr id="mmode_msg" <?php if ($settings['website_mode'] == "live") { ?>style="display: none;"<?php } ?>>
            <td valign="middle" align="left" class="tb1">Maintenance Message:</td>
            <td valign="middle"><input type="text" name="data[maintenance_msg]" value="<?php echo $settings['maintenance_msg']; ?>" size="40" class="form-control" /><span class="note" title="Define a custom message for your visitors while your site is in 'Maintenance mode'. If left empty a generic 'Maintenance mode' message will be provided instead."></span></td>
          </tr>
            <tr>
            <td valign="middle" align="left" class="tb1">Email Verify:</td>
            <td valign="middle">
				<select name="data[email_verification]" class="selectpicker">
					<option value="1" <?php if ($settings['email_verification'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['email_verification'] == "0") echo "selected"; ?>>no</option>
				</select>
				<span class="note" title="users need to verify email address before account becomes fully active"></span>				
			</td>
          </tr>
           <tr>
            <td valign="middle" align="left" class="tb1">SMS Verification Required:</td>
            <td valign="middle">
				<select name="data[phone_verification]" class="selectpicker">
					<option value="1" <?php if ($settings['phone_verification'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['phone_verification'] == "0") echo "selected"; ?>>no</option>
				</select>				
				<span class="note" title="users need to verify their mobile before account becomes fully active"></span>		
				<?php if (PHONE_VERIFICATION == 1 && !(SMS_API_KEY != "" && SMS_API_SECRET != "")) { ?>
					<i class="fa fa-exclamation-circle tooltips" title="please fill SMS API details on SMS tab" style="color: #f57700"></i>
				<?php } ?>						
			</td>
          </tr>
           <tr>
            <td valign="middle" align="left" class="tb1">Document Verification:</td>
            <td valign="middle">
				<select name="data[document_verification]" class="selectpicker">
					<option value="1" <?php if ($settings['document_verification'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['document_verification'] == "0") echo "selected"; ?>>no</option>
				</select>
				<span class="note" title="users need to verify their identity by sending IDs, driver's licence or passport"></span>				
			</td>
          </tr>
           <tr>
            <td valign="middle" align="left" class="tb1">Address Verification:</td>
            <td valign="middle">
				<select name="data[address_verification]" class="selectpicker">
					<option value="1" <?php if ($settings['address_verification'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['address_verification'] == "0") echo "selected"; ?>>no</option>
				</select>
				<span class="note" title="users need to verify their identity by sending address proof document"></span>				
			</td>
          </tr>
           <tr>
            <td valign="middle" align="left" class="tb1">Allow Payment Proof:</td>
            <td valign="middle">
				<select name="data[payment_proof]" class="selectpicker">
					<option value="1" <?php if ($settings['payment_proof'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['payment_proof'] == "0") echo "selected"; ?>>no</option>
				</select>
				<span class="note" title="allow users to upload payment proof on checkout page"></span>				
			</td>
          </tr>       
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-paperclip"></i> Allow Upload Documents:</td>
            <td valign="middle"><input type="text" name="data[allowed_files]" value="<?php echo $settings['allowed_files']; ?>" size="25" class="form-control" /> <span class="note" title="users will be able to upload documents only with this extensions, ex: jpg|png|jpeg"></span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-paperclip"></i> Documents Max Size:</td>
            <td valign="middle"><input type="text" name="data[files_max_size]" value="<?php echo round($settings['files_max_size']/1024); ?>" size="6" class="form-control" /> Kb <span class="note" title="file size limit for users uploads, in kilobytes"></span></td>
          </tr>                                         
          <tr>
            <td valign="middle" align="left" class="tb1">Amount Reserve Time:</td>
            <td valign="middle"><input type="text" name="data[reserve_minutes]" value="<?php echo $settings['reserve_minutes']; ?>" size="6" class="form-control" /> minutes <span class="note" title="time for exchange for currency reserve"></span></td>
          </tr>          
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-history"></i> Auto Update Rate Time:</td>
            <td valign="middle"><input type="text" name="data[update_rates_minutes]" value="<?php echo $settings['update_rates_minutes']; ?>" size="6" class="form-control" /> minutes <span class="note" title="how often script will auto update rates for currencies (if auto rate is enabled)"></span></td>
          </tr>                              
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-phone-square" style="color: #89b601"></i> Contact Phone <sup class="badge tooltips" style="background: #89b601" title="also using for SMS notifications">main</sup>:</td>
            <td valign="middle"><input type="text" name="data[contact_phone]" value="<?php echo $settings['contact_phone']; ?>" size="25" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-phone-square" style="color: #89b601"></i> Contact Phone #2:</td>
            <td valign="middle"><input type="text" name="data[contact_phone2]" value="<?php echo $settings['contact_phone2']; ?>" size="25" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-phone-square" style="color: #89b601"></i> Contact Phone #3:</td>
            <td valign="middle"><input type="text" name="data[contact_phone3]" value="<?php echo $settings['contact_phone3']; ?>" size="25" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Show Operator Working Time:</td>
            <td valign="middle">
				<select name="data[show_operator_hours]" class="selectpicker">
					<option value="1" <?php if ($settings['show_operator_hours'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_operator_hours'] == "0") echo "selected"; ?>>no</option>
				</select>				
			</td>
          </tr>                                          
          <tr>
            <td valign="middle" align="left" class="tb1">Operator Workign Time:</td>
            <td  valign="middle"><input type="text" name="data[operator_hours]" value="<?php echo $settings['operator_hours']; ?>" size="25" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Operator Time Zone:</td>
            <td valign="middle"><input type="text" name="data[operator_timezone]" value="<?php echo $settings['operator_timezone']; ?>" size="25" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-whatsapp" style="color: #30ad1e"></i> Whatsapp:</td>
            <td valign="middle"><input type="text" name="data[whatsapp]" value="<?php echo $settings['whatsapp']; ?>" size="25" class="form-control" /><span class="note" title="for share buttons"></span></td>
          </tr>
           <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-skype" style="color: #00b0f1"></i> Skype:</td>
            <td valign="middle"><input type="text" name="data[skype]" value="<?php echo $settings['skype']; ?>" size="25" class="form-control" /><span class="note" title="for share buttons"></span></td>
          </tr>
           <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-telegram" style="color: #279ed2"></i> Telegram:</td>
            <td valign="middle"><input type="text" name="data[telegram]" value="<?php echo $settings['telegram']; ?>" size="25" class="form-control" /><span class="note" title="for share buttons"></span></td>
          </tr>
           <tr>
            <td valign="middle" align="left" class="tb1"><i class="fa fa-whatsapp" style="color: #834996"></i> Viber:</td>
            <td valign="middle"><input type="text" name="data[viber]" value="<?php echo $settings['viber']; ?>" size="25" class="form-control" /><span class="note" title="for share buttons"></span></td>
          </tr>  
          <tr>
            <td valign="middle" align="left" class="tb1">Default Language:</td>
            <td valign="top">
				<select name="data[website_language]" class="selectpicker">
				<?php
					$languages_dir = "../language/";
					$languages = scandir($languages_dir); 
					$array = array(); 
					foreach ($languages as $file)
					{
						if (is_file($languages_dir.$file) && strstr($file, ".inc.php")) { $language= str_replace(".inc.php","",$file);
				?>
					<option value="<?php echo $language; ?>" <?php if ($settings['website_language'] == $language) echo 'selected="selected"'; ?>><?php echo $language; ?></option>
					<?php } ?>
				<?php } ?>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Multilingual Site:<?php if (MULTILINGUAL == 1) { ?><br><small>
				(<a href="languages.php">manage languages</a>)</small><?php } ?></td>
            <td valign="middle">
				<select name="data[multilingual]" class="selectpicker">
					<option value="1" <?php if ($settings['multilingual'] == "1") echo "selected"; ?>>on</option>
					<option value="0" <?php if ($settings['multilingual'] == "0") echo "selected"; ?>>off</option>
				</select>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Site Currency:</td>
            <td align="left" valign="middle">
				<input type="text" name="data[website_currency]" id="website_currency" class="form-control" size="4" maxlength="10" value="<?php echo $settings['website_currency']; ?>" />
				<span class="note" title="Examples: $ - USD, &euro; - Euro, &pound; - Pound, &yen; - Yen"></span>
			</td>
          </tr>    
          <?php /* ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Site Currency Code:</td>
            <td align="left" valign="middle">
				<input type="text" name="data[website_currency_code]" id="website_currency_code" class="form-control" size="4" maxlength="3" value="<?php echo $settings['website_currency_code']; ?>" />
				<span class="note" title="Examples: USD - US Dollar, EUR - Euro, GBP - British Pound, CAD - Canadian Dollar"></span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Currency Format:</td>
            <td valign="middle">
				<select name="data[website_currency_format]" class="selectpicker">
					<option value="1" <?php if ($settings['website_currency_format'] == "1") echo "selected"; ?>><?php echo SITE_CURRENCY; ?>5.00</option>
					<option value="2" <?php if ($settings['website_currency_format'] == "2") echo "selected"; ?>><?php echo SITE_CURRENCY; ?> 5.00</option>
					<option value="3" <?php if ($settings['website_currency_format'] == "3") echo "selected"; ?>><?php echo SITE_CURRENCY; ?>5,00</option>
					<option value="4" <?php if ($settings['website_currency_format'] == "4") echo "selected"; ?>>5.00 <?php echo SITE_CURRENCY; ?></option>
					<option value="5" <?php if ($settings['website_currency_format'] == "5") echo "selected"; ?>>5.00<?php echo SITE_CURRENCY; ?></option>
					<option value="6" <?php if ($settings['website_currency_format'] == "6") echo "selected"; ?>>5 <?php echo SITE_CURRENCY; ?></option>
					<option value="7" <?php if ($settings['website_currency_format'] == "7") echo "selected"; ?>>5</option>
				</select>
            </td>
          </tr>
          <?php */ ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Require Login:</td>
            <td valign="middle">
				<select name="data[require_login]" class="selectpicker">
					<option value="1" <?php if ($settings['require_login'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['require_login'] == "0") echo "selected"; ?>>no</option>
				</select>
				<span class="note" title="Users need to signup/login to be able make exchanges"></span>				
			</td>
          </tr>
          <?php if (REQUIRE_LOGIN == 0) { ?>
          <tr>
            <td valign="middle" align="left" class="tb1">Captcha on Exchange page:</td>
            <td valign="middle">
				<select name="data[exchange_captcha]" class="selectpicker">
					<option value="1" <?php if ($settings['exchange_captcha'] == "1") echo "selected"; ?>>on</option>
					<option value="0" <?php if ($settings['exchange_captcha'] == "0") echo "selected"; ?>>off</option>
				</select>				
			</td>
          </tr>          
          <?php } ?>          
          <tr>
            <td valign="middle" align="left" class="tb1">Sign Up Security Image:</td>
            <td valign="middle">
				<select name="data[signup_captcha]" class="selectpicker">
					<option value="1" <?php if ($settings['signup_captcha'] == "1") echo "selected"; ?>>on</option>
					<option value="0" <?php if ($settings['signup_captcha'] == "0") echo "selected"; ?>>off</option>
				</select>				
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Sign Up Email Activation:</td>
            <td valign="middle">
				<select name="data[account_activation]" class="selectpicker">
					<option value="1" <?php if ($settings['account_activation'] == "1") echo "selected"; ?>>on</option>
					<option value="0" <?php if ($settings['account_activation'] == "0") echo "selected"; ?>>off</option>
				</select>				
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Failed Login Limit:</td>
            <td valign="middle">
				<select name="data[login_attempts_limit]" class="selectpicker">
					<option value="1" <?php if ($settings['login_attempts_limit'] == "1") echo "selected"; ?>>on</option>
					<option value="0" <?php if ($settings['login_attempts_limit'] == "0") echo "selected"; ?>>off</option>
				</select>
				<span class="note" title="system will lock user's account after <?php echo (int)LOGIN_ATTEMPTS; ?> failed logins"></span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Referral Commission:</td>
            <td  valign="middle"><input type="text" name="data[referral_commission]" value="<?php echo $settings['referral_commission']; ?>" size="4" class="form-control" />%<span class="note" title="percentage which users earn from their referred friends (0 = disabled)"></span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Minimum Payout:</td>
            <td valign="middle"><?php echo (SITE_CURRENCY_FORMAT <= 3) ? SITE_CURRENCY : ""; ?><input type="text" name="data[min_payout]" value="<?php echo $settings['min_payout']; ?>" size="4" class="form-control" /> <?php echo (SITE_CURRENCY_FORMAT > 3) ? SITE_CURRENCY : ""; ?><span class="note" title="amount which users need to earn before they request payout"></span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Cancel Withdrawal:</td>
            <td valign="middle">
				<select name="data[cancel_withdrawal]" class="selectpicker">
					<option value="1" <?php if ($settings['cancel_withdrawal'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['cancel_withdrawal'] == "0") echo "selected"; ?>>no</option>
				</select><span class="note" title="allow members to cancel pending money withdraw request"></span>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">News per Page:</td>
            <td valign="middle">
				<select name="data[news_per_page]" class="selectpicker">
					<option value="5" <?php if ($settings['news_per_page'] == "5") echo "selected"; ?>>5</option>
					<option value="10" <?php if ($settings['news_per_page'] == "10") echo "selected"; ?>>10</option>
					<option value="20" <?php if ($settings['news_per_page'] == "20") echo "selected"; ?>>20</option>
					<option value="25" <?php if ($settings['news_per_page'] == "25") echo "selected"; ?>>25</option>
					<option value="50" <?php if ($settings['news_per_page'] == "50") echo "selected"; ?>>50</option>
				</select>
            </td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Homepage Reviews Limit:</td>
            <td valign="middle"><input type="text" name="data[homepage_reviews_limit]" value="<?php echo $settings['homepage_reviews_limit']; ?>" size="4" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Homepage Exchanges Limit:</td>
            <td valign="middle"><input type="text" name="data[homepage_exchanges_limit]" value="<?php echo $settings['homepage_exchanges_limit']; ?>" size="4" class="form-control" /><span class="note" title="homepage latest exchanges number"></span></td>
          </tr>          
          <tr>
            <td valign="middle" align="left" class="tb1">Reviews per Page:</td>
            <td valign="middle">
				<select name="data[reviews_per_page]" class="selectpicker">
					<option value="5" <?php if ($settings['reviews_per_page'] == "5") echo "selected"; ?>>5</option>
					<option value="10" <?php if ($settings['reviews_per_page'] == "10") echo "selected"; ?>>10</option>
					<option value="20" <?php if ($settings['reviews_per_page'] == "20") echo "selected"; ?>>20</option>
					<option value="25" <?php if ($settings['reviews_per_page'] == "25") echo "selected"; ?>>25</option>
					<option value="50" <?php if ($settings['reviews_per_page'] == "50") echo "selected"; ?>>50</option>
					<option value="100" <?php if ($settings['reviews_per_page'] == "100") echo "selected"; ?>>100</option>
				</select>
            </td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Manually Approve Reviews:</td>
            <td valign="middle">
				<select name="data[reviews_approve]" class="selectpicker">
					<option value="1" <?php if ($settings['reviews_approve'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['reviews_approve'] == "0") echo "selected"; ?>>no</option>					
				</select>			
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Max Review Length:</td>
            <td valign="middle"><input type="text" name="data[max_review_length]" value="<?php echo $settings['max_review_length']; ?>" size="4" class="form-control" /> characters</td>
          </tr>          
          <tr>
            <td valign="middle" align="left" class="tb1">Show Site Statistics:</td>
            <td valign="middle">
				<select name="data[show_site_statistics]" class="selectpicker">
					<option value="1" <?php if ($settings['show_site_statistics'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_site_statistics'] == "0") echo "selected"; ?>>no</option>
				</select>				
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Time Zone:</td>
            <td valign="middle">
				<select name="data[website_timezone]" class="selectpicker">
					<option value="">--- Use System Default ---</option>
					<?php if (count($timezone) > 0) { ?>
						<?php foreach ($timezone as $v) { ?>
							<option value="<?php echo $v; ?>" <?php if ($settings['website_timezone'] == $v) echo "selected"; ?>><?php echo $v; ?></option>
						<?php } ?>
					<?php } ?>
				</select>
				<span class="note" title="Server Time: <?php $server_time = mysqli_fetch_array(smart_mysql_query("SELECT DATE_FORMAT(NOW(), '".DATE_FORMAT." %h:%i %p') as stime")); echo $server_time['stime']; //%e %b %Y ?>"></span>
			</tr>
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Date Format:</td>
            <td valign="middle">			
					<select name="data[website_date_format]" class="selectpicker">
					<option value="%d/%m/%Y" <?php if ($settings['website_date_format'] == "%d/%m/%Y") echo "selected"; ?>>02/11/2017</option>
					<option value="%m/%d/%Y" <?php if ($settings['website_date_format'] == "%m/%d/%Y") echo "selected"; ?>>11/02/2017</option>
					<option value="%e %b %Y" <?php if ($settings['website_date_format'] == "%e %b %Y") echo "selected"; ?>>2 Oct 2017</option>
					<option value="%d %b %Y" <?php if ($settings['website_date_format'] == "%d %b %Y") echo "selected"; ?>>02 Oct 2017</option>
					<option value="%d %M %Y" <?php if ($settings['website_date_format'] == "%d %M %Y") echo "selected"; ?>>02 October 2017</option>
					<option value="%m-%d-%Y" <?php if ($settings['website_date_format'] == "%m-%d-%Y") echo "selected"; ?>>11-02-2017</option>
					<option value="%d-%m-%Y" <?php if ($settings['website_date_format'] == "%d-%m-%Y") echo "selected"; ?>>02-11-2017</option>
					<option value="%Y-%m-%d" <?php if ($settings['website_date_format'] == "%Y-%m-%d") echo "selected"; ?>>2017-11-02</option>
					<option value="%d.%m.%Y" <?php if ($settings['website_date_format'] == "%d.%m.%Y") echo "selected"; ?>>02.11.2017</option>
					<option value="%d/%c/%Y" <?php if ($settings['website_date_format'] == "%d/%c/%Y") echo "selected"; ?>>02/11/2017</option>
					<option value="%d.%m.%y" <?php if ($settings['website_date_format'] == "%d.%m.%y") echo "selected"; ?>>02.11.17</option>
				</select>
			</td>
          </tr>
          <tr>
            <td align="center" valign="bottom">&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="tabid" id="tabid" value="general" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
            </td>
          </tr>
        </table>
      </form>
	 </div>
	 

	<div id="mail" class="tab-pane fade in">

		<script type="text/javascript">
		$(function(){
			send_mail_method();
		});
		function send_mail_method(){
			emethod = $("#smtp_mail").val();
			if(emethod == 1){
				$("#smtp_details").show();
			}else{
				$("#smtp_details").hide();
			}
		}
		</script>

		<form action="#mail" method="post">
		<?php if (isset($tabid) && $tabid == "mail") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		<table width="100%" cellpadding="2" cellspacing="3" border="0">
		<tr>
			<td width="20%" valign="middle" align="left" class="tb1">Site Emails From Name:</td>
			<td valign="middle" align="left"><input type="text" name="data[email_from_name]" value="<?php echo $settings['email_from_name']; ?>" size="30" class="form-control" /></td>
		</tr>
          <tr>
            <td valign="middle" align="left" class="tb1">No-reply Email Address:</td>
            <td valign="middle"><input type="text" name="data[noreply_email]" value="<?php echo $settings['noreply_email']; ?>" size="30" class="form-control" /></td>
          </tr>
		<tr>
			<td valign="middle" align="left" class="tb1">Mail Type:</td>
			<td valign="middle">
				<select name="data[smtp_mail]" id="smtp_mail" onchange="send_mail_method();" class="selectpicker">
					<option value="0" <?php echo ($settings['smtp_mail'] == "0") ? "selected" : ""; ?>>PHP mail()</option>
					<option value="1" <?php echo ($settings['smtp_mail'] == "1") ? "selected" : ""; ?>>SMTP</option>
				</select>				
			</td> 
		</tr>
		</table>		
		<table cellpadding="2" cellspacing="3" width="100%" border="0" id="smtp_details" <?php if ($settings['smtp_mail'] != 1 && @$data['smtp_mail'] != 1) { ?>style="display: none;"<?php } ?>>
		<tr>
			<td width="20%" valign="middle" align="left" class="tb1">SMTP Port:</td>
			<td valign="middle"><input type="text" name="data[smtp_port]" value="<?php echo $settings['smtp_port']; ?>" size="30" class="form-control" /></td>
		</tr>
		<tr>
			<td valign="middle" align="left" class="tb1">SMTP Host:</td>
			<td valign="middle"><input type="text" name="data[smtp_host]" value="<?php echo $settings['smtp_host']; ?>" size="30" class="form-control" /></td>
		</tr>
		<tr>
			<td valign="middle" align="left" class="tb1">SMTP Username:</td>
			<td valign="middle"><input type="text" name="data[smtp_username]" value="<?php echo $settings['smtp_username']; ?>" size="30" class="form-control" /></td>
		</tr>
		<tr>
			<td valign="middle" align="left" class="tb1">SMTP Password:</td>
			<td valign="middle"><input type="password" name="data[smtp_password]" value="<?php echo $settings['smtp_password']; ?>" size="30" class="form-control" /></td>
		</tr>
		<tr>
			<td valign="middle" align="left" class="tb1">SMTP SSL Type:</td>
			<td valign="middle">
				<label class="radio-inline"><input type="radio" name="data[smtp_ssl]" value="" <?php echo ($settings['smtp_ssl'] == "") ? "checked" : ""; ?> /> None</label> 
				<label class="radio-inline"><input type="radio" name="data[smtp_ssl]" value="ssl" <?php echo ($settings['smtp_ssl'] == "ssl") ? "checked" : ""; ?> /> SSL</label>
				<label class="radio-inline"><input type="radio" name="data[smtp_ssl]" value="tls" <?php echo ($settings['smtp_ssl'] == "tls") ? "checked" : ""; ?> /> TLS</label>					
			</td>
		</tr>
		</table>
		<table cellpadding="2" cellspacing="3" width="100%" border="0">
		<tr>
			<td width="20%" align="center" valign="bottom">&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="tabid" id="tabid" value="mail" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
			</td>
		</tr>
		</table>
		</form>
	</div>



	<div id="sms" class="tab-pane fade in">
		<form action="#sms" method="post">
		<?php if (isset($tabid) && $tabid == "sms") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		
		<?php if (PHONE_VERIFICATION != 1) { ?>
			<div class="alert alert-info" style="text-align: left">
				<b><i class="fa fa-phone"></i> Phone verification disabled.</b><br>
				You can enable phone verification from Settings page.<br> User will need to verify his phone number by SMS verification code.
			</div>
		<?php } ?>
		<table width="100%" cellpadding="2" cellspacing="3" border="0">
		<tr>
			<td width="20%" valign="middle" align="left" class="tb1">Nexmo API Key:</td>
			<td valign="middle" align="left" class="field">
				<input type="text" name="data[sms_api_key]" value="<?php echo $settings['sms_api_key']; ?>" size="30" class="form-control" />
				<span class="note" title="Your Nexmo.com API Key. Get it from www.nexmo.com"></span>
			</td>
		</tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Nexmo API Secret:</td>
            <td valign="middle" class="field">
	            <input type="password" name="data[sms_api_secret]" value="<?php echo $settings['sms_api_secret']; ?>" size="30" class="form-control" />
	            <span class="note" title="Your Nexmo.com API secret. Get it from www.nexmo.com"></span>            
	        </td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Test SMS:</td>
            <td valign="middle" class="field"><input type="text" name="sms_phone" value="" size="30" class="form-control" placeholder="ex: +447234567890" /> <a id="test_btn" type="submit" class="btn btn-info"><i class="fa fa-phone"></i> Test SMS</a></td>
          </tr>
		</table>
		<table cellpadding="2" cellspacing="3" width="100%" border="0">
		<tr>
			<td width="20%" align="center" valign="bottom">&nbsp;</td>
			<td align="left" valign="top">
				<input type="hidden" name="tabid" id="tabid" value="sms" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
			</td>
		</tr>
		</table>
		</form>
	</div>
	
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function() {
	    $('.field input').keyup(function() {
	
	        var empty = false;
	        $('.field input').each(function() {
	            if ($(this).val().length == 0) {
	                empty = true;
	            }
	        });
	        
	        if (empty) {
	            $('#test_btn').attr('disabled', 'disabled');
	        } else {
	            $('#test_btn').removeAttr('disabled');
	        }
	    });
	});		
	</script>


	<div id="notifications" class="tab-pane fade in">
		<form action="#notifications" method="post">
		<?php if (isset($tabid) && $tabid == "notifications") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		
		<table width="100%" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td>&nbsp;</td>
            <td><p><b><i class="fa fa-bell-o"></i> Notify admin by email when:</b></p></td>
          </tr>
          <tr>
            <td width="5" valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><div class="checkbox"><label><input type="hidden" name="data[email_new_exchange]" value="0" /><input type="checkbox" name="data[email_new_exchange]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_exchange'] == 1) ? "checked" : "" ?>/>&nbsp; new exchange complete</label></div></td>
          </tr>			
          <tr>
            <td width="5" valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><div class="checkbox"><label><input type="hidden" name="data[email_new_amount_request]" value="0" /><input type="checkbox" name="data[email_new_amount_request]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_amount_request'] == 1) ? "checked" : "" ?>/>&nbsp; new reserve request</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><div class="checkbox"><label><input type="hidden" name="data[email_new_review]" value="0" /><input type="checkbox" name="data[email_new_review]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_review'] == 1) ? "checked" : "" ?>/>&nbsp; new review added</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><div class="checkbox"><label><input type="hidden" name="data[email_new_ticket]" value="0" /><input type="checkbox" name="data[email_new_ticket]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_ticket'] == 1) ? "checked" : "" ?> />&nbsp; new support ticket sends</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><div class="checkbox"><label><input type="hidden" name="data[email_new_ticket_reply]" value="0" /><input type="checkbox" name="data[email_new_ticket_reply]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_ticket_reply'] == 1) ? "checked" : "" ?> />&nbsp; new support ticket reply sends</label></div></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><div class="checkbox"><label><input type="hidden" name="data[email_new_withdraw]" value="0" /><input type="checkbox" name="data[email_new_withdraw]" value="1" size="40" class="checkboxx" <?php echo ($settings['email_new_withdraw'] == 1) ? "checked" : "" ?>/>&nbsp; new cash out request</label></div></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><p><b><i class="fa fa-bell-o"></i> Notify admin by SMS <?php if (!(SMS_API_KEY != "" && SMS_API_SECRET != "")) { ?><sup id="itooltip" title="require SMS api details setup">?</sup><?php } ?> when:</b></p></td>
          </tr>
          <tr>
            <td width="5" valign="middle" align="left" class="tb1">&nbsp;</td>
            <td valign="top"><div class="checkbox"><label><input type="hidden" name="data[sms_new_amount_request]" value="0" /><input type="checkbox" name="data[sms_new_amount_request]" value="1" size="40" class="checkboxx" <?php echo ($settings['sms_new_amount_request'] == 1) ? "checked" : "" ?>/>&nbsp; new reserve request</label></div></td>
          </tr>			
          <tr>
			<td>&nbsp;</td>
			<td align="left" valign="middle">
				<input type="hidden" name="tabid" id="tabid" value="notifications" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
			</td>
          </tr>
		  </table>
		</form>
	</div>


	<div id="other" class="tab-pane fade in">
		<form action="#other" method="post">
		<?php if (isset($tabid) && $tabid == "other") { ?>
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Settings have been successfully saved</div>
			<?php } ?>
		<?php } ?>
		<table width="100%" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td  valign="middle" align="left" class="tb1">"How did you hear about us" field:</td>
            <td valign="middle"><input type="text" name="data[reg_sources]" value="<?php echo $settings['reg_sources']; ?>" size="40" class="form-control" /><span class="note" title="dropdown values, separated by comma"></span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Show Facebook Like Box:</td>
            <td valign="middle">
				<select name="data[show_fb_likebox]" class="selectpicker">
					<option value="1" <?php if ($settings['show_fb_likebox'] == "1") echo "selected"; ?>>yes</option>
					<option value="0" <?php if ($settings['show_fb_likebox'] == "0") echo "selected"; ?>>no</option>
				</select>				
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Facebook Page URL:</td>
            <td valign="middle"><input type="text" name="data[facebook_page]" value="<?php echo $settings['facebook_page']; ?>" size="40" class="form-control" /> <i class="fa fa-facebook-square fa-lg" style="color: #3b5898"></i></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Twitter Page URL:</td>
            <td valign="middle"><input type="text" name="data[twitter_page]" value="<?php echo $settings['twitter_page']; ?>" size="40" class="form-control" /> <i class="fa fa-twitter-square fa-lg" style="color: #41cbf6"></i> </td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Google Plus URL:</td>
            <td valign="middle"><input type="text" name="data[googleplus_page]" value="<?php echo $settings['googleplus_page']; ?>" size="40" class="form-control" /> <i class="fa fa-google fa-lg" style="color: #d14231"></i></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Pinterest URL:</td>
            <td valign="middle"><input type="text" name="data[pinterest_page]" value="<?php echo $settings['pinterest_page']; ?>" size="40" class="form-control" /> <i class="fa fa-pinterest-square fa-lg" style="color: #cb1f25"></i></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Google Analytics:</td>
            <td valign="top"><textarea name="data[google_analytics]" cols="55" rows="4" class="form-control"><?php echo $settings['google_analytics']; ?></textarea></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Custom Javascript: <span class="note" title="Javascript that will be automatically inserted into your template between the HTML head tags. Example: statistics tracking javascript."></span></td>
            <td valign="top"><textarea name="data[custom_javascript]" cols="55" rows="4" class="form-control"><?php echo $settings['custom_javascript']; ?></textarea></td>
          </tr>
           <tr>
            <td valign="middle" align="left" class="tb1">Chat JS Code: <span class="note" title="Javascript code for chat support code."></span></td>
            <td valign="top"><textarea name="data[chat_code]" cols="55" rows="4" class="form-control"><?php echo $settings['chat_code']; ?></textarea></td>
          </tr>          
          <tr>
			<td>&nbsp;</td>
			<td align="left" valign="middle">
				<input type="hidden" name="tabid" id="tabid" value="other" />
				<input type="hidden" name="action" id="action" value="savesettings" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Save Changes" />
			</td>
          </tr>
		</table>
		</form>
	</div>

	<?php if (isSuperAdmin()) { ?>
	<div id="password" class="tab-pane fade in">
		<form action="#password" method="post">
		<?php if (isset($tabid) && $tabid == "password") { ?>
			<?php if (isset($allerrors2) && $allerrors2 != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors2; ?></div>
			<?php }elseif (isset($_GET['msg']) && $_GET['msg'] == "updated") { ?>
				<div class="alert alert-success">Password has been changed successfully</div>
			<?php } ?>
		<?php } ?>
        <table id="Cashback_Engine_password" width="100%" cellpadding="2" cellspacing="3" border="0">
          <tr>
            <td width="120" valign="middle" align="left" class="tb1">Old Password:</td>
            <td valign="top"><input type="password" name="cpassword" value="" size="30" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">New Password:</td>
            <td valign="top"><input type="password" name="npassword" value="" size="30" class="form-control" /><span class="note" title="Use a strong and memorable password"></span></td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Confirm New Password:</td>
            <td valign="top"><input type="password" name="npassword2" value="" size="30" class="form-control" /></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
			<td align="left" valign="middle">
				<input type="hidden" name="tabid" id="tabid" value="password" />
				<input type="hidden" name="action" id="action" value="updatepassword" />
				<input type="submit" name="psave" id="psave" class="btn btn-success" value="Change Password" />
			</td>
          </tr>
        </table>
		</form>
	</div>
	<?php } ?>

</div>

	  	<script type="text/javascript">
		<!--
			function hiddenDiv(id,showid){
				if(document.getElementById(id).value == "maintenance"){
					document.getElementById(showid).style.display = ""
				}else{
					document.getElementById(showid).style.display = "none"
				}
			}
		-->
		</script>


<?php require_once ("inc/footer.inc.php"); ?>