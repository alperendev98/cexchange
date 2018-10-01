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

	$cpage = 2;

	CheckAdminPermissions($cpage);

	if (isset($_POST['params']) && $_POST['params'] != "") $params = substr($_POST['params'],0,200);

	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
	{
		$userid = (int)$_GET['id'];
		DeleteUser($userid);	
		header("Location: users.php?".$params."msg=deleted");
		exit();
	}

	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 10;

		// Approve users //
		if (isset($_POST['approve']) && $_POST['approve'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$userid = (int)$v;
					ApproveUser($userid);
				}

				header("Location: users.php?".$params."msg=approved");
				exit();
			}	
		}

		// DeActivate users //
		if (isset($_POST['deactivate']) && $_POST['deactivate'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$uid = (int)$v;
					smart_mysql_query("UPDATE exchangerix_users SET status='inactive' WHERE user_id='$uid' LIMIT 1");
				}

				header("Location: users.php?".$params."msg=updated");
				exit();
			}
		}


		// Delete users //
		if (isset($_POST['delete']) && $_POST['delete'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$userid = (int)$v;
					DeleteUser($userid);
				}

				header("Location: users.php?".$params."msg=deleted");
				exit();
			}	
		}

		$where = " WHERE 1=1";

		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "fname": $rrorder = "fname"; break;
					case "lname": $rrorder = "lname"; break;
					case "email": $rrorder = "email"; break;
					case "country": $rrorder = "country"; break;
					
					case "verified_email": $rrorder = "verified_email"; break;
					case "verified_phone": $rrorder = "verified_phone"; break;
					case "verified_document": $rrorder = "verified_document"; break;
					case "verified_address": $rrorder = "verified_address"; break;
					
					case "reg_source": $rrorder = "reg_source"; break;
					case "user_group": $rrorder = "user_group"; break;
					case "ids": $rrorder = "user_id"; break;
					case "status": $rrorder = "status"; break;
					case "ref_id": $rrorder = "ref_id"; break;
					default: $rrorder = "user_id"; break;
				}
			}
			else
			{
				$rrorder = "user_id";
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
			if (isset($_GET['filter']) && $_GET['filter'] != "")
			{
				$filter	= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
				$search_by = "username LIKE '%".$filter."%'";

				switch ($_GET['search_type'])
				{
					case "username": $search_by = "username='".$filter."'"; break;
					case "fullname": if (strstr($filter, " ")) { $nnn = explode(" ",$filter); $search_by = "fname LIKE '%".$nnn[0]."%' AND lname LIKE '%".$nnn[1]."%'"; }else{ $search_by = "fname LIKE '%".$filter."%' OR lname LIKE '%".$filter."%'";}  break;
					case "email": $search_by = "email='".$filter."'"; break;
					//case "reg_source": $search_by = "reg_source='".$filter."'"; break;
					case "ip": $search_by = "ip='".$filter."' OR last_ip='".$filter."'"; break;
				}
				$where .= " AND (".$search_by.")";
				$totitle = " - Search Results";
			}
		///////////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		if (isset($_GET['user_group']) && is_numeric($_GET['user_group']))
		{
			$user_group = (int)$_GET['user_group'];
			$where .= " AND user_group='$user_group' ";
			
			switch ($user_group)
			{
				case "1": $title2 = "Administrator"; break;
				case "2": $title2 = "Moderator"; break;
				case "3": $title2 = "Editor"; break;
			}
		}
		
		if (isset($_GET['only_verifications']) && is_numeric($_GET['only_verifications']))
		{
			$only_verifications = (int)$_GET['only_verifications'];
			$where .= " AND (length(verified_document) > 10 OR length(verified_document) > 10) ";
			
			$title2 = "Waiting for document verification";
		}		

		// hide other admins and moderators from non superadmin
		if (!isSuperAdmin())
		{
			$where .= " AND user_group='0' ";
		}

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." <br><small>%h:%i %p</small>') AS signup_date FROM exchangerix_users $where ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM exchangerix_users".$where;
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;

		$title = "Users";

		require_once ("inc/header.inc.php");

?>

       <div id="addnew">
			<i class="fa fa-user-o" style="color: #f39425"></i> <a href="users.php?only_verifications=1" style="margin-right: 10px">Verification Requests <?php if (GetVerificationRequestsTotal() > 0) { ?><sup class="badge" style="background: #f39425"><?php echo GetVerificationRequestsTotal(); ?></sup><?php } ?></a>
			<?php if ($total > 0) { ?>
				<a class="export tooltips" href="xls_export.php?action=export_users" title="Export to Excel" style="margin-right: 10px">Export</a>
			<?php } ?>
			<a class="addnew" href="user_add.php">Add User</a>
       </div>

       <h2><i class="fa fa-users" aria-hidden="true"></i> <?php echo @$title2; ?> Users <?php echo @$totitle; ?> <?php if ($total > 0) { ?><sup class="badge" style="background: #73b9d1"><?php echo number_format($total); ?></sup><?php } ?></h2>

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success">
			<?php
					switch ($_GET['msg'])
					{
						case "added": echo "User has been successfully added"; break;
						case "updated": echo "User information has been successfully edited"; break;
						case "approved": echo "Users have been successfully approved"; break;
						case "deleted": echo "User has been successfully deleted"; break;
					}
			?>
		</div>
		<?php } ?>

		<form id="form1" name="form1" method="get" action="" class="form-inline">
		<div class="row" style="background:#F9F9F9; margin: 10px 0; padding: 7px 0;">
		<div class="col-md-5" style="white-space: nowrap">
           Sort by: 
          <select name="column" id="column" class="form-control" onChange="document.form1.submit()">
			<option value="ids" <?php if ($_GET['column'] == "ids") echo "selected"; ?>>Signup Date</option>
			<option value="fname" <?php if ($_GET['column'] == "fname") echo "selected"; ?>>First Name</option>
			<option value="lname" <?php if ($_GET['column'] == "lname") echo "selected"; ?>>Last Name</option>
			<option value="email" <?php if ($_GET['column'] == "email") echo "selected"; ?>>Email</option>
			<option value="country" <?php if ($_GET['column'] == "country") echo "selected"; ?>>Country</option>
			
			<option value="verified_email" <?php if ($_GET['column'] == "verified_email") echo "selected"; ?>>Email Verified</option>
			<option value="verified_document" <?php if ($_GET['column'] == "verified_document") echo "selected"; ?>>Document Verified</option>
			<option value="verified_phone" <?php if ($_GET['column'] == "verified_phone") echo "selected"; ?>>Phone Verified</option>
			<option value="verified_address" <?php if ($_GET['column'] == "verified_address") echo "selected"; ?>>Address Verified</option>
			
			<option value="reg_source" <?php if ($_GET['column'] == "reg_source") echo "selected"; ?>>Signup Source</option>
			<option value="user_group" <?php if ($_GET['column'] == "user_group") echo "selected"; ?>>User Role</option>
			<!--<option value="ref_id" <?php if ($_GET['column'] == "ref_id") echo "selected"; ?>>Referral</option>-->
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
          </select>
          <select name="order" id="order" class="form-control" onChange="document.form1.submit()">
			<option value="desc" <?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>
		  <span class="hidden-xs"><!--&nbsp;&nbsp;Results:--></span>
          <select name="show" id="show" class="form-control" onChange="document.form1.submit()">
			<option value="10" <?php if ($_GET['show'] == "10") echo "selected"; ?>>10</option>
			<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
			<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
			<option value="111111111" <?php if ($_GET['show'] == "111111111") echo "selected"; ?>>ALL</option>
          </select>
		</div>
		<div class="col-md-5 text-center" style="white-space: nowrap">
				<div class="admin_filter">
					<input type="text" name="filter" value="<?php echo $filter; ?>" placeholder="Enter keyword" class="form-control" size="20" required="required" />
					<select name="search_type" class="form-control">
					 <option value="username" <?php if ($_GET['search_type'] == "username") echo "selected"; ?>>Username</option>
					 <option value="fullname" <?php if ($_GET['search_type'] == "fullname") echo "selected"; ?>>Name</option>
					 <option value="email" <?php if ($_GET['search_type'] == "email") echo "selected"; ?>>Email</option>
					 <option value="ip" <?php if ($_GET['search_type'] == "ip") echo "selected"; ?>>IP Address</option>
					</select>
					<?php if (isset($_GET['search'])) { ?><input type="hidden" name="search" value="search" /><?php } ?>
					<input type="submit" class="btn btn-success" value="Search" />
					<?php if (isset($filter) && $filter != "") { ?><a title="Cancel Search" href="users.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Search" /></a><?php } ?> 
				</div>
		</div>
		<div class="col-md-2 text-right" style="white-space: nowrap; padding-top: 8px;">
			   <?php if ($total > 0) { ?><?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
		</div>
		</div>
		</form>


        <?php if ($total > 0) { ?>

			<form id="form2" name="form2" method="post" action="">
			<div class="table-responsive">
            <table align="center" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr bgcolor="#DCEAFB" align="center">
				<th width="3%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<th width="10%">User ID</th>
				<th width="28%">Name</th>
				<th width="20%">Email / Phone</th>
				<th width="10%"><i class="fa fa-users"></i> Referrals<!--Country--></th>
				<!--<th width="8%">IP</th>
				<th width="10%">Balance</th>-->
				<th width="8%"><i class="fa fa-refresh"></i> Exchanges</th>
				<th width="11%">Signup Date</th>
				<th width="12%">Status</th>
				<th width="12%">Actions</th>
			</tr>
			 <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
			 <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['user_id']; ?>]" id="id_arr[<?php echo $row['user_id']; ?>]" value="<?php echo $row['user_id']; ?>" /></td>
					<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['user_id']; ?></td>
					<td align="left" valign="middle" style="padding: 8px 0;" nowrap>
						<a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a>
						<?php if ($row['user_group'] == 1) { ?><sup style="background: #68d4dd; color: #FFF; padding: 1px 3px; border-radius: 3px;">admin</sup><?php } ?>
						<?php if ($row['user_group'] == 2) { ?><sup style="background: #a3b7ba; color: #FFF; padding: 1px 3px; border-radius: 3px;"><i class="fa fa-user-circle-o" aria-hidden="true"></i> operator</sup><?php } ?>
						<br>
						<?php if ($row['verified_email'] == 1) { ?>
							<i id="itooltip" title="Email Verified" class="fa fa-check-circle-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
						<?php }else{ ?>
							<i id="itooltip" title="Email Not Verified" class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
						<?php } ?>						
						<?php if ($row['verified_document'] == 1) { ?>
							<i id="itooltip" title="Document Verified" class="fa fa-check-circle-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
						<?php }elseif (strlen($row['verified_document']) > 10) { ?>
							<i id="itooltip" title="Document uploaded and waiting for admin verification" class="fa fa-clock-o fa-lg" aria-hidden="true" style="color: #f39425"></i>
						<?php }else{ ?>
							<i id="itooltip" title="Document Not Verified" class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
						<?php } ?>
						<?php if ($row['verified_phone'] == 1) { ?>
							<i id="itooltip" title="Phone Number Verified" class="fa fa-check-circle-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
						<?php }elseif ($row['verified_phone'] == 0 && $row['sms_code'] != "") { ?>
							<i id="itooltip" title="SMS sent, waiting for user's confirmation" class="fa fa-clock-o fa-lg" aria-hidden="true" style="color: #f39425"></i>
						<?php }else{ ?>
							<i id="itooltip" title="Phone Number Not Verified" class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
						<?php } ?>
						<?php if ($row['verified_address'] == 1) { ?>
							<i id="itooltip" title="Address Verified" class="fa fa-check-circle-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
						<?php }elseif (strlen($row['verified_address']) > 10) { ?>
							<i id="itooltip" title="Proof uploaded and waiting for admin verification" class="fa fa-clock-o fa-lg" aria-hidden="true" style="color: #f39425"></i>
						<?php }else{ ?>
							<i id="itooltip" title="Address Not Verified" class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
						<?php } ?>
					</td>
					<td align="left" valign="middle" style="padding-left: 10px">
						<a href="email2users.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['email']; ?></a>
						<?php /*if ($row['verified_email'] == 1) { ?> <sup style="background: #37950f; color: #FFF; padding: 1px 3px; margin-left: 5px; border-radius: 3px;">verified</sup><?php }*/ ?>
						<?php if ($row['phone'] != "") { ?><br><span style="color: #585858"><i class="fa fa-phone-square"></i> <?php echo $row['phone']; ?></span><?php } ?>
					</td>
					<td align="center" valign="middle" nowrap="nowrap"><a href="user_referrals.php?id=<?php echo $row['user_id']; ?>"><span style="color: #FFF" class="badge"><?php echo GetReferralsTotal($row['user_id']); ?></span></a><?php //echo GetCountry($row['country'], $show_only_icon = 1); ?></td>
					<!--<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['ip']; ?></td>
					<td align="left" valign="middle" style="padding-left: 10px;"><a href="user_payments.php?id=<?php echo $row['user_id']; ?>"><?php //echo GetUserBalance($row['user_id']); ?></a></td>-->
					<td align="center" valign="middle" nowrap="nowrap">
						<!--<a class="badge" style="background: #89b601" href="user_payments.php?id=<?php echo $row['user_id']; ?>">-->
						<a class="badge" style="background: #89b601" href="exchanges.php?filter=<?php echo $row['user_id']; ?>&search_type=member&action=filter">
							<?php echo GetUserExchangesTotal($row['user_id']); ?>
						</a>
					</td>
					<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['signup_date']; ?></td>
					<td align="left" valign="middle" style="padding-left: 20px"><?php echo ($row['status'] == "inactive") ? "<span class='label label-default'>inactive</span>" : "<span class='label label-success'>active</span>"; ?></td>
					<td align="center" valign="middle" nowrap="nowrap">
						<a href="user_details.php?id=<?php echo $row['user_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="user_edit.php?id=<?php echo $row['user_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this user?') )location.href='users.php?id=<?php echo $row['user_id']; ?>&action=delete';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
			</tr>
			<?php } ?>
			<tr>
				<td style="border-top: 1px solid #F5F5F5" colspan="10" align="left">
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="submit" class="btn btn-success" name="approve" id="GoButton1" value="Activate" disabled="disabled" />
					<input type="submit" class="btn btn-warning" name="deactivate" id="GoButton1" value="Deactivate" disabled="disabled" />
					<input type="submit" class="btn btn-danger" name="delete" id="GoButton2" value="Delete Selected" disabled="disabled" onclick="return confirm('Are you sure you really want to delete?')" />
				</td>
			</tr>
            </table>
			</div>

					<?php
							
							$params = "";

							if (@$_GET['column'])		$params .= "column=".$_GET['column']."&";
							if (@$_GET['order'])		$params .= "order=".$_GET['order']."&";
							if (@$user_group)			$params .= "user_group=$user_group&";
							if (@$only_verifications)	$params .= "only_verifications=$only_verifications&";
							if (@$filter)				$params .= "filter=$filter&";
							if (@$_GET['search_type'])	$params .= "search_type=".$_GET['search_type']."&";
							if (@$end_date)				$params .= "end_date=$end_date&";
							if (@$_GET['search'])		$params .= "search=search&";				
							if (@$action)				$params .= "action=$action&";
							if (@$_GET['show'])			$params .= "show=$results_per_page&";
							if (@$_GET['page'])			$params .= "page=$page&";

							echo ShowPagination("users",$results_per_page,"users.php?".$params,$where);
							
					?>
				<input type="hidden" name="params" value="<?php echo $params; ?>" />
			</form>

        <?php }else{ ?>
				<?php if (isset($filter)) { ?>
					<div class="alert alert-info">No user found for your search criteria.</div>
				<?php }else{ ?>
					<div class="alert alert-info">There are no users at this time.</div>
				<?php } ?>
        <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>