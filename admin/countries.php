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

	$cpage = 13;

	CheckAdminPermissions($cpage);

	if (isset($_GET['show']) && $_GET['show'] == "all")
		$results_per_page = 1000;
	else
		$results_per_page = 15;

		// Delete countries //
		if (isset($_POST['delete']) && $_POST['delete'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$countryid = (int)$v;
					DeleteCountry($countryid);
				}

				header("Location: countries.php?msg=deleted");
				exit();
			}	
		}


	// add ////////////////////////////////////////
	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
		$country_name	= mysqli_real_escape_string($conn, getPostParameter('country_name'));
		$signup			= (int)getPostParameter('signup');

		if (!$country_name)
		{
			$errormsg = "Please enter country name";
		}
		else
		{
			$check_query = smart_mysql_query("SELECT * FROM exchangerix_countries WHERE name='$country_name'");
			if (mysqli_num_rows($check_query) == 0)
			{
				$sql = "INSERT INTO exchangerix_countries SET name='$country_name', signup='$signup', status='active'";

				if (smart_mysql_query($sql))
				{
					header("Location: countries.php?msg=added");
					exit();
				}
			}
			else
			{
				header("Location: countries.php?msg=exists");
				exit();
			}
		}
	}


	// edit ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = (int)$_GET['id'];

		$iquery = "SELECT * FROM exchangerix_countries WHERE country_id='$id' LIMIT 1";
		$irs = smart_mysql_query($iquery);
		$itotal = mysqli_num_rows($irs);

		if ($itotal > 0)
		{
			$irow = mysqli_fetch_array($irs);
		}
	}
	if (isset($_POST["action"]) && $_POST["action"] == "edit")
	{
		unset($errors);
		$errors = array();
 
		$country_id		= (int)getPostParameter('country_id');
		$country_name	= mysqli_real_escape_string($conn, getPostParameter('country_name'));
		$signup			= (int)getPostParameter('signup');
		$status			= mysqli_real_escape_string($conn, getPostParameter('status'));
		$sort_order		= (int)getPostParameter('sort_order');

		if (!$country_name)
		{
			$errormsg = "Please fill in all required fields";
		}
		else
		{
			smart_mysql_query("UPDATE exchangerix_countries SET name='$country_name', signup='$signup', sort_order='$sort_order', status='$status' WHERE country_id='$country_id' LIMIT 1");

			header("Location: countries.php?msg=updated");
			exit();
		}
	}


	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;

	$query = "SELECT * FROM exchangerix_countries ORDER BY sort_order, name LIMIT $from, $results_per_page";
	$result = smart_mysql_query($query);

	$total_result = smart_mysql_query("SELECT * FROM exchangerix_countries ORDER BY sort_order, name");
	$total = mysqli_num_rows($total_result);

	$cc = 0;

	$title = "Countries";
	require_once ("inc/header.inc.php");

