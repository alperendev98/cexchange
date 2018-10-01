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
	require_once("./inc/ce.inc.php");

	$cpage = 1;

	// check moderators
	if (!isSuperAdmin())
	{
		$check_result = smart_mysql_query("SELECT * FROM exchangerix_users WHERE user_id='".(int)$_SESSION['adm']['id']."' AND status='active' LIMIT 1");
		if (mysqli_num_rows($check_result) == 0)
		{
			header ("Location: logout.php");
			exit();
		}
	}

	// check permissions
	if (!@in_array($cpage, $_SESSION['adm']['pages']))
	{
		exit();
	}
	
	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
	{
		$did = (int)$_GET['id'];
		//DeleteExchange($pid);
		smart_mysql_query("DELETE FROM exchangerix_exchanges WHERE exchange_id='$did'");
		header("Location: index.php?msg=deleted#latest");
		exit();
	}	
	
	if (isset($_POST['action']) && $_POST['action'] == "update_reserves")
	{
			$reserves_arr = array();
			$reserves_arr = $_POST['reserve'];

			if (count($reserves_arr) > 0)
			{
				foreach ($reserves_arr as $k=>$v)
				{	
					if ($v != "") $new_reserve = (float)$v; else $new_reserve = "";
					
					if ($new_reserve >= 0 || $new_reserve == "")
					  smart_mysql_query("UPDATE exchangerix_currencies SET reserve='".$new_reserve."' WHERE currency_id='".(int)$k."'");
				}
				
				header("Location: index.php?msg=reserves_updated");
				exit();					
			}	
	}
	
	smart_mysql_query("UPDATE exchangerix_exchanges SET status='timeout' WHERE (created < (NOW() - INTERVAL 120 MINUTE) AND status='waiting')");

	$today = date("Y-m-d");
	$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d") - 1, date("Y")));

	$exchanges_today = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE date(created)='$today'"));
	$exchanges_today = $exchanges_today['total'];
	
	//$exchanges_today = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE created > DATE_SUB(NOW(), INTERVAL 1 DAY)"));

	$exchanges_yesterday = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE date(created)='$yesterday'"));
	$exchanges_yesterday = $exchanges_yesterday['total'];
	
	//$exchanges_yesterday = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE created BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE()"));
	//$exchanges_yesterday = $exchanges_yesterday['total'];	

	$exchanges_7days = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE date_sub(curdate(), interval 7 day) <= created"));
	$exchanges_7days = $exchanges_7days['total'];

	$exchanges_30days = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE date_sub(curdate(), interval 30 day) <= created"));
	$exchanges_30days = $exchanges_30days['total'];

	$users_yesterday = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_users WHERE date(created)='$yesterday'"));
	$users_yesterday = $users_yesterday['total'];

	$users_today = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_users WHERE date(created)='$today'"));
	$users_today = $users_today['total'];

	$users_7days = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_users WHERE date_sub(curdate(), interval 7 day) <= created"));
	$users_7days = $users_7days['total'];

	$users_30days = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_users WHERE date_sub(curdate(), interval 30 day) <= created"));
	$users_30days = $users_30days['total'];

	$all_users = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_users"));
	$all_users = $all_users['total'];

	$all_currencies = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_currencies"));
	$all_currencies = $all_currencies['total'];
	
	$all_ex_directions = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exdirections"));
	$all_ex_directions = $all_ex_directions['total'];	

	$all_exchanges = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE status!='request'"));
	$all_exchanges = $all_exchanges['total'];

	$exchanges_today = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE date(created)='$today'"));
	$exchanges_today = $exchanges_today['total'];	

	$all_reviews = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_reviews"));
	$all_reviews = $all_reviews['total'];

	$reviews_today = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_reviews WHERE date(added)='$today'"));
	$reviews_today = $reviews_today['total'];

	$title = "Admin Dashboard";
	require_once ("inc/header.inc.php");

