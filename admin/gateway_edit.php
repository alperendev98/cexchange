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

	$cpage = 888;

	//CheckAdminPermissions($cpage);

	if (isset($_POST['action']) && $_POST['action'] == "edit")
	{
		unset($errs);
		$errs = array();

		$gateway_id			= (int)getPostParameter('did');
		// account_id
		// api_key
		// secret_key		
		$account_field_1	= mysqli_real_escape_string($conn, getPostParameter('account_field_1'));//gateway_field1
		$account_field_2 	= mysqli_real_escape_string($conn, getPostParameter('account_field_2'));//gateway_field2		
		$account_field_3 	= mysqli_real_escape_string($conn, getPostParameter('account_field_3'));//gateway_field3
		$account_field_4 	= mysqli_real_escape_string($conn, getPostParameter('account_field_4'));//gateway_field4
		$account_field_5 	= mysqli_real_escape_string($conn, getPostParameter('account_field_5'));//gateway_field5	
		$status				= mysqli_real_escape_string($conn, getPostParameter('status'));

		if(!($status))
		{
			$errs[] = "Please fill in all fields";
		}

		if (count($errs) == 0)
		{
			$sql = "UPDATE exchangerix_gateways SET account_id='$account_field_1', gateway_description='', status='$status' WHERE gateway_id='$gateway_id' LIMIT 1";
			if (smart_mysql_query($sql))
			{
				header("Location: gateways.php?msg=updated");
				exit();
			}
		}
		else
		{
			$errormsg = "";
			foreach ($errs as $errorname)
				$errormsg .= $errorname."<br/>";
		}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$did = (int)$_GET['id'];

		$query = "SELECT * FROM exchangerix_gateways WHERE gateway_id='$did' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "Edit Gateway";
	require_once ("inc/header.inc.php");

?>
 
      <?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

        <h2><?php if ($row['logo'] != "") { ?><img src="images/currencies/<?php echo $row['logo']; ?>" align="absmiddle" width="44" border="0" style="border-radius: 50%" /><?php } ?> <?php echo $row['gateway_name']; ?> Settings</h2>

		<?php if (isset($errormsg) && $errormsg != "") { ?>
			<div class="alert alert-danger"><?php echo $errormsg; ?></div>
		<?php } ?>
		
		<div style="width: 100%; padding: 10px; background: #F9F9F9">
			<form action="" method="post">
		          
		          <!--
		          <h3>HOW TO SETUP</h3>
		          1. login
		          2. get api details
		          3. return url <?php echo SITE_URL; ?>checkout.php?gateway=1000
		          -->
		          
				<div id="account_data">
					<?php
					
					$gateway = $row['gateway_name'];
					
					if ($gateway == "PayPal") {
						?>
						<div class="form-group">
							<label>Your PayPal account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Skrill") {
						?>
						<div class="form-group">
							<label>Your Skrill account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<div class="form-group">
							<label>Your Skrill secret key:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_2" value="<?php echo $row['account_field_2']; ?>">
						</div>
						<?php
					} elseif($gateway == "WebMoney") {
						?>
						<div class="form-group">
							<label>Your WebMoney account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Payeer") { // Please enter a valid Payeer Account (ex: P1000000)
						?>
						<div class="form-group">
							<label>Your Payeer account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<div class="form-group">
							<label>Your Payeer secret key:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_2" value="<?php echo $row['account_field_2']; ?>">
						</div>
						<?php
					} elseif($gateway == "Perfect Money") { // Please enter a valid PerfectMoney Account (ex: Uxxxxxx)
						?>
						<div class="form-group">
							<label>Your Perfect Money account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<div class="form-group">
							<label>Account ID or API NAME:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_3" value="<?php echo $row['account_field_3']; ?>">
						</div>
						<div class="form-group">
							<label>Passpharse:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_2" value="<?php echo $row['account_field_2']; ?>">
							<small>Alternate Passphrase you entered in your Perfect Money account.</small>
						</div>
						<?php
					} elseif($gateway == "AdvCash") { // Please enter a valid AdvCash Account (ex: example@gmail.com)
						?>
						<div class="form-group">
							<label>Your AdvCash account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "OKPay") {
						?>
						<div class="form-group">
							<label>Your OKPay account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Entromoney") { 
						?>
						<div class="form-group">
							<label>Your Entromoney Account ID:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<div class="form-group">
							<label>Your Entromoney Receiver:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_2" value="<?php echo $row['account_field_2']; ?>">
							<span class="note" title="ex: U11111111 or E1111111"></span>
						</div>
						<div class="form-group">
							<label>SCI id:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_3" value="<?php echo $row['account_field_3']; ?>">
						</div>
						<div class="form-group">
							<label>SCI pass:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_4" value="<?php echo $row['account_field_4']; ?>">
						</div>
						<?php
					} elseif($gateway == "SolidTrust Pay") {
						?>
						<div class="form-group">
							<label>Your SolidTrust Pay account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<div class="form-group">
							<label>SCI Name:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_2" value="<?php echo $row['account_field_2']; ?>">
						</div>
						<div class="form-group">
							<label>SCI Password:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_3" value="<?php echo $row['account_field_3']; ?>">
						</div>
						<?php
					} elseif($gateway == "Neteller") {
						?>
						<div class="form-group">
							<label>Your Neteller account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "UQUID") {
						?>
						<div class="form-group">
							<label>Your UQUID account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Wex") {
						?>
						<div class="form-group">
							<label>Your Wex.nz account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Yandex Money") {
						?>
						<div class="form-group">
							<label>Your Yandex Money account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "QIWI") {
						?>
						<div class="form-group">
							<label>Your QIWI account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Payza") {
						?>
						<div class="form-group">
							<label>Your Payza account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<div class="form-group">
							<label>IPN security code:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_2" value="<?php echo $row['account_field_2']; ?>">
						</div>
						<?php
					} elseif($gateway == "Bitcoin") { // Please enter a valid Bitcoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)
						?>
						<div class="form-group">
							<label>Your Bitcoin address:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<div class="form-group">
							<label>Your Block.io API Key (For Bitcoin Network):</label><br>
							<input type="text" class="form-control" size="40" name="account_field_2" value="<?php echo $row['account_field_2']; ?>"><br>
							<small>Please <a href="https://block.io" target="_blank">sign up</a> to get API detaials from Block.io.</small>
						</div>
						<div class="form-group">
							<label>Your Block.io Account Secret PIN:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_3" value="<?php echo $row['account_field_3']; ?>">
						</div>
						<div class="form-group">
							<label>Your Block.io Wallet Address:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_4" value="<?php echo $row['account_field_4']; ?>">
						</div>												
						<?php
					} elseif($gateway == "Litecoin") { //Please enter a valid Litecoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)
						?>
						<div class="form-group">
							<label>Your Litecoin address:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Dogecoin") { //Please enter a valid Dogecoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)
						?>
						<div class="form-group">
							<label>Your Dogecoin address:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Dash") { //Please enter a valid Dash Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)
						?>
						<div class="form-group">
							<label>Your Dash address:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Peercoin") { //Please enter a valid Peercoin Address (ex: 1XXXXxxXXx1XXx2xxX3XX456xXx)
						?>
						<div class="form-group">
							<label>Your Peercoin address:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Ethereum") { // Please enter a valid Ethereum Address (ex: 0xaax00110aax00110aax00110aax00110aax00110)
						?>
						<div class="form-group">
							<label>Your Ethereum address:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<?php
					} elseif($gateway == "Bank Transfer") {
						?>
						<div class="form-group">
							<label>Bank Account Holder's Name:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<div class="form-group">
							<label>Bank Account Number/IBAN:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_2" value="<?php echo $row['account_field_2']; ?>">
						</div>
						<div class="form-group">
							<label>SWIFT Code:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_3" value="<?php echo $row['account_field_3']; ?>">
						</div>
						<div class="form-group">
							<label>Bank Name in Full:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_4" value="<?php echo $row['account_field_4']; ?>">
						</div>
						<div class="form-group">
							<label>Bank Branch Country, City, Address:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_5" value="<?php echo $row['account_field_5']; ?>">
						</div>
						<?php
					} elseif($gateway == "Western Union") {
						?>
						<div class="form-group">
							<label>Recipient name:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<div class="form-group">
							<label>Recipient location:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_2" value="<?php echo $row['account_field_2']; ?>">
						</div>
						<?php
					} elseif($gateway == "Moneygram") {
						?>
						<div class="form-group">
							<label>Recipient name:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>
						<div class="form-group">
							<label>Recipient location:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_2" value="<?php echo $row['account_field_2']; ?>">
						</div>
						<?php
					} else { ?>
						<div class="form-group">
							<label>Your <?php echo substr($row['gateway_name'], 0, 50); ?> account:</label><br>
							<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo $row['account_id']; ?>">
						</div>					
					<?php } ?>
				</div>
          <div class="form-group">
            <label>Status:</label><br>
			<select name="status" class="selectpicker">
				<option value="active" <?php if ($row['status'] == "active") echo "selected"; ?>>active</option>
				<option value="inactive" <?php if ($row['status'] == "inactive") echo "selected"; ?>>inactive</option>
			</select>
          </div>
			<input type="hidden" name="did" id="did" value="<?php echo (int)$row['gateway_id']; ?>" />
			<input type="hidden" name="action" id="action" value="edit" />
			<button type="submit" name="save" id="save" class="btn btn-success">Save Settings</button>
			<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='gateways.php'" />
      </form>
      </div>

      <?php }else{ ?>
      		<h2>Gateway</h2>
			<div class="alert alert-info">Sorry, gateway not found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>