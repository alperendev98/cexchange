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

	$cpage = 20;

	CheckAdminPermissions($cpage);

	// add ////////////////////////////////////////
	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
		unset($errs);
		$errs = array();

		$pmethod_title		= mysqli_real_escape_string($conn, getPostParameter('pmethod_title'));
		//$min_amount		= mysqli_real_escape_string($conn, getPostParameter('min_amount')); //dev
		//$account_id		= mysqli_real_escape_string($conn, getPostParameter('account_id'));
		//$account_key		= mysqli_real_escape_string($conn, getPostParameter('account_key'));
		$commission			= mysqli_real_escape_string($conn, getPostParameter('commission'));
		$commission_sign	= mysqli_real_escape_string($conn, getPostParameter('commission_sign'));
		$pmethod_details	= mysqli_real_escape_string($conn, nl2br(getPostParameter('pmethod_details')));

		if(!($pmethod_title && $pmethod_details))
		{
			$errs[] = "Please fill in all required fields";
		}
		else
		{
			if ($min_amount && !is_numeric($min_amount))
				$errs[] = "Please enter correct min payment value";

			if ($commission && !is_numeric($commission))
				$errs[] = "Please enter correct commission value";

			if (isset($commission) && is_numeric($commission))
			{
				switch ($commission_sign)
				{
					case "currency":	$commission_sign = ""; break;
					case "%":			$commission_sign = "%"; break;
				}
				$commission = $commission.$commission_sign;
			}
			else
			{
				$commission = "";
			}

			$check_query = smart_mysql_query("SELECT * FROM exchangerix_pmethods WHERE pmethod_title='$pmethod_title'");
			if (mysqli_num_rows($check_query) != 0)
			{
				$errs[] = "Sorry, payment method exists";
			}
		}

		if (count($errs) == 0)
		{
			$sql = "INSERT INTO exchangerix_pmethods SET pmethod_title='$pmethod_title', min_amount='$min_amount', commission='$commission', pmethod_details='$pmethod_details', status='active'";

			if (smart_mysql_query($sql))
			{
				header("Location: payment_methods.php?msg=added");
				exit();
			}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}
	}


	// edit ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$pmid = (int)$_GET['id'];

		$query = "SELECT * FROM exchangerix_pmethods WHERE pmethod_id='$pmid' LIMIT 1";
		$result = smart_mysql_query($query);
		$res_total = mysqli_num_rows($result);

		if ($res_total > 0)
		{
			$row = mysqli_fetch_array($result);
		}
	}


	if (isset($_POST['action']) && $_POST['action'] == "edit")
	{
		unset($errs);
		$errs = array();

		$pmethod_id			= (int)getPostParameter('pmethodid');
		$pmethod_title		= mysqli_real_escape_string($conn, getPostParameter('pmethod_title'));
		//$min_amount		= mysqli_real_escape_string($conn, getPostParameter('min_amount')); //dev
		//$account_id		= mysqli_real_escape_string($conn, getPostParameter('account_id'));
		//$account_key		= mysqli_real_escape_string($conn, getPostParameter('account_key'));
		$commission			= mysqli_real_escape_string($conn, getPostParameter('commission'));
		$commission_sign	= mysqli_real_escape_string($conn, getPostParameter('commission_sign'));
		$pmethod_details	= mysqli_real_escape_string($conn, nl2br(getPostParameter('pmethod_details')));
		$status				= mysqli_real_escape_string($conn, getPostParameter('status'));

		if(!($pmethod_title && $pmethod_details && $status))
		{
			$errs[] = "Please fill in all required fields";
		}
		else
		{
			if ($min_amount && !is_numeric($min_amount))
				$errs[] = "Please enter correct min payment value";

			if ($commission && !is_numeric($commission))
				$errs[] = "Please enter correct commission value";
		
			if (isset($commission) && is_numeric($commission))
			{
				switch ($commission_sign)
				{
					case "currency":	$commission_sign = ""; break;
					case "%":			$commission_sign = "%"; break;
				}
				$commission = $commission.$commission_sign;
			}
			else
			{
				$commission = "";
			}	
		}

		if (count($errs) == 0)
		{	
			$sql = "UPDATE exchangerix_pmethods SET pmethod_title='$pmethod_title', min_amount='$min_amount', pmethod_details='$pmethod_details', commission='$commission', status='$status' WHERE pmethod_id='$pmethod_id' LIMIT 1";

			if (smart_mysql_query($sql))
			{
				header("Location: payment_methods.php?msg=updated");
				exit();
			}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}
	}


	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
	{
		$pmid = (int)$_GET['id'];
		smart_mysql_query("DELETE FROM exchangerix_pmethods WHERE pmethod_id='$pmid'");
		header("Location: payment_methods.php?msg=deleted");
		exit();
	}

	$query = "SELECT * FROM exchangerix_pmethods ORDER BY status";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	$cc = 0;


	$title = "Payment Methods";
	require_once ("inc/header.inc.php");