?>

		<div id="add_new_form" style="display: <?php echo ($_POST['action'] && !$_GET['id']) ? "" : "none"; ?>">
			<h2><i class="fa fa-globe" aria-hidden="true"></i> Add Country</h2>
			  <?php if (isset($errormsg) && $errormsg != "") { ?>
				<div class="alert alert-danger"><?php echo $errormsg; ?></div>
			  <?php } ?>
			  <form action="" method="post">
			  <table style="background: #F7F7F7" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			  <tr>
				<td width="40%" valign="middle" align="right" class="tb1">Name:</td>
				<td align="left" valign="middle"><input type="text" name="country_name" id="country_name" value="" size="35" class="form-control" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td valign="middle" align="left"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="signup" value="1" <?php if (getPostParameter('signup') == 1) echo "checked=\"checked\""; ?> /> Signup page</label></div></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align="left" valign="top" >
					<input type="hidden" name="action" id="action" value="add" />
					<input type="submit" name="add" id="add" class="btn btn-success" value="Add Country" />
					<input type="button" class="btn btn-default" name="cancel" value="Cancel" onclick="$('#add_new_form').hide();$('#all_list').show();" />
				</td>
			  </tr>
			  </table>
			  </form>
		</div>


	<div id="edit_form" style="display: <?php echo ((isset($_POST['action']) && $_POST['action'] != "add") || $_GET['id']) ? "" : "none"; ?>">
		<h2><i class="fa fa-globe" aria-hidden="true"></i> Edit Country</h2>
		  <?php if (isset($errormsg) && $errormsg != "") { ?>
			<div id="error_box" class="alert alert-danger"><?php echo $errormsg; ?></div>
		  <?php } ?>
		<?php if ($total > 0) { ?>
      <form action="" method="post">
        <table width="100%" style="background:#F9F9F9" cellpadding="2" cellspacing="3"  border="0" align="center">
          <tr>
            <td width="12%" valign="middle" align="left" class="tb1">Country Code:</td>
            <td valign="middle"><b><?php echo $irow['code']; ?></b></td>
          </tr>
          <tr>
            <td nowrap valign="middle" align="left" class="tb1">Country Name:</td>
            <td valign="middle"><input type="text" name="country_name" id="country_name" value="<?php echo $irow['name']; ?>" size="32" class="form-control" /> 
			<?php if ($irow['code'] != "" && file_exists('../images/flags/'.strtolower($irow['code']).'.png')) { ?>
				<img src="../images/flags/<?php echo strtolower($irow['code']); ?>.png" align="absmiddle" />
			<?php } ?>
			</td>
			</tr>
            <tr>
				<td valign="middle" align="left" class="tb1">Signup Page</td>
				<td valign="middle"><div class="checkbox"><label><input type="checkbox" class="checkbox" name="signup" value="1" <?php if ($irow['signup'] == 1) echo "checked=\"checked\""; ?> /> Yes <span class="note" title="show country on signup page"></span></label></div></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">Sort Order:</td>
				<td valign="middle"><input type="text" class="form-control" name="sort_order" value="<?php echo $irow['sort_order']; ?>" size="5" /></td>
            </tr>
            <tr>
            <td valign="middle" align="left" class="tb1">Status:</td>
            <td valign="top">
				<select name="status" class="selectpicker">
					<option value="active" <?php if ($irow['status'] == "active") echo "selected"; ?>>active</option>
					<option value="inactive" <?php if ($irow['status'] == "inactive") echo "selected"; ?>>inactive</option>
				</select>
			</td>
            </tr>
            <tr>
              <td align="center" valign="bottom">&nbsp;</td>
			  <td align="left" valign="top">
				<input type="hidden" name="country_id" id="country_id" value="<?php echo (int)$irow['country_id']; ?>" />
				<input type="hidden" name="action" id="action" value="edit">
				<input type="submit" class="btn btn-success" name="update" id="update" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='countries.php'" />
			  </td>
            </tr>
          </table>
      </form>
		  <?php }else{ ?>
				<div class="alert alert-info">Sorry, no country found.</div>
		  <?php } ?>
	</div>


	<div id="all_list" style="display: <?php echo ($_POST['action'] || $_GET['id']) ? "none" : ""; ?>">

		<div id="addnew" style="margin: -10px 0"><a class="addnew" href="#" onclick="$('#add_new_form').toggle('fast');$('.error_box').hide();$('#all_list').toggle('fast');">Add Country</a></div>

		<h2><i class="fa fa-globe" aria-hidden="true"></i> Countries <?php if ($total > 0) { ?><sup class="badge" style="background: #73b9d1"><?php echo number_format($total); ?></sup><?php } ?></h2>

        <?php if ($total > 0) { ?>

			<form id="form2" name="form2" method="post" action="">
			<div class="col-md-6 col-md-offset-3">
			<table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<td colspan="6" align="center" valign="bottom">
					<?php if ($total > 15) { ?><div style="text-align: right"><a href="countries.php?show=all" style="color: #777; text-align: right">show all <b><?php echo $total; ?></b> countires &#155;</a></div><?php } ?>
					<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
					<div class="alert alert-success">
						<?php
							switch ($_GET['msg'])
							{
								case "added":	echo "Country was successfully added"; break;
								case "exists":	echo "Sorry, country exists"; break;
								case "updated": echo "Country has been successfully edited"; break;
								case "deleted": echo "Country has been successfully deleted"; break;
							}
						?>
					</div>
					<?php } ?>
				</td>
			</tr>
			<tr bgcolor="#DCEAFB" align="center">
				<th width="5%"><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkboxx" /></th>
				<th width="10%">&nbsp;</th>
				<th width="40%">Country Name</th>
				<th width="15%">Signup Page <sup class="tooltip" title="Country will be displayed on Signup page">?</sup></th>
				<th width="15%">Status</th>
				<th width="12%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkboxx" name="id_arr[<?php echo $row['country_id']; ?>]" id="id_arr[<?php echo $row['country_id']; ?>]" value="<?php echo $row['country_id']; ?>" /></td>
					<td  valign="middle" align="center"><?php if ($row['code'] != "") { ?><img src="../images/flags/<?php echo strtolower($row['code']); ?>.png" align="absmiddle" /><?php } ?></td>
					<td nowrap align="left" valign="middle"><?php echo $row['name']; ?></td>
					<td valign="middle" align="center"><?php echo ($row['signup'] == 1) ? "<img src='./images/icons/yes.png' align='absmiddle'>" : "<img src='./images/icons/no.png' align='absmiddle'>"; ?></td>
					<td valign="middle" align="left" style="padding-left: 5px;">
						<?php
							switch ($row['status'])
							{
								case "active": echo "<span class='label label-success'>active</span>"; break;
								case "inactive": echo "<span class='label label-default'>inactive</span>"; break;
								default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
							}
						?>					
					</td>
					<td align="center" valign="middle" nowrap="nowrap">
						<a href="countries.php?id=<?php echo $row['country_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
					<td style="border-top: 1px solid #F5F5F5" colspan="6" align="left">
						<input type="hidden" name="page" value="<?php echo $page; ?>" />
						<input type="submit" class="btn btn-danger" name="delete" id="GoButton1" value="Delete Selected" disabled="disabled" />
					</td>
				</tr>
            </table>
			</div>
			</form>

				<?php echo ShowPagination("countries",$results_per_page,"?",""); ?>
          
		  <?php }else{ ?>
				<div class="alert alert-info">There are no countries at this time.</div>
          <?php } ?>

	</div>


<?php require_once ("inc/footer.inc.php"); ?>