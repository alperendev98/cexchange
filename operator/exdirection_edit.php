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

	if (isset($_POST["action"]) && $_POST["action"] == "edit")
	{
			unset($errors);
			$errors = array();

			$did					= (int)getPostParameter('did');
			$from_currency 		 	= (int)getPostParameter('from_currency');
			$to_currency  			= (int)getPostParameter('to_currency');
			$from_rate				= mysqli_real_escape_string($conn, getPostParameter('from_rate'));
			$to_rate				= mysqli_real_escape_string($conn, getPostParameter('to_rate'));
			$auto_rate				= (int)getPostParameter('auto_rate');
			$fee					= mysqli_real_escape_string($conn, getPostParameter('fee'));
			$min_amount				= mysqli_real_escape_string($conn, getPostParameter('min_amount'));
			$max_amount				= mysqli_real_escape_string($conn, getPostParameter('max_amount'));
			$instructions			= mysqli_real_escape_string($conn, nl2br(getPostParameter('instructions')));
			$description			= mysqli_real_escape_string($conn, $_POST['description']);
			$is_manual				= (int)getPostParameter('is_manual');
			$hide_from_visitors		= (int)getPostParameter('hide_from_visitors');
			$allow_affiliate		= (int)getPostParameter('allow_affiliate');
			$sort_order				= (int)getPostParameter('sort_order');
			$status					= mysqli_real_escape_string($conn, getPostParameter('status'));;

			if (!($from_currency && $to_currency && $from_rate && $to_rate))
			{
				$errs[] = "Please ensure that all fields marked with an asterisk are complete";
			}
			else
			{
				$check_query = smart_mysql_query("SELECT * FROM exchangerix_exdirections WHERE from_currency='$from_currency' AND to_currency='$to_currency' AND exdirection_id!='$did'"); //AND status='active'
				if (mysqli_num_rows($check_query) > 0)
				{
					$errs[] = "Sorry, current exchange direction is exists";
				}				
				
				if ((isset($from_rate) && $from_rate != "" && !is_numeric($from_rate)) || (isset($to_rate) && $to_rate != "" && !is_numeric($to_rate)))
					$errs[] = "Please enter correct exchange rate (numbers only)";
					
				if (isset($fee) && $fee != "" && !is_numeric($fee) && !strstr($fee, "%"))
					$errs[] = "Please enter correct fee value";					
					
				if (isset($min_amount) && $min_amount != "" && !is_numeric($min_amount))
					$errs[] = "Please enter correct minimum exchange value";
					
				if (isset($max_amount) && $max_amount != "" && !is_numeric($max_amount))
					$errs[] = "Please enter correct maximum exchange value";
					
				if (isset($min_amount) && is_numeric($min_amount) && isset($max_amount) && is_numeric($max_amount) && $min_amount>$max_amount)
					$errs[] = "Max exchange value cant be less than min value";
					
				if ($auto_rate == 1)
				{
					$fsym = GetCurrencyCode($from_currency);
					$tsyms = GetCurrencyCode($to_currency);
					$url = "https://min-api.cryptocompare.com/data/price?fsym=".$fsym."&tsyms=".$tsyms;
					$json = json_decode(file_get_contents($url), true);
					
					if ($json["Response"] != "Error")
					{
						//$from_rate = 1;
						//$to_rate = $json[$tsyms];
					}
					else
					{
						$errs[] = "Sorry, auto price update not available for these currencies";
					}
				}					
					
			}

			if (count($errs) == 0)
			{
				$exchange_rate 	= $to_rate/$from_rate;
				
				smart_mysql_query("UPDATE exchangerix_exdirections SET from_currency='$from_currency', to_currency='$to_currency', from_rate='$from_rate', to_rate='$to_rate', exchange_rate='$exchange_rate', auto_rate='$auto_rate', fee='$fee', min_amount='$min_amount', max_amount='$max_amount', user_instructions='$instructions', description='$description', is_manual='$is_manual', hide_from_visitors='$hide_from_visitors', allow_affiliate='$allow_affiliate', sort_order='$sort_order', status='$status', updated=NOW() WHERE exdirection_id='$did' LIMIT 1");

				header("Location: exdirections.php?msg=updated");
				exit();
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
		$id	= (int)$_GET['id'];

		$query = "SELECT * FROM exchangerix_exdirections WHERE exdirection_id='$id' LIMIT 1";
		$rs	= smart_mysql_query($query);
		$total = mysqli_num_rows($rs);
	}


	$title = "Edit Exchange Direction";
	require_once ("inc/header.inc.php");

?>


    <h2><i class="fa fa-arrow-circle-right" aria-hidden="true"></i> <i class="fa fa-arrow-circle-left" aria-hidden="true"></i> Edit Exchange Direction</h2>

	<?php if ($total > 0) {
		
		$row = mysqli_fetch_array($rs);

	?>

	<?php if (isset($errormsg) && $errormsg != "") { ?>
		<div class="alert alert-danger"><?php echo $errormsg; ?></div>
	<?php } ?>


		<form action="" method="post" name="form3">
	     <table style="background:#F9F9F9" width="100%" cellpadding="2" cellspacing="3" border="0" align="center">
		     <tr>
			     <td width="60%" valign="top">
				     
		     
			        <table style="background:#F9F9F9" width="100%" cellpadding="2" cellspacing="3" border="0" align="center">
			          <tr>
				          <td colspan="2" align="center"><h3><i class="fa fa-arrow-up" aria-hidden="true" style="color: #8dc6fb"></i> User Send</h3></td>
			          </tr>
			          <tr>
					   <td width="20%" valign="middle" align="left" class="tb1"><span class="req">* </span>Exchange Direction:</td>
					   <td valign="top">
							<select class="form-control" id="from_currency" name="from_currency" required>
							<option value="">--- select ---</option>
								<?php
									$sql_curr_send = smart_mysql_query("SELECT * FROM exchangerix_currencies ORDER BY currency_name ASC");
									// WHERE allow_send='1' AND status='active' 
									while ($row_curr_send = mysqli_fetch_array($sql_curr_send))
									{
										if ($row['from_currency'] == $row_curr_send['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
										echo "<option value=\"".$row_curr_send['currency_id']."\"".$selected.">".$row_curr_send['currency_name'];
										//if ($row_curr_send['is_crypto'] != 1 && $row_curr_send['hide_code'] != 1)
										echo " ".$row_curr_send['currency_code'];
										echo "</option>";
									}
								?>
							</select>
					   </td>
			          </tr>
						<tr>
							<td valign="middle" align="left" class="tb1">Auto Rate:</td>
							<td valign="top">
								<select name="auto_rate" class="form-control" onchange="check_auto_rate(this.value);">
									<option value="0" <?php if ($row['auto_rate'] == "0") echo "selected"; ?>>no</option>
									<option value="1" <?php if ($row['auto_rate'] == "1") echo "selected"; ?>>yes</option>
								</select>
								<span class="note" title="auto update exchange rate">				
							</td>
						</tr>			          
			          <tr>
			            <td valign="middle" align="left" class="tb1"><span class="req">* </span>Exchange Rate:</td>
			            <td valign="middle">
				            <input type="text" name="from_rate" id="from_rate" value="<?php echo $row['from_rate']; ?>" size="18" class="form-control" />
				            <span id="curr_box" style="display: none; padding-left: 5px;"></span>
				            <span style="float: right; padding: 8px 80px 0 0;"> = </span> </td>
			          </tr>
						<tr>
							<td valign="middle" align="left" class="tb1">Fee:</td>
							<td valign="top"><input type="text" name="fee" id="fee" value="<?php echo ($row['fee'] > 0) ? $row['fee'] : "0"; ?>" size="5" class="form-control" /><span class="note" title="eg. 5% or 10 (do not use currency code)"></td>
						</tr>
			            <tr>
							<td valign="middle" align="left" class="tb1">Min Amount:</td>
							<td valign="middle"><input type="text" name="min_amount" id="min_amount" value="<?php echo $row['min_amount']; ?>" size="18" class="form-control"><span class="note" title="minimum amount for exchange (leave empty or fill zero for no limit)"></td>
			            </tr>
			            <tr>
							<td valign="middle" align="left" class="tb1">Max Amount:</td>
							<td valign="middle"><input type="text" name="max_amount" id="max_amount" value="<?php echo $row['max_amount']; ?>" size="18" class="form-control"><span class="note" title="maximum amount for exchange (leave empty or fill zero for no limit)"></td>
			            </tr><!-- Max amount for auto exchange, auto after 3 sucees manually , discount for users //dev -->
						<tr>
							<td valign="middle" align="left" class="tb1">Hide from unregistered users:</td>
							<td valign="top">
								<select name="hide_from_visitors" class="form-control">
									<option value="0" <?php if ($row['hide_from_visitors'] == "0") echo "selected"; ?>>no</option>
									<option value="1" <?php if ($row['hide_from_visitors'] == "1") echo "selected"; ?>>yes</option>
								</select>				
							</td>
						</tr> 
						<tr>
							<td valign="middle" align="left" class="tb1">Allow affiliate commission:</td>
							<td valign="top">
								<select name="allow_affiliate" class="form-control">
									<option value="1" <?php if ($row['allow_affiliate'] == "1") echo "selected"; ?>>yes</option>
									<option value="0" <?php if ($row['allow_affiliate'] == "0") echo "selected"; ?>>no</option>
								</select>					
							</td>
						</tr>
						<tr>
							<td valign="middle" align="left" class="tb1">Manual Processing:</td>
							<td valign="top">
								<select name="is_manual" class="form-control">
									<option value="1" <?php if ($row['is_manual'] == "1") echo "selected"; ?>>yes</option>
									<option value="0" <?php if ($row['is_manual'] == "0") echo "selected"; ?>>no</option>
								</select>					
							</td>
						</tr>						
						</table>
			
        
			     </td>
			     <td width="40%" align="left" valign="top">
        
					<table style="background:#F9F9F9" width="100%" cellpadding="2" cellspacing="3" border="0" align="center">
			          <tr>
				          <td colspan="2"><h3>User Receive <i class="fa fa-arrow-down" aria-hidden="true" style="color: #5cb85c"></i></h3></td>
			          </tr>
			          <tr>
					   <td valign="top">
							<select class="form-control" id="to_currency" name="to_currency" required>
							<option value="">--- select ---</option>
								<?php
									$sql_curr_receive = smart_mysql_query("SELECT * FROM exchangerix_currencies ORDER BY currency_name ASC");
									// WHERE allow_receive='1' AND status='active'
									while ($row_curr_receive = mysqli_fetch_array($sql_curr_receive))
									{
										if ($row_curr_receive['currency_id'] == $row['from_currency']) $disabled = "disabled"; else $disabled = "";
										if ($row['to_currency'] == $row_curr_receive['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
										echo "<option value=\"".$row_curr_receive['currency_id']."\"".$selected." $disabled>".$row_curr_receive['currency_name'];
										//if ($row_curr_receive['is_crypto'] != 1 && $row_curr_receive['hide_code'] != 1)
										echo " ".$row_curr_receive['currency_code'];
										echo "</option>";							
									}
								?>
							</select>
					   </td>
			          </tr>
						<tr>
							<td height="35" valign="top">&nbsp;</td>
						</tr>			          
			          <tr>
			            <td valign="middle">
				            <input type="text" name="to_rate" id="to_rate" value="<?php echo $row['to_rate']; ?>" size="18" class="form-control" />
							<span id="curr_box2" style="display: none; padding-left: 5px;"></span>
			            </td>
			          </tr>
						<tr>
							<td valign="top"><input type="text" name="" id="" value="0" size="5" class="form-control" /><span class="note" title="our site -> user's account transfer fee"></td>
						</tr>
			            <tr>
							<td valign="middle"><input type="text" name="" id="" value="" size="18" class="form-control" /><span class="note" title="leave empty to use reserve value"></td><!-- //dev-->
			            </tr>
			            <tr>
							<td valign="middle"><input type="text" name="" id="" value="" size="18" class="form-control" /><span class="note" title="leave empty to use reserve value"></td>
			            </tr>
			            <tr>
							<td valign="middle"></td>
			            </tr>
			        </table>   
        
			     </td>
		     </tr>
		     <tr>
			     <td colspan="2" align="left" valign="top">   

					<table style="background:#F9F9F9" width="100%" cellpadding="2" cellspacing="3" border="0" align="center">			           
					<tr>
						<td width="19%" valign="middle" align="left" class="tb1">Instructions for user:</td>
						<td valign="top"><textarea name="instructions" cols="112" rows="5" style="width:100%;" class="form-control"><?php echo strip_tags($row['user_instructions']); ?></textarea></td>
		            </tr>
					<tr>
						<td valign="middle" align="left" class="tb1">Description:</td>
						<td valign="top"><textarea name="description" id="editor1" cols="75" rows="8" class="form-control"><?php echo stripslashes($row['description']); ?></textarea></td>
		            </tr>
						<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
						<script>
							CKEDITOR.replace( 'editor1' );
						</script>            
		            <!--
		            <tr>
						<td valign="middle" align="left" class="tb1">Sort Order:</td>
						<td valign="middle"><input type="text" class="form-control" name="sort_order" value="<?php echo $row['sort_order']; ?>" size="5" /></td>
		            </tr>
		            -->
		            <tr>
						<td valign="middle" align="left" class="tb1">Status:</td>
						<td valign="middle">
							<select name="status" class="selectpicker">
								<option value="active" <?php if ($row['status'] == "active") echo "selected"; ?>>active</option>
								<option value="inactive" <?php if ($row['status'] == "inactive") echo "selected"; ?>>inactive</option>
							</select>
						</td>
		            </tr>            
		        </table>				     
				     
				     
			     </td>
		     </tr>
		     <tr>
			     <td colspan="2" align="center">
				     <br>
					<input type="hidden" name="did" id="did" value="<?php echo (int)$row['exdirection_id']; ?>" />
					<input type="hidden" name="action" id="action" value="edit">
					<input type="submit" class="btn btn-success" name="update" id="update" value="Update Direction" />
					<input type="button" class="btn btn-default" name="cancel" value="Cancel" onclick="history.go(-1);return false;" />
			     </td>
		     </tr>
	     </table>
      </form>



      <?php }else{ ?>
			<div class="alert alert-info">Sorry, no exchange direction found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>