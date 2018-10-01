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

	$cpage = 12;

	CheckAdminPermissions($cpage);

	// add ////////////////////////////////////////
	if (isset($_POST['action']) && $_POST['action'] == "add")
	{
			unset($errors);
			$errors = array();

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
			$status					= "active";

			if (!($from_currency && $to_currency && $from_rate && $to_rate))
			{
				$errs[] = "Please ensure that all fields marked with an asterisk are complete";
			}
			else
			{
				$check_query = smart_mysql_query("SELECT * FROM exchangerix_exdirections WHERE from_currency='$from_currency' AND to_currency='$to_currency'"); //AND status='active'
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
					//like function //dev
					$fsym = GetCurrencyCode($from_currency);
					$tsyms = GetCurrencyCode($to_currency);
					$url = "https://min-api.cryptocompare.com/data/price?fsym=".$fsym."&tsyms=".$tsyms;
					//https://min-api.cryptocompare.com/data/price?fsym=BTC&tsyms=USD,EUR
					//https://www.cryptocompare.com/api/#public-api-invocation
					$json = json_decode(file_get_contents($url), true);
					
					if ($json["Response"] != "Error")
					{
						//if (strstr($json[$tsyms], 'e'))
						//{
						$from_rate = 1;
						$to_rate = floatval($json[$tsyms]);
						//}
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
				
				$insert_sql = "INSERT INTO exchangerix_exdirections SET from_currency='$from_currency', to_currency='$to_currency', from_rate='$from_rate', to_rate='$to_rate', exchange_rate='$exchange_rate', auto_rate='$auto_rate', fee='$fee', min_amount='$min_amount', max_amount='$max_amount', user_instructions='$instructions', description='$description', is_manual='$is_manual', hide_from_visitors='$hide_from_visitors', allow_affiliate='$allow_affiliate', sort_order='$sort_order', status='$status', added=NOW()"; //user_id='0'
				$result = smart_mysql_query($insert_sql);
				$new_coupon_id = mysqli_insert_id($conn);

				header("Location: exdirections.php?msg=added");
				exit();
			}
			else
			{
				$errormsg = "";
				foreach ($errs as $errorname)
					$errormsg .= $errorname."<br/>";
			}
	}
	//////////////////////////////////////////////////

	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
	{
		$did	= (int)$_GET['id'];
		//DeleteExDirection($did);
		smart_mysql_query("DELETE FROM exchangerix_exdirections WHERE exdirection_id='$did'");
		header("Location: exdirections.php?msg=deleted");
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
			$sorts_arr	= $_POST['from_rate'];
			
			$from_rate	= array();
			$from_rate	= $_POST['from_rate'];
			
			$to_rate	= array();
			$to_rate	= $_POST['to_rate'];

			if (count($sorts_arr) > 0)
			{
				foreach ($sorts_arr as $k=>$v)
				{
					$new_from_rate = (float)$from_rate[$k];
					$new_to_rate = (float)$to_rate[$k];
					
					$up_query = "";
					
					if ($new_from_rate > 0) 	$up_query .= "from_rate='".$new_from_rate."',";
					if ($new_to_rate > 0) 		$up_query .= "to_rate='".$new_to_rate."',";
					
					if ($new_to_rate > 0 && $new_to_rate > 0) { $exchange_rate = $new_to_rate/$new_from_rate; $up_query .= "exchange_rate='$exchange_rate',"; }elseif($new_to_rate == 0) { $up_query .= "status='inactive',"; }
					
					smart_mysql_query("UPDATE exchangerix_exdirections SET $up_query sort_order='".(int)$v."' WHERE exdirection_id='".(int)$k."'");
				}
			}
			
			header("Location: exdirections.php?msg=updated");
			exit();
		}

		// Delete //
		if (isset($_POST['delete']))
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$did = (int)$v;
					smart_mysql_query("DELETE FROM exchangerix_exdirections WHERE exdirection_id='$did'");
					//DeleteExDirection($did);
				}

				header("Location: exdirections.php?msg=deleted");
				exit();
			}
		}

		$where = "1=1";

		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "title": $rrorder = "title"; break;
					case "sort_order": $rrorder = "sort_order"; break;
					case "added": $rrorder = "added"; break;
					case "last_visit": $rrorder = "last_visit"; break;
					case "auto_rate": $rrorder = "auto_rate"; break;
					case "total_exchanges": $rrorder = "total_exchanges"; break;
					case "today_exchanges": $rrorder = "today_exchanges"; break;
					case "visists": $rrorder = "visits"; break;
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

		/*		
		if (isset($_GET['store']) && $_GET['store'] != "")
		{
			$store = substr(trim(getGetParameter('store')), 0, 10);
			$store = mysqli_real_escape_string($conn, $store); //dev
			//$where .= " AND currency_code='$store' ";
			$title2 = $store;
		}*/
		
		if (isset($_GET['from_filter']) && is_numeric($_GET['from_filter']))
		{
			$from_filter = (int)$_GET['from_filter'];
			$where .= " AND from_currency='$from_filter' ";
			$title2 .= " ".GetCurrencyName($from_filter)." <i class='fa fa-arrow-right' aria-hidden='true'></i> ";
		}		
		
		if (isset($_GET['to_filter']) && is_numeric($_GET['to_filter']))
		{
			$to_filter = (int)$_GET['to_filter'];
			$where .= " AND to_currency='$to_filter' ";
			$title2 .= "<i class='fa fa-arrow-left' aria-hidden='true'></i> ".GetCurrencyName($to_filter);
		}		

		$query = "SELECT *, DATE_FORMAT(added, '".DATE_FORMAT."') AS date_added FROM exchangerix_exdirections WHERE $where ORDER BY $rrorder $rorder, added DESC LIMIT $from, $results_per_page";
		
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM exchangerix_exdirections WHERE ".$where;
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;

		//$title = $title2." Exchange Directions";
		$title = "Exchange Directions";
		require_once ("inc/header.inc.php");

