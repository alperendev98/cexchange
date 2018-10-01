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
	require_once("./inc/admin_funcs.inc.php");

	$cpage = 19;

	CheckAdminPermissions($cpage);

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$rid	= (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(added, '".DATE_FORMAT." %h:%i %p') AS date_added FROM exchangerix_currencies WHERE currency_id='$rid' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Currency Details";
	require_once ("inc/header.inc.php");

?>

	 <?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

		<h2><?php if ($row['image'] != "") { ?><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $row['image']; ?>" width="33" style="border-radius: 50%;" /><?php }else{ ?><i class="fa fa-money" aria-hidden="true"></i><?php } ?> <?php echo $row['currency_name']; ?></h2>

		<div style="width: 400px; padding: 7px 5px; border-radius: 5px; text-align: center; position: absolute; right: 10px; margin: 5px;" />

					<table width="100%" border="0" cellspacing="0" cellpadding="10">
					<tr>
						<td width="50%" align="center" valign="top">
							<h3>Allow to Send Payments <i class="fa fa-arrow-right" aria-hidden="true" style="color: #8dc6fb"></i></h3>
							<h3>
								<?php if ($row['allow_send'] == 1) { ?>
									<i class="fa fa-check-square-o" aria-hidden="true" style="color: #1fb40e"></i>
								<?php }else{ ?>
									<i class="fa fa-times-circle-o" aria-hidden="true" style="color: #797474"></i>
								<?php } ?>
							</h3>
							<?php if ($row['default_send'] == 1) { ?><span class="label label-success">default gateway</label><?php } ?>
						</td>
						<td width="50%" align="center" valign="top">
							<h3><i class="fa fa-arrow-left" aria-hidden="true" style="color: #5cb85c"></i> Allow to Receive Payments</h3>
							<h3>
								<?php if ($row['allow_receive'] == 1) { ?>
									<i class="fa fa-check-square-o" aria-hidden="true" style="color: #1fb40e"></i>
								<?php }else{ ?>
									<i class="fa fa-times-circle-o" aria-hidden="true" style="color: #797474"></i>
								<?php } ?>
							</h3>
							<?php if ($row['default_receive'] == 1) { ?><span class="label label-success">default gateway</label><?php } ?>
						</td>
					</tr>
					</table>
		</div>

		<table style="height: 200px; background:#F9F9F9" width="100%" cellpadding="3" cellspacing="5"  border="0" align="center">		
			<?php if ($row['gateway_id'] > 0) { ?>
			<?php 	$gresult = smart_mysql_query("SELECT * FROM exchangerix_gateways WHERE gateway_id='".(int)$row['gateway_id']."' LIMIT 1");
					if (mysqli_num_rows($gresult) > 0) $grow = mysqli_fetch_array($gresult);
			?>
			<tr>
				<td valign="middle" align="left" class="tb1">Gateway:</td>
				<td valign="middle"><b><?php echo GetGatewayName($row['gateway_id']); ?></b></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Account:</td>
				<td valign="middle"><?php echo $grow['account_id']; ?> <sup><a href="gateway_edit.php?id=<?php echo $row['gateway_id']; ?>">change</a></sup></td>
			</tr>
			<?php }else{ ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Account:</td>
				<td valign="middle">not filled fill on gateways page</td>
			</tr>			
			<?php } ?>
			
			<?php if ($row['gateway_code'] != "") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Gateway Code:</td>
				<td valign="middle"><b><?php echo $row['gateway_code']; ?></b></td>
			</tr>
			<?php } ?>			
			<tr>
				<td width="22%" valign="middle" align="left" class="tb1">Currency Code:</td>
				<td valign="middle"><?php echo $row['currency_code']; ?></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Hide Currency Code:</td>
				<td valign="middle">
					<?php if ($row['hide_code'] == 1) { ?>
						<i class="fa fa-check-square-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
					<?php }else{ ?>
						<i class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
					<?php } ?>
				</td>
			</tr>			
			<tr>
				<td valign="middle" align="left" class="tb1">Cryptocurrency:</td>
				<td valign="middle">
					<?php if ($row['is_crypto'] == 1) { ?>
						<i class="fa fa-check-square-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
					<?php }else{ ?>
						<i class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
					<?php } ?>
				</td>
			</tr>			
			<!--
			<tr>
				<td valign="middle" align="left" class="tb1">Account ID:</td>
				<td valign="middle"><b><?php echo $row['account_id']; ?></b></td>
			</tr>
			-->
			<tr>
				<td valign="middle" align="left" class="tb1">Fee:</td>
				<td valign="middle"><?php echo ($row['fee'] > 0) ? $row['fee']."%" : "no fee"; ?></td>
			</tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Allow Affiliate Withdrawals:</td>
				<td valign="middle">
					<?php if ($row['allow_affiliate'] == 1) { ?>
						<i class="fa fa-check-square-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
					<?php }else{ ?>
						<i class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
					<?php } ?>
				</td>
			</tr>
			<?php if ($row['min_reserve'] != "") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Min Reserve:</td>
				<td valign="middle"><?php echo floatval($row['min_reserve'])." ".$row['currency_code']; ?></td>
			</tr>
			<?php } ?>
			<?php if ($row['instructions'] != "") { ?>
			<tr>
				<td valign="top" align="left" class="tb1">Instructions for user:</td>
				<td valign="top"><?php echo $row['instructions']; ?></td>
			</tr>
			<?php } ?>
			<?php if ($row['website'] != "") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1"><i class="fa fa-external-link"></i> Website:</td>
				<td valign="middle"><a href="<?php echo $row['website']; ?>" target="_blank"><?php echo $row['website']; ?></a></td>
			</tr>
			<?php } ?>			
			<?php if ($row['site_code'] != "") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Site Code:</td>
				<td valign="middle"><?php echo $row['site_code']; ?></td>
			</tr>
			<?php } ?>
			<?php if ($row['xml_code'] != "") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">XML Code:</td>
				<td valign="middle"><?php echo $row['xml_code']; ?></td>
			</tr>
			<?php } ?>
			<!--			
			<tr>
				<td nowrap valign="middle" align="left" class="tb1">Date Added:</td>
				<td valign="middle"><?php echo $row['date_added']; ?></td>
            </tr>
            -->
            <tr>
				<td valign="middle" align="left" class="tb1">Status:</td>
				<td valign="middle">
					<?php
						switch ($row['status'])
						{
							case "active": echo "<span class='active_s'>".$row['status']."</span>"; break;
							case "inactive": echo "<span class='inactive_s'>".$row['status']."</span>"; break;
							default: echo "<span class='default_status'>".$row['status']."</span>"; break;
						}
					?>
				</td>
            </tr>
            <tr>
	            <td colspan="2" align="center" valign="top">
		            
					<br>
					<h3 class="text-center"><i class="fa fa-bar-chart" aria-hidden="true"></i> <?php echo $row['currency_name']; ?> Stats</h3>
		
					<table width="97%" align="center" style="background: #FFF; border-radius: 5px;" border="0" cellspacing="0" cellpadding="10">
					<tr>
						<td width="20%" align="center" valign="top">
							<br>
							<h3><a href="exchanges.php?ft_filter=<?php echo $row['currency_id']; ?>&period=today"><?php echo GetCurrencyTotalTransactions($row['currency_id'], "today"); ?></a></h3>
							exchanges today
							<br><br>
						</td>
						<td width="20%" align="center" valign="top">
							<br>
							<h3><a href="exchanges.php?ft_filter=<?php echo $row['currency_id']; ?>"><?php echo GetCurrencyTotalTransactions($row['currency_id']); ?></a></h3>
							total exchanges
							<br><br>
						</td>
						<td width="20%" align="center" valign="top">
							<br>
							<h3><?php echo GetCurrencySends($row['currency_name']." ".$row['currency_code']); ?> <sup><?php echo $row['currency_code']; ?></sup></h3>
							sent <i class="fa fa-arrow-right" aria-hidden="true"></i>
							<br><br>
						</td>
						<td width="20%" align="center" valign="top">
							<br>
							<h3><?php echo GetCurrencyReceives($row['currency_name']." ".$row['currency_code']); ?> <sup><?php echo $row['currency_code']; ?></sup></h3>
							<i class="fa fa-arrow-left" aria-hidden="true"></i> received
							<br><br>
						</td>
						<td width="20%" align="center" valign="top">
							<br>
							<h3><?php echo ($row['reserve'] != "") ? floatval($row['reserve'])." <sup>".$row['currency_code']."</sup>" : "unlimited"; ?></h3>
							current reserve
							<br><br>
						</td>																
					</tr>
					</table>
					<br>		            
		            
	            </td>
            </tr>
          </table>

            <p align="center">
				<a class="btn btn-success" href="currency_edit.php?id=<?php echo $row['currency_id']; ?>"><i class="fa fa-cog"></i> Edit Settings</a>
				<a class="btn btn-default" href="#" onclick="history.go(-1);return false;">Go Back <i class="fa fa-angle-right" aria-hidden="true"></i></a>
				<a class="btn btn-danger pull-right" href="#" onclick="if (confirm('Are you sure you really want to delete this currency?') )location.href='currencies.php?id=<?php echo $row['currency_id']; ?>&action=delete';"><i class="fa fa-times" aria-hidden="true"></i> Delete</a>
			</p>  

	  <?php }else{ ?>
			<h2>Currency not found</h2>
			<div class="alert alert-info">Sorry, no currency found.</div>
			<p align="center"><a class="btn btn-default" href="#" onclick="history.go(-1);return false;">Go Back <i class="fa fa-angle-right" aria-hidden="true"></i></a></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>