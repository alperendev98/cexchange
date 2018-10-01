<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ------------ Exchangerix IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("../inc/auth_operator.inc.php");
	require_once("../inc/config.inc.php");
	require_once("./inc/admin_funcs.inc.php");

	$cpage = 19;

	CheckAdminPermissions($cpage);

	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
			unset($errors);
			$errors = array();
	 
			//$gateway_id		= (int)getPostParameter('gateway_id');
			//$category			= array();
			//$category			= $_POST['category_id'];
			//$country			= array();
			//$country			= $_POST['country_id'];
			
			$cname				= mysqli_real_escape_string($conn, getPostParameter('cname'));
			$currency			= mysqli_real_escape_string($conn, getPostParameter('currency'));
			$other_currency_code = mysqli_real_escape_string($conn, getPostParameter('other_currency_code'));
			$gateway_id			= mysqli_real_escape_string($conn, getPostParameter('gateway_id'));	
			//$account_id		= mysqli_real_escape_string($conn, getPostParameter('account_id'));	
			
			$img				= mysqli_real_escape_string($conn, trim($_POST['image_url']));
			$img_save			= (int)getPostParameter('image_save');
			//$url				= mysqli_real_escape_string($conn, trim($_POST['url']));
			$reserve			= mysqli_real_escape_string($conn, getPostParameter('reserve'));
			$min_reserve		= mysqli_real_escape_string($conn, getPostParameter('min_reserve'));
			$fee				= mysqli_real_escape_string($conn, getPostParameter('fee'));
			$site_code			= mysqli_real_escape_string($conn, getPostParameter('site_code'));
			$xml_code			= mysqli_real_escape_string($conn, getPostParameter('xml_code'));

			//$description		= mysqli_real_escape_string($conn, $_POST['description']);
			$instructions		= mysqli_real_escape_string($conn, nl2br(getPostParameter('instructions')));
			$website			= mysqli_real_escape_string($conn, getPostParameter('website'));
			if ($website != "" && !strstr($website, 'http://') && !strstr($website, 'https://')) $website = "http://".$website;

			$allow_send			= (int)getPostParameter('allow_send');
			$allow_receive		= (int)getPostParameter('allow_receive');
			$allow_affiliate	= (int)getPostParameter('allow_affiliate');
			$default_send		= (int)getPostParameter('default_send');
			$default_receive	= (int)getPostParameter('default_receive');
			$is_crypto			= (int)getPostParameter('is_crypto');
			$is_new_currency	= 0;
			$hide_code			= (int)getPostParameter('hide_code');
			$sort_order 		= (int)getPostParameter('sort_order');
			$status				= mysqli_real_escape_string($conn, getPostParameter('status'));

			if (!($cname && $currency))
			{
				$errs[] = "Please ensure that all fields marked with an asterisk are complete";
			}
			else
			{
				if (isset($currency) && $currency == "other")
				{
					if (!$other_currency_code) { $errs[] = "Please enter currency code"; }else{ $is_new_currency = 1; $currency = $other_currency_code; }
				}

				if (isset($reserve) && $reserve != "" && !is_numeric($reserve)) // > 0 //dev
					$errs[] = "Please enter correct reserve value";
				
				if (isset($min_reserve) && $min_reserve != "" && !is_numeric($min_reserve)) // > 0 //dev
					$errs[] = "Please enter correct minimum reserve value";

				if (isset($fee) && $fee != "" && !is_numeric($fee))
					$errs[] = "Please enter correct fee value";
					
				if (isset($min_reserve)	&& $min_reserve > 0 && $min_reserve >= $reserve)
					$errs[] = "Min reserve value cant be less than reserve amount";
				
				$check_query = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_name='$currency_name' AND currency_code='$currency_code' AND status='active'");
				if (mysqli_num_rows($check_query) > 0)
				{
					$errs[] = "Sorry, currency with same name and code is exists";
				}			
				
				if ($default_send == 1 && $default_receive == 1)
					$errs[] = "Sorry, one currency can't be default for send and receive payments";				
				
				if ($default_send == 1)
				{
					$check_query2 = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE default_send='1' AND status='active'");
					if (mysqli_num_rows($check_query2) > 0)
					{
						$errs[] = "Sorry, only one currency can be default for sending";
					}					
				}
				
				if ($default_receive == 1)
				{
					$check_query3 = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE default_receive='1' AND status='active'");
					if (mysqli_num_rows($check_query3) > 0)
					{
						$errs[] = "Sorry, only one currency can be default for receiving";
					}					
				}
				
				if (isset($site_code) && $site_code != "")
				{
					$check_query4 = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE site_code='$site_code'");
					if (mysqli_num_rows($check_query4) > 0)
					{
						$errs[] = "Sorry, currency with same 'Site code' is exists";
					}					
				}
				
				if (isset($xml_code) && $xml_code != "")
				{
					$check_query5 = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE xml_code='$xml_code'");
					if (mysqli_num_rows($check_query5) > 0)
					{
						$errs[] = "Sorry, currency with same 'Xml code' is exists";
					}					
				}
			}
			

			if (count($errs) == 0)
			{
				if ($_FILES['logo_file']['tmp_name'])
				{
					if (is_uploaded_file($_FILES['logo_file']['tmp_name']))
					{
						list($width, $height, $type) = getimagesize($_FILES['logo_file']['tmp_name']);
	
						if ($_FILES['logo_file']['size'][$key] > PROOF_MAX_SIZE)
						{
							$errs[] = "The $img_id image file size is too big. It exceeds 2Mb";
						}
						elseif (preg_match('/\\.(gif|jpg|png|jpeg)$/i', $_FILES['logo_file']['name']) != 1)
						{
							$errs[] = "Please upload PNG, JPG, GIF file only";
							//$errs[] = "Please upload ".strtoupper(str_replace("|", ", .", PROOF_ALLOWED_FILES))." file only"; //PROOF_ALLOWED_FILES = dd|ddd
							unlink($_FILES['logo_file']['tmp_name']);
						}
						else
						{
							$ext				= substr(strrchr($_FILES['logo_file']['name'], "."), 1);
							$upload_file_name	= "logo_".mt_rand(1,100).time().".".$ext; //md5(substr($ip, 0, -5).mt_rand(1,10000).time()).".".$ext;
							
							$img				= $upload_file_name;
							$upload_path		= PUBLIC_HTML_PATH.'/images/currencies/'.$upload_file_name;
							$resized_path 		= $upload_path; //PUBLIC_HTML_PATH.'/images/currencies/'.$upload_file_name_resized;
							
							// upload file
							move_uploaded_file($_FILES['logo_file']['tmp_name'], $upload_path);

							$imgData 			= resize_image($resized_path, 48, 48);
							imagepng($imgData, $upload_path);
						}
					}
				}
				else
				{
					$img = "no_image.png";
				}
			}
			else
			{
				$errormsg = "";
				foreach ($errs as $errorname)
					$errormsg .= $errorname."<br/>";
			}			
			

			if (count($errs) == 0)
			{
					$insert_sql = "INSERT INTO exchangerix_currencies SET currency_name='$cname', currency_code='$currency', gateway_id='$gateway_id', image='$img', instructions='$instructions', website='$website', is_crypto='$is_crypto', reserve='$reserve', fee='$fee', min_reserve='$min_reserve', site_code='$site_code', xml_code='$xml_code', allow_send='$allow_send', allow_receive='$allow_receive', allow_affiliate='$allow_affiliate', default_send='$default_send', default_receive='$default_receive', sort_order='$sort_order', is_new_currency='$is_new_currency', hide_code='$hide_code', status='$status', added=NOW()";
					$result = smart_mysql_query($insert_sql);
					$new_insert_id = mysqli_insert_id($conn);

					/*
					if (count($category) > 0)
					{
						foreach ($category as $cat_id)
						{
							$cats_insert_sql = "INSERT INTO exchangerix_retailer_to_category SET retailer_id='$new_retailer_id', category_id='$cat_id'";
							smart_mysql_query($cats_insert_sql);
						}
					}

					if ($_POST['all_countries_check'] != 1 && count($country) > 0)
					{
						foreach ($country as $country_id)
						{
							$countries_insert_sql = "INSERT INTO exchangerix_retailer_to_country SET retailer_id='$new_retailer_id', country_id='$country_id'";
							smart_mysql_query($countries_insert_sql);
						}
					}
					*/

					header("Location: currencies.php?msg=added");
					exit();
			}
			else
			{
				$errormsg = "";
				foreach ($errs as $errorname)
					$errormsg .= $errorname."<br/>";
			}
	}

	$title = "Add Currency";
	require_once ("inc/header.inc.php");

