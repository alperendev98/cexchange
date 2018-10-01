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

	$cpage = 19;

	//CheckAdminPermissions($cpage);

	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
		unset($errs);
		$errs = array();

		$gateway_name 		= mysqli_real_escape_string($conn, getPostParameter('gateway_name'));

		$account_field_1	= mysqli_real_escape_string($conn, getPostParameter('account_field_1'));
		$account_field_2 	= mysqli_real_escape_string($conn, getPostParameter('account_field_2'));
		
		$account_field_3 	= mysqli_real_escape_string($conn, getPostParameter('account_field_3'));
		$account_field_4 	= mysqli_real_escape_string($conn, getPostParameter('account_field_4'));
		$account_field_5 	= mysqli_real_escape_string($conn, getPostParameter('account_field_5'));

		if(!($gateway_name))
		{
			$errs[] = "Please enter gateway name";
		}
		else
		{
			$check_query = smart_mysql_query("SELECT * FROM exchangerix_gateways WHERE gateway_name='$gateway_name'");
			if (mysqli_num_rows($check_query) != 0)
			{
				$errs[] = "Sorry, gateway with same name is exists";
			}
		}

		if (count($errs) == 0)
		{
			$sql = "INSERT INTO exchangerix_gateways SET gateway_name='$gateway_name', account_id='$account_field_1', gateway_description='', status='active', added=NOW()";

			if (smart_mysql_query($sql))
			{
				header("Location: gateways.php?msg=added");
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

	$title = "Add Gateway";
	require_once ("inc/header.inc.php");

?>

        <h2>Add Gateway</h2>

		<?php if (isset($errormsg) && $errormsg != "") { ?>
			<div class="alert alert-danger"><?php echo $errormsg; ?></div>
		<?php } ?>

		<div style="width: 100%; padding: 10px; background: #F9F9F9">
        <form action="" method="post">
          <div class="form-group">
            <label><span class="req">* </span>Gateway Name:</label><br>
            <input type="text" name="gateway_name" id="gateway_name" value="<?php echo getPostParameter('gateway_name'); ?>" size="40" class="form-control" />
          </div>
		<div class="form-group">
			<label><span class="req">* </span>Account ID:</label><br>
			<input type="text" class="form-control" size="40" name="account_field_1" value="<?php echo getPostParameter('account_field_1'); ?>">
		</div>          
			<input type="hidden" name="action"id="action" value="add" />
			<button type="submit" name="add" id="add" class="btn btn-success">Add Gateway</button>
			<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='gateways.php'" />
      	</form>
		</div>


<?php require_once ("inc/footer.inc.php"); ?>