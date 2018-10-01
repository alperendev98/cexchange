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
		DeletePayment($pid);
		header("Location: payments.php?".$params."msg=deleted");
		exit();
	}


	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 20;


		// Confirm payments //
		if (isset($_POST['confirm']) && $_POST['confirm'] != "")
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

				header("Location: payments.php?".$params."msg=confirmed");
				exit();
			}
		}
		

		// Decline payments //
		if (isset($_POST['confirm']) && $_POST['confirm'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$pid = (int)$v;
					DeclinePayment($pid); //dev
				}

				header("Location: payments.php?".$params."msg=declined");
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
					DeletePayment($pid);
				}

				header("Location: payments.php?".$params."msg=deleted");
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
					//case "ptype": $rrorder = "payment_type"; break;
					case "from_currency": $rrorder = "from_currency"; break;
					case "from_amount": $rrorder = "from_amount"; break; //dev
					case "to_amount": $rrorder = "to_amount"; //dev
					case "to_currency": $rrorder = "to_currency"; break;
					case "amount": $rrorder = "amount"; break;
					case "status": $rrorder = "status"; break;
					case "ids": $rrorder = "transaction_id"; break;
					default: $rrorder = "transaction_id"; break;
				}
			}
			else
			{
				$rrorder = "transaction_id";
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
					case PAYMENT_TYPE_CASHBACK:		$filter = "cashback"; break;
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
			
			if ($_GET['period'] == "today") $filter_by .= " AND date(created)='$today' ";
				
			$totitle2 = "Today's";
		}

		if (isset($_GET['from_filter']) && is_numeric($_GET['from_filter']))
		{
			$from_filter = (int)$_GET['from_filter'];
			//$filter_by .= " AND from_currency='$from_filter' "; //dev
			//$title2 = GetCurrencyName($store);
		}		
		
		if (isset($_GET['to_filter']) && is_numeric($_GET['to_filter']))
		{
			$to_filter = (int)$_GET['to_filter'];
			//$filter_by .= " AND to_currency='$to_filter' "; //dev
			//$title2 = GetCurrencyName($store);
		}
		
		if (isset($_GET['status_filter']) && $_GET['status_filter'] != "")
		{
			$status_filter	= mysqli_real_escape_string($conn, trim(getGetParameter('status_filter')));
			$status_filter	= substr($status_filter, 0, 16);
			$filter_by .= " AND status='$status_filter' ";
			//$title2 = GetCurrencyName($store);
		}		

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;
		
		
		//smart_mysql_query("UPDATE exchangerix_transactions SET status='timeout' WHERE created != '0000-00-00 00:00:00' AND created <= NOW()");

		
		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." <sup>%h:%i %p</sup>') AS payment_date FROM exchangerix_transactions WHERE status!='request' $filter_by ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM exchangerix_transactions WHERE status!='request'".$filter_by;
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);
		
		// delete all calcelled payments //
		if (isset($_GET['act']) && $_GET['act'] == "delete_cancelled")
		{
			smart_mysql_query("DELETE FROM exchangerix_transactions WHERE status='expired' OR status='timeout' OR status='cancelled'");
			header("Location: payments.php?msg=exp_deleted");
			exit();
		}

		$cc = 0;

		$title = "Exchanges";
		require_once ("inc/header.inc.php");