?>
	<script type="text/javascript" src="js/jquery.min.js"></script>

	<h2><i class="fa fa-user-circle" aria-hidden="true"></i> Admin Dashboard</h2>

	<?php if (file_exists("../install.php")) { ?>
		<div class="alert alert-danger alert-dismissible">You must now delete "install.php" from your server. Failing to delete these files is a serious security risk!</div>
	<?php } ?>

	 <div class="row">
		<div class="col-md-6">

			<table id="Exchangerix_info" align="center" width="100%" border="0" cellpadding="3" cellspacing="2">
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2"><b>Exchangerix</b> version:</td>
				<td align="right" valign="middle"><?php echo $exchangerix_version; ?></td>
			</tr>			
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">License Key:</td>
				<td align="right" valign="middle"><?php echo GetSetting('license'); //echo (isSuperAdmin()) ? GetSetting('license') : "xxxx-xxxx-xxxx-xxxx"; ?></td>
			</tr>
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">Last Login:</td>
				<td align="right" valign="middle"><?php $last_login = strtotime(GetSetting('last_admin_login')); echo date("d M Y h:i A", $last_login); ?></td>
			</tr>
			<!--
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">Last Login IP:</td>
				<td align="right" valign="middle"><?php //echo GetSetting('last_admin_ip'); ?></td>
			</tr>
			-->
			<tr>
				<td colspan="2"><div class="sline"></div></td>
			</tr>
			</table>

		</div>
		<div class="col-md-3">

			<table id="Exchangerix_stats2" align="center" width="100%" border="0" cellpadding="3" cellspacing="2">
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">Exchanges Today:</td>
				<td width="20%" align="right" valign="middle" class="stat_s"><a href="exchanges.php?period=today"><span style="color: #2F97EB"><?php echo ($exchanges_today > 0) ? "+".$exchanges_today : "0"; ?></span></a></td>
			</tr>
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">Exchanges Yesterday:</td>
				<td align="right" valign="middle" class="stat_s"><a href="exchanges.php?period=yesterday"><?php echo $exchanges_yesterday; ?></a></td>
			</tr>
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">Last 7 Days Exchanges:</td>
				<td align="right" valign="middle" class="stat_s"><a href="exchanges.php?period=7days"><?php echo $exchanges_7days; ?></a></td>
			</tr>
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">Last 30 Days Exchanges:</td>
				<td align="right" valign="middle" class="stat_s"><a href="exchanges.php?period=30days"><?php echo $exchanges_30days; ?></a></td>
			</tr>
			<tr>
				<td colspan="2"><div class="sline"></div></td>
			</tr>
			</table>

		</div>
		<div class="col-md-3">

			<table id="Exchangerix_stats3" align="center" width="100%" border="0" cellpadding="3" cellspacing="2">
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">Users Today:</td>
				<td width="20%" align="right" valign="middle" class="stat_s"><a href="users.php"><span style="color: #2F97EB"><?php echo ($users_today > 0) ? "+".$users_today : "0"; ?></span></a></td>
			</tr>
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">Users Yesterday:</td>
				<td align="right" valign="middle" class="stat_s"><?php echo $users_yesterday; ?></td>
			</tr>
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">Last 7 Days Users:</td>
				<td align="right" valign="middle" class="stat_s"><?php echo $users_7days; ?></td>
			</tr>
			<tr class="wrow">
				<td align="left" valign="middle" class="tb2">Last 30 Days Users:</td>
				<td align="right" valign="middle" class="stat_s"><?php echo $users_30days; ?></td>
			</tr>
			<tr>
				<td colspan="2"><div class="sline"></div></td>
			</tr>
			</table>

		</div>
	 </div>

	 <div class="row" id="coinstats" style="margin: 10px 0;">
		 <div class="col-md-12 text-center" style="background: #F9F9F9; width: 99%">
			<script type="text/javascript">
			baseUrl = "https://widgets.cryptocompare.com/";
			var scripts = document.getElementsByTagName("script");
			var embedder = scripts[ scripts.length - 1 ];
			var cccTheme = {"General":{"background":"#F9F9F9"}};
			(function (){
			var appName="local";
			var s = document.createElement("script");
			s.type = "text/javascript";
			s.async = true;
			var theUrl = baseUrl+'serve/v2/coin/header?fsyms=BTC,ETH,XMR,LTC&tsyms=USD,EUR,CNY,GBP';
			s.src = theUrl + ( theUrl.indexOf("?") >= 0 ? "&" : "?") + "app=" + appName;
			embedder.parentNode.appendChild(s);
			})();
			</script>
		 </div>
	 </div>

	 <div class="row">
		 <div class="col-md-6">
				 
				 <h2><i class="fa fa-bars" aria-hidden="true"></i> Our Reserves</h2>
	
				<?php if (isset($_GET['msg']) && $_GET['msg'] == "reserves_updated") { ?>
				<div class="alert alert-success">
					<i class="fa fa-check" aria-hidden="true"></i> Reserves has been successfully updated
				</div>
				<?php } ?>	
				
				<form action="" method="post">	
				<?php
					
					$res_query = "SELECT * FROM exchangerix_currencies WHERE status='active' ORDER BY reserve DESC, currency_id LIMIT 0,10";
					$res_result = smart_mysql_query($res_query);
					$total_res = mysqli_num_rows($res_result);
					$cc = 0;
					$columns_res = intval($total_res/2);
					
					if ($total_res > 0)
					{
				?>
						<table width="100%" border="0" cellpadding="3" cellspacing="2">
						<tr>
							<th width="10%">&nbsp;</th>
							<th width="45%">Currency</th>
							<th width="40%">Reserve <sup id="itooltip" title="leave empty for no limit">?</sup></th>
						</tr>
						<?php while ($row_res = mysqli_fetch_array($res_result)) { $cc++; ?>
						<?php //if ($cc == 3) echo "<div class='bbb' style='display: none'>"; ?>
						<tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
							<td align="center"><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $row_res['image']; ?>" width="33" height="33" style="border-radius: 50%;" /></td>
							<td align="left" style="padding-left: 5px"><h4><?php echo $row_res['currency_name']; ?></h4></td>
							<td align="left" style="padding-left: 10px" nowrap><input type="text" name="reserve[<?php echo $row_res['currency_id']; ?>]" value="<?php echo ($row_res['reserve'] != "") ? floatval($row_res['reserve']) : ""; ?>" class="form-control" <?php if ($row_res['reserve'] == "") { ?> placeholder="no limit" <?php } ?> size="10" /> &nbsp;<?php echo $row_res['currency_code']; ?></td>
						 </tr>
						<?php } ?>
						</table>
						<?php //if ($total_res > 5) echo "</div>"; ?>
						

				<?php }else{ ?>		
					<p>No currencies added at this time.</p>
				<?php } ?>

				<?php
					
					$res_query = "SELECT * FROM exchangerix_currencies WHERE status='active' ORDER BY reserve DESC, currency_id LIMIT 10, 18446744073709551615";
					$res_result = smart_mysql_query($res_query);
					$total_res = mysqli_num_rows($res_result);
					$cc = 0;
					
					if ($total_res > 0)
					{
				?>
						<div class="other_list" style="display: none">
						<table width="100%" border="0" cellpadding="3" cellspacing="2">
						<?php while ($row_res = mysqli_fetch_array($res_result)) { $cc++; ?>
						<tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
							<td width="10%" align="center"><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $row_res['image']; ?>" width="33" height="33" style="border-radius: 50%;" /></td>
							<td width="45%" align="left" style="padding-left: 5px"><h4><?php echo $row_res['currency_name']; ?></h4></td>
							<td width="40%" align="left" style="padding-left: 10px" nowrap><input type="text" name="reserve[<?php echo $row_res['currency_id']; ?>]" value="<?php echo ($row_res['reserve'] != "") ? floatval($row_res['reserve']) : ""; ?>" class="form-control" <?php if ($row_res['reserve'] == "") { ?> placeholder="no limit" <?php } ?> size="10" /> &nbsp;<?php echo $row_res['currency_code']; ?></td>
						 </tr>
						<?php } ?>
						</table>
						</div>
						<p class="text-center"><a href="#;" onclick="return false;" class="btn-default show_more"><i class="fa fa-arrow-down" aria-hidden="true"></i> show all currencies</a></p>

				<?php } ?>				

						
						<table width="100%">
						<tr>
							<td style="border-top: 2px solid #F5F5F5" colspan="3">
								<p>
								<input type="hidden" name="action" id="action" value="update_reserves">
								<button type="submit" class="btn btn-success" name="update"><i class="fa fa-refresh"></i> Update Reserves</button>
								</p>
							</td>
						</tr>
						</table>				
				</form>
	
			 </div>
			 <div class="col-md-6">
				 
				 <h2><i class="fa fa-arrow-circle-right" aria-hidden="true" style="color: #8dc6fb"></i> <i class="fa fa-arrow-circle-left" aria-hidden="true" style="color: #5cb85c"></i> Top Exchange Directions</h2>
				 
				<?php
					
					$res2_query = "SELECT *, date(last_exchange_date) AS last_update FROM exchangerix_exdirections WHERE total_exchanges > 0 ORDER BY total_exchanges DESC, today_exchanges DESC LIMIT 10";
					$res2_result = smart_mysql_query($res2_query);
					$total2_res = mysqli_num_rows($res2_result);
					$cc = 0;
					
					if ($total2_res > 0)
					{
				?>
						<div class="table-responsive">
						<table width="100%" style="border-bottom: 1px solid #F5F5F5" border="0" cellpadding="3" cellspacing="2">
						<tr>
							<th width="65%">Direction</th>
							<th width="15%"><span id="itooltip" title="Today Exchanges">Today</span></th>
							<th width="15%"><span id="itooltip" title="All Time Exchanges">All time</span></th>
						</tr>
						<?php while ($row2_res = mysqli_fetch_array($res2_result)) { $cc++; ?>
						<tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
							<td width="20%"><h4><?php echo GetCurrencyImg($row2_res['from_currency']); ?> <a href="currency_details.php?id=<?php echo $row2_res['from_currency']; ?>" style="color: #000"><?php echo GetCurrencyName($row2_res['from_currency']); ?></a> <i class="fa fa-long-arrow-right fa-lg" aria-hidden="true"></i> <?php echo GetCurrencyImg($row2_res['to_currency']); ?> <a href="currency_details.php?id=<?php echo $row2_res['to_currency']; ?>" style="color: #000"><?php echo GetCurrencyName($row2_res['to_currency']); ?></a></h4></td>
							<td style="border-top: 1px solid #FFF" align="center"><h4 class="badge" style="background: #eee; font-size: 15px; color: #000; padding: 8px"><?php echo ($row2_res['today_exchanges'] > 0 && $row2_res['last_update'] == $today) ? "<span style='color: #3ea6e0'>+".number_format($row2_res['today_exchanges'])."</span>" : "<span style='color: #FFF'>0</span>" ; ?></h4></td>
							<td align="center"><h4 class="badge" style="background: #eee;  font-size: 15px; color: #000; padding: 8px"><?php echo number_format($row2_res['total_exchanges']); ?></h4></td>
						 </tr>
						<?php } ?>
						</table>
						</div>
				
				<?php }else{ ?>		
					<p>No stats at this time.</p>
				<?php } ?>		 

			 </div>
		 </div>
	 <a name="latest"></a>
	<br>

	<a class="pull-right" style="padding-top: 20px; color: #777" href="exchanges.php">view all</a></a>
	<h2><i class="fa fa-refresh" aria-hidden="true"></i> 10 Latest Exchanges</h2>

		<?php if (isset($_GET['msg']) && $_GET['msg'] == "deleted") { ?>
			<div class="alert alert-success">Exchange has been successfully deleted</div>
		<?php } ?>	

				<?php
					
					$res3_query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." <sup>%h:%i %p</sup>') AS payment_date FROM exchangerix_exchanges WHERE status!='request' ORDER BY created DESC LIMIT 10";
					$res3_result = smart_mysql_query($res3_query);
					$total3_res = mysqli_num_rows($res3_result);
					$cc = 0;
					
					if ($total3_res > 0)
					{
				?>
						<div id="loader"><p align="center"><img src="images/loading_line.gif" /></p></div>
				
						<div class="table-responsive">
						<table width="100%" style="border-bottom: 1px solid #F5F5F5" border="0" cellpadding="3" cellspacing="2" id="10records">
						<tr>
							<th width="7%">ID</th>
							<th width="12%">Reference ID</th>
							<th width="15%">Date</th>
							<th width="35%">Exchange Direction / Rate / Amount</th>
							<th width="3%">&nbsp;</th>
							<th width="20%"><i class="fa fa-user-o" aria-hidden="true"></i> User</th>
							<th width="15%">Status</th>
							<th width="15%">Actions</th>
						</tr>
						<?php while ($row3 = mysqli_fetch_array($res3_result)) { $cc++; ?>
						<tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>" style="background: <?php /*if ($row3['status'] == "confirmed") echo "#edf9ee";*/ if ($row3['status'] == "pending") echo "#f9f2e8"; if ($row3['status'] == "cancelled" || $row3['status'] == "declined" || $row3['status'] == "timeout") echo "#f9f2f2"; ?>; border-bottom: 1px solid #FFF">
							<td align="center" valign="middle" nowrap="nowrap"><?php echo $row3['exchange_id']; ?></td>
							<td align="center" valign="middle"><a href="exchange_details.php?id=<?php echo $row3['exchange_id']; ?>"><?php echo $row3['reference_id']; ?></a></td>
							<td align="center" valign="middle" nowrap="nowrap"><?php echo findTimeAgo($row3['created']); //$row3['payment_date']; ?></td>
							<td align="left" valign="middle" style="padding-left: 15px;">
								<p>
								<?php echo GetCurrencyImg($row3['from_currency_id'], $width=20); ?> <?php echo $row3['from_currency']; ?> <i class="fa fa-long-arrow-right fa-lg" aria-hidden="true"></i> <?php echo GetCurrencyImg($row3['to_currency_id'], $width=20); ?> <?php echo $row3['to_currency']; ?>
								<br>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><?php echo floatval($row3['exchange_amount']); ?> <sup><?php echo substr($row3['from_currency'], -4); ?></sup> <i class="fa fa-long-arrow-right fa-lg" aria-hidden="true"></i> <?php echo floatval($row3['receive_amount']); ?> <sup><?php echo substr($row3['to_currency'], -4); ?></sup></b>
								<br>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small>(Exchange rate: <?php echo $row3['ex_from_rate']; ?> <?php echo substr($row3['from_currency'], -4); ?> = <?php echo $row3['ex_to_rate']; ?> <?php echo substr($row3['to_currency'], -4); ?>)</small>
								<br>
								</p>
							</td>
							<td align="center" valign="middle"><?php if ($row3['country_code'] != "") { ?><img src="<?php echo SITE_URL; ?>images/flags/<?php echo $row3['country_code']; ?>.png" width="16" height="11" /><?php } ?></td>
							<td align="left" valign="middle">
						&nbsp; <?php if ($row3['user_id'] > 0) { ?><i class="fa fa-user-circle" aria-hidden="true"></i> <a href="user_details.php?id=<?php echo $row3['user_id']; ?>"><?php echo GetUsername($row3['user_id'], $type=2); ?></a><?php }else{ ?><i class="fa fa-user-o" aria-hidden="true"></i> <?php echo $row3['client_details']; ?><!--Visitor--><?php } ?>
						<br><a href="mailto:<?php echo $row3['client_email']; ?>" style="color: #999"><?php echo $row3['client_email']; ?></a>
							</td>
							<td align="left" valign="middle" style="padding-left: 5px;">
							<?php
								switch ($row3['status'])
							  {
									case "confirmed": echo "<span class='label label-success'><i class='fa fa-check'></i> confirmed</span>"; break;
									case "pending": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting confirmation</span>"; break;
									case "waiting": echo "<span class='label label-default'><i class='fa fa-clock-o'></i> waiting for payment</span>"; break;
									case "declined": echo "<span class='label label-danger'><i class='fa fa-times'></i> declined</span>"; break;
									case "cancelled": echo "<span class='label label-danger'><i class='fa fa-times'></i> cancelled</span>"; break;
									case "failed": echo "<span class='label label-danger'><i class='fa fa-times'></i> failed</span>"; break;
									case "timeout": echo "<span class='label label-danger'><i class='fa fa-times'></i> timeout</span>"; break;
									case "request": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting approval</span>"; break;
									case "paid": echo "<span class='label label-success'><i class='fa fa-check'></i paid</span>"; break;
									default: echo "<span class='label label-default'>".$row3['status']."</span>"; break;
								}
							?>
							<?php if ($row3['reason'] != "") { ?><span class="note" title="<?php echo $row3['reason']; ?>"></span><?php } ?>
							</td>
							<td align="center" valign="middle" nowrap="nowrap">
								<a href="exchange_details.php?id=<?php echo $row3['exchange_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
								<a href="#" onclick="if (confirm('Are you sure you really want to delete this exchange?') )location.href='index.php?id=<?php echo $row3['exchange_id']; ?>&action=delete';" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
							</td>
						 </tr>
						<?php } ?>
						</table>
						</div>
				
				<?php }else{ ?>		
					<p>No exchanges at this time.</p>
				<?php } ?>
	
	<br>
	<h3><i class="fa fa-bar-chart" aria-hidden="true"></i> All Time Stats</h3>
	
	<div class="row" style="background: #F9F9F9; padding: 10px 0; border-radius: 8px;">

		<div class="col-xs-6 col-md-4 col-lg-2 text-center">
			<span class="stats_total"><?php echo $all_users; ?></span><br/>
			<i class="fa fa-users fa-lg"></i> <?php echo ($all_users == 1) ? "user" : "users"; ?>
			<?php if ($users_today > 0) { ?><p><span class="todays_total">+<?php echo $users_today; ?> today</span></p><?php } ?>
		</div>
		<div class="col-xs-6 col-md-4 col-lg-2 text-center">
			<span class="stats_total"><?php echo $all_exchanges; ?></span><br/>
			<i class="fa fa-refresh fa-lg"></i> <?php echo ($all_exchanges == 1) ? "exchanges" : "exchanges"; ?>
			<?php if ($exchanges_today > 0) { ?><p><span class="todays_total">+<?php echo $exchanges_today; ?> today</span></p><?php } ?>
		</div>		
		<div class="col-xs-6 col-md-4 col-lg-2 text-center">
			<span class="stats_total"><?php echo $all_currencies; ?></span><br/>
			<i class="fa fa-money fa-lg"></i> <?php echo ($all_currencies == 1) ? "currency" : "currencies"; ?>
		</div>
		<div class="col-xs-6 col-md-4 col-lg-2 text-center">
			<span class="stats_total"><?php echo $all_ex_directions; ?></span><br/>
			<i class="fa fa-exchange fa-lg"></i> <?php echo ($all_ex_directions == 1) ? "direction" : "directions"; ?>
		</div>
		<div class="col-xs-6 col-md-4 col-lg-2 text-center">
			<span class="stats_total"><?php echo $all_reviews; ?></span><br/>
			<i class="fa fa-comments-o fa-lg"></i> <?php echo ($all_reviews == 1) ? "testimonial" : "testimonials"; ?>
			<?php if ($reviews_today > 0) { ?><p><span class="todays_total">+<?php echo $reviews_today; ?> today</span></p><?php } ?>
		</div>
		<div class="col-xs-6 col-md-4 col-lg-2 text-center">
			<span class="stats_total" style="color: #8CD706;">$<?php echo number_format(floatval(getsetting('total_exchanges_usd'))); ?></span><br/> exchanges in USD <!-- //dev -->
		</div>

	 </div>
	 <?php echo ShowCBEInfo(); ?>


	<?php
			if (isset($_GET['stats_period']) && is_numeric($_GET['stats_period']) && $_GET['stats_period'] > 0)
				$stats_period = (int)$_GET['stats_period'];
			else
				$stats_period = 30;
	?>


	<script src="js/raphael-min.js" language="javascript"></script>
	<script src="js/morris.min.js" language="javascript"></script>

	<?php
			if (isset($_GET['stats_period2']) && is_numeric($_GET['stats_period2']) && $_GET['stats_period2'] > 0)
				$stats_period2 = (int)$_GET['stats_period2'];
			else
				$stats_period2 = 22222;
	?>
	<br/><br/>
	<h3 class="pull-left"><i class="fa fa-area-chart" aria-hidden="true"></i> <?php echo (@$_GET['show_for'] == "signups") ? "Sign Ups" : "Exchanges"; ?> Stats</h3>
	<div class="pull-right">
	<form id="form6" name="form6" method="get" action="#cstats">
	Show for: <select name="show_for" onChange="document.form6.submit()" class="selectpicker">
		<option value="cashback" <?php if (@$_GET['show_for'] == "cashback") echo "selected='selected'"; ?>>exchanges</option>
		<option value="signups" <?php if (@$_GET['show_for'] == "signups") echo "selected='selected'"; ?>>sign ups</option>
	</select>
	&nbsp;&nbsp;
	Period: <select name="stats_period2" onChange="document.form6.submit()" class="selectpicker">
		<option value="7" <?php if (@$stats_period2 == 7) echo "selected='selected'"; ?>>last 7 days</option>
		<option value="30" <?php if (@$stats_period2 == 30) echo "selected='selected'"; ?>>last 30 days</option>
		<option value="90" <?php if (@$stats_period2 == 90) echo "selected='selected'"; ?>>last 90 days</option>
		<option value="180" <?php if (@$stats_period2 == 180) echo "selected='selected'"; ?>>last 180 days</option>
		<option value="22222" <?php if (@$stats_period2 == 22222) echo "selected='selected'"; ?>>past months</option>
		<option value="11111" <?php if (@$stats_period2 == 11111) echo "selected='selected'"; ?>>past years</option>
	</select>
	</form>
	</div>
	<div id="statstchart2" style="height: 270px;"></div>
	<a name="cstats"></a>
	<br/>
	<?php
			unset($chart_data2);

			if ($stats_period2 == 11111)
			{
				for ($i=0; $i<=7; $i++)
				{
					$d = date("Y", strtotime('-'. $i .' year'));
					$years[$d] = 0;			
				}

				$eee = "YEAR(created)";
				$www = "%Y";
				$vvv = "";

				$chart_data2 = $years;
			}
			elseif ($stats_period2 == 22222)
			{
				for ($i=0; $i<=12; $i++)
				{
					$d = date("M Y", strtotime('-'. $i .' month'));
					$months[$d] = 0;			
				}

				$eee = "YEAR(created), MONTH(created)";
				$www = "%b %Y";
				$vvv = "";

				$chart_data2 = array_reverse($months);
			}
			else
			{
				for ($i=0; $i<=$stats_period2; $i++)
				{
					$d = date("d M", strtotime('-'. $i .' days'));
					$days[$d] = 0;			
				}
				
				$eee = "YEAR(created), MONTH(created), DAY(created)";
				$www = "%d %b";
				$vvv = "AND created > created - ".$stats_period2."";

				$chart_data2 = array_reverse($days);
			}

			if (isset($_GET['show_for']) && $_GET['show_for'] == "signups")
				$chart_result2 = smart_mysql_query("SELECT DATE_FORMAT(created, '".$www."') as stats_date, COUNT(*) as stats_amount FROM exchangerix_users WHERE 1=1 ".$vvv." GROUP BY ".$eee);
			else
				$chart_result2 = smart_mysql_query("SELECT DATE_FORMAT(created, '".$www."') as stats_date, SUM(receive_amount) as stats_amount FROM exchangerix_exchanges WHERE status='confirmed' ".$vvv." GROUP BY ".$eee);
			
			if (mysqli_num_rows($chart_result2) > 0)
			{
				while ($chart_row2 = mysqli_fetch_array($chart_result2))
				{
					if (array_key_exists($chart_row2['stats_date'], $chart_data2))
						$chart_data2[$chart_row2['stats_date']] = $chart_row2['stats_amount'];
				}
			}
	?>

	<script language="javascript" type="text/javascript">
	new Morris.Bar({
	  element: 'statstchart2',
	  data: [ <?php foreach ($chart_data2 as $k => $v) { echo "{ statsdate: '$k', value: $v },"; } ?> ],
	  xkey: 'statsdate',
	  ykeys: ['value'],
	<?php if (isset($_GET['show_for']) && $_GET['show_for'] == "signups") { ?>
	  labels: ['Sign Ups'],
	  barColors: ['#999999'],
	<?php }else{ ?>
	  labels: ['Amount <?php echo SITE_CURRENCY; ?>'],
	  barColors: ['#8ec120'],
	<?php } ?>
	  barRatio: 0.4,
	  hideHover: 'auto'
	});
	</script>

	
	<script type="text/javascript">
	   setTimeout(function(){
	       location.reload();
	   },100000);
	   
	   $(document).ready(function() {
				
				$("#10records").hide().delay(2000).fadeIn('fast');
				$("#loader").show().delay(2000).fadeOut();
			});
	</script>
	
	<br><br><br><br>


<?php require_once ("inc/footer.inc.php"); ?>