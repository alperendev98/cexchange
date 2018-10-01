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

	$cpage = 12;

	CheckAdminPermissions($cpage);

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$rid	= (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(added, '".DATE_FORMAT." %h:%i %p') AS date_added FROM exchangerix_exdirections WHERE exdirection_id='$rid' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Exchange Direction Details";
	require_once ("inc/header.inc.php");

?>

	 <?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

		<h2><?php echo GetCurrencyImg($row['from_currency'], $width = 35); ?> <?php echo GetCurrencyName($row['from_currency']); ?></b> <i class="fa fa-long-arrow-right" aria-hidden="true"></i> <?php echo GetCurrencyImg($row['to_currency'], $width = 35); ?>  <?php echo GetCurrencyName($row['to_currency']); ?></h2>

		<div style="width: 400px; padding: 7px 5px; border-radius: 5px; text-align: center; position: absolute; right: 10px; margin: 5px;" />

					<h3><i class="fa fa-refresh" aria-hidden="true"></i> Exchanges</h3>
					<table width="100%" border="0" cellspacing="0" cellpadding="10">
					<tr>
						<td width="33%" align="center" valign="top">
							<div style="background:#e5fde3; padding: 10px; margin: 5px; border-radius: 5px">
								<h3><?php $stotal1 = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE status='confirmed'")); echo $stotal1['total']; ?></h3>
								success
							</div>
						</td>
						<td width="33%" align="center" valign="top">
							<div style="background:#fdf3e6; padding: 10px; margin: 5px; border-radius: 5px">
								<h3><?php $stotal2 = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE status='pending' OR status='waiting'")); echo $stotal2['total']; ?></h3>
								pendning
							</div>
						</td>
						<td width="33%" align="center" valign="top">
							<div style="background:#ffecec; padding: 10px; margin: 5px; border-radius: 5px">
								<h3><?php $stotal3 = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE status='declined' OR status='canceled' OR status='timeout'")); echo $stotal3['total']; ?></h3>
								failed
							</div>
						</td>					
					</tr>
					</table>
		</div>

		<table style="height: 200px; background:#F9F9F9" width="100%" cellpadding="3" cellspacing="5"  border="0" align="center">	
			<tr>
				<td valign="middle" align="left" class="tb1">Type:</td>
				<td valign="middle">
					<?php if ($row['is_manual'] == 1) { ?>
						<span class="label label-default" style="background: #BBB"><i class="fa fa-hand-o-right fa-lg" aria-hidden="true"></i> Manual Proccessing</span>
					<?php }else{ ?>
						<span class="label label-info">Automatic Proccessing</span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td width="22%" valign="middle" align="left" class="tb1">Exchange Rate:</td>
				<td valign="middle"><h3 style="color: #5cb85c"><b><?php echo $row['from_rate']; ?></b> <span style="color: #000"><?php echo GetCurrencyCode($row['from_currency']); ?></span> = <b><?php echo $row['to_rate']; ?></b> <span style="color: #000"><?php echo GetCurrencyCode($row['to_currency']); ?></span></h3></td>
			</tr>
			<?php if ($row['fee'] != "" && $row['fee'] != "0") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Fee:</td>
				<td valign="middle"><?php echo (strstr($row['fee'], "%")) ? $row['fee'] : $row['fee']." ".GetCurrencyCode($row['from_currency']); ?></td>
			</tr>
			<?php } ?>					
			<tr>
				<td valign="middle" align="left" class="tb1">Auto Rate:</td>
				<td valign="middle">
					<?php if ($row['auto_rate'] == 1) { ?>
						<i class="fa fa-check-square-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
					<?php }else{ ?>
						<i class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
					<?php } ?>
				</td>
			</tr>
			<?php if ($row['hide_from_visitors'] == 1) { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Hidden for unregistered:</td>
				<td valign="middle">
					<i class="fa fa-check-square-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Allow affiliate commission:</td>
				<td valign="middle">
					<?php if ($row['allow_affiliate'] == 1) { ?>
						<i class="fa fa-check-square-o fa-lg" aria-hidden="true" style="color: #1fb40e"></i>
					<?php }else{ ?>
						<i class="fa fa-times-circle-o fa-lg" aria-hidden="true" style="color: #797474"></i>
					<?php } ?>
				</td>
			</tr>
			<?php if ($row['min_amount'] != "") { ?>
			<tr>
				<td width="17%" valign="middle" align="left" class="tb1">Min. Amount:</td>
				<td valign="middle"><?php echo $row['min_amount']; ?> <sup><?php echo GetCurrencyCode($row['from_currency']); ?></sup></td>
			</tr>
			<?php } ?>
			<?php if ($row['max_amount'] != "") { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Max. Amount:</td>
				<td valign="middle"><?php echo $row['max_amount']; ?> <sup><?php echo GetCurrencyCode($row['from_currency']); ?></sup></td>
			</tr>
			<?php } ?>								
			<?php if ($row['user_instructions'] != "") { ?>
			<tr>
				<td valign="top" align="left" class="tb1">User Instructions:</td>
				<td valign="top"><a href="#" class="show_more2"><i class="fa fa-arrow-down" aria-hidden="true"></i> show description</a><div class="other_list2" style="display: none;"><?php echo stripslashes($row['user_instructions']); ?></div></td>
            </tr>
			<?php } ?>					
			<?php if ($row['description'] != "") { ?>
			<tr>
				<td valign="top" align="left" class="tb1">Description:</td>
				<td valign="top"><a href="#" class="show_more"><i class="fa fa-arrow-down" aria-hidden="true"></i> show description</a><div class="other_list" style="display: none;"><?php echo stripslashes($row['description']); ?></div></td>
            </tr>
			<?php } ?>		
			<?php if ($row['meta_keywords'] != "") { ?>
			<tr>
				<td valign="top" align="left" class="tb1">Meta Keywords:</td>
				<td valign="top"><?php echo $row['meta_keywords']; ?></td>
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
							case "active": echo "<span class='label label-success'>".$row['status']."</span>"; break;
							case "inactive": echo "<span class='label label-default'>".$row['status']."</span>"; break;
							default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
						}
					?>
				</td>
            </tr>
            <tr>
	            <td colspan="2" align="center" valign="top">
					<br>
					<h3 class="text-center"><i class="fa fa-bar-chart" aria-hidden="true"></i> Exchanges Stats</h3>
		
					<table width="97%" align="center" style="background: #FFF; border-radius: 5px;" border="0" cellspacing="0" cellpadding="10">
					<tr>
						<td width="25%" align="center" valign="top">
							<br>
							<h3><a href="exchanges.php?from_filter=<?php echo $row['from_currency']; ?>&to_filter=<?php echo $row['to_currency']; ?>&period=today"><?php echo number_format($row['today_exchanges']); ?></a></h3>
							exchanges today
							<br><br>
						</td>
						<td width="25%" align="center" valign="top">
							<br>
							<h3><a href="exchanges.php?from_filter=<?php echo $row['from_currency']; ?>&to_filter=<?php echo $row['to_currency']; ?>"><?php echo number_format($row['total_exchanges']); ?></a></h3>
							total exchanges
							<br><br>
						</td>
						<td width="25%" align="center" valign="top">
							<br>
							<h3><a href="#"><?php echo GetCurrencySends($row['currency_name']." ".$row['currency_code']); ?></a> <sup><?php echo $row['currency_code']; ?></sup></h3>
							amount sent <i class="fa fa-arrow-right" aria-hidden="true"></i>
							<br><br>
						</td>
						<td width="25%" align="center" valign="top">
							<br>
							<h3><a href="#"><?php echo GetCurrencyReceives($row['currency_name']." ".$row['currency_code']); ?></a> <sup><?php echo $row['currency_code']; ?></sup></h3>
							<i class="fa fa-arrow-left" aria-hidden="true"></i> amount received
							<br><br>
						</td>																
					</tr>
					</table>
					<br>		            
	            </td>
            </tr>
          </table>       
          

            <p align="center">
				<a class="btn btn-success" href="exdirection_edit.php?id=<?php echo $row['exdirection_id']; ?>"><i class="fa fa-pencil-square-o"></i> Edit Direction</a>
				<a class="btn btn-default" href="#" onclick="history.go(-1);return false;">Go Back <i class="fa fa-angle-right" aria-hidden="true"></i></a>
				<a class="btn btn-danger pull-right" href="#" onclick="if (confirm('Are you sure you really want to delete this direction?') )location.href='exdirections.php?id=<?php echo $row['exdirection_id']; ?>&action=delete';"><i class="fa fa-times" aria-hidden="true"></i> Delete</a>
			</p>  

	  <?php }else{ ?>
			<h2>Currency not found</h2>
			<div class="alert alert-info">Sorry, no exchange direction found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>