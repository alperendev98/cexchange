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
	require_once("../inc/pagination.inc.php");
	require_once("./inc/admin_funcs.inc.php");

	$cpage = 9;

	CheckAdminPermissions($cpage);

	if (isset($_POST['params']) && $_POST['params'] != "") $params = substr($_POST['params'],0,200);

	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
	{
		$pid = (int)$_GET['id'];
		//DeleteExchange($pid);
		// delete proof //dev
		smart_mysql_query("DELETE FROM exchangerix_exchanges WHERE exchange_id='$pid'");
		header("Location: exchanges.php?".$params."msg=deleted");
		exit();
	}


	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 20;


		// Confirm payments //
		if (isset($_POST['xconfirm']) && $_POST['xconfirm'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$pid = (int)$v;
					ConfirmPayment($pid);
				}

				header("Location: exchanges.php?".$params."msg=confirmed");
				exit();
			}
		}
		

		// Decline payments //
		if (isset($_POST['decline']) && $_POST['decline'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$pid = (int)$v;
					DeclinePayment($pid);
				}

				header("Location: exchanges.php?".$params."msg=declined");
				exit();
			}
		}		


		// Delete payments //
		if (isset($_POST['delete']) && $_POST['delete'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$pid = (int)$v;
					//DeletePayment($pid);
					smart_mysql_query("DELETE FROM exchangerix_exchanges WHERE exchange_id='$pid'");
				}

				header("Location: exchanges.php?".$params."msg=deleted");
				exit();
			}
		}

		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "username": $rrorder = "user_id"; break;
					case "email": $rrorder = "client_email"; break;
					case "rate": $rrorder = "exchange_rate"; break;
					case "from_currency": $rrorder = "from_currency"; break;
					case "from_amount": $rrorder = "exchange_amount"; break;
					case "to_amount": $rrorder = "receive_amount";
					case "to_currency": $rrorder = "to_currency"; break;
					case "amount": $rrorder = "amount"; break;
					case "status": $rrorder = "status"; break;
					case "ids": $rrorder = "exchange_id"; break;
					default: $rrorder = "exchange_id"; break;
				}
			}
			else
			{
				$rrorder = "exchange_id";
			}

			if (isset($_GET['order']) && $_GET['order'] != "")
			{
				switch ($_GET['order'])
				{
					case "asc": $rorder = "asc"; break;
					case "desc": $rorder = "desc"; break;
					default: $rorder = "asc"; break;
				}
			}
			else
			{
				$rorder = "desc";
			}

			if (isset($_GET['action']) && $_GET['action'] == "filter")
			{
				$action		= "filter";
				$filter_by	= "";
				$filter		= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
				$start_date	= mysqli_real_escape_string($conn, getGetParameter('start_date'));
				$start_date	= substr($start_date, 0, 16);
				$end_date	= mysqli_real_escape_string($conn, getGetParameter('end_date'));
				$end_date	= substr($end_date, 0, 16);

				switch ($filter)
				{
					case PAYMENT_TYPE_WITHDRAWAL:	$filter = "withdrawal"; break;
					case PAYMENT_TYPE_FBONUS:		$filter = "friend_bonus"; break;
					case PAYMENT_TYPE_SBONUS:		$filter = "signup_bonus"; break;
					case PAYMENT_TYPE_RCOMMISSION:	$filter = "referral_commission"; break;
				}

				if ($filter != "")
				{				
					$search_by = " (reference_id='$filter' OR payment_type='$filter')";

					switch ($_GET['search_type'])
					{
						case "reference_id": $search_by = "reference_id='".$filter."'"; break;
						case "member": $search_by = "user_id='".$filter."'"; break;
						case "email": $search_by = "client_email LIKE '%".$filter."%'"; break;
						case "send_account": $search_by = "send_account='".$filter."'"; break;
						case "receive_account": $search_by = "receive_account='".$filter."'"; break;
						//case "amount": $filter = preg_replace("/[^0-9.]/", "", $filter); $search_by = "amount='".$filter."'"; break;
						case "send_amount": $filter = preg_replace("/[^0-9.]/", "", $filter); $search_by = "send_amount='".$filter."'"; break;
						case "receive_amount": $filter = preg_replace("/[^0-9.]/", "", $filter); $search_by = "receive_amount='".$filter."'"; break;
						case "payment_type": $search_by = "payment_type='".$filter."'"; break;
					}

					$filter_by .= " AND ".$search_by;
				}

				if ($start_date != "")	$filter_by .= " AND created>='$start_date 00:00:00'";
				if ($end_date != "")	$filter_by .= " AND created<='$end_date 23:59:59'";
				$totitle = " - Search Results";
			}
		///////////////////////////////////////////////////////

		if (isset($_GET['period']) && $_GET['period'] != "")
		{
			$today = date("Y-m-d");
			$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d") - 1, date("Y")));
			
			if ($_GET['period'] == "today") { $filter_by .= " AND date(created)='$today' "; $totitle2 = "Today's"; }
			if ($_GET['period'] == "yesterday") { $filter_by .= " AND date(created)='$yesterday' "; $totitle2 = "Yesterday's"; }
			if ($_GET['period'] == "7days") { $filter_by .= " AND date_sub(curdate(), interval 7 day) <= created "; $totitle2 = "Last 7 Days"; }
			if ($_GET['period'] == "30days") { $filter_by .= " AND date_sub(curdate(), interval 30 day) <= created "; $totitle2 = "Last 30 Days"; }
		}

		if (isset($_GET['from_filter']) && is_numeric($_GET['from_filter']))
		{
			$from_filter = (int)$_GET['from_filter'];
			$filter_by .= " AND from_currency_id='$from_filter' ";
			$title2 .= GetCurrencyName($from_filter);
			$totitle2 .= " ".$title2;
		}		
		
		if (isset($_GET['to_filter']) && is_numeric($_GET['to_filter']))
		{
			$to_filter = (int)$_GET['to_filter'];
			$filter_by .= " AND to_currency_id='$to_filter' ";
			$title2 = GetCurrencyName($to_filter);
			$totitle2 .= " ".$title2;
		}
		
		if (isset($_GET['ft_filter']) && is_numeric($_GET['ft_filter']))
		{
			$ft_filter = (int)$_GET['ft_filter'];
			$filter_by .= " AND (from_currency_id='$ft_filter' OR to_currency_id='$ft_filter') ";
			$title2 = GetCurrencyName($ft_filter);
			$totitle2 .= " ".$title2;
		}		
		
		if (isset($_GET['status_filter']) && $_GET['status_filter'] != "")
		{
			$status_filter	= mysqli_real_escape_string($conn, trim(getGetParameter('status_filter')));
			$status_filter	= substr($status_filter, 0, 16);
			$filter_by .= " AND status='$status_filter' ";
			//$title2 .= GetCurrencyName($status_filter);
		}		

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;
		
		
		//smart_mysql_query("UPDATE exchangerix_exchanges SET viewed='1' WHERE viewed='0'"); //dev
		//smart_mysql_query("UPDATE exchangerix_exchanges SET status='timeout' WHERE created != '0000-00-00 00:00:00' AND created <= NOW()");
		smart_mysql_query("UPDATE exchangerix_exchanges SET status='timeout', updated=NOW() WHERE (created < (NOW() - INTERVAL 60 MINUTE) AND status='waiting')");
				
		
		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." <br><small>%h:%i %p</small>') AS payment_date FROM exchangerix_exchanges WHERE status!='request' $filter_by ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM exchangerix_exchanges WHERE status!='request'".$filter_by;
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$total_declined = mysqli_num_rows(smart_mysql_query("SELECT * FROM exchangerix_exchanges WHERE 1=1 ".$where." AND (status='cancelled' OR status='timeout' OR status='declined')"));

		// delete all calcelled payments //
		if (isset($_GET['act']) && $_GET['act'] == "delete_cancelled")
		{
			smart_mysql_query("DELETE FROM exchangerix_exchanges WHERE status='expired' OR status='timeout' OR status='cancelled'");
			header("Location: exchanges.php?msg=exp_deleted");
			exit();
		}

		$cc = 0;

		$title = "Exchanges";
		require_once ("inc/header.inc.php");

