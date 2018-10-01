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

	if (isset($_POST["action"]) && $_POST["action"] == "edit")
	{
			unset($errors);
			$errors = array();

			$currency_id		= (int)getPostParameter('did');
			$gateway_id			= (int)getPostParameter('gateway_id');
			$currency			= mysqli_real_escape_string($conn, getPostParameter('currency'));
			$other_currency_code = mysqli_real_escape_string($conn, getPostParameter('other_currency_code'));
			//$account_id			= mysqli_real_escape_string($conn, getPostParameter('account_id'));	
						
			//$category			= array();
			//$category			= $_POST['category_id'];
			//$country			= array();
			//$country			= $_POST['country_id'];
			
			$cname				= mysqli_real_escape_string($conn, getPostParameter('cname'));
			
			//$img				= mysqli_real_escape_string($conn, trim($_POST['image_url']));
			//$img_save			= (int)getPostParameter('image_save');
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

			if (!($cname && $currency && $status))
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

				$check_query = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id!='".(int)$currency_id."' AND currency_name='$currency_name' AND currency_code='$currency_code' AND status='active'");
				if (mysqli_num_rows($check_query) > 0)
				{
					$errs[] = "Sorry, currency with same name and code is exists";
				}
				
				if ($default_send == 1 && $default_receive == 1)
					$errs[] = "Sorry, one currency can't be default for send and receive payments";
				
				if ($default_send == 1)
				{
					$check_query2 = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id!='".(int)$currency_id."' AND default_send='1' AND status='active'");
					if (mysqli_num_rows($check_query2) > 0)
					{
						$errs[] = "Sorry, only one currency can be default for sending";
					}					
				}
				
				if ($default_receive == 1)
				{
					$check_query3 = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id!='".(int)$currency_id."' AND default_receive='1' AND status='active'");
					if (mysqli_num_rows($check_query3) > 0)
					{
						$errs[] = "Sorry, only one currency can be default for receiving";
					}					
				}
				
				if (isset($site_code) && $site_code != "")
				{
					$check_query4 = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id!='".(int)$currency_id."' AND site_code='$site_code'");
					if (mysqli_num_rows($check_query4) > 0)
					{
						$errs[] = "Sorry, currency with same 'Site code' is exists";
					}					
				}
				
				if (isset($xml_code) && $xml_code != "")
				{
					$check_query5 = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id!='".(int)$currency_id."' AND xml_code='$xml_code'");
					if (mysqli_num_rows($check_query5) > 0)
					{
						$errs[] = "Sorry, currency with same 'Xml code' is exists";
					}					
				}								
			}


			if (count($errs) == 0)
			{	
				//if ($reserve == "") $reserve = "999999999999999999";
				
				// check reserve notification // and send email is needed

				smart_mysql_query("UPDATE exchangerix_currencies SET currency_name='$cname', currency_code='$currency', gateway_id='$gateway_id', instructions='$instructions', is_crypto='$is_crypto', website='$website', reserve='$reserve', fee='$fee', min_reserve='$min_reserve', site_code='$site_code', xml_code='$xml_code', allow_send='$allow_send', allow_receive='$allow_receive', allow_affiliate='$allow_affiliate', default_send='$default_send', default_receive='$default_receive', sort_order='$sort_order', is_new_currency='$is_new_currency', hide_code='$hide_code', status='$status' WHERE currency_id='$currency_id' LIMIT 1"); //image='$img'

				/*
				smart_mysql_query("DELETE FROM exchangerix_retailer_to_category WHERE retailer_id='$retailer_id'");
				if (count($category) > 0)
				{
					foreach ($category as $cat_id)
					{
						$cats_insert_sql = "INSERT INTO exchangerix_retailer_to_category SET retailer_id='$retailer_id', category_id='$cat_id'";
						smart_mysql_query($cats_insert_sql);
					}
				}

				smart_mysql_query("DELETE FROM exchangerix_retailer_to_country WHERE retailer_id='$retailer_id'");
				if ($_POST['all_countries_check'] != 1 && count($country) > 0)
				{
					foreach ($country as $country_id)
					{
						$countries_insert_sql = "INSERT INTO exchangerix_retailer_to_country SET retailer_id='$retailer_id', country_id='$country_id'";
						smart_mysql_query($countries_insert_sql);
					}
				}
				*/

				header("Location: currencies.php?msg=updated");
				exit();
			}
			else
			{
				$errormsg = "";
				foreach ($errs as $errorname)
					$errormsg .= "<i class='fa fa-times' aria-hidden='true'></i> ".$errorname."<br/>";
			}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id	= (int)$_GET['id'];

		$query = "SELECT * FROM exchangerix_currencies WHERE currency_id='$id' LIMIT 1";
		$rs	= smart_mysql_query($query);
		$total = mysqli_num_rows($rs);
	}


	$title = "Edit Currency";
	require_once ("inc/header.inc.php");

