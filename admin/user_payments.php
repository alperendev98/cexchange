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
	require_once("../inc/pagination.inc.php");

	$cpage = 2;

	CheckAdminPermissions($cpage);

	if (isset($_GET['id']) && is_numeric($_GET['id'])) $uid = (int)$_GET['id'];

	// delete ////////////////////////////////////////
	if (isset($_GET['pid']) && is_numeric($_GET['pid']) && $_GET['action'] == "delete")
	{
		$uid = (int)$_GET['id'];
		$pid = (int)$_GET['pid'];
		DeletePayment($pid);
		header("Location: user_payments.php?id=$uid&msg=deleted");
		exit();
	}

	$cc = 0;

	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 15;


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

				header("Location: user_payments.php?id=$uid&msg=deleted");
				exit();
			}
		}

	$where = " 1=1 AND ";
	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "created": $rrorder = "created"; break;
					case "transaction_id": $rrorder = "transaction_id"; break;
					case "reference_id": $rrorder = "reference_id"; break;
					case "payment_type": $rrorder = "payment_type"; break;
					case "amount": $rrorder = "amount"; break;
					case "status": $rrorder = "status"; break;
					default: $rrorder = "added"; break;
				}
			}
			else
			{
				$rrorder = "created";
			}

			if (isset($_GET['order']) && $_GET['order'] != "")
			{
				switch ($_GET['order'])
				{
					case "asc": $rorder = "asc"; break;
					case "desc": $rorder = "desc"; break;
					default: $rorder = "desc"; break;
				}
			}
			else
			{
				$rorder = "desc";
			}
			if (isset($_GET['filter']) && $_GET['filter'] != "")
			{
				$filter	= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
				$where .= " (reference_id='$filter') AND ";
			}
			if (isset($_GET['only_exchanges']) && $_GET['only_exchanges'] == 1)
			{
				$where .= " payment_type='exchange' AND ";
			}
		///////////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$uid = (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS date_created FROM exchangerix_exchanges WHERE $where user_id='$uid' ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM exchangerix_exchanges WHERE $where user_id='$uid'";
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cash_row = mysqli_fetch_array(smart_mysql_query("SELECT SUM(receive_amount) AS total FROM exchangerix_exchanges WHERE $where user_id='".(int)$uid."' AND status='confirmed' OR status='pending'"));

		$title2 = GetUsername($uid);
	}

		$title = $title2." Payment History";
		require_once ("inc/header.inc.php");

