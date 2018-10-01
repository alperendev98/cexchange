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

	$cpage = 19;

	CheckAdminPermissions($cpage);


	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
	{
		$currency_id = (int)$_GET['id'];
		smart_mysql_query("DELETE FROM exchangerix_currencies WHERE currency_id='$currency_id'");
		smart_mysql_query("DELETE FROM exchangerix_exdirections WHERE from_currency='$currency_id' OR to_currency='$currency_id'");
		// delete from exchanges
		header("Location: currencies.php?msg=deleted");
		exit();
	}


	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 10;


		// Update //
		if (isset($_POST['update']) && $_POST['update'] != "")
		{
			$sorts_arr	= array();
			$sorts_arr	= $_POST['sort_arr'];

			if (count($sorts_arr) > 0)
			{
				foreach ($sorts_arr as $k=>$v)
				{
					smart_mysql_query("UPDATE exchangerix_currencies SET sort_order='".(int)$v."' WHERE currency_id='".(int)$k."'");
				}

				header("Location: currencies.php?msg=updated");
				exit();
			}
		}

		// Delete //
		if (isset($_POST['delete']) && $_POST['delete'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$did = (int)$v;
					smart_mysql_query("DELETE FROM exchangerix_currencies WHERE currency_id='$did'");
					smart_mysql_query("DELETE FROM exchangerix_exdirections WHERE from_currency='$did' OR to_currency='$did'");
					// delete from exchanges
				}

				header("Location: currencies.php?msg=deleted");
				exit();
			}
		}

		$where = "1=1";

		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "sort_order": $rrorder = "sort_order"; break;
					case "currency_name": $rrorder = "currency_name"; break;
					case "currency_code": $rrorder = "currency_code"; break;	
					case "added": $rrorder = "added"; break;
					case "reserve": $rrorder = "convert(reserve, decimal)"; break;
					case "status": $rrorder = "status"; break;
					default: $rrorder = "sort_order"; break;
				}
			}
			else
			{
				$rrorder = "sort_order";
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
				$rorder = "asc";
			}
			if (isset($_GET['filter']) && $_GET['filter'] != "")
			{
				$filter	= mysqli_real_escape_string($conn, trim(getGetParameter('filter')));
				$where .= " AND (title LIKE '%$filter%' OR code LIKE '%$filter%') ";
				$totitle = " - Search Results";
			}
		///////////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		if (isset($_GET['store']) && $_GET['store'] != "")
		{
			$store = substr(trim(getGetParameter('store')), 0, 10);
			$store = mysqli_real_escape_string($conn, $store); //dev
			$where .= " AND currency_code='$store' ";
			$title2 = $store;
		}
		
		if (isset($_GET['direction']) && is_numeric($_GET['direction']))
		{
			$direction = (int)$_GET['direction'];
			if ($direction == 1)  { $where .= " AND allow_send='1' "; $title2 .= " Send"; }
			if ($direction == 2)  { $where .= " AND allow_receive='1' "; $title2 .= " Receive"; }
		}	

		$query = "SELECT * FROM exchangerix_currencies WHERE $where ORDER BY $rrorder $rorder, currency_id ASC LIMIT $from, $results_per_page"; //currency_name
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM exchangerix_currencies WHERE ".$where;
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;
	
		$title = $title2." Currencies";
		require_once ("inc/header.inc.php");