?>

    <h2><i class="fa fa-money" aria-hidden="true"></i> <a href="currencies.php">Currencies</a> <i class="fa fa-angle-right" aria-hidden="true"></i> Add Currency</h2>

	<?php if (isset($errormsg) && $errormsg != "") { ?>
		<div class="alert alert-danger"><?php echo $errormsg; ?></div>
	<?php } elseif (isset($_GET['msg']) && ($_GET['msg']) == "added") { ?>
		<div class="alert alert-success">Currency has been successfully added</div>
	<?php } ?>

      <form action="" method="post" enctype="multipart/form-data" name="form1">
        <table style="background:#F9F9F9" width="100%" cellpadding="2" cellspacing="3" border="0" align="center">
          <tr>
            <td width="17%" valign="middle" align="left" class="tb1"><span class="req">* </span>Title:</td>
            <td valign="top"><input type="text" name="cname" id="cname" value="<?php echo getPostParameter('cname'); ?>" size="34" class="form-control" required="required" /></td>
          </tr>
          <tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Currency Code:</td>
				<td valign="middle">
						<select class="selectpicker" name="currency" id="currency" required="required">
							<option value="">--- select ---</option>
							<optgroup label="Popular Currencies">
								<option value="USD" <?php if (getPostParameter('currency') == "USD") echo "selected";?>>USD - US Dollar</option>
								<option value="EUR" <?php if (getPostParameter('currency') == "EUR") echo "selected";?>>EUR - Euro</option>
								<option value="GBP" <?php if (getPostParameter('currency') == "GBP") echo "selected";?>>GBP - British Pound</option>			
								<option value="INR" <?php if (getPostParameter('currency') == "INR") echo "selected";?>>INR - Indian Rupee</option>
								<option value="AED" <?php if (getPostParameter('currency') == "AED") echo "selected";?>>AED - Emirati Dirham</option>
								<option value="AUD" <?php if (getPostParameter('currency') == "AUD") echo "selected";?>>AUD - Australian Dollar</option>
								<option value="CAD" <?php if (getPostParameter('currency') == "CAD") echo "selected";?>>CAD - Canadian Dollar</option>
								<option value="SGD" <?php if (getPostParameter('currency') == "SGD") echo "selected";?>>SGD - Singapore Dollar</option>
								<option value="CHF" <?php if (getPostParameter('currency') == "CHF") echo "selected";?>>CHF - Swiss Franc</option>
								<option value="MYR" <?php if (getPostParameter('currency') == "MYR") echo "selected";?>>MYR - Malaysian Ringgit</option>
								<option value="JPY" <?php if (getPostParameter('currency') == "JPY") echo "selected";?>>JPY - Japanese Yen</option>
								<option value="CNY" <?php if (getPostParameter('currency') == "CNY") echo "selected";?>>CNY - Chinese Yuan Renminbi</option>
								<option value="RUB" <?php if (getPostParameter('currency') == "RUB") echo "selected";?>>RUB - Russian Ruble</option>
							</optgroup>											
							<optgroup label="Popular Cryptocurrencies">
								<option value="BTC" <?php if (getPostParameter('currency') == "BTC") echo "selected";?>>BTC - Bitcoin</option>
								<option value="BCH" <?php if (getPostParameter('currency') == "BCH") echo "selected";?>>BCH - Bitcoin Cash</option>
								<option value="ETH" <?php if (getPostParameter('currency') == "ETH") echo "selected";?>>ETH - Ethereum</option>
								<option value="LTC" <?php if (getPostParameter('currency') == "LTC") echo "selected";?>>LTC - Litecoin</option>		
								<option value="XRP" <?php if (getPostParameter('currency') == "XRP") echo "selected";?>>XRP - Ripple</option>
								<option value="BCH" <?php if (getPostParameter('currency') == "BCH") echo "selected";?>>BCH - Bitcoin Cash</option>
								<option value="NEM" <?php if (getPostParameter('currency') == "NEM") echo "selected";?>>NEM - XEM</option>
								<option value="NEO" <?php if (getPostParameter('currency') == "NEO") echo "selected";?>>NEO - NEO</option>
								<option value="DOGE" <?php if (getPostParameter('currency') == "DOGE") echo "selected";?>>DOGE - Dogecoin</option>
								<option value="DASH" <?php if (getPostParameter('currency') == "DASH") echo "selected";?>>DASH - Dash</option>
								<option value="XMR" <?php if (getPostParameter('currency') == "XMR") echo "selected";?>>XMR - Monero</option>
								<option value="ZEC" <?php if (getPostParameter('currency') == "ZEC") echo "selected";?>>ZEC - Zcash</option>
							</optgroup>
							<optgroup label="Other Currencies">
								<option value="AED" <?php if (getPostParameter('currency') == "AED") echo "selected";?>>AED - United Arab Emirates Dirham</option>
								<option value="AFN" <?php if (getPostParameter('currency') == "AFN") echo "selected";?>>AFN - Afghanistan Afghani</option>
								<option value="ALL" <?php if (getPostParameter('currency') == "ALL") echo "selected";?>>ALL - Albania Lek</option>
								<option value="AMD" <?php if (getPostParameter('currency') == "AMD") echo "selected";?>>AMD - Armenia Dram</option>
								<option value="ANG" <?php if (getPostParameter('currency') == "ANG") echo "selected";?>>ANG - Netherlands Antilles Guilder</option>
								<option value="AOA" <?php if (getPostParameter('currency') == "AOA") echo "selected";?>>AOA - Angola Kwanza</option>
								<option value="ARS" <?php if (getPostParameter('currency') == "ARS") echo "selected";?>>ARS - Argentina Peso</option>
								<option value="AUD" <?php if (getPostParameter('currency') == "AUD") echo "selected";?>>AUD - Australia Dollar</option>
								<option value="AWG" <?php if (getPostParameter('currency') == "AWG") echo "selected";?>>AWG - Aruba Guilder</option>
								<option value="AZN" <?php if (getPostParameter('currency') == "AZN") echo "selected";?>>AZN - Azerbaijan New Manat</option>
								<option value="BAM" <?php if (getPostParameter('currency') == "BAM") echo "selected";?>>BAM - Bosnia and Herzegovina Marka</option>
								<option value="BBD" <?php if (getPostParameter('currency') == "BBD") echo "selected";?>>BBD - Barbados Dollar</option>
								<option value="BDT" <?php if (getPostParameter('currency') == "BDT") echo "selected";?>>BDT - Bangladesh Taka</option>
								<option value="BGN" <?php if (getPostParameter('currency') == "BGN") echo "selected";?>>BGN - Bulgaria Lev</option>
								<option value="BHD" <?php if (getPostParameter('currency') == "BHD") echo "selected";?>>BHD - Bahrain Dinar</option>
								<option value="BIF" <?php if (getPostParameter('currency') == "BIF") echo "selected";?>>BIF - Burundi Franc</option>
								<option value="BMD" <?php if (getPostParameter('currency') == "BMD") echo "selected";?>>BMD - Bermuda Dollar</option>
								<option value="BND" <?php if (getPostParameter('currency') == "BND") echo "selected";?>>BND - Brunei Darussalam Dollar</option>
								<option value="BOB" <?php if (getPostParameter('currency') == "BOB") echo "selected";?>>BOB - Bolivia Boliviano</option>
								<option value="BRL" <?php if (getPostParameter('currency') == "BRL") echo "selected";?>>BRL - Brazil Real</option>
								<option value="BSD" <?php if (getPostParameter('currency') == "BSD") echo "selected";?>>BSD - Bahamas Dollar</option>
								<option value="BTN" <?php if (getPostParameter('currency') == "BTN") echo "selected";?>>BTN - Bhutan Ngultrum</option>	
								<option value="BWP" <?php if (getPostParameter('currency') == "BWP") echo "selected";?>>BWP - Botswana Pula</option>
								<option value="BYR" <?php if (getPostParameter('currency') == "BYR") echo "selected";?>>BYR - Belarus Ruble</option>
								<option value="BZD" <?php if (getPostParameter('currency') == "BZD") echo "selected";?>>BZD - Belize Dollar</option>
								<option value="CAD" <?php if (getPostParameter('currency') == "CAD") echo "selected";?>>CAD - Canada Dollar</option>
								<option value="CDF" <?php if (getPostParameter('currency') == "CDF") echo "selected";?>>CDF - Congo/Kinshasa Franc</option>
								<option value="CHF" <?php if (getPostParameter('currency') == "CHF") echo "selected";?>>CHF - Switzerland Franc</option>
								<option value="CLP" <?php if (getPostParameter('currency') == "CLP") echo "selected";?>>CLP - Chile Peso</option>
								<option value="CNY" <?php if (getPostParameter('currency') == "CNY") echo "selected";?>>CNY - China Yuan Renminbi</option>
								<option value="COP" <?php if (getPostParameter('currency') == "COP") echo "selected";?>>COP - Colombia Peso</option>
								<option value="CRC" <?php if (getPostParameter('currency') == "CRC") echo "selected";?>>CRC - Costa Rica Colon</option>
								<option value="CUC" <?php if (getPostParameter('currency') == "CUC") echo "selected";?>>CUC - Cuba Convertible Peso</option>
								<option value="CUP" <?php if (getPostParameter('currency') == "CUP") echo "selected";?>>CUP - Cuba Peso</option>
								<option value="CVE" <?php if (getPostParameter('currency') == "CVE") echo "selected";?>>CVE - Cape Verde Escudo</option>
								<option value="CZK" <?php if (getPostParameter('currency') == "CZK") echo "selected";?>>CZK - Czech Republic Koruna</option>
								<option value="DJF" <?php if (getPostParameter('currency') == "DJF") echo "selected";?>>DJF - Djibouti Franc</option>
								<option value="DKK" <?php if (getPostParameter('currency') == "DKK") echo "selected";?>>DKK - Danish Krone</option>
								<option value="DOP" <?php if (getPostParameter('currency') == "DOP") echo "selected";?>>DOP - Dominican Republic Peso</option>
								<option value="DZD" <?php if (getPostParameter('currency') == "DZD") echo "selected";?>>DZD - Algeria Dinar</option>
								<option value="EGP" <?php if (getPostParameter('currency') == "EGP") echo "selected";?>>EGP - Egypt Pound</option>
								<option value="ERN" <?php if (getPostParameter('currency') == "ERN") echo "selected";?>>ERN - Eritrea Nakfa</option>
								<option value="ETB" <?php if (getPostParameter('currency') == "ETB") echo "selected";?>>ETB - Ethiopia Birr</option>
								<option value="EUR" <?php if (getPostParameter('currency') == "EUR") echo "selected";?>>EUR - Euro</option>
								<option value="FJD" <?php if (getPostParameter('currency') == "FJD") echo "selected";?>>FJD - Fiji Dollar</option>
								<option value="FKP" <?php if (getPostParameter('currency') == "FKP") echo "selected";?>>FKP - Falkland Islands Pound</option>
								<option value="GBP" <?php if (getPostParameter('currency') == "GBP") echo "selected";?>>GBP - United Kingdom Pound</option>
								<option value="GEL" <?php if (getPostParameter('currency') == "GEL") echo "selected";?>>GEL - Georgia Lari</option>
								<option value="GGP" <?php if (getPostParameter('currency') == "GGP") echo "selected";?>>GGP - Guernsey Pound</option>
								<option value="GHS" <?php if (getPostParameter('currency') == "GHS") echo "selected";?>>GHS - Ghana Cedi</option>
								<option value="GIP" <?php if (getPostParameter('currency') == "GIP") echo "selected";?>>GIP - Gibraltar Pound</option>
								<option value="GMD" <?php if (getPostParameter('currency') == "GMD") echo "selected";?>>GMD - Gambia Dalasi</option>
								<option value="GNF" <?php if (getPostParameter('currency') == "GNF") echo "selected";?>>GNF - Guinea Franc</option>
								<option value="GTQ" <?php if (getPostParameter('currency') == "GTQ") echo "selected";?>>GTQ - Guatemala Quetzal</option>
								<option value="GYD" <?php if (getPostParameter('currency') == "GYD") echo "selected";?>>GYD - Guyana Dollar</option>
								<option value="HKD" <?php if (getPostParameter('currency') == "HKD") echo "selected";?>>HKD - Hong Kong Dollar</option>
								<option value="HNL" <?php if (getPostParameter('currency') == "HNL") echo "selected";?>>HNL - Honduras Lempira</option>
								<option value="HPK" <?php if (getPostParameter('currency') == "HPK") echo "selected";?>>HRK - Croatia Kuna</option>
								<option value="HTG" <?php if (getPostParameter('currency') == "HTG") echo "selected";?>>HTG - Haiti Gourde</option>
								<option value="HUF" <?php if (getPostParameter('currency') == "HUF") echo "selected";?>>HUF - Hungary Forint</option>
								<option value="IDR" <?php if (getPostParameter('currency') == "IDR") echo "selected";?>>IDR - Indonesia Rupiah</option>
								<option value="ILS" <?php if (getPostParameter('currency') == "ILS") echo "selected";?>>ILS - Israel Shekel</option>
								<option value="IMP" <?php if (getPostParameter('currency') == "IMP") echo "selected";?>>IMP - Isle of Man Pound</option>
								<option value="INR" <?php if (getPostParameter('currency') == "INR") echo "selected";?>>INR - India Rupee</option>
								<option value="IQD" <?php if (getPostParameter('currency') == "IQD") echo "selected";?>>IQD - Iraq Dinar</option>
								<option value="IRR" <?php if (getPostParameter('currency') == "IRR") echo "selected";?>>IRR - Iran Rial</option>
								<option value="ISK" <?php if (getPostParameter('currency') == "ISK") echo "selected";?>>ISK - Iceland Krona</option>
								<option value="JEP" <?php if (getPostParameter('currency') == "JEP") echo "selected";?>>JEP - Jersey Pound</option>
								<option value="JMD" <?php if (getPostParameter('currency') == "JMD") echo "selected";?>>JMD - Jamaica Dollar</option>
								<option value="JOD" <?php if (getPostParameter('currency') == "JOD") echo "selected";?>>JOD - Jordan Dinar</option>		
								<option value="JPY" <?php if (getPostParameter('currency') == "JPY") echo "selected";?>>JPY - Japan Yen</option>
								<option value="KES" <?php if (getPostParameter('currency') == "KES") echo "selected";?>>KES - Kenya Shilling</option>
								<option value="KGS" <?php if (getPostParameter('currency') == "KGS") echo "selected";?>>KGS - Kyrgyzstan Som</option>
								<option value="KHR" <?php if (getPostParameter('currency') == "KHR") echo "selected";?>>KHR - Cambodia Riel</option>
								<option value="KMF" <?php if (getPostParameter('currency') == "KMF") echo "selected";?>>KMF - Comoros Franc</option>
								<option value="KPW" <?php if (getPostParameter('currency') == "KPW") echo "selected";?>>KPW - Korea (North) Won</option>
								<option value="KRW" <?php if (getPostParameter('currency') == "KRW") echo "selected";?>>KRW - Korea (South) Won</option>
								<option value="KWD" <?php if (getPostParameter('currency') == "KWD") echo "selected";?>>KWD - Kuwait Dinar</option>
								<option value="KYD" <?php if (getPostParameter('currency') == "KYD") echo "selected";?>>KYD - Cayman Islands Dollar</option>
								<option value="KZT" <?php if (getPostParameter('currency') == "KZT") echo "selected";?>>KZT - Kazakhstan Tenge</option>
								<option value="LAK" <?php if (getPostParameter('currency') == "LAK") echo "selected";?>>LAK - Laos Kip</option>
								<option value="LBP" <?php if (getPostParameter('currency') == "LBP") echo "selected";?>>LBP - Lebanon Pound</option>
								<option value="LKR" <?php if (getPostParameter('currency') == "LKR") echo "selected";?>>LKR - Sri Lanka Rupee</option>
								<option value="LRD" <?php if (getPostParameter('currency') == "LRD") echo "selected";?>>LRD - Liberia Dollar</option>
								<option value="LSL" <?php if (getPostParameter('currency') == "LSL") echo "selected";?>>LSL - Lesotho Loti</option>
								<option value="LYD" <?php if (getPostParameter('currency') == "LYD") echo "selected";?>>LYD - Libya Dinar</option>
								<option value="MAD" <?php if (getPostParameter('currency') == "MAD") echo "selected";?>>MAD - Morocco Dirham</option>	
								<option value="MDL" <?php if (getPostParameter('currency') == "MDL") echo "selected";?>>MDL - Moldova Leu</option>
								<option value="MGA" <?php if (getPostParameter('currency') == "MGA") echo "selected";?>>MGA - Madagascar Ariary</option>
								<option value="MKD" <?php if (getPostParameter('currency') == "MKD") echo "selected";?>>MKD - Macedonia Denar</option>
								<option value="MMK" <?php if (getPostParameter('currency') == "MMK") echo "selected";?>>MMK - Myanmar (Burma) Kyat</option>
								<option value="MNT" <?php if (getPostParameter('currency') == "MNT") echo "selected";?>>MNT - Mongolia Tughrik</option>
								<option value="MOP" <?php if (getPostParameter('currency') == "MPO") echo "selected";?>>MOP - Macau Pataca</option>
								<option value="MRO" <?php if (getPostParameter('currency') == "MRO") echo "selected";?>>MRO - Mauritania Ouguiya</option>
								<option value="MUR" <?php if (getPostParameter('currency') == "MUR") echo "selected";?>>MUR - Mauritius Rupee</option>
								<option value="MVR" <?php if (getPostParameter('currency') == "MVR") echo "selected";?>>MVR - Maldivian Rufiyaa</option>
								<option value="MWK" <?php if (getPostParameter('currency') == "MWK") echo "selected";?>>MWK - Malawi Kwacha</option>
								<option value="MXN" <?php if (getPostParameter('currency') == "MXN") echo "selected";?>>MXN - Mexico Peso</option>
								<option value="MYR" <?php if (getPostParameter('currency') == "MYR") echo "selected";?>>MYR - Malaysia Ringgit</option>
								<option value="MZN" <?php if (getPostParameter('currency') == "MZN") echo "selected";?>>MZN - Mozambique Metical</option>
								<option value="NAD" <?php if (getPostParameter('currency') == "NAD") echo "selected";?>>NAD - Namibia Dollar</option>
								<option value="NGN" <?php if (getPostParameter('currency') == "NGN") echo "selected";?>>NGN - Nigeria Naira</option>
								<option value="NTO" <?php if (getPostParameter('currency') == "NTO") echo "selected";?>>NIO - Nicaragua Cordoba</option>
								<option value="NOK" <?php if (getPostParameter('currency') == "NOK") echo "selected";?>>NOK - Norway Krone</option>
								<option value="NPR" <?php if (getPostParameter('currency') == "NPR") echo "selected";?>>NPR - Nepal Rupee</option>
								<option value="NZD" <?php if (getPostParameter('currency') == "NZD") echo "selected";?>>NZD - New Zealand Dollar</option>
								<option value="OMR" <?php if (getPostParameter('currency') == "OMR") echo "selected";?>>OMR - Oman Rial</option>
								<option value="PAB" <?php if (getPostParameter('currency') == "PAB") echo "selected";?>>PAB - Panama Balboa</option>
								<option value="PEN" <?php if (getPostParameter('currency') == "PEN") echo "selected";?>>PEN - Peru Nuevo Sol</option>
								<option value="PGK" <?php if (getPostParameter('currency') == "PGK") echo "selected";?>>PGK - Papua New Guinea Kina</option>
								<option value="PHP" <?php if (getPostParameter('currency') == "PHP") echo "selected";?>>PHP - Philippines Peso</option>
								<option value="PKR" <?php if (getPostParameter('currency') == "PKR") echo "selected";?>>PKR - Pakistan Rupee</option>
								<option value="PLN" <?php if (getPostParameter('currency') == "PLN") echo "selected";?>>PLN - Poland Zloty</option>
								<option value="PYG" <?php if (getPostParameter('currency') == "PYG") echo "selected";?>>PYG - Paraguay Guarani</option>
								<option value="QAR" <?php if (getPostParameter('currency') == "QAR") echo "selected";?>>QAR - Qatar Riyal</option>
								<option value="RON" <?php if (getPostParameter('currency') == "RON") echo "selected";?>>RON - Romania New Leu</option>
								<option value="RSD" <?php if (getPostParameter('currency') == "RSD") echo "selected";?>>RSD - Serbia Dinar</option>
								<option value="RUB" <?php if (getPostParameter('currency') == "RUB") echo "selected";?>>RUB - Russian Ruble</option>
								<option value="RWF" <?php if (getPostParameter('currency') == "RWF") echo "selected";?>>RWF - Rwanda Franc</option>
								<option value="SAR" <?php if (getPostParameter('currency') == "SAR") echo "selected";?>>SAR - Saudi Arabia Riyal</option>
								<option value="SBD" <?php if (getPostParameter('currency') == "SBD") echo "selected";?>>SBD - Solomon Islands Dollar</option>
								<option value="SCR" <?php if (getPostParameter('currency') == "SCR") echo "selected";?>>SCR - Seychelles Rupee</option>
								<option value="SDG" <?php if (getPostParameter('currency') == "SDG") echo "selected";?>>SDG - Sudan Pound</option>
								<option value="SEK" <?php if (getPostParameter('currency') == "SEK") echo "selected";?>>SEK - Sweden Krona</option>	
								<option value="SGD" <?php if (getPostParameter('currency') == "SGD") echo "selected";?>>SGD - Singapore Dollar</option>
								<option value="SHP" <?php if (getPostParameter('currency') == "SHP") echo "selected";?>>SHP - Saint Helena Pound</option>
								<option value="SLL" <?php if (getPostParameter('currency') == "SLL") echo "selected";?>>SLL - Sierra Leone Leone</option>
								<option value="SOS" <?php if (getPostParameter('currency') == "SOS") echo "selected";?>>SOS - Somalia Shilling</option>
								<option value="SRL" <?php if (getPostParameter('currency') == "SRL") echo "selected";?>>SPL - Seborga Luigino</option>
								<option value="SRD" <?php if (getPostParameter('currency') == "SRD") echo "selected";?>>SRD - Suriname Dollar</option>
								<option value="STD" <?php if (getPostParameter('currency') == "STD") echo "selected";?>>STD - Sao Tome and Principe Dobra</option>
								<option value="SVC" <?php if (getPostParameter('currency') == "SVC") echo "selected";?>>SVC - El Salvador Colon</option>
								<option value="SYP" <?php if (getPostParameter('currency') == "SYP") echo "selected";?>>SYP - Syria Pound</option>
								<option value="SZL" <?php if (getPostParameter('currency') == "SZL") echo "selected";?>>SZL - Swaziland Lilangeni</option>
								<option value="THB" <?php if (getPostParameter('currency') == "THB") echo "selected";?>>THB - Thailand Baht</option>
								<option value="TJS" <?php if (getPostParameter('currency') == "TJS") echo "selected";?>>TJS - Tajikistan Somoni</option>
								<option value="TMT" <?php if (getPostParameter('currency') == "TMT") echo "selected";?>>TMT - Turkmenistan Manat</option>
								<option value="TND" <?php if (getPostParameter('currency') == "TND") echo "selected";?>>TND - Tunisia Dinar</option>
								<option value="TOP" <?php if (getPostParameter('currency') == "TOP") echo "selected";?>>TOP - Tonga Pa'anga</option>
								<option value="TRY" <?php if (getPostParameter('currency') == "TRY") echo "selected";?>>TRY - Turkey Lira</option>
								<option value="TTD" <?php if (getPostParameter('currency') == "TTD") echo "selected";?>>TTD - Trinidad and Tobago Dollar</option>
								<option value="TVD" <?php if (getPostParameter('currency') == "TVD") echo "selected";?>>TVD - Tuvalu Dollar</option>
								<option value="TWD" <?php if (getPostParameter('currency') == "TWD") echo "selected";?>>TWD - Taiwan New Dollar</option>
								<option value="TZS" <?php if (getPostParameter('currency') == "TZS") echo "selected";?>>TZS - Tanzania Shilling</option>		
								<option value="UAH" <?php if (getPostParameter('currency') == "UAH") echo "selected";?>>UAH - Ukrainian Hryvnia</option>
								<option value="UGX" <?php if (getPostParameter('currency') == "UGX") echo "selected";?>>UGX - Uganda Shilling</option>
								<option value="USD" <?php if (getPostParameter('currency') == "USD") echo "selected";?>>USD - United States Dollar</option>
								<option value="UYU" <?php if (getPostParameter('currency') == "UYU") echo "selected";?>>UYU - Uruguay Peso</option>
								<option value="UZS" <?php if (getPostParameter('currency') == "UZS") echo "selected";?>>UZS - Uzbekistan Som</option>
								<option value="VEF" <?php if (getPostParameter('currency') == "VEF") echo "selected";?>>VEF - Venezuela Bolivar</option>
								<option value="VND" <?php if (getPostParameter('currency') == "VND") echo "selected";?>>VND - Viet Nam Dong</option>
								<option value="VUV" <?php if (getPostParameter('currency') == "VUV") echo "selected";?>>VUV - Vanuatu Vatu</option>
								<option value="WST" <?php if (getPostParameter('currency') == "WST") echo "selected";?>>WST - Samoa Tala</option>
								<option value="XAF" <?php if (getPostParameter('currency') == "XAF") echo "selected";?>>XAF - CFA Franc BEAC</option>
								<option value="XCD" <?php if (getPostParameter('currency') == "XCD") echo "selected";?>>XCD - East Caribbean Dollar</option>
								<option value="XDR" <?php if (getPostParameter('currency') == "XDR") echo "selected";?>>XDR - IMF Special Drawing Rights</option>
								<option value="XOF" <?php if (getPostParameter('currency') == "XOF") echo "selected";?>>XOF - CFA Franc</option>
								<option value="XPF" <?php if (getPostParameter('currency') == "XPF") echo "selected";?>>XPF - CFP Franc</option>
								<option value="YER" <?php if (getPostParameter('currency') == "YER") echo "selected";?>>YER - Yemen Rial</option>
								<option value="ZAR" <?php if (getPostParameter('currency') == "ZAR") echo "selected";?>>ZAR - South Africa Rand</option>
								<option value="ZMW" <?php if (getPostParameter('currency') == "ZMW") echo "selected";?>>ZMW - Zambia Kwacha</option>
								<option value="ZWD" <?php if (getPostParameter('currency') == "ZWD") echo "selected";?>>ZWD - Zimbabwe Dollar</option>
						</optgroup>
						<optgroup label="Other Currency">
								<option value="other" <?php if (getPostParameter('currency') == "other") echo "selected";?>>... other</option>
						</optgroup>
					</select>
				</td>
          </tr>
          <tr id="other_currency" <?php if (@$currency != "other") { ?>style="display: none;" <?php }else{ ?>style="display: ;"<?php } ?>>
            <td valign="middle" align="left" class="tb1"><span class="req">* </span>Other Currency Code:</td>
            <td valign="middle"><input type="text" name="other_currency_code" value="<?php echo getPostParameter('other_currency_code'); ?>" size="26" class="form-control" /></td>
          </tr>
          <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="hide_code" value="1" <?php if (getPostParameter('hide_code') == 1) echo "checked=\"checked\""; ?> /> hide currency code </label> <span class="note" title="do not show currency code, just currency name (eg. show Bitcoin instead Bitcoin BTC)"></span></div></td>
          </tr>          
		<tr>
            <td  valign="middle" align="left" class="tb1">Gateway:<br><small>(<a href="gateways.php">manage</a>)</small></td>
            <td valign="middle">
				<select class="selectpicker" id="gateway_id" name="gateway_id">
				<option value="">--- none ---</option>
					<?php
						$sql_affs = smart_mysql_query("SELECT * FROM exchangerix_gateways WHERE status='active' ORDER BY gateway_name ASC");
						if (mysqli_num_rows($sql_affs) > 0)
						{
							while ($row_affs = mysqli_fetch_array($sql_affs))
							{
								if ($row['gateway_id'] == $row_affs['gateway_id']) $selected = " selected=\"selected\""; else $selected = "";
								echo "<option value=\"".$row_affs['gateway_id']."\"".$selected.">".$row_affs['gateway_name']."</option>";
							}
						}
					?>
				</select>
				<span class="note" title="payment processing gateway for this currency"</span>
			</td>
          </tr>   
          <tr>
            <td valign="middle" align="left" class="tb1">Logo:</td>
            <td valign="middle"><input type="file" name="logo_file" class="form-control" accept="image/*" /></td>
          </tr>
          <!--
			<tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Your Account:</td>
				<td valign="middle">
					<input type="text" name="account_id" id="account_id" value="<?php echo getPostParameter('account_id'); ?>" size="34" class="form-control" /> <span class="note" title="Account to receive/send money, e.g. mypaypal@gmail.com"></span>
				</td>
			</tr>
			-->							
			<tr>
				<td valign="middle" align="left" class="tb1"><i class="fa fa-bars" aria-hidden="true"></i> Reserve:</td>
				<td valign="middle">
					<input type="text" name="reserve" id="reserve" value="<?php echo getPostParameter('reserve'); ?>" size="15" class="form-control" /> <span class="note" title="leave empty for unlimited reserve"></span>
				</td>
			</tr>
			<!--			
			<tr>
				<td valign="middle" align="left" class="tb1">Fast Exchange:</td>
				<td valign="middle">
					<select name="fast_exchange" class="form-control">
						<option value="0" <?php if ($fast_exchange == "0") echo "selected"; ?>>no</option>
						<option value="1" <?php if ($fast_exchange == "1") echo "selected"; ?>>yes</option>
					</select>
				</td>
			</tr>
			-->		
			<tr>
				<td valign="middle" align="left" class="tb1"><?php echo SITE_TITLE; ?> Fee:</td>
				<td valign="middle">
					<input type="text" name="fee" id="fee" value="<?php echo getPostParameter('fee'); ?>" size="5" class="form-control" /> % <span class="note" title="exchange fee (0 = disabled)"></span>
				</td>
			</tr>						
			<tr>
				<td valign="middle" align="left" class="tb1">Site Code:</td>
				<td valign="middle">
					<input type="text" name="site_code" id="site_code" value="<?php echo getPostParameter('site_code'); ?>" size="10" class="form-control" /> e.g. BTCUSD
				</td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">XML Code:</td>
				<td valign="middle">
					<input type="text" name="xml_code" id="xml_code" value="<?php echo getPostParameter('xml_code'); ?>" size="10" class="form-control" /> e.g. BTCUSD <span class="note" title="need for xml rates page"></span>
				</td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Min Reserve Alert:</td>
				<td valign="middle">
					<input type="text" name="min_reserve" id="min_reserve" value="<?php echo getPostParameter('min_reserve'); ?>" size="15" class="form-control" /> <span class="note" title="minimal reserve value for admin notification"></span><!-- //dev -->
				</td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Instructions for user:</td>
				<td valign="top"><textarea name="instructions" cols="112" rows="5" style="width:90%;" class="form-control"><?php echo getPostParameter('conditions'); ?></textarea></td>
            </tr>            
			<tr>
				<td valign="middle" align="left" class="tb1">Website:</td>
				<td valign="middle"><input type="text" name="website" id="website" value="<?php echo getPostParameter('website'); ?>" size="40" class="form-control" /><span class="note" title="e.g. bitcoin.org (you can add your affiliate link)"></span></td>
            </tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Cryptocurrency?:</td>
				<td valign="middle">
					<select name="is_crypto" class="form-control">
						<option value="0" <?php if ($is_crypto == "0") echo "selected"; ?>>no</option>
						<option value="1" <?php if ($is_crypto == "1") echo "selected"; ?>>yes</option>
					</select>
				</td>
			</tr>            
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="allow_send" value="1" <?php if (!$_POST['action'] || getPostParameter('allow_send') == 1) echo "checked=\"checked\""; ?> /> allow send payments</label></div></td>
            </tr>               
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="allow_receive" value="1" <?php if (!$_POST['action'] || getPostParameter('allow_receive') == 1) echo "checked=\"checked\""; ?> /> allow receive payments</label></div></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="allow_affiliate" value="1" <?php if (getPostParameter('allow_affiliate') == 1) echo "checked=\"checked\""; ?> /> allow affiliates withdrawals via this method</label></div></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="default_send" value="1" <?php if (getPostParameter('default_send') == 1) echo "checked=\"checked\""; ?> /> default send method</label></div></td>
            </tr>            
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="default_receive" value="1" <?php if (getPostParameter('default_receive') == 1) echo "checked=\"checked\""; ?> /> default receive method</label></div></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">Sort Order:</td>
				<td valign="middle"><input type="text" class="form-control" name="sort_order" value="<?php echo (isset($_POST['sort_order'])) ? getPostParameter('sort_order') : "0"; ?>" size="5" /></td>
            </tr>                              
            <tr>
				<td valign="middle" align="left" class="tb1">Status:</td>
				<td valign="middle">
					<select name="status" class="form-control">
						<option value="active" <?php if ($status == "active") echo "selected"; ?>>active</option>
						<option value="inactive" <?php if ($status == "inactive") echo "selected"; ?>>inactive</option>
					</select>
				</td>
            </tr>
            <tr>
				<td align="left" valign="bottom">&nbsp;</td>
				<td align="left" valign="bottom">
					<input type="hidden" name="action" id="action" value="add">
					<input type="submit" class="btn btn-success" name="add" id="add" value="Add Currency" />
					<input type="button" class="btn btn-default" name="cancel" value="Cancel" onclick="history.go(-1);return false;" />
				</td>
            </tr>
          </table>
      </form>

<?php require_once ("inc/footer.inc.php"); ?>