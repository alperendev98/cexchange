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

	$cc = 0;
	static $ee = 0;
	
	$updated = 0;
	
	$result = smart_mysql_query("SELECT *, TIMESTAMPDIFF(MINUTE,updated,NOW()) AS last_update_mins FROM exchangerix_exdirections WHERE to_currency IN (SELECT currency_id FROM exchangerix_currencies WHERE (reserve > 0 OR reserve='') AND (allow_send='1' OR allow_receive='1') AND status='active') AND status='active' ORDER BY from_currency");
	$total = mysqli_num_rows($result);

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Exchange Rates"; //CBE1_RATES_TITLE

	require_once ("inc/header.inc.php");

?>
					
	<p class="pull-right" style="padding-top: 10px"><a href="<?php echo SITE_URL; ?>xml_rates.php" class="label label-warning"><i class="fa fa-rss" aria-hidden="true"></i> XML</a></p>
	
	<h1><i class="fa fa-area-chart" aria-hidden="true" style="color: #5bbc2e"></i> Exchange Rates<?php //echo CBE1_RATES_TITLE; ?></h1>
	
	<div id="loading"><br><center><i class="fa fa-spinner fa-spin fa-3x" style="color: #82c91d"></i></center><br></div>

		<script type="text/javascript" src="<?php echo SITE_URL; ?>js/jquery.min.js"></script>
		<script type="text/javascript">
			(function($){ 
			// preloader
				$(window).ready(function(){
				 $('#loading').delay(1000).hide();
				 $('#ltable').delay(1000).fadeIn(1000);			    
				})		
			}(jQuery));										
		</script>


	<?php if ($total > 0) { ?>

		<div class="table-responsive" style="border: none;">
		<table id="ltable" style="display: none; border-bottom: 1px solid #EEE;" width="100%" cellpadding="2" cellspacing="3" border="0" align="center" class="table table-nonfluid">
		<tr>
			<th width="35%">Currency Send <i class="fa fa-arrow-circle-right fa-lg" aria-hidden="true"></i></th>
			<th width="5%">&nbsp;</th>
			<th width="35%"><i class="fa fa-arrow-circle-left fa-lg" aria-hidden="true"></i> Currency Receive</th>
			<th width="20%" nowrap><i class="fa fa-bars fa-lg" aria-hidden="true"></i> Our Reserve</th>
			<th width="5%" nowrap>Want more?</th>
		</tr>
		<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
			<?php
				
				// updates rate
				if ($row['auto_rate'] == 1 && $row['last_update_mins'] > UPDATE_RATES_MINUTES)
				{
					$from 	= GetCurrencyCode($row['from_currency']);
					$to 	= GetCurrencyCode($row['to_currency']);
										
					exchagerix_update_rate($from, $to, $row['fee'], $row['exdirection_id']);
						
					$old_rate	 	= $row['exchange_rate'];	
					$exchange_rate  = GetDirectionRate($row['exdirection_id']);
					
					$updated = 1;
				}
						
			?>		
		<?php if ($row['from_currency'] != $ee) { ?><tr style="background: #e5e9e2; border-bottom: 1px solid #eee;" height="45"><td colspan="5">&nbsp; <span class="label label-success" style="font-size: 18px;"><?php echo GetCurrencyImg($row['from_currency'], $width = 35); ?> <?php echo GetCurrencyFName($row['from_currency']); ?></span></td></tr><?php } $ee = $row['from_currency']; ?>
		<tr id="lirate" class="href-row <?php //if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>" style="background: <?php if ($old_rate < $exchange_rate) echo "#fcf2f2"; elseif($old_rate > $exchange_rate) echo "#e3edd7"; ?>; cursor: pointer; height: 50px" data-href="<?php echo SITE_URL; ?>index.php?currency_send=<?php echo $row['from_currency']; ?>&currency_receive=<?php echo $row['to_currency']; ?>">
			
			<td valign="middle"  style="padding-left: 10px;"><table width="100%"><td width="55%" nowrap><h3><?php if ($old_rate < $exchange_rate) { ?><i class="fa fa-arrow-down rate_arrow" style="color: red"></i><?php }elseif($old_rate > $exchange_rate){ ?><i class="fa fa-arrow-up rate_arrow" style="color: #5bbc2e"></i><?php } ?>  <?php echo $row['from_rate']." ".GetCurrencyCode($row['from_currency']); ?></h3></td><td width="45%" nowrap><h3 class="hidden-xs"> <?php echo GetCurrencyName($row['from_currency'])." ".GetCurrencyCode($row['from_currency']); ?></h3></td></table></td>
			
			<td valign="middle" align="center" style="padding-top: 15px;"><i class="fa fa-chevron-right fa-lg" aria-hidden="true" style="color: #5bbc2e"></i></td>
			<td valign="middle" style="padding-left: 10px;"><table width="100%"><td width="45%" nowrap><h3><?php echo $row['to_rate']." ".GetCurrencyCode($row['to_currency']); ?></h3></td><td width="10%">&nbsp;</td><td width="45%" nowrap><h3><?php echo GetCurrencyName($row['to_currency']);//." ".GetCurrencyCode($row['to_currency']); ?>&nbsp;</h3></td></table></td>
			<td valign="middle" style="padding-left: 15px; background: #5cb85c; color: #FFF; border-top: 1px solid #FFF; border-bottom: 1px solid #FFF" align="left" nowrap><span class="reserve_a"><?php echo GetCurrencyReserve($row['to_currency']); ?></span> <sup><?php echo GetCurrencyCode($row['to_currency']); ?></sup></td>
			<td valign="middle" align="center" nowrap  style="padding-top: 15px;"><div class="morediv" style="padding: 0;">&nbsp;<a href="#" data-toggle="modal" data-id="<?php echo $row['to_currency']; ?>" data-id2="<?php echo GetCurrencyName($row['to_currency'])." ".GetCurrencyCode($row['to_currency']); ?>" data-target="#ResDialog" class="open-ReserveDialog newa">want more?</a></div></td>
		</tr>
		<?php } ?>
		</table>
		</div>
		<br>
					<script type="text/javascript">
						<?php if ($updated == 1) { ?>
							$('.rate_arrow').delay(4000).fadeOut('slow');
							$('.href-row')
								  .delay(4000)
								  .queue(function (next) { 
								    $(this).css('background', 'none'); 
								    next(); 
							});
							// reload
							<?php if (UPDATE_RATES_MINUTES > 1) { ?>
							setTimeout(function(){
							   window.location.reload(1);
							}, 4000);
							<?php } ?>
						<?php } ?>										
					</script>

			<!-- start modal -->			
			<div id="ResDialog" class="modal fade" role="dialog">
			  <div class="modal-dialog">
			
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal">&times;</button>
			        <h2 class="modal-title"><i class="fa fa-bell-o" aria-hidden="true"></i> Reserve request for <span id="mname"></span></h2>
			      </div>
			      <div class="modal-body" id="staticParent">
				     
				    <form id="reqform" action="" method="post"> 
			        <p>Please send us your amount request and we will contact you shortly.</p>
					  <div class="form-group">
					    <label for="amount">Required Amount <span class="req">*</span></label>
					    <input type="text" name="amount" class="form-control" id="amount" placeholder="0.00" required />  
					  </div>
					  <div class="form-group">
					    <label for="email">Email <span class="req">*</span></label>
					    <input type="email" name="email" class="form-control" id="email" required />
					  </div>
					  <!--
					  <div class="form-group">
					    <label for="mobile">Mobile (SMS)</label>
					    <input type="text" name="mobile" class="form-control" id="mobile" required />
					    <small>for sms notifications about reserve</small>
					  </div>
					  -->				  				  			        
					  <div class="form-group">
					    <label for="comment">Comment (optional)</label>
					    <textarea name="comment" class="form-control" cols="50" rows="5"></textarea>
					  </div>
					<input type="hidden" name="action" value="reserve_request" />		        
					
					<!--	<input type="hidden" name="exdirId" id="exdirId" value="<?php echo $row['exdirection_id']; ?>" />-->
					<input type="hidden" name="currId" id="currId" value="<?php echo $row['to_currency']; ?>" />
					<button type="submit" onClick="SendRequest()" class="btn btn-success btn-lg">Submit Reserve Request</button>			        
			        </form>
			        
			      </div>
			    </div>
			
			  </div>
			</div>
			<!--- end modal -->
			
			<script type="text/javascript">
			function SendRequest() {
			    $("#reqform").on("submit", function (event) {
			        event.preventDefault();
			        $.ajax({
			            url: "<?php echo SITE_URL; ?>postdata.php",
			            type: "POST",
			            data: $("#reqform").serialize(),
			            success: function (result) {
			                //console.log(result)
			                $('#reqform').html("<div class='alert alert-success'><i class='fa fa-check'></i> Thank you! You reserve request has been sent to us!</div><br><br>");
			            }
			        });
			    })
			}				
			</script>			
			

	<?php }else{ ?>
				<div class="alert alert-info"><i class="fa fa-info-circle fa-lg"></i> No exchange rates at this time.<?php //echo CBE1_RATES_NO; ?></div>
	<?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>