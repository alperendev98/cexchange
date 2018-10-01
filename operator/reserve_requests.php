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
	require_once("../inc/pagination.inc.php");
	require_once("./inc/admin_funcs.inc.php");

	$cpage = 99;

	CheckAdminPermissions($cpage);

	// confirm ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "confirm")
	{
		$pid = (int)$_GET['id'];
		smart_mysql_query("UPDATE exchangerix_reserve_requests SET status='confirmed' WHERE reserve_request_id='$pid'");
		
		//send email to user //dev
		header("Location: reserve_requests.php?msg=confirmed");
		exit();
	}

	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
	{
		$pid = (int)$_GET['id'];
		smart_mysql_query("DELETE FROM exchangerix_reserve_requests WHERE reserve_request_id='$pid'");
		
		header("Location: reserve_requests.php?msg=deleted");
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
					smart_mysql_query("DELETE FROM exchangerix_reserve_requests WHERE reserve_request_id='$pid'");
				}

				header("Location: reserve_requests.php?msg=deleted");
				exit();
			}
		}

	////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "username": $rrorder = "user_id"; break;
					case "amount": $rrorder = "amount"; break;
					case "currency": $rrorder = "currency_name"; break;
					case "exdirection": $rrorder = "exdirection_id"; break;
					default: $rrorder = "reserve_request_id"; break;
				}
			}
			else
			{
				$rrorder = "reserve_request_id";
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

		$query = "SELECT *, DATE_FORMAT(added, '".DATE_FORMAT." <br><small>%h:%i %p</small>') AS date_created FROM exchangerix_reserve_requests ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM exchangerix_reserve_requests";
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;


	$title = "Reserve Amount Requests";
	require_once ("inc/header.inc.php");

?>

       <h2><i class="fa fa-bell-o" aria-hidden="true"></i> Reserve Amount Requests <?php if ($total > 0) { ?><sup class="badge" style="background: #f6931c"><?php echo number_format($total); ?></sup><?php } ?></h2>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "confirmed": echo "Request has been successfully confirmed"; break;
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
			<option value="currency" <?php if ($_GET['column'] == "currency") echo "selected"; ?>>Currency</option>
			<option value="exdirection" <?php if ($_GET['column'] == "exdirection") echo "selected"; ?>>Exchange Direction</option>
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
			   Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?>&nbsp;
			</td>
			</tr>
			</table>
			</form>

			<form id="form2" name="form2" method="post" action="">
            <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<th width="18%">Date</th>
				<th width="22%"><i class="fa fa-user"></i> Member</th>
				<th width="25%">Currency</th>
				<th width="25%">Amount</th>
				<th width="12%">Status</th>
				<th width="12%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['reserve_request_id']; ?>]" id="id_arr[<?php echo $row['reserve_request_id']; ?>]" value="<?php echo $row['reserve_request_id']; ?>" /></td>
					<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['date_created']; ?></td>
					<td align="left" valign="middle"><h4><?php if ($row['user_id'] > 0) { ?><i class="fa fa-user-circle" aria-hidden="true"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo GetUsername($row['user_id']); ?></a><?php }else{ ?><i class="fa fa-user-o" aria-hidden="true"></i> Visitor<?php } ?></h4>
					<i class="fa fa-envelope-o" aria-hidden="true"></i> <a style="color: #777" href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a>
					<?php if ($row['phone'] != "") { ?><br><i class="fa fa-phone-square" aria-hidden="true"></i> <?php echo $row['phone']; ?><?php } ?>
					<br>
					</td>
					<td align="left" valign="middle">
						<h4 class="text-center"><?php echo GetCurrencyImg($row['currency_id']); ?> <?php echo $row['currency_name']; ?></h4>
						<?php if ($row['exdirection_id'] > 0) { ?>
							<p>Direction: <b><a style="color: #333" href="exdirection_details.php?id=<?php echo $row['exdirection_id']; ?>"><?php echo GetDirectionName($row['exdirection_id']); ?></a></b></p>			
						<?php } ?>						
					</td>
					<td align="center" valign="middle">
						<h4><span style="color: #f6931c"><?php echo $row['amount']; ?></span> <sup><?php echo $row['currency_code']; ?></sup></h4>
					</td>
					<td align="left" valign="middle" style="padding-left: 3px"><?php if ($row['status'] == "pending") { ?><span class="label label-warning"><i class="fa fa-clock-o"></i> <?php echo $row['status']; ?></span><?php }elseif($row['status'] == "confirmed"){ ?><span class="label label-success"><i class="fa fa-check"></i> <?php echo $row['status']; ?></span><?php } ?></td>
					<td align="center" valign="middle" nowrap="nowrap">
						<?php if ($row['status'] == "pending") { ?>
							<a href="reserve_requests.php?id=<?php echo $row['reserve_request_id']; ?>&action=confirm"><i class="fa fa-check fa-lg tooltips" style="color: #53a804" title="Confirm"></i></a>
						<?php } ?>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this request?') )location.href='reserve_requests.php?id=<?php echo $row['reserve_request_id']; ?>&action=delete';"><i class="fa fa-times fa-lg tooltips" style="color: #c34848" title="Delete"></i></a>
					</td>
				  </tr>
				<?php if ($row['comment'] !="") { ?>
				<tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td colspan="2">&nbsp;</td><td colspan="5" valign="top"><div style="width: 99%; background: #eff6f7; padding: 10px 10px 10px 20px; margin: 10px 0; border-radius: 5px;"><i class="fa fa-comment-o" aria-hidden="true"></i> <b>Comment from user:</b><br><?php echo $row['comment']; ?></div></td>
				</tr>
				<?php } ?>				  
			<?php } ?>
				<tr>
					<td style="border-top: 1px solid #F5F5F5" colspan="7" align="left">
						<input type="submit" class="btn btn-danger" name="delete" id="GoButton1" value="Delete Selected" disabled="disabled" onclick="return confirm('Are you sure you really want to delete?')" />
					</td>
				</tr>
            </table>
			</form>

					<?php 
							if (@$_GET['column'])	$params .= "column=".$_GET['column']."&";
							if (@$_GET['order'])	$params .= "order=".$_GET['order']."&";
							if (@$_GET['show'])		$params .= "show=$results_per_page&";
							
							echo ShowPagination("reserve_requests",$results_per_page,"reserve_requests.php?".$params,"WHERE status='request'");
					?>

          <?php }else{ ?>
				<div class="alert alert-info">There are no reserve requests at this time.</div>
          <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>