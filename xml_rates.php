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

	header("Content-Type: text/xml;charset=UTF-8");
   
	$query = "SELECT * FROM exchangerix_exdirections WHERE status='active' ORDER BY sort_order DESC, from_currency DESC";
	$result = smart_mysql_query($query); //dev LEFT JOIN currencies ON
	$total = mysqli_num_rows($result);

	if ($total > 0)
	{		
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<rates>';

		while ($row = mysqli_fetch_array($result))
		{
			$from_id 		= $row['from_currency'];
			$to_id 			= $row['to_currency'];
			$send_row 		= mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='$from_id' LIMIT 1"));
			$receive_row 	= mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='$to_id' LIMIT 1"));	
			
			if ($send_row['xml_code'] != "") $send_code = $send_row['xml_code']; else $send_code = $send_row['currency_code'];
			if ($send_row['xml_code'] != "") $receive_code = $receive_row['xml_code']; else $receive_code = $receive_row['currency_code'];
			
			$minamount		= $row['min_amount']." ".$send_code;
			$maxamount		= $row['max_amount']." ".$send_code;
			
			if ($send_code != "" && $receive_code != "")
			{	
				$ex_url	= SITE_URL."index.php?currency_send=".$from_id."&amp;currency_receive=".$to_id;
				$year	= substr($row['added'],0,4);
				$month  = substr($row['added'],5,2);
				$day	= substr($row['added'],8,2);
				$i_date = ''.$year.'-'.$month.'-'.$day.'';
	
				echo  
				'
				<item>
				<from>'.$send_code.'</from>
				<to>'.$receive_code.'</to>
				<in>'.floatval($row['from_rate']).'</in>
				<out>'.floatval($row['to_rate']).'</out>
				<amount>'.GetCurrencyReserve($row['to_currency']).'</amount>
				';
				
				if ($row['min_amount'] != "") echo '<minamount>'.$minamount.'</minamount>';
				if ($row['max_amount'] != "") echo '<maxamount>'.$maxamount.'</maxamount>';
				
				echo
				'
				<url>'.$ex_url.'</url>
				</item>
				';
			}
		}

		echo  
		'</rates>'; 
	}

?>