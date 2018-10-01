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

	$cpage = 10;

	CheckAdminPermissions($cpage);

	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
	{
		$pid = (int)$_GET['id'];
		DeletePayment($pid);
		header("Location: cashout_requests.php?msg=deleted");
		exit();
	}

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

				header("Location: cashout_requests.php?msg=deleted");
				exit();
			}
		}

	////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "username": $rrorder = "u.username"; break;
					case "amount": $rrorder = "t.amount"; break;
					case "ids": $rrorder = "t.transaction_id"; break;
					case "payment_method": $rrorder = "t.payment_method"; break;
					default: $rrorder = "t.transaction_id"; break;
				}
			}
			else
			{
				$rrorder = "t.transaction_id";
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
	///////////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT t.*, DATE_FORMAT(t.created, '".DATE_FORMAT."') AS date_created, u.username, u.email FROM exchangerix_transactions t, exchangerix_users u WHERE t.status='request' AND t.user_id=u.user_id ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM exchangerix_transactions WHERE status='request'";
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;


	$title = "Withdrawal Requests";
	require_once ("inc/header.inc.php");

?>

		<div id="addnew">
			<?php if ($total > 0) { ?>
				<a class="export" href="xls_export.php?action=export<?php if (isset($filter) && $filter != "") echo "&filter=".$filter; if (isset($start_date) && $start_date != "") echo "&start_date=".$start_date; if (isset($end_date) && $end_date != "") echo "&end_date=".$end_date; ?>&type=withdraw" title="Export to Excel">Export</a>
			<?php } ?>
		</div>

       <h2>Withdrawal Requests</h2>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "processed": echo "Payment has been successfully processed"; break;
						case "updated": echo "Payment has been successfully edited"; break;
						case "deleted": echo "Request has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

       <?php if ($total > 0) { ?>

		<form id="form1" name="form1" method="get" action="">
		<table style="background:#F9F9F9" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
		<td valign="middle" align="left" width="50%">
           Sort by: 
          <select name="column" id="column" class="form-control" onChange="document.form1.submit()">
			<option value="ids" <?php if ($_GET['column'] == "ids") echo "selected"; ?>>Date</option>
			<option value="username" <?php if ($_GET['column'] == "username") echo "selected"; ?>>Member</option>
			<option value="amount" <?php if ($_GET['column'] == "amount") echo "selected"; ?>>Amount</option>
			<option value="payment_method" <?php if ($_GET['column'] == "payment_method") echo "selected"; ?>>Payment Method</option>
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
            </form>
			</td>
			<td valign="middle" width="45%" align="right">
			   Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?>
			</td>
			</tr>
			</table>
			</form>

			<form id="form2" name="form2" method="post" action="">
            <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<th width="12%">Reference ID</th>
				<th width="22%">Member</th>
				<th width="12%">User Balance</th>
				<th width="10%">Amount</th>
				<th width="10%">Commission</th>
				<th width="15%">Payment Method</th>
				<th width="10%">Request Date</th>
				<th width="5%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['transaction_id']; ?>]" id="id_arr[<?php echo $row['transaction_id']; ?>]" value="<?php echo $row['transaction_id']; ?>" /></td>
					<td align="center" valign="middle" nowrap="nowrap"><a href="payment_details.php?id=<?php echo $row['transaction_id']; ?>"><?php echo $row['reference_id']; ?></a></td>
					<td align="left" valign="middle"><a href="user_details.php?id=<?php echo $row['user_id']; ?>" class="user"><?php echo GetUsername($row['user_id']); ?></a></td>
					<td  align="left" valign="middle"><?php echo GetUserBalance($row['user_id']); ?></td>
					<td align="left" valign="middle"><?php echo DisplayMoney($row['amount']); ?></td>
					<td align="left" valign="middle"><?php echo ($row['transaction_commision'] != "0.0000") ? DisplayMoney($row['transaction_commision']) : "---"; ?></td>
					<td align="left" valign="middle">
						<?php if ($row['payment_method'] == 1) { ?><img src="images/paypal.png" align="absmiddle" />&nbsp;<?php }else{ ?>
							<?php echo GetPaymentMethodByID($row['payment_method']); ?>
						<?php } ?>
					</td>
					<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['date_created']; ?></td>
					<td align="center" valign="middle" nowrap="nowrap">
						<a title="Proceed" href="payment_process.php?id=<?php echo $row['transaction_id']; ?>"><img src="images/proceed.png" border="0" alt="Proceed" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this cashout request?') )location.href='cashout_requests.php?id=<?php echo $row['transaction_id']; ?>&action=delete';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
					<td style="border-top: 1px solid #F5F5F5" colspan="9" align="left">
						<input type="submit" class="btn btn-danger" name="delete" id="GoButton1" value="Delete Selected" disabled="disabled" onclick="return confirm('Are you sure you really want to delete?')" />
					</td>
				</tr>
            </table>
			</form>

					<?php 
							if (@$_GET['column'])	$params .= "column=".$_GET['column']."&";
							if (@$_GET['order'])	$params .= "order=".$_GET['order']."&";
							if (@$_GET['show'])		$params .= "show=$results_per_page&";
							
							echo ShowPagination("transactions",$results_per_page,"cashout_requests.php?".$params,"WHERE status='request'");
					?>

          <?php }else{ ?>
				<div class="alert alert-info">There are no cash out requests at this time.</div>
          <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>