?>

	<div id="add_new_form" style="display: <?php echo ($_POST['action'] && !$_GET['id']) ? "" : "none"; ?>">
        <h2>Add Payment Method</h2>
		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>
        <form action="" method="post">
          <table width="100%" style="background:#F9F9F9" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td width="35%"  valign="middle" align="right" class="tb1"><span class="req">* </span>Name:</td>
            <td valign="top"><input type="text" name="pmethod_title" id="pmethod_title" value="<?php echo getPostParameter('pmethod_title'); ?>" size="47" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">
				<span class="req">* </span>Payment Details:
				<a href="#" title="Payment Details" data-toggle="popover" data-trigger="hover" data-content="User will need to provide this information to complete the money transfer">(?)</a>
			</td>
            <td valign="top"><textarea name="pmethod_details" cols="45" rows="7" class="form-control"><?php echo getPostParameter('pmethod_details'); ?></textarea></td>
          </tr>
		  <!--
          <tr>
            <td valign="middle" align="right" class="tb1">Min Payment:</td>
            <td valign="top"><?php echo (SITE_CURRENCY_FORMAT <= 3) ? SITE_CURRENCY : ""; ?><input type="text" name="min_amount" id="min_amount" value="<?php echo getPostParameter('min_amount'); ?>" size="6" class="form-control" /> <?php echo (SITE_CURRENCY_FORMAT > 3) ? SITE_CURRENCY : ""; ?></td>
          </tr>
		  -->
          <tr>
            <td valign="middle" align="right" class="tb1">Commission:</td>
            <td valign="top">
				<input type="text" name="commission" id="commission" value="" size="5" class="form-control" />
				<select name="commission_sign" class="selectpicker">
					<option value="%" <?php if ($commission_sign == "%") echo "selected='selected'"; ?>>%</option>
					<option value="currency" <?php if ($commission_sign == "currency") echo "selected='selected'"; ?>><?php echo SITE_CURRENCY; ?></option>
				</select>
				<span class="note" title="commission per transaction"></span></td>
          </tr>
          <tr>
            <td align="center" valign="middle" nowrap="nowrap">&nbsp;</td>
			<td align="left" valign="middle">
				<input type="hidden" name="action" id="action" value="add" />
				<input type="submit" name="add" id="add" class="btn btn-success" value="Add Payment Method" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='payment_methods.php'" />
            </td>
          </tr>
        </table>
      </form>
	</div>


	<div id="edit_form" style="display: <?php echo ((isset($_POST['action']) && $_POST['action'] != "add") || $_GET['id']) ? "" : "none"; ?>">
        <h2><?php echo $row['pmethod_title']; ?></h2>
		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>
		<?php if ($res_total > 0) { ?>
        <form action="" method="post">
          <table width="100%" style="background:#F9F9F9" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td width="35%"  valign="middle" align="right" class="tb1"><span class="req">* </span>Name:</td>
            <td valign="top"><input type="text" name="pmethod_title" id="pmethod_title" value="<?php echo $row['pmethod_title']; ?>" size="47" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">
				<span class="req">* </span>Payment Details:
				<a href="#" title="Payment Details" data-toggle="popover" data-trigger="hover" data-content="User will need to provide this information to complete the money transfer">(?)</a>
			</td>
            <td valign="top"><textarea name="pmethod_details" cols="45" rows="7" class="form-control"><?php echo strip_tags($row['pmethod_details']); ?></textarea></td>
          </tr>
		  <!--
          <tr>
            <td valign="middle" align="right" class="tb1">Min Payment:</td>
            <td valign="top"><?php echo (SITE_CURRENCY_FORMAT <= 3) ? SITE_CURRENCY : ""; ?><input type="text" name="min_amount" id="min_amount" value="<?php echo ($row['min_amount'] != "0.0000" ? $row['min_amount'] : ""); ?>" size="6" class="form-control" /> <?php echo (SITE_CURRENCY_FORMAT > 3) ? SITE_CURRENCY : ""; ?></td>
          </tr>
		  -->
			<?php
					if (strstr($row['commission'], '%'))
					{
						$commission = str_replace('%','',$row['commission']);
						$selected1 = "";
						$selected2 = "selected";
					}
					elseif ($row['commission'] != 0)
					{
						$commission = $row['commission'];
						$selected2 = "";
						$selected1 = "selected";
					}
			?>
          <tr>
            <td valign="middle" align="right" class="tb1">Commission:</td>
            <td valign="top">
				<input type="text" name="commission" id="commission" value="<?php echo $commission; ?>" size="5" class="form-control" />
				<select name="commission_sign" class="selectpicker">
					<option value="%" <?php echo $selected2; ?>>%</option>
					<option value="currency" <?php echo $selected1; ?>><?php echo SITE_CURRENCY; ?></option>
				</select>
				<span class="note" title="commission per transaction"></span></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">Status:</td>
            <td valign="top">
				<select name="status" class="selectpicker">
					<option value="active" <?php if ($row['status'] == "active") echo "selected"; ?>>active</option>
					<option value="inactive" <?php if ($row['status'] == "inactive") echo "selected"; ?>>inactive</option>
				</select>
			</td>
          </tr>
          <tr>
            <td align="center" valign="middle" nowrap="nowrap">&nbsp;</td>
			<td align="left" valign="middle">
				<input type="hidden" name="pmethodid" id="pmethodid" value="<?php echo (int)$row['pmethod_id']; ?>" />
				<input type="hidden" name="action" id="action" value="edit" />
				<input type="submit" name="save" id="save" class="btn btn-success" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='payment_methods.php'" />
            </td>
          </tr>
        </table>
      </form>

      <?php }else{ ?>
			<div class="alert alert-info">Sorry, no payment method found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>
	  </div>


	<div id="all_list" style="display: <?php echo ($_POST['action'] || $_GET['id']) ? "none" : ""; ?>">

		<div id="addnew" style="margin: -10px 0"><a class="addnew" href="#" onclick="$('#add_new_form').toggle('fast');$('#error_box').hide();$('#all_list').hide();">Add Payment Method</a></div>

		<h2>Payment Methods</h2>

        <?php if ($total > 0) { ?>

			<h3 align="center"><img src="images/withdrawal.png" align="absmiddle" /> Withdrawal Methods</h3>
			<p align="center">You can manage money withdrawal methods here.</p>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div style="width:49%;" class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "added":	echo "Payment method was successfully added"; break;
						case "updated": echo "Payment method has been successfully edited"; break;
						case "deleted": echo "Payment method has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

			<table align="center" width="50%" class="tbl" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="55%">Payment Method</th>
				<th width="20%">Status</th>
				<th width="25%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>		  
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="left" valign="middle" class="row_title" style="padding-left: 20px;">
						<h4><a href="payment_methods.php?id=<?php echo $row['pmethod_id']; ?>"><?php echo $row['pmethod_title']; ?></a></h4>
					</td>
					<td align="center" valign="middle" nowrap="nowrap">
						<?php if ($row['status'] == "inactive") echo "<span class='inactive_s'>".$row['status']."</span>"; else echo "<span class='active_s'>".$row['status']."</span>"; ?>
					</td>
					<td align="center" valign="middle" nowrap="nowrap">
						<a href="payment_methods.php?id=<?php echo $row['pmethod_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this payment method?') )location.href='payment_methods.php?id=<?php echo $row['pmethod_id']; ?>&action=delete'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
            </table>

          <?php }else{ ?>
				<div class="alert alert-info">There are no payment methods at this time.</div>
          <?php } ?>

	</div>

<?php require_once ("inc/footer.inc.php"); ?>