?>

	<div id="add_new_form" style="display: <?php echo ($_POST['action']) ? "" : "none"; ?>">
    <h2><i class="fa fa-arrow-circle-right" aria-hidden="true"></i> <i class="fa fa-arrow-circle-left" aria-hidden="true"></i> Add Exchange Direction</h2>
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
									$sql_curr_send = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE allow_send='1' AND status='active' ORDER BY currency_name ASC"); 
									while ($row_curr_send = mysqli_fetch_array($sql_curr_send))
									{
										if ($from_currency == $row_curr_send['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
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
								<select name="auto_rate" id="auto_rate" class="form-control">
									<option value="0" <?php if (@$auto_rate == "0") echo "selected"; ?>>no</option>
									<option value="1" <?php if (@$auto_rate == "1") echo "selected"; ?>>yes</option>
								</select>
								<span class="note" title="auto update exchange rate">	
							</td>
						</tr>			          
			          <tr id="exchange_rate_box" <?php /*if (getPostParameter('auto_rate') == 1) { ?>style="display: none;" <?php }else{ ?>style="display: ;"<?php }*/ ?>>
			            <td valign="middle" align="left" class="tb1"><span class="req">* </span>Exchange Rate:</td>
			            <td valign="middle">
				            <input type="text" name="from_rate" id="from_rate" value="<?php echo getPostParameter('from_rate'); ?>" size="18" class="form-control" />
				            <span id="curr_box" style="display: none; padding-left: 5px;"></span>
				            <span style="float: right; padding: 8px 80px 0 0;"> = </span> </td>
			          </tr>
						<tr>
							<td valign="middle" align="left" class="tb1">Fee:</td>
							<td valign="top"><input type="text" name="fee" id="fee" value="<?php echo (@$fee) ? getPostParameter('fee') : "0"; ?>" size="5" class="form-control" /><span class="note" title="eg. 5% or 10 (do not use currency code)"></td>
						</tr>
			            <tr>
							<td valign="middle" align="left" class="tb1">Min Amount:</td>
							<td valign="middle"><input type="text" name="min_amount" id="min_amount" value="<?php echo getPostParameter('min_amount'); ?>" size="18" class="form-control"><span class="note" title="minimum amount for exchange (leave empty or fill zero for no limit)"></td>
			            </tr>
			            <tr>
							<td valign="middle" align="left" class="tb1">Max Amount:</td>
							<td valign="middle"><input type="text" name="max_amount" id="max_amount" value="<?php echo getPostParameter('max_amount'); ?>" size="18" class="form-control"><span class="note" title="maximum amount for exchange (leave empty or fill zero for no limit)"></td>
			            </tr><!-- Max amount for auto exchange, auto after 3 success manually, discount for users //dev -->
						<tr>
							<td valign="middle" align="left" class="tb1">Hide from unregistered users:</td>
							<td valign="top">
								<select name="hide_from_visitors" class="form-control">
									<option value="0" <?php if (@$hide_from_visitors == "0") echo "selected"; ?>>no</option>
									<option value="1" <?php if (@$hide_from_visitors == "1") echo "selected"; ?>>yes</option>
								</select>				
							</td>
						</tr> 
						<tr>
							<td valign="middle" align="left" class="tb1">Allow affiliate commission:</td>
							<td valign="top">
								<select name="allow_affiliate" class="form-control">
									<option value="1" <?php if (@$allow_affiliate == "1") echo "selected"; ?>>yes</option>
									<option value="0" <?php if (@$allow_affiliate == "0") echo "selected"; ?>>no</option>
								</select>					
							</td>
						</tr>
						<tr>
							<td valign="middle" align="left" class="tb1">Manual Processing:</td>
							<td valign="top">
								<select name="is_manual" class="form-control">
									<option value="1" <?php if (@$is_manual == "1") echo "selected"; ?>>yes</option>
									<option value="0" <?php if (@$$is_manual == "0") echo "selected"; ?>>no</option>
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
									$sql_curr_receive = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE allow_receive='1' AND status='active' ORDER BY currency_name ASC");
									while ($row_curr_receive = mysqli_fetch_array($sql_curr_receive))
									{
										if ($to_currency == $row_curr_receive['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
										echo "<option value=\"".$row_curr_receive['currency_id']."\"".$selected.">".$row_curr_receive['currency_name'];
										//if ($row_curr_receive['is_crypto'] != 1 && $row_curr_receive['hide_code'] != 1)
										echo " ".$row_curr_receive['currency_code'];
										echo "</option>";							
									}
								?>
							</select>
							
							<!--
							https://finance.google.com/finance/converter?a=1&from=BTC&to=USD&meta=ei%3D_kTXWcG6K8-SswHvj4D4Dw  //dev
							<?php //echo currencyConvertor2("btc","usd"); ?>
							For other cryptocurrencies like Litecoin, Dogecoin, and TheBillioncoin need to enter rates manually in this page.
							 wex.nz - show current rate soryy auto not available for your currency
							-->
					   </td>
			          </tr>
						<tr>
							<td height="35" valign="top">&nbsp;</td>
						</tr>			          
			          <tr id="exchange_rate_box2" <?php /*if (getPostParameter('auto_rate') == 1) { ?>style="display: none;" <?php }else{ ?>style="display: ;"<?php }*/ ?>>
			            <td valign="middle">
				            <input type="text" name="to_rate" id="to_rate" value="<?php echo getPostParameter('to_rate'); ?>" size="18" class="form-control" />
							<span id="curr_box2" style="display: none; padding-left: 5px;"></span>
			            </td>
			          </tr>
						<tr>
							<td valign="top"><input type="text" name="" id="" value="0" size="5" class="form-control" /><span class="note" title="our site -> user's account transfer fee"></td>
						</tr>
			            <tr>
							<td valign="middle"><input type="text" name="" id="" value="" size="18" class="form-control" /><span class="note" title="leave empty to use reserve value"></td>
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
						<td valign="top"><textarea name="instructions" cols="112" rows="5" style="width:100%;" class="form-control"><?php echo getPostParameter('instructions'); ?></textarea></td>
		            </tr>
					<tr>
						<td valign="middle" align="left" class="tb1">Description:</td>
						<td valign="top"><textarea name="description" id="editor1" cols="75" rows="8" class="form-control"><?php echo getPostParameter('description'); ?></textarea></td>
		            </tr>
						<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
						<script>
							CKEDITOR.replace( 'editor1' );
						</script>            
		            <!--
			        <tr>
						<td valign="middle" align="left" class="tb1">Sort Order:</td>
						<td valign="middle"><input type="text" class="form-control" name="sort_order" value="<?php echo getPostParameter('sort_order'); ?>" size="5" /></td>
		            </tr>
		            -->
		            <tr>
						<td valign="middle" align="left" class="tb1">Status:</td>
						<td valign="middle">
							<select name="status" class="selectpicker">
								<option value="active" <?php if ($status == "active") echo "selected"; ?>>active</option>
								<option value="inactive" <?php if ($status == "inactive") echo "selected"; ?>>inactive</option>
							</select>
						</td>
		            </tr>            
		        </table>				     
				     
				     
			     </td>
		     </tr>
		     <tr>
			     <td colspan="2" align="center">
				    <br>
					<input type="hidden" name="action" id="action" value="add">
					<input type="submit" class="btn btn-success" name="add" id="add" value="Add Direction" />
					<input type="button" class="btn btn-default" name="cancel" value="Cancel" onclick="$('#add_new_form').hide();$('#all_list').show();" />
			     </td>
		     </tr>
	     </table>
       
        
          </table>
      </form>

	  </div>       
  
 
	

	<div id="all_list" style="display: <?php echo ($_POST['action'] || $_GET['id']) ? "none" : ""; ?>">

		<div id="addnew" style="margin: -10px 0">
			<a class="addnew" href="#" onclick="$('#add_new_form').toggle('fast');$('.error_box').hide();$('#all_list').toggle('fast');">Add Direction</a><br>
		</div>

		<h2><i class="fa fa-exchange" aria-hidden="true"></i> <?php echo $title2; ?> Exchange Directions <?php echo @$totitle; ?> <?php if ($total > 0) { ?><sup class="badge" style="background: #73b9d1"><?php echo number_format($total); ?></sup><?php } ?></h2>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "added": echo "Exchange direction has been successfully added"; break;
						case "updated": echo "Exchange directions has been successfully edited"; break;
						case "deleted": echo "Exchange direction has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>
			
			
					
		<script type="text/javascript" src="<?php echo SITE_URL; ?>js/jquery.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				//$("#eee1").show().delay(3000).fadeIn();
				$("#loader").hide().delay(3000).fadeOut();
			});
		</script>
		

		<center><img src="images/loading.gif" id="loader" /></center>
		

		<form id="form1" name="form1" method="get" action="">
		<div class="row" style="background:#F9F9F9; margin: 10px 0; padding: 7px 0;" id="eee1">
		<div class="col-md-5" style="white-space: nowrap">
           Sort by: 
          <select name="column" id="column" class="form-control" onChange="document.form1.submit()">
			<option value="sort_order" <?php if ($_GET['column'] == "sort_order") echo "selected"; ?>>Sort Order</option>
			<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Newest</option>
			<option value="total_exchanges" <?php if ($_GET['column'] == "total_exchanges") echo "selected"; ?>>Popularity</option>
			<!--
			<option value="last_exchange_date" <?php if ($_GET['column'] == "last_exchange_date") echo "selected"; ?>>Latest Used</option>
			<option value="title" <?php if ($_GET['column'] == "title") echo "selected"; ?>>Title</option>
			-->
			<option value="from_currency" <?php if ($_GET['column'] == "from_currency") echo "selected"; ?>>Send Currency</option>
			<option value="to_currency" <?php if ($_GET['column'] == "to_currency") echo "selected"; ?>>Receive Currency</option>			
			<option value="auto_rate" <?php if ($_GET['column'] == "auto_rate") echo "selected"; ?>>Auto Rate</option>
			<option value="fee" <?php if ($_GET['column'] == "fee") echo "selected"; ?>>Fee</option>
			<option value="today_exchanges" <?php if ($_GET['column'] == "today_exchanges") echo "selected"; ?>>Today Exchanges</option>
			<option value="total_exchanges" <?php if ($_GET['column'] == "total_exchanges") echo "selected"; ?>>Total Exchanges</option>
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
			<?php if ($from_filter) { ?><input type="hidden" name="from_filter" value="<?php echo $from_filter; ?>" /><?php } ?>
			<?php if ($to_filter) { ?><input type="hidden" name="to_filter" value="<?php echo $to_filter; ?>" /><?php } ?>
		</div>
		<div class="col-md-5 text-center" style="white-space: nowrap">
				<select name="from_filter" id="from_filter" onChange="document.form1.submit()" style="width: 130px;" class="form-control">
				<option value="">--- send ---</option>
					<?php
						$sql_curr_send = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE allow_send='1' AND status='active' ORDER BY currency_name ASC"); 
						while ($row_curr_send = mysqli_fetch_array($sql_curr_send))
						{
							if ($from_filter == $row_curr_send['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
							echo "<option value=\"".$row_curr_send['currency_id']."\"".$selected.">".$row_curr_send['currency_name'];
							if ($row_curr_send['is_crypto'] != 1) echo " ".$row_curr_send['currency_code'];
							echo "</option>";
						}
					?>
				</select>
				<select name="to_filter" id="to_filter" onChange="document.form1.submit()" style="width: 130px;" class="form-control">
					<option value="">--- receive ---</option>
					<?php
						$sql_curr_receive = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE allow_receive='1' AND status='active' ORDER BY currency_name ASC");
						while ($row_curr_receive = mysqli_fetch_array($sql_curr_receive))
						{
							if ($to_filter == $row_curr_receive['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
							echo "<option value=\"".$row_curr_receive['currency_id']."\"".$selected.">".$row_curr_receive['currency_name'];
							if ($row_curr_receive['is_crypto'] != 1) echo " ".$row_curr_receive['currency_code'];
							echo "</option>";							
						}
					?>
				</select>
				<input type="submit" class="btn btn-success" value="Filter" />							
				<?php if ($from_filter || $to_filter) { ?><a href="exdirections.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Delete Filter" /></a><?php } ?>
		</div>
		<div class="col-md-2 text-right" style="white-space: nowrap; padding-top: 8px;">
			<?php if ($total > 0) { ?>Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?><?php } ?>
		</div>
		</div>
		</form>


			<form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="5%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<th width="37%">Exchange Direction</th>
				<th width="27%">Exchange Rate</th>
				<th width="7%">Min<br> Amount</th>
				<th width="7%">Max<br> Amount</th>
				<th width="7%">Fee</th>
				<th width="11%"><i class="fa fa-refresh" aria-hidden="true"></i> Exchanges<br/><span style="font-weight:normal">all time / today</span></th>
				<th width="11%">Status</th>
				<th width="10%">Actions</th>
			</tr>
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
			<?php
					if ($row['auto_rate'] == 1)
					{
						$fsym = GetCurrencyCode($row['from_currency']);
						$tsyms = GetCurrencyCode($row['to_currency']);
						$url = "https://min-api.cryptocompare.com/data/price?fsym=".$fsym."&tsyms=".$tsyms;
						$json = json_decode(file_get_contents($url), true);
						
						if ($json["Response"] != "Error")
						{
							$json[$tsyms] = strtolower($json[$tsyms]);
							if ($json[$tsyms] < 0.01) //if (strstr($json[$tsyms], "e")) die(W);
							{
								$json2 = json_decode(file_get_contents("https://min-api.cryptocompare.com/data/price?fsym=".$tsyms."&tsyms=".$fsym), true);
								$row['from_rate'] = $json2[$fsym];
								$row['to_rate'] = 1;	
								//print_r($json2[$tsyms]); die();
							}
							else
							{
								$row['from_rate'] = 1;
								$row['to_rate'] = floatval($json[$tsyms]);
							}						
							
							$exchange_rate  = $row['to_rate']/$row['from_rate'];
							
							smart_mysql_query("UPDATE exchangerix_exdirections SET from_rate='".$row['from_rate']."', to_rate='".$row['to_rate']."', exchange_rate='$exchange_rate', updated=NOW() WHERE exdirection_id='".(int)$row['exdirection_id']."' LIMIT 1");	
						}
					}		
			?>							  
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['exdirection_id']; ?>]" id="id_arr[<?php echo $row['exdirection_id']; ?>]" value="<?php echo $row['exdirection_id']; ?>" /></td>
					<td align="left" valign="middle">
						<br>
						<a href="currency_details.php?id=<?php echo $row['from_currency']; ?>" style="color: #000"><?php echo GetCurrencyImg($row['from_currency'], $width = 25); ?> <b><?php echo GetCurrencyName($row['from_currency']); ?></b></a> <i class="fa fa-long-arrow-right fa-lg" aria-hidden="true"></i> <a href="currency_details.php?id=<?php echo $row['to_currency']; ?>" style="color: #000"><?php echo GetCurrencyImg($row['to_currency'], $width = 25); ?> <b><?php echo GetCurrencyName($row['to_currency']); ?></b></a><br>
						<div style="margin: 5px 0">
							<?php if ($row['is_manual'] == 1) { ?><span class="label label-default" style="background: #BBB"><i class="fa fa-hand-o-right fa-lg" aria-hidden="true"></i> manual</span><?php }else{ ?><span class="label label-info"><i class="fa fa-refresh fa-lg" aria-hidden="true"></i> auto</span><?php } ?>
							<?php if ($row['hide_from_visitors'] == 1) { ?><span id="itooltip" title="hidden from guests (unregistered users)" class="label label-default"><i class="fa fa-eye fa-lg" aria-hidden="true"></i> hidden</span><?php } ?>
						</div>
						<small><?php echo GetCurrencyName($row['from_currency']); ?> reserve: <b><?php echo GetCurrencyReserve($row['from_currency']); ?></b> <?php echo GetCurrencyCode($row['from_currency']); ?>
						<br> <?php echo GetCurrencyName($row['to_currency']); ?> reserve: <b><?php echo GetCurrencyReserve($row['to_currency']); ?></b> <?php echo GetCurrencyCode($row['to_currency']); ?>
						</small>
						<br>
						<br>
					</td>
					<td align="left" bgcolor="#edf4e6" valign="middle" nowrap style="padding: 0 5px; border-top: 1px solid #FFF">
						<input type="text" name="from_rate[<?php echo $row['exdirection_id']; ?>]" value="<?php echo $row['from_rate']; ?>" class="form-control" size="12" <?php echo ($row['auto_rate'] == 1) ? "readonly style='background: #d2e7a8'" : ""; ?>/> <?php echo GetCurrencyCode($row['from_currency']); ?> = 
						<input type="text" name="to_rate[<?php echo $row['exdirection_id']; ?>]" value="<?php echo $row['to_rate']; ?>" class="form-control" size="12" <?php echo ($row['auto_rate'] == 1) ? "readonly style='background: #d2e7a8'" : ""; ?>/> <?php echo GetCurrencyCode($row['to_currency']); ?>
						<?php if ($row['auto_rate'] == 1) { ?><br><center><span class="label label-success"><i class="fa fa-history"></i> auto rate update</span></center><?php } ?>
					</td>
					<td align="center" valign="middle" nowrap><?php echo ($row['min_amount'] > 0) ? $row['min_amount']." ".GetCurrencyCode($row['from_currency']) : "---"; ?></td>
					<td align="center" valign="middle" nowrap><?php echo ($row['max_amount'] > 0) ? $row['max_amount']." ".GetCurrencyCode($row['from_currency']) : "---"; ?></td>
					<td align="center" valign="middle"><?php if ($row['fee'] != "" && $row['fee'] != "0") { ?><?php echo (strstr($row['fee'], "%")) ? $row['fee'] : $row['fee']." ".GetCurrencyCode($row['from_currency']); ?><?php }else{ ?>---<?php } ?></td>		
					<td align="center" valign="middle"><span class="label label-success" style="background: #8dc6fb"><?php echo $row['total_exchanges']; ?></span> <sup><?php if($row['today_exchanges'] > 0) echo "+"; echo $row['today_exchanges']; ?></sup></td><!-- show up down compare yesterday //dev -->
					<td align="left" valign="middle" style="padding-left: 5px;">
						<?php
							switch ($row['status'])
							{
								case "active": echo "<span class='label label-success'>".$row['status']."</span>"; break;
								case "inactive": echo "<span class='label label-default'>".$row['status']."</span>"; break;
								case "expired": echo "<span class='expired_status'>".$row['status']."</span>"; break;
								default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
							}
						?>
						<?php echo ($row['start_date'] != "0000-00-00 00:00:00" && $row['time_left'] > 0) ? "<sup class='tooltip' title='will be available from ".$row['coupon_start_date']."' style='margin-left: 3px'>?</sup>" : ""; ?>
					</td>
					<td align="center" valign="middle" nowrap="nowrap">
						<a href="exdirection_details.php?id=<?php echo $row['exdirection_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="exdirection_edit.php?id=<?php echo $row['exdirection_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this exchange direction?') )location.href='exdirections.php?id=<?php echo $row['exdirection_id']; ?>&action=delete'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
				<td style="border-top: 1px solid #F5F5F5" colspan="9" align="left">
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="submit" class="btn btn-success" name="update" id="GoUpdate" value="Update" />
					<button type="submit" class="btn btn-danger" name="delete" id="GoButton1" disabled="disabled" onclick="return confirm('Are you sure you really want to delete?')" /><i class="fa fa-times" aria-hidden="true"></i> Delete Selected</button>
				</td>
				</tr>
          <?php }else{ ?>
				<tr>
				<td style="border-top: 1px solid #F5F5F5" colspan="9" align="left">
					<?php if (isset($filter)) { ?>
						<div class="alert alert-info">No directions found for your search criteria. <a href="exdirections.php">See all &#155;</a></div>
					<?php }else{ ?>
						<div class="alert alert-info">There are no exchange directions at this time. <?php if ($store) { ?><a href="exdirections.php">See all &#155;</a><?php } ?></div>
						<?php if ($_GET['store'] || $_GET['user']) { ?>
							<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
						<?php } ?>
					<?php } ?>
				</td>
				</tr>
          <?php } ?>
            </table>
			</form>

				<?php
							$params = "";

							if (@$_GET['column'])	$params .= "column=".$_GET['column']."&";
							if (@$_GET['order'])	$params .= "order=".$_GET['order']."&";
							if (@$from_filter)		$params .= "from_filter=$from_filter&";
							if (@$to_filter)		$params .= "to_filter=$to_filter&";
							if (@$_GET['show'])		$params .= "show=$results_per_page&";
							if (@$_GET['page'])		$params .= "page=$page&";

							echo ShowPagination("exdirections",$results_per_page,"exdirections.php?".$params, "WHERE ".$where);
				?>

		  </div>

<?php require_once ("inc/footer.inc.php"); ?>