<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	session_start();
	require_once("inc/auth.inc.php");
	require_once("inc/config.inc.php");
	require_once("inc/pagination.inc.php");

	// cancel pending withdrawal request
	if (isset($_GET['act']) && $_GET['act'] == "cancel" && CANCEL_WITHDRAWAL == 1)
	{
		$transaction_id = (int)$_GET['id'];
		smart_mysql_query("DELETE FROM exchangerix_transactions WHERE user_id='$userid' AND transaction_id='$transaction_id' AND payment_type='Withdrawal' AND status='request'");
		header("Location: mybalance.php?msg=cancelled");
		exit();
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = CBE1_BALANCE_TITLE;

	require_once ("inc/header.inc.php");

?>
		<div class="row">
			<div class="col-md-12 hidden-xs">
			<div id="acc_user_menu">
				<ul><?php require("inc/usermenu.inc.php"); ?></ul>
			</div>
		</div>

		<h1><!--<i class="fa fa-exchange" aria-hidden="true"></i>--> <?php echo CBE1_BALANCE_TITLE; ?></h1>


					<h2 class="text-right"><i class="fa fa-money icircle" style="color: #5bbc2e"></i> Balance Summary</h2>
					<div class="widget" style="background: #F9F9F9">
					<div class="row">
						<div class="col-md-3 text-center">
								<span class="total_balance_" style="font-size: 30px; color: #5bbc2e;"><b><?php echo GetUserBalance($userid); ?></b></span><br>
								<h4 style="margin: 5px 0">Account Balance</h4>
								
								<a class="btn btn-success" href="<?php SITE_URL; ?>withdraw.php"><i class="fa fa-money"></i> withdraw</a>
							
						</div>
						<div class="col-md-3 text-center">
							<a href="<?php SITE_URL; ?>invite.php#referrals"><span style="font-size: 30px; color: #5bbc2e;"><b>$<?php echo CalculatePercentage(GetReferralEarningTotal($userid), REFERRAL_COMMISSION); ?></b></span></a>							
							<h4><i class="fa fa-users fa-2x" style="color: #5bbc2e; vertical-align: middle;"></i> Referrals Earnings</h4>
						</div>						
						<div class="col-md-3 text-center">
							<a href="<?php SITE_URL; ?>mybalance.php#exchanges"><span style="font-size: 20px; background: #5bbc2e; color: #FFF; padding: 8px 15px; margin: 5px 0" class="badge">
								<?php $conf_ex = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE user_id='".(int)$userid."' AND status='confirmed'")); echo number_format($conf_ex['total']); ?></span></a>							
							<h4><i class="fa fa-check-circle fa-2x" style="color: #5bbc2e; vertical-align: middle;"></i> Completed Exchanges</h4>
						</div>
						<div class="col-md-3 text-center">
							<a href="#"><span style="font-size: 20px; background: #5bbc2e; color: #FFF; padding: 8px 15px; margin: 5px 0" class="badge"><?php echo GetUserDiscount($userid); ?>%</span></a>
							<h4><i class="fa fa-percent fa-2x" style="color: #5bbc2e; vertical-align: middle;"></i> Discount</h4>	
						</div>						
					</div>
					</div>


     <?php

		$cc = 0;
		$results_per_page = 10;

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." <sup>%h:%i %p</sup>') AS date_created, DATE_FORMAT(updated, '".DATE_FORMAT."') AS updated_date FROM exchangerix_exchanges WHERE user_id='$userid' AND status!='unknown' ORDER BY created DESC LIMIT $from, $results_per_page";

		$total_result = smart_mysql_query("SELECT * FROM exchangerix_exchanges WHERE user_id='$userid' AND status!='unknown' ORDER BY created DESC");
		$total = mysqli_num_rows($total_result);

		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

     ?>
     
     		<br>
     		<div id="exchanges"></div>
     		<p class="pull-right"><span class="badge" style="padding: 6px">Your Discount: <b><?php echo GetUserDiscount($userid); ?>%</b></span></p>
	 		
			<h2><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo CBE1_BALANCE_TITLE2; ?> <?php if ($total > 0) { ?><sup class="badge" style="background: #5bbc2e"><?php echo number_format($total); ?></sup><?php } ?></h2>	
	
			<div class="table-responsive">
            <table align="center" class="btb" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="14%"><i class="fa fa-clock-o"></i> <?php echo CBE1_BALANCE_DATE; ?></th>
				<th width="14%" nowrap><?php echo CBE1_PAYMENTS_ID; ?> <sup class="itooltip" title="use Reference ID to contact us with any questions">?</sup></th>
				<th width="28%">Direction</th>
				<th width="15%" nowrap><i class="fa fa-arrow-up" aria-hidden="true"></i> Amount Send</th>
                <th width="15%" nowrap>Amount Receive <i class="fa fa-arrow-down" aria-hidden="true"></i></th>
                <th width="22%">To Account</th>
                <th width="13%"><?php echo CBE1_BALANCE_STATUS; ?></th>
              </tr>
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td valign="middle" align="center" nowrap><?php echo $row['date_created']; ?></td>
				  <td valign="middle" align="center"><a href="<?php echo SITE_URL; ?>track_order.php?id=<?php echo $row['reference_id']; ?>"><?php echo $row['reference_id']; ?></a></td>
				  <td valign="middle" align="left" style="padding-left: 10px" nowrap><?php echo GetCurrencyImg($row['from_currency_id'], $width=25); ?> <?php echo substr($row['from_currency'], 0, -4); ?> <i class="fa fa-long-arrow-right" aria-hidden="true"></i> <?php echo GetCurrencyImg($row['to_currency_id'], $width=25); ?> <?php echo substr($row['to_currency'], 0, -4); ?></td>
                  <td valign="middle" align="left" style="padding-left: 10px" nowrap><b style="font-size: 16px"><?php echo number_format($row['exchange_amount'], 2, '.', ''); ?></b> <?php echo substr($row['from_currency'], -4); ?></td>
                  <td valign="middle" align="left" style="padding-left: 10px" nowrap><b style="font-size: 16px"><?php echo number_format($row['receive_amount'], 2, '.', ''); ?></b> <?php echo substr($row['to_currency'], -4); ?></td>
                  <td valign="middle" align="left" style="padding: 10px"><?php echo $row['client_details']; ?><br><?php echo $row['client_email']; ?><br><?php echo $row['to_account']; ?></td>
                  <td valign="middle" align="left" style="padding: 0 5px;">
					<?php
							switch ($row['status'])
							{
								case "confirmed":	echo "<span class='label label-success'>".STATUS_CONFIRMED."</span>"; break;
								case "pending":		echo "<span class='label label-warning'>".STATUS_PENDING."</span>"; break;
								case "waiting":		echo "<span class='label label-warning'>waiting</span>"; break;
								case "cancelled":	echo "<span class='label label-danger'><i class='fa fa-times'></i> cancelled</span>"; break;
								case "timeout":		echo "<span class='label label-danger'><i class='fa fa-clock-o'></i> timeout</span>"; break;
								case "declined":	echo "<span class='label label-danger'>".STATUS_DECLINED."</span>"; break;
								case "failed":		echo "<span class='label label-danger'>".STATUS_FAILED."</span>"; break;
								case "request":		echo "<span class='label label-default'>".STATUS_REQUEST."</span>"; break;
								case "paid":		echo "<span class='label label-success'>".STATUS_PAID."</span>"; break;
								default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
							}

							if ($row['status'] == "declined" && $row['reason'] != "")
							{
								echo " <span class='exchangerix itooltip' title='".$row['reason']."'><img src='".SITE_URL."images/info.png' align='absmiddle' /></span>";
							}
					?>
					<?php /*if ($row['status'] == "pending") { ?><a class="btn btn-warning">make payment</a><?php } //dev */ ?>
				  </td>
                </tr>
			<?php } ?>
           
					<?php echo ShowPagination("exchanges",$results_per_page,"mybalance.php?","WHERE user_id='$userid' AND status!='unknown'"); ?>
			
			<?php }else{ ?>
				<tr height="30"><td colspan="7" align="center" valign="middle"><br><p><?php echo CBE1_PAYMENTS_NO; ?></p></td></tr>
			<?php } ?>
		   </table>
			</div>


		<?php

		$cc = 0;
		$results_per_page = 10;

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." <sup>%h:%i %p</sup>') AS date_created, DATE_FORMAT(process_date, '".DATE_FORMAT." <sup>%h:%i %p</sup>') AS process_date FROM exchangerix_transactions WHERE user_id='$userid' AND status!='unknown' ORDER BY created DESC LIMIT $from, $results_per_page";

		$total_result = smart_mysql_query("SELECT * FROM exchangerix_transactions WHERE user_id='$userid' AND status!='unknown' ORDER BY created DESC");
		$total = mysqli_num_rows($total_result);

		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		?>
			<br/>
			<div name="payments"></div>
			<h2><i class="fa fa-money"></i> <?php echo CBE1_PAYMENTS_TITLE; ?></h2>

			<div class="table-responsive">
            <table align="center" class="btb" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
                <th width="15%"><i class="fa fa-clock-o"></i> <?php echo CBE1_PAYMENTS_DATE; ?></th>
				<th width="17%"><?php echo CBE1_PAYMENTS_ID; ?></th>
                <th width="22%"><?php echo CBE1_PAYMENTS_TYPE; ?></th>
				<th width="15%"><?php echo CBE1_PAYMENTS_AMOUNT; ?></th>
				<th width="17%"><?php echo CBE1_PAYMENTS_PDATE; ?></th>
				<th width="10%"><?php echo CBE1_PAYMENTS_STATUS; ?></th>
				<th width="10%"></th>
              </tr>
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td valign="middle" align="center"><?php echo $row['date_created']; ?></td>
                  <td valign="middle" align="center"><?php echo $row['reference_id']; ?></td>
                  <td valign="middle" align="center">
					<?php
							switch ($row['payment_type'])
							{
								case "Withdrawal":			echo PAYMENT_TYPE_WITHDRAWAL; break;
								case "Referral Commission": echo PAYMENT_TYPE_RCOMMISSION; break;
								case "friend_bonus":		echo PAYMENT_TYPE_FBONUS; break;
								case "signup_bonus":		echo PAYMENT_TYPE_SBONUS; break;
								default:					echo $row['payment_type']; break;
							}
					?>
				  </td>
                  <td valign="middle" align="center"><?php echo DisplayMoney($row['amount']); ?></td>
				  <td valign="middle" align="center"><?php echo $row['process_date']; ?></td>
                  <td nowrap="nowrap" valign="middle" align="left" style="padding-left: 20px;">
					<?php
							switch ($row['status'])
							{
								case "confirmed":	echo "<span class='label label-success'>".STATUS_CONFIRMED."</span>"; break;
								case "pending":		echo "<span class='label label-warning'>".STATUS_PENDING."</span>"; break;
								case "waiting":		echo "<span class='label label-warning'>".STATUS_PENDING."</span>"; break;
								case "declined":	echo "<span class='label label-danger'>".STATUS_DECLINED."</span>"; break;
								case "failed":		echo "<span class='label label-danger'>".STATUS_DECLINED."</span>"; break;
								case "request":		echo "<span class='label label-default'>".STATUS_REQUEST."</span>"; break;
								case "paid":		echo "<span class='label label-success'>".STATUS_PAID."</span>"; break;
								default:			echo "<span class='label label-default'>".$row['status']."</span>"; break;
							}

							if ($row['status'] == "declined" && $row['reason'] != "")
							{
								echo " <span class='exchangerix itooltip' title='".$row['reason']."'><img src='".SITE_URL."images/info.png' align='absmiddle' /></span>";
							}
					?>
				  </td>
				  <td nowrap="nowrap" valign="middle" align="center">
					<a href="<?php echo SITE_URL; ?>mybalance.php?id=<?php echo $row['transaction_id']; if ($page > 1) echo "&page=".$page; ?>#details" id="show_payment"><img src="<?php echo SITE_URL; ?>images/icon_view.png" /></a>
					<?php if (CANCEL_WITHDRAWAL == 1 && $row['payment_type'] == "Withdrawal" && $row['status'] == "request") { ?>
					<a href="#" onclick="if (confirm('<?php echo CBE1_PAYMENTS_CANCEL_MSG; ?>') )location.href='<?php echo SITE_URL; ?>mybalance.php?id=<?php echo $row['transaction_id']; ?>&act=cancel'" title="<?php echo CBE1_PAYMENTS_CANCEL; ?>"><img src="<?php echo SITE_URL; ?>images/cancel.png" border="0" alt="<?php echo CBE1_PAYMENTS_CANCEL; ?>" /></a>
					<?php } ?>
				  </td>
                </tr>
				<?php } ?>

					<?php echo ShowPagination("transactions",$results_per_page,"mybalance.php?","WHERE user_id='$userid' AND status!='unknown'"); ?>

			<?php }else{ ?>
				<tr height="30"><td colspan="7" align="center" valign="middle"><br><p><?php echo CBE1_PAYMENTS_NO; ?></p></td></tr>
			<?php } ?>
			 </table>
			</div>


		<a name="details"></a>
		<?php
	
		// payment details //
		if (isset($_GET['id']) && is_numeric($_GET['id']))
		{
			$transaction_id = (int)$_GET['id'];
			$payment_result = smart_mysql_query("SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS date_created, DATE_FORMAT(process_date, '".DATE_FORMAT." %h:%i %p') AS process_date FROM exchangerix_transactions WHERE transaction_id='$transaction_id' AND user_id='$userid' AND status<>'unknown' LIMIT 1");
			
			if (mysqli_num_rows($payment_result) > 0)
			{
				$payment_row = mysqli_fetch_array($payment_result);
		?>
		 <div id="payment_info"> 
		 <br/>
		 <div style="float: right; margin-top: 20px"><a id="hide_payment" href="javascript:void(0)"><img src="<?php echo SITE_URL; ?>images/icon_hide.png" /></a></div>
		 <h3><?php echo CBE1_PAYMENTS_DETAILS; ?> #<?php echo $payment_row['reference_id']; ?></h3>
		 
		 <div class="payment_details">
		 <table width="100%" cellpadding="5" cellspacing="3" border="0">
           <tr>
            <td width="25%" nowrap="nowrap" valign="middle" align="left" class="tb1"><?php echo CBE1_PAYMENTS_TYPE; ?>:</td>
            <td align="left" valign="middle">
				<?php

						switch ($payment_row['payment_type'])
						{
							case "Cashback":			echo PAYMENT_TYPE_CASHBACK; break;
							case "Withdrawal":			echo PAYMENT_TYPE_WITHDRAWAL; break;
							case "Referral Commission": echo PAYMENT_TYPE_RCOMMISSION; break;
							case "friend_bonus":		echo PAYMENT_TYPE_FBONUS; break;
							case "signup_bonus":		echo PAYMENT_TYPE_SBONUS; break;
							default:					echo $payment_row['payment_type']; break;
						}
				?>
				<?php if ($payment_row['ref_id'] > 0) { ?> &nbsp; <span class="user"><?php echo GetUsername($payment_row['ref_id'], $hide_lastname = 1); ?></span><?php } ?>
			</td>
          </tr>
		<?php if ($payment_row['payment_type'] == "Withdrawal" && $payment_row['payment_method'] != "") { ?>
          <tr>
            <td nowrap="nowrap" valign="middle" align="left" class="tb1"><?php echo CBE1_PAYMENTS_METHOD; ?>:</td>
            <td align="left" valign="middle">
					<?php if ($payment_row['payment_method'] == "paypal") { ?><img src="<?php echo SITE_URL; ?>images/icon_paypal.png" align="absmiddle" />&nbsp;<?php } ?>
					<?php echo GetPaymentMethodByID($payment_row['payment_method']); ?>
			</td>
          </tr>
		<?php } ?>
		<?php if ($payment_row['payment_details'] != "") { ?>
           <tr>
            <td nowrap="nowrap" valign="middle" align="left" class="tb1"><?php echo CBE1_PAYMENTS_DETAILS; ?>:</td>
            <td align="left" valign="middle"><?php echo $payment_row['payment_details']; ?></td>
          </tr>
		 <?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1"><?php echo CBE1_PAYMENTS_AMOUNT; ?>:</td>
            <td align="left" valign="middle"><?php echo DisplayMoney($payment_row['amount']); ?></td>
          </tr>
		<?php if ($payment_row['payment_type'] == "Withdrawal" && $payment_row['transaction_commision'] != "0.0000") { ?>
          <tr>
             <td valign="middle" align="left" class="tb1"><?php echo CBE1_PAYMENTS_COMMISSION; ?>:</td>
             <td align="left" valign="middle"><?php echo DisplayMoney($payment_row['transaction_commision']); ?></td>
          </tr>
          <tr>
             <td valign="middle" align="left" class="tb1"><?php echo CBE1_PAYMENTS_TAMOUNT; ?>:</td>
             <td align="left" valign="middle"><b><?php echo DisplayMoney($payment_row['amount']-$payment_row['transaction_commision']); ?></b></td>
          </tr>
	    <?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1"><?php echo CBE1_PAYMENTS_DATE; ?>:</td>
            <td align="left" valign="middle"><?php echo $payment_row['date_created']; ?></td>
          </tr>
		  <?php if ($payment_row['process_date'] != "") { ?>
          <tr>
            <td nowrap="nowrap" valign="middle" align="left" class="tb1"><?php echo CBE1_PAYMENTS_PDATE; ?>:</td>
            <td align="left" valign="middle"><?php echo $payment_row['process_date']; ?></td>
          </tr>
		  <?php } ?>
          <tr>
            <td valign="middle" align="left" class="tb1"><?php echo CBE1_PAYMENTS_STATUS; ?>:</td>
            <td align="left" valign="middle">
					<?php
							switch ($payment_row['status'])
							{
								case "confirmed":	echo "<span class='confirmed_status'>".STATUS_CONFIRMED."</span>"; break;
								case "pending":		echo "<span class='pending_status'>".STATUS_PENDING."</span>"; break;
								case "declined":	echo "<span class='declined_status'>".STATUS_DECLINED."</span>"; break;
								case "failed":		echo "<span class='failed_status'>".STATUS_FAILED."</span>"; break;
								case "request":		echo "<span class='request_status'>".STATUS_REQUEST."</span>"; break;
								case "paid":		echo "<span class='paid_status'>".STATUS_PAID."</span>"; break;
								default:			echo "<span class='payment_status'>".$payment_row['status']."</span>"; break;
							}

							if ($payment_row['status'] == "declined" && $payment_row['reason'] != "")
							{
								echo " <div class='exchangerix_tooltip'><img src='".SITE_URL."images/info.png' align='absmiddle' /><span class='tooltip'>".$payment_row['reason']."</span></div>";
							}
					?>				
			</td>
          </tr>
          </table>
		  </div>
		  </div>
		<?php
			}
		} // end payment details
		?>
		
		<script src="<?php echo SITE_URL; ?>js/jquery.min.js" language="javascript"></script>

		<script type="text/javascript">
		$("#hide_payment").click(function () {
		  $("#payment_info").hide();
		});
		$("#show_payment").click(function () {
		  $("#payment_info").show('fast');
		});
		</script>
		<br>
		<br>

<?php require_once ("inc/footer.inc.php"); ?>