?>

    <h2><i class="fa fa-money" aria-hidden="true"></i> <a href="currencies.php">Currencies</a> <i class="fa fa-angle-right" aria-hidden="true"></i> Edit Currency</h2>

	<?php if ($total > 0) { $row = mysqli_fetch_array($rs); ?>

		<?php if (isset($errormsg) && $errormsg != "") { ?>
			<div class="alert alert-danger"><?php echo $errormsg; ?></div>
		<?php } ?>

      <div style="text-align: right; position: absolute; right: 10px; margin: 5px; padding: 5px;">
		<?php if ($row['image'] != "") { ?><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $row['image']; ?>" width="33" align="left" alt="" border="0" style="margin-top: 3px;" class="imgs" /><?php } ?>
      </div>

      <form action="" method="post" name="form1">
        <table style="background:#F9F9F9" width="100%" cellpadding="2" cellspacing="3"  border="0" align="center">
          <tr>
            <td width="17%" valign="middle" align="left" class="tb1"><span class="req">* </span>Title:</td>
            <td valign="top"><input type="text" name="cname" id="cname" value="<?php echo $row['currency_name']; ?>" size="34" class="form-control" required="required" <?php //if ($row['currency_id'] < 10) echo "disabled"; //dev ?> /></td>
			</tr>
		   <tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Currency Code:</td>
				<td valign="middle">

					<select class="selectpicker" name="currency" id="currency" style="width: 33%"; required="required">
						<option value="">--- select ---</option> <!-- //dev select make -->
							<optgroup label="Popular Currencies">
								<option value="USD" <?php if ($row['currency_code'] == "USD") echo "selected";?>>USD - US Dollar</option>
								<option value="EUR" <?php if ($row['currency_code'] == "EUR") echo "selected";?>>EUR - Euro</option>
								<option value="GBP" <?php if ($row['currency_code'] == "GBP") echo "selected";?>>GBP - British Pound</option>			
								<option value="INR" <?php if ($row['currency_code'] == "INR") echo "selected";?>>INR - Indian Rupee</option>
								<option value="AED" <?php if ($row['currency_code'] == "AED") echo "selected";?>>AED - Emirati Dirham</option>
								<option value="AUD" <?php if ($row['currency_code'] == "AUD") echo "selected";?>>AUD - Australian Dollar</option>
								<option value="CAD" <?php if ($row['currency_code'] == "CAD") echo "selected";?>>CAD - Canadian Dollar</option>
								<option value="SGD" <?php if ($row['currency_code'] == "SGD") echo "selected";?>>SGD - Singapore Dollar</option>
								<option value="CHF" <?php if ($row['currency_code'] == "CHF") echo "selected";?>>CHF - Swiss Franc</option>
								<option value="MYR" <?php if ($row['currency_code'] == "MYR") echo "selected";?>>MYR - Malaysian Ringgit</option>
								<option value="JPY" <?php if ($row['currency_code'] == "JPY") echo "selected";?>>JPY - Japanese Yen</option>
								<option value="CNY" <?php if ($row['currency_code'] == "CNY") echo "selected";?>>CNY - Chinese Yuan Renminbi</option>
								<option value="RUB" <?php if ($row['currency_code'] == "RUB") echo "selected";?>>RUB - Russian Ruble</option>
							</optgroup>											
							<optgroup label="Popular Cryptocurrencies">
								<option value="BTC" <?php if ($row['currency_code'] == "BTC") echo "selected";?>>BTC - Bitcoin</option>
								<option value="BCH" <?php if ($row['currency_code'] == "BCH") echo "selected";?>>BCH - Bitcoin Cash</option>
								<option value="ETH" <?php if ($row['currency_code'] == "ETH") echo "selected";?>>ETH - Ethereum</option>
								<option value="LTC" <?php if ($row['currency_code'] == "LTC") echo "selected";?>>LTC - Litecoin</option>		
								<option value="XRP" <?php if ($row['currency_code'] == "XRP") echo "selected";?>>XRP - Ripple</option>
								<option value="BCH" <?php if ($row['currency_code'] == "BCH") echo "selected";?>>BCH - Bitcoin Cash</option>
								<option value="NEM" <?php if ($row['currency_code'] == "NEM") echo "selected";?>>NEM - XEM</option>
								<option value="NEO" <?php if ($row['currency_code'] == "NEO") echo "selected";?>>NEO - NEO</option>
								<option value="DOGE" <?php if ($row['currency_code'] == "DOGE") echo "selected";?>>DOGE - Dogecoin</option>
								<option value="DASH" <?php if ($row['currency_code'] == "DASH") echo "selected";?>>DASH - Dash</option>
								<option value="XMR" <?php if ($row['currency_code'] == "XMR") echo "selected";?>>XMR - Monero</option>
								<option value="ZEC" <?php if ($row['currency_code'] == "ZEC") echo "selected";?>>ZEC - Zcash</option>
							</optgroup>
							<optgroup label="Other Currencies">
								<option value="AED" <?php if ($row['currency_code'] == "AED") echo "selected";?>>AED - United Arab Emirates Dirham</option>
								<option value="AFN" <?php if ($row['currency_code'] == "AFN") echo "selected";?>>AFN - Afghanistan Afghani</option>
								<option value="ALL" <?php if ($row['currency_code'] == "ALL") echo "selected";?>>ALL - Albania Lek</option>
								<option value="AMD" <?php if ($row['currency_code'] == "AMD") echo "selected";?>>AMD - Armenia Dram</option>
								<option value="ANG" <?php if ($row['currency_code'] == "ANG") echo "selected";?>>ANG - Netherlands Antilles Guilder</option>
								<option value="AOA" <?php if ($row['currency_code'] == "AOA") echo "selected";?>>AOA - Angola Kwanza</option>
								<option value="ARS" <?php if ($row['currency_code'] == "ARS") echo "selected";?>>ARS - Argentina Peso</option>
								<option value="AUD" <?php if ($row['currency_code'] == "AUD") echo "selected";?>>AUD - Australia Dollar</option>
								<option value="AWG" <?php if ($row['currency_code'] == "AWG") echo "selected";?>>AWG - Aruba Guilder</option>
								<option value="AZN" <?php if ($row['currency_code'] == "AZN") echo "selected";?>>AZN - Azerbaijan New Manat</option>
								<option value="BAM" <?php if ($row['currency_code'] == "BAM") echo "selected";?>>BAM - Bosnia and Herzegovina Marka</option>
								<option value="BBD" <?php if ($row['currency_code'] == "BBD") echo "selected";?>>BBD - Barbados Dollar</option>
								<option value="BDT" <?php if ($row['currency_code'] == "BDT") echo "selected";?>>BDT - Bangladesh Taka</option>
								<option value="BGN" <?php if ($row['currency_code'] == "BGN") echo "selected";?>>BGN - Bulgaria Lev</option>
								<option value="BHD" <?php if ($row['currency_code'] == "BHD") echo "selected";?>>BHD - Bahrain Dinar</option>
								<option value="BIF" <?php if ($row['currency_code'] == "BIF") echo "selected";?>>BIF - Burundi Franc</option>
								<option value="BMD" <?php if ($row['currency_code'] == "BMD") echo "selected";?>>BMD - Bermuda Dollar</option>
								<option value="BND" <?php if ($row['currency_code'] == "BND") echo "selected";?>>BND - Brunei Darussalam Dollar</option>
								<option value="BOB" <?php if ($row['currency_code'] == "BOB") echo "selected";?>>BOB - Bolivia Boliviano</option>
								<option value="BRL" <?php if ($row['currency_code'] == "BRL") echo "selected";?>>BRL - Brazil Real</option>
								<option value="BSD" <?php if ($row['currency_code'] == "BSD") echo "selected";?>>BSD - Bahamas Dollar</option>
								<option value="BTN" <?php if ($row['currency_code'] == "BTN") echo "selected";?>>BTN - Bhutan Ngultrum</option>	
								<option value="BWP" <?php if ($row['currency_code'] == "BWP") echo "selected";?>>BWP - Botswana Pula</option>
								<option value="BYR" <?php if ($row['currency_code'] == "BYR") echo "selected";?>>BYR - Belarus Ruble</option>
								<option value="BZD" <?php if ($row['currency_code'] == "BZD") echo "selected";?>>BZD - Belize Dollar</option>
								<option value="CAD" <?php if ($row['currency_code'] == "CAD") echo "selected";?>>CAD - Canada Dollar</option>
								<option value="CDF" <?php if ($row['currency_code'] == "CDF") echo "selected";?>>CDF - Congo/Kinshasa Franc</option>
								<option value="CHF" <?php if ($row['currency_code'] == "CHF") echo "selected";?>>CHF - Switzerland Franc</option>
								<option value="CLP" <?php if ($row['currency_code'] == "CLP") echo "selected";?>>CLP - Chile Peso</option>
								<option value="CNY" <?php if ($row['currency_code'] == "CNY") echo "selected";?>>CNY - China Yuan Renminbi</option>
								<option value="COP" <?php if ($row['currency_code'] == "COP") echo "selected";?>>COP - Colombia Peso</option>
								<option value="CRC" <?php if ($row['currency_code'] == "CRC") echo "selected";?>>CRC - Costa Rica Colon</option>
								<option value="CUC" <?php if ($row['currency_code'] == "CUC") echo "selected";?>>CUC - Cuba Convertible Peso</option>
								<option value="CUP" <?php if ($row['currency_code'] == "CUP") echo "selected";?>>CUP - Cuba Peso</option>
								<option value="CVE" <?php if ($row['currency_code'] == "CVE") echo "selected";?>>CVE - Cape Verde Escudo</option>
								<option value="CZK" <?php if ($row['currency_code'] == "CZK") echo "selected";?>>CZK - Czech Republic Koruna</option>
								<option value="DJF" <?php if ($row['currency_code'] == "DJF") echo "selected";?>>DJF - Djibouti Franc</option>
								<option value="DKK" <?php if ($row['currency_code'] == "DKK") echo "selected";?>>DKK - Danish Krone</option>
								<option value="DOP" <?php if ($row['currency_code'] == "DOP") echo "selected";?>>DOP - Dominican Republic Peso</option>
								<option value="DZD" <?php if ($row['currency_code'] == "DZD") echo "selected";?>>DZD - Algeria Dinar</option>
								<option value="EGP" <?php if ($row['currency_code'] == "EGP") echo "selected";?>>EGP - Egypt Pound</option>
								<option value="ERN" <?php if ($row['currency_code'] == "ERN") echo "selected";?>>ERN - Eritrea Nakfa</option>
								<option value="ETB" <?php if ($row['currency_code'] == "ETB") echo "selected";?>>ETB - Ethiopia Birr</option>
								<option value="EUR" <?php if ($row['currency_code'] == "EUR") echo "selected";?>>EUR - Euro</option>
								<option value="FJD" <?php if ($row['currency_code'] == "FJD") echo "selected";?>>FJD - Fiji Dollar</option>
								<option value="FKP" <?php if ($row['currency_code'] == "FKP") echo "selected";?>>FKP - Falkland Islands Pound</option>
								<option value="GBP" <?php if ($row['currency_code'] == "GBP") echo "selected";?>>GBP - United Kingdom Pound</option>
								<option value="GEL" <?php if ($row['currency_code'] == "GEL") echo "selected";?>>GEL - Georgia Lari</option>
								<option value="GGP" <?php if ($row['currency_code'] == "GGP") echo "selected";?>>GGP - Guernsey Pound</option>
								<option value="GHS" <?php if ($row['currency_code'] == "GHS") echo "selected";?>>GHS - Ghana Cedi</option>
								<option value="GIP" <?php if ($row['currency_code'] == "GIP") echo "selected";?>>GIP - Gibraltar Pound</option>
								<option value="GMD" <?php if ($row['currency_code'] == "GMD") echo "selected";?>>GMD - Gambia Dalasi</option>
								<option value="GNF" <?php if ($row['currency_code'] == "GNF") echo "selected";?>>GNF - Guinea Franc</option>
								<option value="GTQ" <?php if ($row['currency_code'] == "GTQ") echo "selected";?>>GTQ - Guatemala Quetzal</option>
								<option value="GYD" <?php if ($row['currency_code'] == "GYD") echo "selected";?>>GYD - Guyana Dollar</option>
								<option value="HKD" <?php if ($row['currency_code'] == "HKD") echo "selected";?>>HKD - Hong Kong Dollar</option>
								<option value="HNL" <?php if ($row['currency_code'] == "HNL") echo "selected";?>>HNL - Honduras Lempira</option>
								<option value="HPK" <?php if ($row['currency_code'] == "HPK") echo "selected";?>>HRK - Croatia Kuna</option>
								<option value="HTG" <?php if ($row['currency_code'] == "HTG") echo "selected";?>>HTG - Haiti Gourde</option>
								<option value="HUF" <?php if ($row['currency_code'] == "HUF") echo "selected";?>>HUF - Hungary Forint</option>
								<option value="IDR" <?php if ($row['currency_code'] == "IDR") echo "selected";?>>IDR - Indonesia Rupiah</option>
								<option value="ILS" <?php if ($row['currency_code'] == "ILS") echo "selected";?>>ILS - Israel Shekel</option>
								<option value="IMP" <?php if ($row['currency_code'] == "IMP") echo "selected";?>>IMP - Isle of Man Pound</option>
								<option value="INR" <?php if ($row['currency_code'] == "INR") echo "selected";?>>INR - India Rupee</option>
								<option value="IQD" <?php if ($row['currency_code'] == "IQD") echo "selected";?>>IQD - Iraq Dinar</option>
								<option value="IRR" <?php if ($row['currency_code'] == "IRR") echo "selected";?>>IRR - Iran Rial</option>
								<option value="ISK" <?php if ($row['currency_code'] == "ISK") echo "selected";?>>ISK - Iceland Krona</option>
								<option value="JEP" <?php if ($row['currency_code'] == "JEP") echo "selected";?>>JEP - Jersey Pound</option>
								<option value="JMD" <?php if ($row['currency_code'] == "JMD") echo "selected";?>>JMD - Jamaica Dollar</option>
								<option value="JOD" <?php if ($row['currency_code'] == "JOD") echo "selected";?>>JOD - Jordan Dinar</option>		
								<option value="JPY" <?php if ($row['currency_code'] == "JPY") echo "selected";?>>JPY - Japan Yen</option>
								<option value="KES" <?php if ($row['currency_code'] == "KES") echo "selected";?>>KES - Kenya Shilling</option>
								<option value="KGS" <?php if ($row['currency_code'] == "KGS") echo "selected";?>>KGS - Kyrgyzstan Som</option>
								<option value="KHR" <?php if ($row['currency_code'] == "KHR") echo "selected";?>>KHR - Cambodia Riel</option>
								<option value="KMF" <?php if ($row['currency_code'] == "KMF") echo "selected";?>>KMF - Comoros Franc</option>
								<option value="KPW" <?php if ($row['currency_code'] == "KPW") echo "selected";?>>KPW - Korea (North) Won</option>
								<option value="KRW" <?php if ($row['currency_code'] == "KRW") echo "selected";?>>KRW - Korea (South) Won</option>
								<option value="KWD" <?php if ($row['currency_code'] == "KWD") echo "selected";?>>KWD - Kuwait Dinar</option>
								<option value="KYD" <?php if ($row['currency_code'] == "KYD") echo "selected";?>>KYD - Cayman Islands Dollar</option>
								<option value="KZT" <?php if ($row['currency_code'] == "KZT") echo "selected";?>>KZT - Kazakhstan Tenge</option>
								<option value="LAK" <?php if ($row['currency_code'] == "LAK") echo "selected";?>>LAK - Laos Kip</option>
								<option value="LBP" <?php if ($row['currency_code'] == "LBP") echo "selected";?>>LBP - Lebanon Pound</option>
								<option value="LKR" <?php if ($row['currency_code'] == "LKR") echo "selected";?>>LKR - Sri Lanka Rupee</option>
								<option value="LRD" <?php if ($row['currency_code'] == "LRD") echo "selected";?>>LRD - Liberia Dollar</option>
								<option value="LSL" <?php if ($row['currency_code'] == "LSL") echo "selected";?>>LSL - Lesotho Loti</option>
								<option value="LYD" <?php if ($row['currency_code'] == "LYD") echo "selected";?>>LYD - Libya Dinar</option>
								<option value="MAD" <?php if ($row['currency_code'] == "MAD") echo "selected";?>>MAD - Morocco Dirham</option>	
								<option value="MDL" <?php if ($row['currency_code'] == "MDL") echo "selected";?>>MDL - Moldova Leu</option>
								<option value="MGA" <?php if ($row['currency_code'] == "MGA") echo "selected";?>>MGA - Madagascar Ariary</option>
								<option value="MKD" <?php if ($row['currency_code'] == "MKD") echo "selected";?>>MKD - Macedonia Denar</option>
								<option value="MMK" <?php if ($row['currency_code'] == "MMK") echo "selected";?>>MMK - Myanmar (Burma) Kyat</option>
								<option value="MNT" <?php if ($row['currency_code'] == "MNT") echo "selected";?>>MNT - Mongolia Tughrik</option>
								<option value="MOP" <?php if ($row['currency_code'] == "MPO") echo "selected";?>>MOP - Macau Pataca</option>
								<option value="MRO" <?php if ($row['currency_code'] == "MRO") echo "selected";?>>MRO - Mauritania Ouguiya</option>
								<option value="MUR" <?php if ($row['currency_code'] == "MUR") echo "selected";?>>MUR - Mauritius Rupee</option>
								<option value="MVR" <?php if ($row['currency_code'] == "MVR") echo "selected";?>>MVR - Maldivian Rufiyaa</option>
								<option value="MWK" <?php if ($row['currency_code'] == "MWK") echo "selected";?>>MWK - Malawi Kwacha</option>
								<option value="MXN" <?php if ($row['currency_code'] == "MXN") echo "selected";?>>MXN - Mexico Peso</option>
								<option value="MYR" <?php if ($row['currency_code'] == "MYR") echo "selected";?>>MYR - Malaysia Ringgit</option>
								<option value="MZN" <?php if ($row['currency_code'] == "MZN") echo "selected";?>>MZN - Mozambique Metical</option>
								<option value="NAD" <?php if ($row['currency_code'] == "NAD") echo "selected";?>>NAD - Namibia Dollar</option>
								<option value="NGN" <?php if ($row['currency_code'] == "NGN") echo "selected";?>>NGN - Nigeria Naira</option>
								<option value="NTO" <?php if ($row['currency_code'] == "NTO") echo "selected";?>>NIO - Nicaragua Cordoba</option>
								<option value="NOK" <?php if ($row['currency_code'] == "NOK") echo "selected";?>>NOK - Norway Krone</option>
								<option value="NPR" <?php if ($row['currency_code'] == "NPR") echo "selected";?>>NPR - Nepal Rupee</option>
								<option value="NZD" <?php if ($row['currency_code'] == "NZD") echo "selected";?>>NZD - New Zealand Dollar</option>
								<option value="OMR" <?php if ($row['currency_code'] == "OMR") echo "selected";?>>OMR - Oman Rial</option>
								<option value="PAB" <?php if ($row['currency_code'] == "PAB") echo "selected";?>>PAB - Panama Balboa</option>
								<option value="PEN" <?php if ($row['currency_code'] == "PEN") echo "selected";?>>PEN - Peru Nuevo Sol</option>
								<option value="PGK" <?php if ($row['currency_code'] == "PGK") echo "selected";?>>PGK - Papua New Guinea Kina</option>
								<option value="PHP" <?php if ($row['currency_code'] == "PHP") echo "selected";?>>PHP - Philippines Peso</option>
								<option value="PKR" <?php if ($row['currency_code'] == "PKR") echo "selected";?>>PKR - Pakistan Rupee</option>
								<option value="PLN" <?php if ($row['currency_code'] == "PLN") echo "selected";?>>PLN - Poland Zloty</option>
								<option value="PYG" <?php if ($row['currency_code'] == "PYG") echo "selected";?>>PYG - Paraguay Guarani</option>
								<option value="QAR" <?php if ($row['currency_code'] == "QAR") echo "selected";?>>QAR - Qatar Riyal</option>
								<option value="RON" <?php if ($row['currency_code'] == "RON") echo "selected";?>>RON - Romania New Leu</option>
								<option value="RSD" <?php if ($row['currency_code'] == "RSD") echo "selected";?>>RSD - Serbia Dinar</option>
								<option value="RUB" <?php if ($row['currency_code'] == "RUB") echo "selected";?>>RUB - Russian Ruble</option>
								<option value="RWF" <?php if ($row['currency_code'] == "RWF") echo "selected";?>>RWF - Rwanda Franc</option>
								<option value="SAR" <?php if ($row['currency_code'] == "SAR") echo "selected";?>>SAR - Saudi Arabia Riyal</option>
								<option value="SBD" <?php if ($row['currency_code'] == "SBD") echo "selected";?>>SBD - Solomon Islands Dollar</option>
								<option value="SCR" <?php if ($row['currency_code'] == "SCR") echo "selected";?>>SCR - Seychelles Rupee</option>
								<option value="SDG" <?php if ($row['currency_code'] == "SDG") echo "selected";?>>SDG - Sudan Pound</option>
								<option value="SEK" <?php if ($row['currency_code'] == "SEK") echo "selected";?>>SEK - Sweden Krona</option>	
								<option value="SGD" <?php if ($row['currency_code'] == "SGD") echo "selected";?>>SGD - Singapore Dollar</option>
								<option value="SHP" <?php if ($row['currency_code'] == "SHP") echo "selected";?>>SHP - Saint Helena Pound</option>
								<option value="SLL" <?php if ($row['currency_code'] == "SLL") echo "selected";?>>SLL - Sierra Leone Leone</option>
								<option value="SOS" <?php if ($row['currency_code'] == "SOS") echo "selected";?>>SOS - Somalia Shilling</option>
								<option value="SRL" <?php if ($row['currency_code'] == "SRL") echo "selected";?>>SPL - Seborga Luigino</option>
								<option value="SRD" <?php if ($row['currency_code'] == "SRD") echo "selected";?>>SRD - Suriname Dollar</option>
								<option value="STD" <?php if ($row['currency_code'] == "STD") echo "selected";?>>STD - Sao Tome and Principe Dobra</option>
								<option value="SVC" <?php if ($row['currency_code'] == "SVC") echo "selected";?>>SVC - El Salvador Colon</option>
								<option value="SYP" <?php if ($row['currency_code'] == "SYP") echo "selected";?>>SYP - Syria Pound</option>
								<option value="SZL" <?php if ($row['currency_code'] == "SZL") echo "selected";?>>SZL - Swaziland Lilangeni</option>
								<option value="THB" <?php if ($row['currency_code'] == "THB") echo "selected";?>>THB - Thailand Baht</option>
								<option value="TJS" <?php if ($row['currency_code'] == "TJS") echo "selected";?>>TJS - Tajikistan Somoni</option>
								<option value="TMT" <?php if ($row['currency_code'] == "TMT") echo "selected";?>>TMT - Turkmenistan Manat</option>
								<option value="TND" <?php if ($row['currency_code'] == "TND") echo "selected";?>>TND - Tunisia Dinar</option>
								<option value="TOP" <?php if ($row['currency_code'] == "TOP") echo "selected";?>>TOP - Tonga Pa'anga</option>
								<option value="TRY" <?php if ($row['currency_code'] == "TRY") echo "selected";?>>TRY - Turkey Lira</option>
								<option value="TTD" <?php if ($row['currency_code'] == "TTD") echo "selected";?>>TTD - Trinidad and Tobago Dollar</option>
								<option value="TVD" <?php if ($row['currency_code'] == "TVD") echo "selected";?>>TVD - Tuvalu Dollar</option>
								<option value="TWD" <?php if ($row['currency_code'] == "TWD") echo "selected";?>>TWD - Taiwan New Dollar</option>
								<option value="TZS" <?php if ($row['currency_code'] == "TZS") echo "selected";?>>TZS - Tanzania Shilling</option>		
								<option value="UAH" <?php if ($row['currency_code'] == "UAH") echo "selected";?>>UAH - Ukrainian Hryvnia</option>
								<option value="UGX" <?php if ($row['currency_code'] == "UGX") echo "selected";?>>UGX - Uganda Shilling</option>
								<option value="USD" <?php if ($row['currency_code'] == "USD") echo "selected";?>>USD - United States Dollar</option>
								<option value="UYU" <?php if ($row['currency_code'] == "UYU") echo "selected";?>>UYU - Uruguay Peso</option>
								<option value="UZS" <?php if ($row['currency_code'] == "UZS") echo "selected";?>>UZS - Uzbekistan Som</option>
								<option value="VEF" <?php if ($row['currency_code'] == "VEF") echo "selected";?>>VEF - Venezuela Bolivar</option>
								<option value="VND" <?php if ($row['currency_code'] == "VND") echo "selected";?>>VND - Viet Nam Dong</option>
								<option value="VUV" <?php if ($row['currency_code'] == "VUV") echo "selected";?>>VUV - Vanuatu Vatu</option>
								<option value="WST" <?php if ($row['currency_code'] == "WST") echo "selected";?>>WST - Samoa Tala</option>
								<option value="XAF" <?php if ($row['currency_code'] == "XAF") echo "selected";?>>XAF - CFA Franc BEAC</option>
								<option value="XCD" <?php if ($row['currency_code'] == "XCD") echo "selected";?>>XCD - East Caribbean Dollar</option>
								<option value="XDR" <?php if ($row['currency_code'] == "XDR") echo "selected";?>>XDR - IMF Special Drawing Rights</option>
								<option value="XOF" <?php if ($row['currency_code'] == "XOF") echo "selected";?>>XOF - CFA Franc</option>
								<option value="XPF" <?php if ($row['currency_code'] == "XPF") echo "selected";?>>XPF - CFP Franc</option>
								<option value="YER" <?php if ($row['currency_code'] == "YER") echo "selected";?>>YER - Yemen Rial</option>
								<option value="ZAR" <?php if ($row['currency_code'] == "ZAR") echo "selected";?>>ZAR - South Africa Rand</option>
								<option value="ZMW" <?php if ($row['currency_code'] == "ZMW") echo "selected";?>>ZMW - Zambia Kwacha</option>
								<option value="ZWD" <?php if ($row['currency_code'] == "ZWD") echo "selected";?>>ZWD - Zimbabwe Dollar</option>
						</optgroup>
						<optgroup label="Other Currency">
								<option value="other" <?php if ($row['is_new_currency'] == 1) echo "selected";?>>... other</option>
						</optgroup>
					</select>

				</td>
		   </tr>	
         <tr id="other_currency" <?php if (@$currency != "other" && $row['is_new_currency'] != 1) { ?>style="display: none;" <?php }else{ ?>style="display: ;" <?php } ?>>
            <td valign="middle" align="left" class="tb1">Other Currency Code:</td>
            <td valign="middle"><input type="text" name="other_currency_code" value="<?php echo ($row['is_new_currency'] == 1) ? $row['currency_code'] : ""; ?>" size="26" class="form-control" /></td>
          </tr>
          <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="hide_code" value="1" <?php if ($row['hide_code'] == 1) echo "checked=\"checked\""; ?> /> hide currency code </label> <span class="note" title="do not show currency code, just currency name (eg. show Bitcoin instead Bitcoin BTC)"></span></div></td>
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
          <!--	
			<tr>
				<td valign="top" align="left" class="tb1">Logo:</td>
				<td align="left" valign="top"><input type="file" name="image_url" class="form-control" /></td>
			</tr>		
			<tr>
				<td valign="middle" align="left" class="tb1"><span class="req">* </span>Your Account:</td>
				<td valign="middle">
					<input type="text" name="account_id" id="account_id" value="<?php echo $row['account_id']; ?>" size="34" class="form-control" /> <span class="note" title="Account to receive/send money, e.g. mypaypal@gmail.com"></span>
				</td>
			</tr>
			-->	
			<tr>
				<td valign="middle" align="left" class="tb1"><i class="fa fa-bars" aria-hidden="true"></i> Reserve:</td>
				<td valign="middle">
					<input type="text" name="reserve" id="reserve" value="<?php echo $row['reserve']; ?>" size="15" class="form-control" /> <span class="note" title="leave empty for unlimited reserve"></span>
				</td>
			</tr>
			<!--					
			<tr>
				<td valign="middle" align="left" class="tb1">Fast Exchange:</td>
				<td valign="middle">
					<select name="fast_exchange" class="form-control">
						<option value="0" <?php if ($row['fast_exchange'] == "0") echo "selected"; ?>>no</option>
						<option value="1" <?php if ($row['fast_exchange'] == "1") echo "selected"; ?>>yes</option>
					</select>
				</td>
			</tr>-->
			<tr>
				<td valign="middle" align="left" class="tb1"><?php echo SITE_TITLE; ?> Fee:</td>
				<td valign="middle">
					<input type="text" name="fee" id="fee" value="<?php echo $row['fee']; ?>" size="10" class="form-control" /> % <span class="note" title="exchange fee (0 = disabled)"></span>
				</td>
			</tr>			
			<tr>
				<td valign="middle" align="left" class="tb1">Site Code:</td>
				<td valign="middle">
					<input type="text" name="site_code" id="site_code" value="<?php echo $row['site_code']; ?>" size="10" class="form-control" /> e.g. BTCUSD
				</td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">XML Code:</td>
				<td valign="middle">
					<input type="text" name="xml_code" id="xml_code" value="<?php echo $row['xml_code']; ?>" size="10" class="form-control" /> e.g. BTCUSD
				</td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Currency Exchange code:</td>
				<td valign="middle">
					<input type="text" name="curr_code" id="curr_code" value="<?php echo $row['curr_code']; ?>" size="10" class="form-control" <?php // if ($row['currency_id'] < 10) echo "disabled"; ?>/> <span class="note" title="need for xml rates page"></span>
				</td>
			</tr>				
			<tr>
				<td valign="middle" align="left" class="tb1">Min Reserve Alert:</td>
				<td valign="middle">
					<input type="text" name="min_reserve" id="min_reserve" value="<?php echo $row['min_reserve']; ?>" size="15" class="form-control" /> <span class="note" title="minimal reserve value for admin notification"></span>
				</td>
			</tr>			
            <tr>
				<td valign="middle" align="left" class="tb1">Instructions for users:</td>
				<td valign="top"><textarea name="instructions" cols="112" rows="5" style="width:90%;" class="form-control"><?php echo strip_tags($row['instructions']); ?></textarea></td>
            </tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Website:</td>
				<td valign="middle"><input type="text" name="website" id="website" value="<?php echo $row['website']; ?>" size="40" class="form-control" /><span class="note" title="e.g. bitcoin.org"></span></td>
            </tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Cryptocurrency?:</td>
				<td valign="middle">
					<select name="is_crypto" class="form-control">
						<option value="0" <?php if ($row['is_crypto'] == "0") echo "selected"; ?>>no</option>
						<option value="1" <?php if ($row['is_crypto'] == "1") echo "selected"; ?>>yes</option>
					</select>
				</td>
			</tr>		                      
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="allow_send" value="1" <?php if ($row['allow_send'] == 1) echo "checked=\"checked\""; ?> /> allow send payments</label></div></td>
            </tr>               
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="allow_receive" value="1" <?php if ($row['allow_receive'] == 1) echo "checked=\"checked\""; ?> /> allow receive payments</label></div></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="allow_affiliate" value="1" <?php if ($row['allow_affiliate'] == 1) echo "checked=\"checked\""; ?> /> allow affiliates withdrawals via this method</label></div></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="default_send" value="1" <?php if ($row['default_send'] == 1) echo "checked=\"checked\""; ?> /> default send method</label></div></td>
            </tr>            
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="default_receive" value="1" <?php if ($row['default_receive'] == 1) echo "checked=\"checked\""; ?> /> default receive method</label></div></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">Sort Order:</td>
				<td valign="middle"><input type="text" class="form-control" name="sort_order" value="<?php echo $row['sort_order']; ?>" size="5" /></td>
            </tr>            
            <tr>
	            <td valign="middle" align="left" class="tb1">Status:</td>
	            <td valign="top">
					<select name="status" class="form-control">
						<option value="active" <?php if ($row['status'] == "active") echo "selected"; ?>>active</option>
						<option value="inactive" <?php if ($row['status'] == "inactive") echo "selected"; ?>>inactive</option>
					</select>
				</td>
            </tr>
            <tr>
				<td align="left" valign="bottom">&nbsp;</td>
				<td align="left" valign="bottom">
					<input type="hidden" name="did" id="did" value="<?php echo (int)$row['currency_id']; ?>" />
					<input type="hidden" name="action" id="action" value="edit">
					<input type="submit" class="btn btn-success" name="update" id="update" value="Update Currency" />
					<input type="button" class="btn btn-default" name="cancel" value="Cancel" onclick="history.go(-1);return false;" /><br><br>
              </td>
            </tr>
          </table>
      </form>

      <?php }else{ ?>
			<div class="alert alert-info">Sorry, no currency found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>