?>

	<div id="addnew">
		Account Balance: <span style="background: #59d80f; color: #FFFFFF; padding: 2px 8px; border-radius: 3px;"><b><?php echo GetUserBalance($uid); ?></b></span> &nbsp;&nbsp;&nbsp;
	</div>

	<h2><i class="fa fa-user"></i> <a href="user_details.php?id=<?php echo $uid; ?>"><?php echo $title2; ?></a> - Payment History</h2>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "processed": echo "Payment has been successfully processed"; break;
						case "updated": echo "Payment has been successfully updated"; break;
						case "confirmed": echo "Payments have been successfully confirmed"; break;
						case "deleted": echo "Payment has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

	  <?php if ($total > 0) { $total_amount = 0; ?>

			<form id="form1" name="form1" method="get" action="">
			<table style="background:#F9F9F9" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
			<td  width="65%" valign="middle" align="left">
           Sort by: 
          <select name="column" id="column" class="form-control" onChange="document.form1.submit()">
			<option value="created" <?php if ($_GET['column'] == "created") echo "selected"; ?>>Date</option>
			<option value="reference_id" <?php if ($_GET['column'] == "reference_id") echo "selected"; ?>>Reference ID</option>
			<option value="payment_type" <?php if ($_GET['column'] == "payment_type") echo "selected"; ?>>Payment Type</option>
			<option value="amount" <?php if ($_GET['column'] == "amount") echo "selected"; ?>>Amount</option>
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
          </select>
          <select name="order" id="order" class="form-control" onChange="document.form1.submit()">
			<option value="desc" <?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>
		  &nbsp;&nbsp;Results:  
          <select name="show" id="show" class="form-control" onChange="document.form1.submit()">
			<option value="15" <?php if ($_GET['show'] == "15") echo "selected"; ?>>15</option>
			<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
			<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
			<option value="111111111" <?php if ($_GET['show'] == "111111111") echo "selected"; ?>>ALL</option>
          </select>
          <!--
		  &nbsp;&nbsp;&nbsp;<input type="checkbox" class="checkbox" name="only_exchanges" id="only_exchanges" value="1" <?php echo (@$_GET['only_exchanges'] == 1) ? "checked" : ""; ?> onChange="this.form.submit()" /> Show only exchange payments-->
			<?php if ($uid) { ?><input type="hidden" name="id" value="<?php echo $uid; ?>" /><?php } ?>
			</td>
			<td  width="35%" valign="middle" align="right">
			   Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?>
			</td>
			</tr>
			</table>
			</form>

            <form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" class="brd" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="3%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<th width="17%">Date</th>
				<th width="15%">Reference ID</th>
                <th width="12%">Payment Type</th>
				<th width="35%">Amount</th>
				<th width="13%">Status</th>
				<th width="10%">Actions</th>
              </tr>
				<?php while ($row = mysqli_fetch_array($result)) { $cc++; $total_amount +=$row['amount']; ?>
                <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
                  <td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['transaction_id']; ?>]" id="id_arr[<?php echo $row['transaction_id']; ?>]" value="<?php echo $row['transaction_id']; ?>" /></td>
                  <td valign="middle" align="center"><?php echo $row['date_created']; ?></td>
                  <td valign="middle" align="center"><a href="payment_details.php?id=<?php echo $row['transaction_id']; ?>"><?php echo $row['reference_id']; ?></a></td>
                  <td valign="middle" align="left" style="padding-left: 10px;">
						<?php
								switch ($row['payment_type'])
								{
									case "withdrawal":			echo PAYMENT_TYPE_WITHDRAWAL; break;
									case "referral_commission": echo PAYMENT_TYPE_RCOMMISSION; break;
									default:					echo "Exchange"; break; //$row['payment_type']; break;
								}
						?>				
				  </td>
				  <td valign="middle" align="left" style="padding-left: 5px;">
					<?php if (strstr($row['amount'], "-")) $pcolor = "#DD0000"; else $pcolor = "#000000"; ?>
					<span style="color: <?php echo $pcolor; ?>"><?php echo floatval($row['exchange_amount'])." ".$row['from_currency']; ?> <i class="fa fa-long-arrow-right fa-lg" aria-hidden="true"></i> <?php echo floatval($row['receive_amount'])." ".$row['to_currency']; ?></span>				  
				  </td>
                  <td valign="middle" align="center" style="padding-left: 10px;">
							<?php
									switch ($row['status'])
								   {
										case "confirmed": echo "<span class='label label-success'>confirmed</span>"; break;
										case "pending": echo "<span class='label label-warning'>awaiting confirmation</span>"; break;
										case "waiting": echo "<span class='label label-default'>waiting for payment</span>"; break;
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
					<td align="center" valign="middle" nowrap="nowrap">
						<a href="payment_details.php?id=<?php echo $row['transaction_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<!--
						<a href="payment_edit.php?id=<?php echo $row['transaction_id']; ?>&page=payments" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						-->
						<?php if ($row['status'] == "request") { ?>
							<a title="Proceed" href="payment_process.php?id=<?php echo $row['transaction_id']; ?>"><img src="images/proceed.png" border="0" alt="Proceed" /></a>
						<?php } ?>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this payment?') )location.href='user_payments.php?pid=<?php echo $row['transaction_id']; ?>&id=<?php echo $uid; ?>&action=delete';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
                </tr>
				<?php } ?>
				<!--
				<tr height="25">
				  <td bgcolor="#EEE" colspan="4" style="border-top: 1px solid #F5F5F5" valign="middle" align="left"><b>TOTAL:</b></td>
				  <td bgcolor="#EEE" colspan="3" style="border-top: 1px solid #F5F5F5" align="left" valign="middle"><b><?php echo DisplayMoney($total_amount); ?></b></td>
				</tr>
				-->
				<tr>
				  <td style="border-top: 1px solid #F5F5F5" colspan="7" align="left">
					<input type="hidden" name="id" value="<?php echo $uid; ?>" />
					<input type="submit" class="btn btn-danger" name="delete" id="GoButton2" value="Delete Selected" disabled="disabled" onclick="return confirm('Are you sure you really want to delete?')" />
				  </td>
				</tr>
				</table>
				</form>

					<?php echo ShowPagination("exchanges",$results_per_page,"user_payments.php?id=$uid&column=$rrorder&order=$rorder&show=$results_per_page&", "WHERE $where user_id='$uid'"); ?>

	  <?php }else{ ?>
			<div class="alert alert-info">There are no exchanges at this time.</div>
      <?php } ?>

	  <p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='user_details.php?id=<?php echo $uid; ?>'" /></p>

<?php require_once ("inc/footer.inc.php"); ?>