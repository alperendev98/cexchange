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

	// check permissions
	if (!@in_array("2", $_SESSION['adm']['pages']) && !@in_array("9", $_SESSION['adm']['pages']))
	{
		header("Location: index.php");
		exit();
	}

	$where = "";

	if (isset($_GET['action']) && $_GET['action'] == "export_users")
	{
		if (isset($_GET['filter']) && $_GET['filter'] != "")
		{
			$filter	= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
			$where .= " AND (username='$filter' OR email='%$filter%')";
		}

		if (isset($_GET['date']) && $_GET['date'] != "")
		{
			$date	= mysqli_real_escape_string($conn, getGetParameter('date'));
			$where .= " AND DATE(created)='$date'";
		}

		if (isset($_GET['start_date']) && $_GET['start_date'] != "")
		{
			$start_date	= mysqli_real_escape_string($conn, getGetParameter('start_date'));
			$where .= " AND created>='$start_date 00:00:00'";
		}

		if (isset($_GET['end_date']) && $_GET['end_date'] != "")
		{
			$end_date = mysqli_real_escape_string($conn, getGetParameter('end_date'));
			$where .= " AND created<='$end_date 23:59:59'";
		}

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS signup_date FROM exchangerix_users WHERE 1=1 ".$where." ORDER BY created DESC";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);

		if ($total > 0)
		{		
			$filename_add = "";

			if ($date) $filename_add .= "_".$date;

			if ($filename_add == "")
				$filename = "users_".time().".xls";
			else
				$filename = "users".$filename_add.".xls";


			$contents = "Report Creation Date: ".date("Y-m-d H:i:s")."\n";
			$contents .= "User ID \t Username \t Full Name \t Email \t Country \t Balance \t Signup Date \t Status \t \n";

			while ($row = mysqli_fetch_array($result))
			{
				$contents .= $row['user_id']."\t";
				$contents .= $row['username']."\t";
				$contents .= html_entity_decode($row['fname']." ".$row['lname'], ENT_NOQUOTES, 'UTF-8')."\t";
				$contents .= $row['email']."\t";
				$contents .= GetCountry($row['country'], $display_type = 2)."\t";
				$contents .= GetUserBalance($row['user_id'])."\t";
				$contents .= $row['signup_date']."\t";
				$contents .= $row['status']."\t";
				$contents .= " \n"; 
			}

			header('Content-type: application/ms-excel; charset=utf-8');
			header('Content-Disposition: attachment; filename='.$filename);

			echo $contents;
			exit;
		}
	}
	else
	{
		if (isset($_GET['filter']) && $_GET['filter'] != "")
		{
			$filter	= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
			$where .= " AND (reference_id='$filter' OR payment_type LIKE '%$filter%')";
		}

		if (isset($_GET['date']) && $_GET['date'] != "")
		{
			$date	= mysqli_real_escape_string($conn, getGetParameter('date'));
			$where .= " AND DATE(created)='$date'";
		}

		if (isset($_GET['start_date']) && $_GET['start_date'] != "")
		{
			$start_date	= mysqli_real_escape_string($conn, getGetParameter('start_date'));
			$where .= " AND created>='$start_date 00:00:00'";
		}

		if (isset($_GET['end_date']) && $_GET['end_date'] != "")
		{
			$end_date = mysqli_real_escape_string($conn, getGetParameter('end_date'));
			$where .= " AND created<='$end_date 23:59:59'";
		}

		if (isset($_GET['type']) && $_GET['type'] == "withdraw")
		{
			$where .= " AND status='request'";
		}

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS payment_date FROM exchangerix_transactions WHERE 1=1 ".$where." ORDER BY created DESC";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);

		if ($total > 0)
		{		
			$filename_add = "";

			if ($date) $filename_add .= "_".$date;

			if ($filename_add == "")
				$filename = "payments_".time().".xls";
			else
				$filename = "payments".$filename_add.".xls";


			$contents = "Report Creation Date: ".date("Y-m-d H:i:s")."\n";
			$contents .= "Reference ID \t Username \t Payment Type \t ";
			
			if (isset($_GET['type']) && $_GET['type'] == "withdraw")
				$contents .= "Payment Method \t Payment Details \t";
			
			$contents .= "Amount \t Date \t Status \t \n";

			while ($row = mysqli_fetch_array($result))
			{
				$contents .= html_entity_decode($row['transaction_id'], ENT_NOQUOTES, 'UTF-8')."\t";
				$contents .= html_entity_decode(GetUsername($row['user_id']), ENT_NOQUOTES, 'UTF-8')."\t";

				switch ($row['payment_type'])
				{
					case "cashback":			$payment_type = PAYMENT_TYPE_CASHBACK; break;
					case "withdrawal":			$payment_type = PAYMENT_TYPE_WITHDRAWAL; break;
					case "referral_commission": $payment_type = PAYMENT_TYPE_RCOMMISSION; break;
					case "friend_bonus":		$payment_type = PAYMENT_TYPE_FBONUS; break;
					case "signup_bonus":		$payment_type = PAYMENT_TYPE_SBONUS; break;
					default:					$payment_type = $row['payment_type']; break;
				}

				$contents .= html_entity_decode($payment_type, ENT_NOQUOTES, 'UTF-8')."\t";

				if (isset($_GET['type']) && $_GET['type'] == "withdraw")
				{
					$contents .= GetPaymentMethodByID($row['payment_method'])."\t";
					$contents .= $row['payment_details']."\t";
				}

				$contents .= DisplayMoney($row['amount'], $hide_currency = 1)."\t";
				$contents .= $row['payment_date']."\t";
				$contents .= $row['status']."\t";
				$contents .= " \n"; 
			}

			header('Content-type: application/ms-excel; charset=utf-8');
			header('Content-Disposition: attachment; filename='.$filename);

			echo $contents;
			exit;
		}

	}

?>