?>

		<div id="addnew">
			<?php if ($total > 0) { ?><a style="margin-right: 15px;" href="payments.php?act=delete_cancelled"><img src="images/idelete.png" align="absmiddle" /> Delete cancelled exchanges</a> &nbsp;&nbsp;<?php } ?>
			<a href="javascript:void(0);" class="search" onclick="$('#admin_filter').toggle('slow');">Search</a>
			<?php if ($total > 0) { ?>
				<a class="export" href="xls_export.php?action=export<?php if (isset($filter) && $filter != "") echo "&filter=".$filter; if (isset($start_date) && $start_date != "") echo "&start_date=".$start_date; if (isset($end_date) && $end_date != "") echo "&end_date=".$end_date; ?>" title="Export to Excel">Export</a>
			<?php } ?>
		</div>

       <h2><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo @$totitle2; ?> Exchanges <?php echo @$totitle; ?></h2>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "processed": echo "Payment has been successfully processed"; break;
						case "updated": echo "Payment has been successfully updated"; break;
						case "confirmed": echo "Payments have been successfully confirmed"; break;
						case "declined": echo "Payment has been successfully cancelled"; break;
						case "deleted": echo "Payment has been successfully deleted"; break;
						case "exp_deleted": echo "All cancelled payments have been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

		<form id="form1" name="form1" method="get" action="">
		<table style="background:#F9F9F9" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
		<td colspan="3" valign="middle" align="center" nowrap>
			<div class="admin_filter" id="admin_filter" style="<?php if (!@$_GET['search']) { ?>display: none;<?php } ?> background: #F3F3F3; border-radius: 5px; padding: 8px; margin: 5px; border: 1px solid #EEE">
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
				<input type="submit" class="btn btn-success" name="search" value="Search" />
				<?php if ((isset($filter) && $filter != "") || $start_date || $end_date) { ?><a title="Cancel Search" href="payments.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Search" /></a><?php } ?>
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
			<!--<option value="ptype" <?php if ($_GET['column'] == "ptype") echo "selected"; ?>>Payment Type</option>-->
			<!--<option value="amount" <?php if ($_GET['column'] == "from_amount") echo "selected"; ?>>Amount</option>-->
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
				<?php if (@$status_filter) { ?><a href="payments.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Delete Filter" /></a><?php } ?>
			</div>
		</td>		
		<td width="30%" valign="middle" align="right" nowrap>
			<?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
		</td>
		</tr>
		</table>
		</form>

       
	   <?php if ($total > 0) { $total_amount = 0; ?>

			<form id="form2" name="form2" method="post" action="">
            <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<th width="5%">ID</th>
				<th width="12%">Reference ID</th>
				<th width="12%">Date</th><!-- //10 minutes ago //dev -->
				<!--<th width="7%">User ID</th>-->
				<th width="33%">Exchange Details</th>
				<!--<th width="17%">Rate</th>-->
				<th width="3%">&nbsp;</th>
				<th width="15%">User</th>
				<th width="12%">Status</th>
				<!--<th width="12%">IP</th>-->
				<th width="7%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++;  $total_amount +=$row['amount']; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['transaction_id']; ?>]" id="id_arr[<?php echo $row['transaction_id']; ?>]" value="<?php echo $row['transaction_id']; ?>" /></td>
					<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['transaction_id']; ?></td>
					<td align="left" valign="middle" style="padding-left: 5px;"><a href="payment_details.php?id=<?php echo $row['transaction_id']; ?>"><?php echo $row['reference_id']; ?></a></td>
					<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['payment_date']; ?></td>
					<td align="left" valign="middle" style="padding: 5px 5px 5px 15px;">
						<?php echo $row['from_currency']; ?> <i class="fa fa-long-arrow-right fa-lg" aria-hidden="true"></i> <?php echo $row['to_currency']; ?>
						<br><b><?php echo $row['exchange_amount']; ?></b> <sup><?php echo substr($row['from_currency'], -4); ?></sup> <i class="fa fa-long-arrow-right fa-lg" aria-hidden="true"></i> <b><?php echo $row['receive_amount']; ?></b> <sup><?php echo substr($row['to_currency'], -4); ?></sup> <!-- from account/to account -->
						<br><small>(Rate: <?php echo $row['ex_from_rate']; ?> <?php echo substr($row['from_currency'], -4); ?> = <?php echo $row['ex_to_rate']; ?> <?php echo substr($row['to_currency'], -4); ?>)</small>
						<br>
					</td>
					<!--
					<td align="left" valign="middle">
						<?php
								/*switch ($row['payment_type'])
								{
									case "withdrawal":			echo PAYMENT_TYPE_WITHDRAWAL; break;
									case "referral_commission": echo PAYMENT_TYPE_RCOMMISSION; break;
									case "friend_bonus":		echo PAYMENT_TYPE_FBONUS; break;
									case "signup_bonus":		echo PAYMENT_TYPE_SBONUS; break;
									default:					echo $row['payment_type']; break;
								}*/
						?>
					</td>-->							
					<!--<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['user_id']; ?></td>-->
					<td align="center" valign="middle"><?php if ($row['country_code'] != "") { ?><img src="<?php echo SITE_URL; ?>images/flags/<?php echo $row['country_code']; ?>.png" width="16" height="11" /><?php } ?></td>
					<td align="left" valign="middle">
						&nbsp; <?php if ($row['user_id'] > 0) { ?><i class="fa fa-user-circle" aria-hidden="true"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo GetUsername($row['user_id'], $type=2); ?></a><?php }else{ ?><i class="fa fa-user-o" aria-hidden="true"></i> Visitor<?php } ?>
						<br><a href="mailto:<?php echo $row['client_email']; ?>" style="color: #999"><?php echo $row['client_email']; ?></a>
					</td>
					<td align="left" valign="middle" style="padding-left: 5px;">
					<?php
						switch ($row['status'])
					  {
							case "confirmed": echo "<span class='label label-success'>confirmed</span>"; break;
							case "pending": echo "<span class='label label-warning'>payment confirmation</span>"; break;
							case "waiting": echo "<span class='label label-warning'>waiting for payment</span>"; break;
							case "declined": echo "<span class='label label-danger'>declined</span>"; break;
							case "failed": echo "<span class='label label-danger'>failed</span>"; break;
							case "timeout": echo "<span class='label label-danger'>timeout</span>"; break;
							case "request": echo "<span class='label label-warning'>awaiting approval</span>"; break;
							case "paid": echo "<span class='label label-success'>paid</span>"; break;
							default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
						}
					?>
					<?php if ($row['reason'] != "") { ?><span class="note" title="<?php echo $row['reason']; ?>"></span><?php } ?>
					</td>
					<!--<td align="center" valign="middle" nowrap="nowrap">IP</td>-->
					<td align="center" valign="middle" nowrap="nowrap">
						<a href="payment_details.php?id=<?php echo $row['transaction_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="payment_edit.php?id=<?php echo $row['transaction_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this exchange?') )location.href='payments.php?id=<?php echo $row['transaction_id']; ?>&action=delete'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
             <?php } ?>
             	<?php if (@$from_filter || @$to_filter) { ?>
				<tr height="35">
				  <td bgcolor="#EEE" colspan="4" style="border-top: 1px solid #F5F5F5" valign="middle" align="left"><b>TOTAL</b>:</td>
				  <td bgcolor="#EEE" colspan="5" style="border-top: 1px solid #F5F5F5; padding-left: 10px" align="left" valign="middle"><b><?php echo $total_amount; ?></b></td>
				</tr>
				<?php } ?>
				<tr>
				  <td style="border-top: 1px solid #F5F5F5" colspan="9" align="left">
					<input type="submit" class="btn btn-success" name="confirm" id="GoButton1" value="Confirm Selected" disabled="disabled" />
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
							if (@$status_filter)	$params .= "status_filter=$status_filter&";
							//if (@$store)			$params .= "store=$store&";
							//if (@$start_date)		$params .= "start_date=$start_date&";
							//if (@$end_date)			$params .= "end_date=$end_date&";
							if (@$_GET['search'])	$params .= "search=search&";				
							if (@$action)			$params .= "action=$action&";
							if (@$_GET['show'])		$params .= "show=$results_per_page&";
							if (@$_GET['page'])		$params .= "page=$page&";

							echo ShowPagination("transactions",$results_per_page,"payments.php?".$params," WHERE status!='request' $filter_by");
					
				?>
				<input type="hidden" name="params" value="<?php echo $params; ?>" />
			</form>

		 <?php }else{ ?>
				<?php if (isset($filter)) { ?>
					<div class="alert alert-info">Sorry, no exchanges found for your search criteria.</div>
				<?php }else{ ?>
					<div class="alert alert-info">There are currently no exchanges.</div>
				<?php } ?>
        <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>