?>

		<div id="addnew">
			<?php if ($total_declined > 0) { ?><a style="margin-right: 15px;" href="exchanges.php?act=delete_cancelled"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete <span class="badge" style="background: #d9534f"><?php echo $total_declined; ?></span> not completed exchanges</a> &nbsp;&nbsp;<?php } ?>
			<a href="javascript:void(0);" class="search" onclick="$('#admin_filter').toggle('slow');">Search</a>
			<?php /*if ($total > 0) { ?>
				<a class="export" href="xls_export.php?action=export<?php if (isset($filter) && $filter != "") echo "&filter=".$filter; if (isset($start_date) && $start_date != "") echo "&start_date=".$start_date; if (isset($end_date) && $end_date != "") echo "&end_date=".$end_date; ?>" title="Export to Excel">Export</a>
			<?php }*/ ?>
		</div>

       <h2><i class="fa fa-refresh fa-spin" style="color: #95c939" aria-hidden="true"></i> <?php echo @$totitle2; ?> Exchanges <?php echo @$totitle; ?> <?php if ($total > 0) { ?><sup class="badge" style="background: #73b9d1"><?php echo number_format($total); ?></sup><?php } ?></h2>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "processed": echo "Exchange has been successfully processed"; break;
						case "updated": echo "Exchange has been successfully updated"; break;
						case "confirmed": echo "Exchanges have been successfully confirmed"; break;
						case "declined": echo "Exchange has been successfully cancelled"; break;
						case "deleted": echo "Exchange has been successfully deleted"; break;
						case "exp_deleted": echo "All cancelled exchanges have been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

		<form id="form1" name="form1" method="get" action="">
		<table style="background:#F9F9F9" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
		<td colspan="3" valign="middle" align="center" nowrap>
			<div class="admin_filter" id="admin_filter" style="<?php if (!@$_GET['search'] && !@$_GET['filter']) { ?>display: none;<?php } ?> background: #F3F3F3; border-radius: 5px; padding: 8px; margin: 5px; border: 1px solid #EEE">
				Search for: <input type="text" name="filter" value="<?php echo $filter; ?>" class="form-control" size="27" />
				in <select name="search_type" class="form-control">
					 <option value="">-----</option>
					 <option value="reference_id" <?php if ($_GET['search_type'] == "reference_id") echo "selected"; ?>>Reference ID</option>
					 <option value="member" <?php if ($_GET['search_type'] == "member") echo "selected"; ?>>User ID</option>
					 <option value="email" <?php if ($_GET['search_type'] == "email") echo "selected"; ?>>Email</option><!-- //dev-->
					 <option value="send_account" <?php if ($_GET['search_type'] == "send_account") echo "selected"; ?>>Send Account</option>
					 <option value="receive_account" <?php if ($_GET['search_type'] == "receive_account") echo "selected"; ?>>Receive Account</option>
					 <option value="send_amount" <?php if ($_GET['search_type'] == "send_amount") echo "selected"; ?>>Send Amount</option>
					 <option value="receive_amount" <?php if ($_GET['search_type'] == "receive_amount") echo "selected"; ?>>Receive Amount</option>
					 <option value="amount" <?php if ($_GET['search_type'] == "amount") echo "selected"; ?>>Amount</option>
					 <option value="payment_type" <?php if ($_GET['search_type'] == "payment_type") echo "selected"; ?>>Payment Type</option>
				</select>
				&nbsp;
				Date: <input type="text" name="start_date" id="datetimepicker1" value="<?php echo $start_date; ?>" size="18" class="form-control" /> - <input type="text" name="end_date" id="datetimepicker2" value="<?php echo $end_date; ?>" size="18" class="form-control" />
				<br>
				Send Direction: <select name="from_filter" id="from_filter" onChange="document.form1.submit()" class="form-control">
				<option value="">------</option>
					<?php
						$sql_curr_send = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE allow_send='1' AND status='active' ORDER BY currency_name ASC"); 
						while ($row_curr_send = mysqli_fetch_array($sql_curr_send))
						{
							if ($from_filter == $row_curr_send['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
							echo "<option value=\"".$row_curr_send['currency_id']."\"".$selected.">".$row_curr_send['currency_name'];
							if ($row_curr_send['is_crypto'] != 1) echo " ".$row_curr_send['currency_code'];
							echo "</option>";
						}
					?>
				</select>
				&nbsp; Receive Direction: <select name="to_filter" id="to_filter" onChange="document.form1.submit()" class="form-control">
					<option value="">------</option>
					<?php
						$sql_curr_receive = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE allow_receive='1' AND status='active' ORDER BY currency_name ASC");
						while ($row_curr_receive = mysqli_fetch_array($sql_curr_receive))
						{
							if ($to_filter == $row_curr_receive['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
							echo "<option value=\"".$row_curr_receive['currency_id']."\"".$selected.">".$row_curr_receive['currency_name'];
							if ($row_curr_receive['is_crypto'] != 1) echo " ".$row_curr_receive['currency_code'];
							echo "</option>";							
						}
					?>
				</select>				
				<input type="hidden" name="action" value="filter" />			
				<?php if (isset($_GET['search'])) { ?><input type="hidden" name="search" value="search" /><?php } ?>
				<button type="submit" class="btn btn-success"><i class="fa fa-search" aria-hidden="true"></i> Search</button>
				<?php if ((isset($filter) && $filter != "") || $start_date || $end_date) { ?><a title="Cancel Search" href="exchanges.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Search" /></a><?php } ?>
			</div>
		</td>
		</tr>
		<tr>
		<td width="47%" valign="middle" align="left">
           Sort by: 
          <select name="column" id="column" class="form-control" onChange="document.form1.submit()">
			<option value="ids" <?php if ($_GET['column'] == "ids") echo "selected"; ?>>Date</option>
			<option value="username" <?php if ($_GET['column'] == "username") echo "selected"; ?>>Member</option>
			<option value="email" <?php if ($_GET['column'] == "email") echo "selected"; ?>>Email</option>
			<option value="from_currency" <?php if ($_GET['column'] == "from_currency") echo "selected"; ?>>Send Direction</option>
			<option value="to_currency" <?php if ($_GET['column'] == "to_currency") echo "selected"; ?>>Receive Direction</option>
			<option value="rate" <?php if ($_GET['column'] == "rate") echo "selected"; ?>>Exchange Rate</option>
			<option value="from_amount" <?php if ($_GET['column'] == "from_amount") echo "selected"; ?>>Send Amount</option>
			<option value="to_amount" <?php if ($_GET['column'] == "to_amount") echo "selected"; ?>>Receive Amount</option>
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
          </select>
          <select name="order" id="order" class="form-control" onChange="document.form1.submit()">
			<option value="desc" <?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>
		  &nbsp;&nbsp;Results:  
          <select name="show" id="show" class="form-control" onChange="document.form1.submit()">
			<option value="20" <?php if ($_GET['show'] == "20") echo "selected"; ?>>20</option>
			<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
			<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
			<option value="111111111" <?php if ($_GET['show'] == "111111111") echo "selected"; ?>>ALL</option>
          </select>
		</td>
		<td width="25%" valign="middle" align="left" nowrap>
			<div style="background: #F7F7F7; padding: 7px; border-radius: 7px;">
				Status: <select name="status_filter" id="status_filter" onChange="document.form1.submit()" class="form-control">
					<option value="">--- show all ---</option>
					<option value="confirmed" <?php if ($_GET['status_filter'] == "confirmed") echo "selected"; ?>>confirmed</option>
					<option value="paid" <?php if ($_GET['status_filter'] == "paid") echo "selected"; ?>>paid</option>
					<option value="pending" <?php if ($_GET['status_filter'] == "pending") echo "selected"; ?>>awaiting payment</option>
					<!--<option value="cancelled" <?php if ($_GET['status_filter'] == "cancelled") echo "selected"; ?>>cancelled</option>-->
					<option value="timeout" <?php if ($_GET['status_filter'] == "timeout") echo "selected"; ?>>timeout</option>
					<option value="declined" <?php if ($_GET['status_filter'] == "declined") echo "selected"; ?>>declined</option>
				</select>				
				<!--<input type="submit" class="btn btn-success" value="Filter" />-->							
				<?php if (@$status_filter) { ?><a href="exchanges.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Delete Filter" /></a><?php } ?>
			</div>
		</td>		
		<td width="30%" valign="middle" align="right" nowrap>
			<?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>&nbsp;&nbsp;
		</td>
		</tr>
		</table>
		</form>

       
	   <?php if ($total > 0) { $total_amount = 0; ?>
	   		<div class="table-responsive">
			<form id="form2" name="form2" method="post" action="">
            <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<th width="5%">ID</th>
				<th width="11%">Reference ID</th>
				<th width="10%">Date</th>
				<!--<th width="7%">User ID</th>-->
				<th width="27%">Exchange Direction</th>
				<th width="27%">Amount<br> Send <i class="fa fa-long-arrow-right" aria-hidden="true"></i> Receive</th>
				<th width="20%"><i class="fa fa-user"></i> User</th>
				<th width="12%">Status</th>
				<!--<th width="12%">IP</th>-->
				<th width="7%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++;  $total_amount +=$row['amount']; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>" <?php if ($row['status'] == "pending") echo "style='background: #fff9f2'"; ?> <?php //if ($row['status'] == "declined" || $row['status'] == "timeout" || $row['status'] == "cancelled") echo "style='background: #f9f2f2'"; ?>>
					<td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['exchange_id']; ?>]" id="id_arr[<?php echo $row['exchange_id']; ?>]" value="<?php echo $row['exchange_id']; ?>" /></td>
					<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['exchange_id']; ?></td>
					<td align="left" valign="middle" style="padding-left: 7px;"><a href="exchange_details.php?id=<?php echo $row['exchange_id']; ?>"><?php echo $row['reference_id']; ?></a></td>
					<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['payment_date']; ?></td>
					<td align="left" valign="middle" style="padding: 15px 8px;">
						<!--<table width="100%"><tr><td width="40%"></td><td width="5%"></td><td width="48%" align="right"></td></tr></table>-->
						<?php echo GetCurrencyImg($row['from_currency_id'], $width=20); ?> <?php echo substr($row['from_currency'], 0, -4); ?>
						<i class="fa fa-long-arrow-right" aria-hidden="true"></i>
						<?php echo GetCurrencyImg($row['to_currency_id'], $width=20); ?> <?php echo substr($row['to_currency'], 0, -4); ?>
					</td>
					<td align="left" valign="middle" style="padding: 15px 8px;">
						&nbsp; <b><?php echo floatval($row['exchange_amount']); ?></b> <sup><?php echo substr($row['from_currency'], -4); ?></sup> <i class="fa fa-long-arrow-right" aria-hidden="true"></i> <b><?php echo floatval($row['receive_amount']); //number_format($row['receive_amount'], 2, '.', ''); ?></b> <sup><?php echo substr($row['to_currency'], -4); ?></sup> <!-- from account/to account //dev -->
						<br><span class="badge" style="background: #c9c9c9; color: #fff; font-weight: normal;">rate: <?php echo $row['ex_from_rate']; ?> <?php echo substr($row['from_currency'], -4); ?> = <?php echo $row['ex_to_rate']; ?> <?php echo substr($row['to_currency'], -4); ?></span>
						<br>						
					</td>						
					<!--<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['user_id']; ?></td>-->
					<td align="left" valign="middle" style="padding-left: 7px; font-size: 13px">
						<?php if ($row['country_code'] != "") { ?><img src="<?php echo SITE_URL; ?>images/flags/<?php echo $row['country_code']; ?>.png" width="16" height="11" /><?php } ?>&nbsp; <?php if ($row['user_id'] > 0) { ?><i class="fa fa-user-circle" aria-hidden="true"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo GetUsername($row['user_id'], $type=2); ?></a><?php }else{ ?><i class="fa fa-user-o" aria-hidden="true"></i> <?php echo $row['client_details']; ?><!--Visitor--><?php } ?>
						<br><a href="mailto:<?php echo $row['client_email']; ?>" style="color: #86acc9; padding-left: 3px;"><?php echo $row['client_email']; ?></a>
						<?php if ($row['proof'] != "") { ?><br> <i class="fa fa-paperclip"></i> <a style="color: #5cb85c" href="<?php echo SITE_URL; ?>uploads/<?php echo $row['proof']; ?>" data-lightbox="image-1" data-title="Payment Proof">payment proof</a><?php } ?>
					</td>
					<td align="left" valign="middle" style="padding-left: 5px;">
					<?php
						switch ($row['status'])
					  {
							case "confirmed": echo "<span class='label label-success'><i class='fa fa-check'></i> confirmed</span>"; break;
							case "pending": echo "<span class='label label-warning tooltips' title='awaiting confirmation'><i class='fa fa-clock-o'></i> awaiting</span>"; break;
							case "waiting": echo "<span class='label label-default tooltips' title='waiting for payment'><i class='fa fa-clock-o'></i> waiting</span>"; break;
							case "declined": echo "<span class='label label-danger'><i class='fa fa-times'></i> declined</span>"; break;
							case "failed": echo "<span class='label label-danger'><i class='fa fa-times'></i> failed</span>"; break;
							case "cancelled": echo "<span class='label label-danger'><i class='fa fa-times'></i> cancelled</span>"; break;
							case "timeout": echo "<span class='label label-danger'><i class='fa fa-times'></i> timeout</span>"; break;
							case "request": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting approval</span>"; break;
							case "paid": echo "<span class='label label-success'><i class='fa fa-check'></i> paid</span>"; break;
							default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
						}
					?>
					<?php if ($row['reason'] != "") { ?><span class="note" title="<?php echo $row['reason']; ?>"></span><?php } ?>
					</td>
					<!--<td align="center" valign="middle" nowrap="nowrap">IP</td>-->
					<td align="center" valign="middle" nowrap="nowrap" style="padding: 0 5px;">
						<a href="exchange_details.php?id=<?php echo $row['exchange_id']; ?>"><i class="fa fa-search tooltips" style="font-size: 16px; color: #333" title="Details"></i></a>
						<a href="exchange_edit.php?id=<?php echo $row['exchange_id']; ?>"><i class="fa fa-edit tooltips" style="font-size: 16px; color: #333" title="Edit"></i></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this exchange?') )location.href='exchanges.php?id=<?php echo $row['exchange_id']; ?>&action=delete'"><i class="fa fa-remove tooltips" style="font-size: 18px; color: #ff5d2a" title="Delete"></i></a>
					</td>
				  </tr>
             <?php } ?>
				<tr>
				  <td style="border-top: 1px solid #F5F5F5" colspan="9" align="left">
					<input type="submit" class="btn btn-success" name="xconfirm" id="GoButton1" value="Confirm Selected" disabled="disabled" />
					<input type="submit" class="btn btn-warning" name="decline" id="GoButton2" value="Decline Selected" disabled="disabled" />
					<input type="submit" class="btn btn-danger" name="delete" id="GoButton3" value="Delete Selected" disabled="disabled" onclick="return confirm('Are you sure you really want to delete?')" />
				  </td>
				</tr>
            </table>
 
				<?php
							$params = "";

							if (@$_GET['column'])	$params .= "column=".$_GET['column']."&";
							if (@$_GET['order'])	$params .= "order=".$_GET['order']."&";
							if (@$filter)			$params .= "filter=$filter&";
							if (@$_GET['search_type'])	$params .= "search_type=".$_GET['search_type']."&";
							if (@$from_filter)		$params .= "from_filter=$from_filter&";
							if (@$to_filter)		$params .= "to_filter=$to_filter&";
							if (@$ft_filter)		$params .= "ft_filter=$ft_filter&";
							if (@$status_filter)	$params .= "status_filter=$status_filter&";
							//if (@$store)			$params .= "store=$store&";
							//if (@$start_date)		$params .= "start_date=$start_date&";
							//if (@$end_date)			$params .= "end_date=$end_date&";
							if (@$_GET['search'])	$params .= "search=search&";				
							if (@$action)			$params .= "action=$action&";
							if (@$_GET['show'])		$params .= "show=$results_per_page&";
							if (@$_GET['page'])		$params .= "page=$page&";

							echo ShowPagination("exchanges",$results_per_page,"exchanges.php?".$params," WHERE status!='request' $filter_by");
					
				?>
				<input type="hidden" name="params" value="<?php echo $params; ?>" />
			</form>
	   		</div>

		 <?php }else{ ?>
				<?php if (isset($filter)) { ?>
					<div class="alert alert-info">Sorry, no exchanges found for your search criteria.</div>
				<?php }else{ ?>
					<div class="alert alert-info">There are currently no exchanges.</div>
				<?php } ?>
        <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>