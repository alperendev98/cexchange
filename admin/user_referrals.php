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

	$cpage = 2;

	CheckAdminPermissions($cpage);

	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0)
	{
		$uid = (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS signup_date FROM exchangerix_users WHERE ref_id='$uid' ORDER BY created DESC";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
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

				header("Location: user_referrals.php?id=$uid&msg=deleted");
				exit();
			}	
		}

	$title = "User Referrals";
	require_once ("inc/header.inc.php");

?>

		<div id="addnew"><b>Referral Link</b>: <?php echo SITE_URL; ?>?ref=<?php echo $uid; ?></div>

		<h2><?php echo GetUsername($uid); ?> Referrals <?php echo ($total > 0) ? "(".$total.")" : ""; ?></h2>

		<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success">
			<?php
					switch ($_GET['msg'])
					{
						case "updated": echo "User information has been successfully edited"; break;
						case "approved": echo "Users have been successfully approved"; break;
						case "deleted": echo "Users has been successfully deleted"; break;
					}
			?>
		</div>
		<?php } ?>

	  <?php if ($total > 0) { ?>

            <form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="3%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<th width="10%">User ID</th>
				<th width="22%">Name</th>
				<th width="22%">Username</th>
				<th width="11%">Balance</th>
                <th width="11%">Country</th>
				<th width="17%">Signup Date</th>
                <th width="19%">Status</th>
              </tr>
				<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr height="25" class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
                  <td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['user_id']; ?>]" id="id_arr[<?php echo $row['user_id']; ?>]" value="<?php echo $row['user_id']; ?>" /></td>
				  <td valign="middle" align="center"><?php echo $row['user_id']; ?></td>
                  <td valign="middle" align="left"><a class="user" href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['fname']." ".$row['lname']; ?></a></td>
				  <td valign="middle" align="left"><?php echo $row['username']; ?></td>
				  <td valign="middle" align="left" style="padding-left: 15px"><?php echo GetUserBalance($row['user_id']); ?></td>
				  <td valign="middle" align="center"><?php echo GetCountry($row['country'], 1); ?></td>
                  <td  valign="middle" align="center"><?php echo $row['signup_date']; ?></td>
				  <td valign="middle" align="left" style="padding-left: 5px">
					<?php if ($row['status'] == "inactive") echo "<span class='inactive_s'>".$row['status']."</span>"; else echo "<span class='active_s'>".$row['status']."</span>"; ?>
				  </td>
                </tr>
				<?php } ?>
			<tr>
				<td style="border-top: 1px solid #F5F5F5" colspan="8" align="left">
					<input type="submit" class="btn btn-danger" name="delete" id="GoButton2" value="Delete Selected" disabled="disabled" onclick="return confirm('Are you sure you really want to delete?')" />
				</td>
			</tr>
           </table>
		   </form>

			<br/>
			<h3>Referrals Tree</h3>
			<div style="width: 99%; background: #F9F9F9; padding: 10px 5px;">
			<ul style="line-height: 20px">
				<li style="list-style: none">
					<a class="user" href="user_details.php?id=<?php echo $uid; ?>"><b><?php echo GetUsername($uid); ?></b></a>
					<?php ShowReferralsTree($uid); ?>
				</li>
			</ul>
			</div>

	  <?php }else{ ?>
			<div class="alert alert-info">User has not received any referrals at this time.</div>
      <?php } ?>

	  <p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>

<?php require_once ("inc/footer.inc.php"); ?>