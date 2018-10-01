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

	$cpage = 25;

	CheckAdminPermissions($cpage);
	
	function GetGatewayInCurrencies($gateway_id)
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_currencies WHERE gateway_id='".(int)$gateway_id."'");
		$row = mysqli_fetch_array($result);
		return (int)$row['total'];		
	}


		// delete ////////////////////////////////////////
		if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
		{
			$gateway_id = (int)$_GET['id'];
			smart_mysql_query("DELETE FROM exchangerix_gateways WHERE gateway_id='$gateway_id'");
			header("Location: gateways.php?msg=deleted");
			exit();
		}
			
		$query = "SELECT * FROM exchangerix_gateways ORDER BY account_id DESC, gateway_name";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);

		$cc = 0;

		$title = "Gateways";
		require_once ("inc/header.inc.php");

?>

<!--
				<div id="account_fields">
					<?php
					$gateway = $row['name'];
					
					if ($gateway == "PayPal") {
						?>
						<div class="form-group">
							<label>Your PayPal account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Skrill") {
						?>
						<div class="form-group">
							<label>Your Skrill account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<div class="form-group">
							<label>Your Skrill secret key</label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo $row['a_field_2']; ?>">
						</div>
						<?php
					} elseif($gateway == "WebMoney") {
						?>
						<div class="form-group">
							<label>Your WebMoney account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Payeer") { // Please enter a valid Payeer Account (ex: P1000000)
						?>
						<div class="form-group">
							<label>Your Payeer account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<div class="form-group">
							<label>Your Payeer secret key</label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo $row['a_field_2']; ?>">
						</div>
						<?php
					} elseif($gateway == "Perfect Money") { // Please enter a valid PerfectMoney Account (ex: Uxxxxxx)
						?>
						<div class="form-group">
							<label>Your Perfect Money account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<div class="form-group">
							<label>Account ID or API NAME</label>
							<input type="text" class="form-control" name="a_field_3" value="<?php echo $row['a_field_3']; ?>">
						</div>
						<div class="form-group">
							<label>Passpharse</label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo $row['a_field_2']; ?>">
							<small>Alternate Passphrase you entered in your Perfect Money account.</small>
						</div>
						<?php
					} elseif($gateway == "AdvCash") { // Please enter a valid AdvCash Account (ex: example@gmail.com)
						?>
						<div class="form-group">
							<label>Your AdvCash account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "OKPay") {
						?>
						<div class="form-group">
							<label>Your OKPay account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Entromoney") { 
						?>
						<div class="form-group">
							<label>Your Entromoney Account ID</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<div class="form-group">
							<label>Your Entromoney Receiver (Example: U11111111 or E1111111)</label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo $row['a_field_2']; ?>">
						</div>
						<div class="form-group">
							<label>SCI ID</label>
							<input type="text" class="form-control" name="a_field_3" value="<?php echo $row['a_field_3']; ?>">
						</div>
						<div class="form-group">
							<label>SCI PASS</label>
							<input type="text" class="form-control" name="a_field_4" value="<?php echo $row['a_field_4']; ?>">
						</div>
						<?php
					} elseif($gateway == "SolidTrust Pay") {
						?>
						<div class="form-group">
							<label>Your SolidTrust Pay account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<div class="form-group">
							<label>SCI Name</label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo $row['a_field_2']; ?>">
						</div>
						<div class="form-group">
							<label>SCI Password</label>
							<input type="text" class="form-control" name="a_field_3" value="<?php echo $row['a_field_3']; ?>">
						</div>
						<?php
					} elseif($gateway == "Neteller") {
						?>
						<div class="form-group">
							<label>Your Neteller account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "UQUID") {
						?>
						<div class="form-group">
							<label>Your UQUID account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Wex") {
						?>
						<div class="form-group">
							<label>Your Wex.nz account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Yandex Money") {
						?>
						<div class="form-group">
							<label>Your Yandex Money account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "QIWI") {
						?>
						<div class="form-group">
							<label>Your QIWI account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Payza") {
						?>
						<div class="form-group">
							<label>Your Payza account</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<div class="form-group">
							<label>IPN SECURITY CODE</label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo $row['a_field_2']; ?>">
						</div>
						<?php
					} elseif($gateway == "Bitcoin") { // Please enter a valid Bitcoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)
						?>
						<div class="form-group">
							<label>Your Bitcoin address</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Litecoin") { //Please enter a valid Litecoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)
						?>
						<div class="form-group">
							<label>Your Litecoin address</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Dogecoin") { //Please enter a valid Dogecoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)
						?>
						<div class="form-group">
							<label>Your Dogecoin address</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Dash") { //Please enter a valid Dash Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)
						?>
						<div class="form-group">
							<label>Your Dash address</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Peercoin") { //Please enter a valid Peercoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)
						?>
						<div class="form-group">
							<label>Your Peercoin address</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Ethereum") { // Please enter a valid Ethereum Address (ex: 0xaax00110aax00110aax00110aax00110aax00110)
						?>
						<div class="form-group">
							<label>Your Ethereum address</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<?php
					} elseif($gateway == "Bank Transfer") {
						?>
						<div class="form-group">
							<label>Bank Account Holder's Name</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<div class="form-group">
							<label>Bank Account Number/IBAN</label>
							<input type="text" class="form-control" name="a_field_4" value="<?php echo $row['a_field_4']; ?>">
						</div>
						<div class="form-group">
							<label>SWIFT Code</label>
							<input type="text" class="form-control" name="a_field_5" value="<?php echo $row['a_field_5']; ?>">
						</div>
						<div class="form-group">
							<label>Bank Name in Full</label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo $row['a_field_2']; ?>">
						</div>
						<div class="form-group">
							<label>Bank Branch Country, City, Address</label>
							<input type="text" class="form-control" name="a_field_3" value="<?php echo $row['a_field_3']; ?>">
						</div>
						<?php
					} elseif($gateway == "Western Union") {
						?>
						<div class="form-group">
							<label>Your name (For money receiving)</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<div class="form-group">
							<label>Your location (For money receiving)</label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo $row['a_field_2']; ?>">
						</div>
						<?php
					} elseif($gateway == "Moneygram") {
						?>
						<div class="form-group">
							<label>Your name (For money receiving)</label>
							<input type="text" class="form-control" name="a_field_1" value="<?php echo $row['a_field_1']; ?>">
						</div>
						<div class="form-group">
							<label>Your location (For money receiving)</label>
							<input type="text" class="form-control" name="a_field_2" value="<?php echo $row['a_field_2']; ?>">
						</div>
						<?php
					} else {}
					?>
				</div>