?>        

		<div id="addnew" class="pull-right">
			<span class="label label-success" style="margin-right: 20px"><a style="color: #FFF" href="gateways.php"><i class="fa fa-list-ul" aria-hidden="true"></i> Gateways</a></span>
			<a class="addnew" href="currency_add.php">Add Currency</a>
		</div>

		<h2><i class="fa fa-money" aria-hidden="true"></i> <?php echo $title2; ?> Currencies <?php if ($total > 0) { ?><sup class="badge" style="background: #73b9d1"><?php echo number_format($total); ?></sup><?php } ?></h2>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "added": echo "Currency has been successfully added"; break;
						case "updated": echo "Currency has been successfully edited"; break;
						case "deleted": echo "Currency has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

		<form id="form1" name="form1" method="get" action="">
		<div class="row" style="background:#F9F9F9; margin: 10px 0; padding: 7px 0;">
		<div class="col-md-5" style="white-space: nowrap">
           Sort by: 
          <select name="column" id="column" class="form-control" onChange="document.form1.submit()">
			<option value="sort_order" <?php if ($_GET['column'] == "sort_order") echo "selected"; ?>>Sort Order</option>
			<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Newest</option>
			<option value="currency_name" <?php if ($_GET['column'] == "currency_name") echo "selected"; ?>>Title</option>
			<option value="currency_code" <?php if ($_GET['column'] == "currency_code") echo "selected"; ?>>Code</option>
			<option value="reserve" <?php if ($_GET['column'] == "reserve") echo "selected"; ?>>Reserve</option>
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
          </select>
          <select name="order" id="order" class="form-control" onChange="document.form1.submit()">
			<option value="desc" <?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>
		  <span class="hidden-xs">&nbsp;&nbsp;Results:</span>
          <select name="show" id="show" class="form-control" onChange="document.form1.submit()">
			<option value="10" <?php if ($_GET['show'] == "10") echo "selected"; ?>>10</option>
			<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
			<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
			<option value="111111111" <?php if ($_GET['show'] == "111111111") echo "selected"; ?>>ALL</option>
          </select>
			<?php if ($store) { ?><input type="hidden" name="store" value="<?php echo $store; ?>" /><?php } ?>
			<?php if ($direction) { ?><input type="hidden" name="direction" value="<?php echo $direction; ?>" /><?php } ?>
		</div>
		<div class="col-md-5 text-center" style="white-space: nowrap">
				Show: 
				<select name="store" id="store" onChange="document.form1.submit()" style="width: 120px;" class="form-control">
				<option value="">--- all types ---</option>
				<?php
					$sql_currs = smart_mysql_query("SELECT currency_code FROM exchangerix_currencies GROUP BY currency_code");
					if (mysqli_num_rows($sql_currs) > 0)
					{
						while ($row_currs = mysqli_fetch_array($sql_currs))
						{
							if (isset($store) && $store == $row_currs['currency_code']) $selected = " selected=\"selected\""; else $selected = "";
							echo "<option value=\"".$row_currs['currency_code']."\"".$selected.">".$row_currs['currency_code']."</option>";
						}
					}
				?>
				</select>
				<select name="direction" id="direction" onChange="document.form1.submit()" class="form-control">
					<option value="">--- all directions ---</option>
					<option value="1" <?php if (@$direction == 1) echo "selected"; ?>>send payments</option>
					<option value="2" <?php if (@$direction == 2) echo "selected"; ?>>receive payments</option>
				</select>								
				<?php if ($store || $direction) { ?><a href="currencies.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Delete Filter" /></a><?php } ?>
		</div>
		<div class="col-md-2 text-right" style="white-space: nowrap; padding-top: 8px;">
			<?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
		</div>
		</div>
		</form>
		
			<script type="text/javascript">
			<!--
			setInterval(function(){blink()}, 1000);         
			function blink() {
				$(".hot_alert").fadeTo(100, 0.1).fadeTo(200, 1.0);
			}
			-->
			</script>

			<div class="table-responsive">
			<form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<th width="7%">Sort Order</th>
				<th width="7%">&nbsp;</th>
				<th width="27%">Currency</th>
				<th width="10%">Type</th>
				<th width="7%">For</th>
				<th width="12%">Reserve</th>
				<th width="7%">Fee</th>
				<th width="12%">Sent <i class="fa fa-arrow-right" aria-hidden="true"></i></th>
				<th width="12%"><i class="fa fa-arrow-left" aria-hidden="true"></i> Received</th>
				<th width="7%"><i class="fa fa-refresh" aria-hidden="true"></i> Exchanges<br/><span style="font-weight:normal">all time / today</span></th>
				<th width="10%">Status</th>
				<th width="10%">Actions</th>
			</tr>
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>				  
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['currency_id']; ?>]" id="id_arr[<?php echo $row['currency_id']; ?>]" value="<?php echo $row['currency_id']; ?>" /></td>
					<td align="center" valign="middle" nowrap="nowrap"><input type="text" name="sort_arr[<?php echo $row['currency_id']; ?>]" value="<?php echo $row['sort_order']; ?>" class="form-control" size="3" /></td>
					<td align="center" valign="middle" style="padding: 9px 0;">
						<a href="currency_details.php?id=<?php echo $row['currency_id']; ?>">
							<?php if ($row['image'] != "") { ?>
								<?php if (strstr($row['image'], "logo_")) { ?>
									<img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $row['image']; ?>" width="33" style="border-radius: 50%;" />
								<?php }else{ ?>
									<img src="images/currencies/<?php echo $row['image']; ?>" width="33" style="border-radius: 50%;"  />
								<?php } ?>
							<?php } ?>
						</a>
					</td>
					<td align="left" valign="middle">
						<a href="currency_details.php?id=<?php echo $row['currency_id']; ?>"><h3 style="margin: 10px 2px;"><?php echo $row['currency_name']; ?></h3></a>
						<?php if ($row['default_send'] == 1) { ?><span class="label label-primary">default for sending <i class="fa fa-arrow-right" aria-hidden="true"></i></span><br><?php } ?>
						<?php if ($row['default_receive'] == 1) { ?> <span class="label label-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> default for receiving</span><br><?php } ?>
					</td>
					<td align="center" valign="middle" nowrap><?php echo ($row['currency_code'] != "") ? $row['currency_code'] : ""; ?></td>
					<td align="center" valign="middle" nowrap>
						<?php if ($row['allow_receive'] == "1") { ?><i id="itooltip" class="fa fa-arrow-left" aria-hidden="true" title="receive payments" style="color: #5cb85c"></i> <?php } ?><?php if ($row['allow_send'] == "1") { ?><i id="itooltip" class="fa fa-arrow-right" aria-hidden="true" title="send payments" style="color: #8dc6fb"></i><?php } ?>
					</td>
					<td align="left" valign="middle"  style="padding-left: 20px;" nowrap>
						<?php $strike1=$strike2=""; if ($row['min_reserve'] != "" && $row['reserve'] <= $row['min_reserve']) { $strike1 = '<div class="hot_alert" title="Low Reserve" style="color: #e6454c">'; $strike2 = '</div>'; } ?>
						<?php echo $strike1; ?><?php echo ($row['reserve'] != "") ? floatval($row['reserve']) : "<span class='label label-success'>unlimited</span>"; ; ?><?php echo $strike2; ?>
					</td>
					<td align="center" valign="middle"><?php echo ($row['fee'] > 0) ? $row['fee']."%" : "---"; ?></td>
					<td align="left" valign="middle" style="padding-left: 10px;"><a href="#" style="color: #000"><?php echo GetCurrencySends($row['currency_name']." ".$row['currency_code']); ?> <sup style="color: #777"><?php echo $row['currency_code']; ?></sup></a></td>
					<td align="left" valign="middle" style="padding-left: 10px;"><a href="#" style="color: #000"><?php echo GetCurrencyReceives($row['currency_name']." ".$row['currency_code']); ?> <sup style="color: #777"><?php echo $row['currency_code']; ?></sup></a></td>
					<td align="left" valign="middle" style="padding-left: 15px;"><span class="label label-primary" style="background: #8dc6fb"><?php echo GetCurrencyTotalTransactions($row['currency_id']); ?></span> <sup><?php echo GetCurrencyTotalTransactions($row['currency_id'], "today"); ?></sup></td>
					<td align="left" valign="middle" style="padding-left: 5px;" >
						<?php
							switch ($row['status'])
							{
								case "active": echo "<span class='label label-success'>".$row['status']."</span>"; break;
								case "inactive": echo "<span class='label label-default'>".$row['status']."</span>"; break;
								case "expired": echo "<span class='label label-default'>".$row['status']."</span>"; break;
								default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
							}
						?>
					</td>
					<td align="center" valign="middle" nowrap="nowrap">
						<a href="currency_details.php?id=<?php echo $row['currency_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="currency_edit.php?id=<?php echo $row['currency_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this currency?') )location.href='currencies.php?id=<?php echo $row['currency_id']; ?>&action=delete'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
				<td style="border-top: 1px solid #F5F5F5" colspan="13" align="left">
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="submit" class="btn btn-success" name="update" id="GoUpdate" value="Update Sort Order" />
					<input type="submit" class="btn btn-danger" name="delete" id="GoButton1" value="Delete Selected" disabled="disabled" onclick="return confirm('Are you sure you really want to delete?')" />
				</td>
				</tr>
          <?php }else{ ?>
				<tr>
				<td style="border-top: 1px solid #F5F5F5" colspan="13" align="left">
					<?php if (isset($filter)) { ?>
						<div class="alert alert-info">No currencies found for your search criteria. <a href="currencies.php">See all &#155;</a></div>
					<?php }else{ ?>
						<div class="alert alert-info">There are no currencies at this time. <?php if ($store) { ?><a href="currencies.php">See all &#155;</a><?php } ?></div>
						<?php if ($_GET['store'] || $_GET['user']) { ?>
							<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
						<?php } ?>
					<?php } ?>
				</td>
				</tr>
          <?php } ?>
            </table>
			</form>
			</div>

				<?php
							$params = "";

							if (@$_GET['column'])	$params .= "column=".$_GET['column']."&";
							if (@$_GET['order'])	$params .= "order=".$_GET['order']."&";
							if (@$store)			$params .= "store=$store&";
							if (@$direction)		$params .= "direction=$direction&";
							if (@$_GET['show'])		$params .= "show=$results_per_page&";
							if (@$_GET['page'])		$params .= "page=$page&";

							echo ShowPagination("currencies",$results_per_page,"currencies.php?".$params, "WHERE ".$where);
				?>          

          

<?php require_once ("inc/footer.inc.php"); ?>