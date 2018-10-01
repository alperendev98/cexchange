<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	session_start();
	require_once("inc/config.inc.php");
	
	$what = getGetparameter('what');
			
	if (isset($what) && $what=="rate" && isset($_GET['direction']) && is_numeric($_GET['direction']))
	{
		unset($rate_up);
		
		$exdirection_id = (int)$_GET['direction'];
		
		$result = smart_mysql_query("SELECT *, TIMESTAMPDIFF(MINUTE,updated,NOW()) AS last_update_mins FROM exchangerix_exdirections WHERE exdirection_id='$exdirection_id' LIMIT 1");
		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_array($result);
			
			$updated = 0;
			
			// update rate ///////////////////
			if ($row['auto_rate'] == 1 && $row['last_update_mins'] > 1)
			{
					$from 	= GetCurrencyCode($row['from_currency']);
					$to 	= GetCurrencyCode($row['to_currency']);
										
					exchagerix_update_rate($from, $to, $row['fee'], $row['exdirection_id']);
						
					$old_rate	 	= $row['exchange_rate'];	
					$exchange_rate  = GetDirectionRate($row['exdirection_id']);
					
					$updated = 1;
					
					if ($old_rate != $exchange_rate)
					{
						if ($old_rate > $exchange_rate) $rate_up = 1;
						if ($old_rate < $exchange_rate) $rate_up = 2;
					}										
			}			
			
		?>
					
					<?php if (isset($rate_up) && $rate_up == 1) { ?><i id="rate_arrow" class='fa fa-arrow-up' style='color: #2f9e2d'></i><?php }elseif(isset($rate_up) && $rate_up == 2){ ?><i id="rate_arrow" class='fa fa-arrow-down' style='color: #f75c5d'></i><?php } ?>
				
					<span id="ex_rate_live" style="color: <?php if (isset($rate_up) && $rate_up == 1) echo "#2f9e2d"; elseif(isset($rate_up) && $rate_up == 2) echo "#f75c5d"; else echo "#000" ?>"><?php echo $row['from_rate']; ?> <?php echo GetCurrencyCode($row['from_currency']); ?> = <?php echo $row['to_rate']; ?> <?php echo GetCurrencyCode($row['to_currency']); ?></span>
					
					<script type="text/javascript">
						$('#rate_arrow').delay(800).fadeOut('slow');
						$('#ex_rate_live')
							  .delay(1100)
							  .queue(function (next) { 
							    $(this).css('color', '#000'); 
							    next(); 
						});						
					</script>
				
				
	<?php
		
		}	
	}
	else
	{
		?>
		<div class="table-responsive">
		<table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="5%">Send</th>
				<th width="11%">Receive</th>
				<th width="10%">Amount</th>
				<th width="27%">Username</th>
				<th width="20%">Date</th>
				<th width="12%">Status</th>
			</tr>
			<?php
		$cc = 0;
		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." <br><small>%h:%i %p</small>') AS payment_date FROM exchangerix_exchanges WHERE status!='request' ORDER BY  created desc LIMIT ".HOMEPAGE_EXCHANGES_LIMIT;
		$result = smart_mysql_query($query);

        if (mysqli_num_rows($result) > 0) // WHERE (status='confirmed' OR status='pending' OR status='waiting') 
		{
			while ($row = mysqli_fetch_array($result)) { $cc++;
		?>
		
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>" <?php if ($row['status'] == "pending") echo "style='background: #fff9f2'"; ?> <?php //if ($row['status'] == "declined" || $row['status'] == "timeout" || $row['status'] == "cancelled") echo "style='background: #f9f2f2'"; ?>>
					
					<td align="center" valign="middle" nowrap="nowrap"><?php echo GetCurrencyImg($row['from_currency_id'], $width=20); ?></td>
					<td align="center" valign="middle" nowrap="nowrap"><?php echo GetCurrencyImg($row['to_currency_id'], $width=20); ?> </td>

					<td align="left" valign="middle" style="padding: 15px 8px;">
						&nbsp; <b><?php echo floatval($row['exchange_amount']); ?></b> <sup><?php echo substr($row['from_currency'], -4); ?></sup> <i class="fa fa-long-arrow-right" aria-hidden="true"></i> <b><?php echo floatval($row['receive_amount']); //number_format($row['receive_amount'], 2, '.', ''); ?></b> <sup><?php echo substr($row['to_currency'], -4); ?></sup> <!-- from account/to account //dev -->
						<br><span class="badge" style="background: #c9c9c9; color: #fff; font-weight: normal;">rate: <?php echo $row['ex_from_rate']; ?> <?php echo substr($row['from_currency'], -4); ?> = <?php echo $row['ex_to_rate']; ?> <?php echo substr($row['to_currency'], -4); ?></span>
						<br>						
					</td>
					
					<!--<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['user_id']; ?></td>-->
					<td align="left" valign="middle" style="padding-left: 7px; font-size: 13px">
						<?php 
						if ($row['country_code'] != "" && $row['country_code'] != 0) { ?>
						<img src="<?php echo SITE_URL; ?>images/flags/<?php echo $row['country_code']; ?>.png" width="16" height="11" />
						
						<?php } ?>&nbsp; 
						
						<?php if ($row['user_id'] > 0) { ?>
						<i class="fa fa-user-circle" aria-hidden="true"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo GetUsername($row['user_id'], $type=2); ?></a>
						<?php }else{ ?>
						<i class="fa fa-user-o" aria-hidden="true"></i> <?php echo $row['client_details']; ?><!--Visitor-->
						<?php } ?>
						
						
						<?php if ($row['proof'] != "") { ?><br> <i class="fa fa-paperclip"></i> <a style="color: #5cb85c" href="<?php echo SITE_URL; ?>uploads/<?php echo $row['proof']; ?>" data-lightbox="image-1" data-title="Payment Proof">payment proof</a><?php } ?>
					</td>

					<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['payment_date']; ?></td>

					<td align="left" valign="middle" style="padding-left: 5px;">
					<?php
						switch ($row['status'])
					  {
							case "confirmed": echo "<span class='label label-success'><i class='fa fa-check'></i> confirmed</span>"; break;
							case "pending": echo "<span class='label label-warning tooltips' title='awaiting confirmation'><i class='fa fa-clock-o'></i> awaiting</span>"; break;
							case "waiting": echo "<span class='label label-default tooltips' title='waiting for payment'><i class='fa fa-clock-o'></i> waiting</span>"; break;
							case "declined": echo "<span class='label label-danger'><i class='fa fa-times'></i> declined</span>"; break;
							case "failed": echo "<span class='label label-danger'><i class='fa fa-times'></i> failed</span>"; break;
							case "cancelled": echo "<span class='label label-danger'><i class='fa fa-times'></i> cancelled</span>"; break;
							case "timeout": echo "<span class='label label-danger'><i class='fa fa-times'></i> timeout</span>"; break;
							case "request": echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> awaiting approval</span>"; break;
							case "paid": echo "<span class='label label-success'><i class='fa fa-check'></i> paid</span>"; break;
							default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
						}
					?>
					<?php if ($row['reason'] != "") { ?><span class="note" title="<?php echo $row['reason']; ?>"></span><?php } ?>
					</td>
					<!--<td align="center" valign="middle" nowrap="nowrap">IP</td>-->
				
				  </tr>
             <?php } ?>
			
            </table>
						</div>
		<?php	

		}else{ echo "<p class='text-center'>No exchanges at this time.</p>"; }
		
	}
		
?>