-->


		<div id="addnew">
			<a class="addnew" href="gateway_add.php">Add Gateway</a>
		</div>

		<h2><i class="fa fa-list-ul" aria-hidden="true"></i> Gateways</h2>		

        <?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success" style="width: 70%">
				<?php
					switch ($_GET['msg'])
					{
						case "added":	echo "Gateway was successfully added"; break;
						case "updated": echo "Gateway has been successfully edited"; break;
						case "deleted": echo "Gateway has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

			<div class="table-responsive">
			<table align="center" width="70%" border="0" cellpadding="5" cellspacing="3">
			<tr>
				<th width="10%"></td>
				<th width="35%">Gateway</td>
				<th width="20%">Using in currencies</td>
				<!--<th width="15%">Transactions</td>
				<th width="15%">Last Date Used</td>-->
				<th width="15%">Ready?</td>
				<th width="15%">Status</td>
				<th width="15%">Actions</td>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				 <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>" <?php echo ($row['status'] == "active" && $row['account_id'] != "") ? "style='background: #f1f7ef'" : ""; ?>>
					<td style="height: 50px; border-bottom: 1px #DCEAFB dotted; border-left: 1px #DCEAFB dotted;" valign="middle" class="row_title" align="center">
						<?php if ($row['logo'] != "") { ?>
							<a href="gateway_edit.php?id=<?php echo $row['gateway_id']; ?>"><img src="images/currencies/<?php echo $row['logo']; ?>" alt="<?php echo $row['gateway_name']; ?>" title="<?php echo $row['gateway_name']; ?>" align="absmiddle" width="33" height="33" border="0" style="border-radius: 50%" /></a>
						<?php } ?>
					</td>
					<td align="left" style="border-bottom: 1px #DCEAFB dotted; border-right: 1px #FFF dotted;" valign="middle">
						<a href="gateway_edit.php?id=<?php echo $row['gateway_id']; ?>"><h4><?php echo $row['gateway_name']; ?></h4></a>
					</td>					
					<td align="center" style="border-bottom: 1px #DCEAFB dotted; border-right: 1px #FFF dotted;" valign="middle">
						<span class="badge" style="background: #5bc0de"><?php echo GetGatewayInCurrencies($row['gateway_id']); ?></span>
					</td>
					<!--
					<td align="center" style="border-bottom: 1px #DCEAFB dotted; border-right: 1px #FFF dotted;" valign="middle">
						<b><?php //echo GatewayTotalCurr($row['gateway_id']); ?>xx</b>
					</td>														
					<td align="center" style="border-bottom: 1px #DCEAFB dotted;" valign="middle" ><small><?php echo ($row['last_used'] != "0000-00-00 00:00:00") ? $row['last_used_date'] : "---"; ?></small></td>-->
					<td align="center" style="border-bottom: 1px #DCEAFB dotted; border-right: 1px #FFF dotted;" valign="middle">
						<?php if ($row['account_id'] != "") { ?>
							<i class="fa fa-check-square-o fa-lg tooltips" aria-hidden="true" style="color: #1fb40e" title="Gateway is ready to use!"></i>
						<?php }else{ ?>
							<i class="fa fa-check-square-o fa-lg tooltips" aria-hidden="true" style="color: #CCC" title="Please fill your <?php echo substr($row['gateway_name'], 0, 50); ?> account settings first"></i>
						<?php } ?>
					</td>					
					<td align="center" style="border-bottom: 1px #DCEAFB dotted;" valign="middle">
						<?php if ($row['status'] == "inactive") echo "<span class='label label-default'>".$row['status']."</span>"; else echo "<span class='label label-success'><i class='fa fa-check-square-o'></i> ".$row['status']."</span>"; ?>
					</td>
					<td style="border-bottom: 1px #DCEAFB dotted; border-right: 1px #FFF dotted;" align="center" valign="middle">
						<a href="gateway_edit.php?id=<?php echo $row['gateway_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<?php if ($row['added'] != '0000-00-00 00:00:00') { ?><a href="#" onclick="if (confirm('Are you sure you really want to delete this gateway?') )location.href='gateways.php?id=<?php echo $row['gateway_id']; ?>&action=delete'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a><?php } ?>
					</td>
				  </tr>
			<?php } ?>
            </table>
            </div>
          
		  <?php }else{ ?>
				<div class="alert alert-info">There are no gateways at this time.</div>
          <?php } ?>
          

<?php require_once ("inc/footer.inc.php"); ?>