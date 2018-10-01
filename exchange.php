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
	require_once("inc/pagination.inc.php");

/*
	$blocked_page = 0;
	if (REQUIRE_LOGIN == 1 && !isLoggedIn())
	{
		$blocked_page = 1;
		header ("Location: login.php?login");
		exit();
	}	
*/


	// logout blocked user
	if (isLoggedIn())
	{
		$query	= "SELECT * FROM exchangerix_users WHERE user_id='".(int)$_SESSION['userid']."' AND status='active' LIMIT 1";
		$result = smart_mysql_query($query);
	
		if (mysqli_num_rows($result) == 0)
		{
			header ("Location: logout.php");
			exit();
		}
	}


	if (isset($_GET['currency_send']) && isset($_GET['currency_receive']) && $_GET['currency_send'] != $_GET['currency_receive'])
	{
		$from_id 	= (int)$_GET['currency_send'];
		$to_id 		= (int)$_GET['currency_receive'];
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}
	
	unset($_SESSION['from_amount'], $_SESSION['to_amount'], $_SESSION['transaction_id'], $_SESSION['rid']);

	$query = "SELECT *, TIMESTAMPDIFF(MINUTE,updated,NOW()) AS last_update_mins FROM exchangerix_exdirections WHERE from_currency='$from_id' AND to_currency='$to_id' AND from_currency IN (SELECT currency_id FROM exchangerix_currencies WHERE allow_send='1' AND (reserve>0 || reserve='') AND status='active') AND to_currency IN (SELECT currency_id FROM exchangerix_currencies WHERE allow_receive='1' AND (reserve>0 || reserve='') AND status='active') AND status='active' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	if ($total > 0)
	{
		$row = mysqli_fetch_array($result);
		
		$send_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='$from_id' LIMIT 1"));
		$receive_row = mysqli_fetch_array(smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id='$to_id' LIMIT 1"));
	
		$ip	= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));
		if (filter_var($ip, FILTER_VALIDATE_IP))
			$user_ip = $ip;
		
		
		$ptitle	= "Exchange ".GetCurrencyFName($row['from_currency'])." to ".GetCurrencyFName($row['to_currency']);
		
		// exchange session id
		$_SESSION['rid'] = md5(mt_rand(1,10000).mt_rand(1,10000).time());
		
		
		if (isLoggedIn()) $user_id = (int)$_SESSION['userid']; else $user_id = 0;

		// if ($row['hide_code'] == 1) do not show currency CODE //dev
		
		///////////////// update rate ///////////////////
		if ($row['auto_rate'] == 1 && $row['last_update_mins'] > UPDATE_RATES_MINUTES)
		{		
			$from 	= GetCurrencyCode($row['from_currency']);
			$to 	= GetCurrencyCode($row['to_currency']);
										
			exchagerix_update_rate($from, $to, $row['fee'], $row['exdirection_id']);			
		}
		///////////////////////////////////////////////////////////
		
		$default_from = $row['from_rate'];
		$default_to = $row['to_rate'];
		
	}
	else
	{
		$ptitle = CBE1_NOT_FOUND;
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE			= $ptitle;
	//$PAGE_DESCRIPTION		= $row['meta_description'];
	//$PAGE_KEYWORDS		= $row['meta_keywords'];

	$bg_dark = 1;
	require_once ("inc/header.inc.php");

?>	

	<?php

		if ($total > 0) {

	?>
	
	<div class="row">
		<div class="col-md-8">

			<?php if (REQUIRE_LOGIN == 1 && !isLoggedIn()) { ?>
				<div class="alert alert-info text-center">
					<h4><i class="fa fa-info-circle fa-lg"></i> You need to <i class="fa fa-sign-in"></i> <a href="<?php SITE_URL; ?>login.php">login</a> or <a href="<?php SITE_URL; ?>signup.php">signup</a> to be able to make exchanges.</h4>
				</div>
			<?php } ?>
			
			<div class="widget" id="staticParent" <?php if (REQUIRE_LOGIN == 1 && !isLoggedIn()) { ?>style="opacity: 0.4"<?php } ?>>
			
			<h1 class="extitle text-center">
				<span class="hidden-xs">Exchange</span> 
				<?php if ($send_row['image'] != "no_image.png") { ?>
					<img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $send_row['image']; ?>" width="35" height="35" class="imgrs" />
				<?php } ?> 
				<?php echo $send_row['currency_name']." ".$send_row['currency_code']; ?>
				 <i class="fa fa-long-arrow-right" aria-hidden="true"></i> 
				<?php if ($receive_row['image'] != "no_image.png") { ?>
				 	<img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $receive_row['image']; ?>" width="35" height="35" class="imgrs" />
				<?php } ?> 
				 <?php echo $receive_row['currency_name']." ".$receive_row['currency_code']; ?>
			</h1>
			
				<div class="wrap">
				  <div class="links">
				    <div class="dot current">STEP 1</div>
				    <div class="dot disabled">STEP 2</div>
				    <div class="dot disabled">STEP 3</div>
				  </div>
				</div>

				<h2 class="lined text-center">&nbsp; Enter Amount &nbsp;</h2>
			
				<?php if (ALLOW_AFFILIATE == 1) { // dev ?><p class="text-center"><?php if (!isLoggedIn()) { ?><a href="<?php echo SITE_URL; ?>affiliate.php#discount">Discount: 0%.<br> Want a discount? Just <a href="<?php echo SITE_URL; ?>signup.php">sign up</a>!</a><?php }else{ ?><span class="badge">Your Discount: 3%</span><?php } ?></p><?php } ?>
					
				<div class="row">
				<div class="col-md-10 col-md-offset-1">

					<?php if ($row['is_manual'] == 1) { ?>
						<div class="well">
							<h4><i class="fa fa-hand-o-right fa-lg" aria-hidden="true"></i> Manual Exchange</h4>
							This is manual exchange. Operator will need some time to review your payment.
							<?php if (SHOW_OPERATOR_HOURS == 1) { ?><br><i class="fa fa-clock-o fa-lg"></i> Working hours: <?php echo OPERATOR_HOURS; ?> <?php echo OPERATOR_TIMEZONE; ?><?php } ?>
						</div>						
					<?php } ?>	
	
					<?php if (isset($_GET['err']) && $_GET['err'] != "") { ?>
					<div id="errors" class="alert alert-danger alert-dismissable fade in">
						<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<?php if ($_GET['err'] == "min_amount") { ?>Min Amount: <b><?php echo $row['min_amount']." ".$send_row['currency_code']; ?></b>.<?php } ?>
						<?php if ($_GET['err'] == "max_amount") { ?>Max Amount: <b><?php echo $row['max_amount']." ".$send_row['currency_code']; ?></b>.<?php } ?>
						<?php if ($_GET['err'] == "low_reserve") { ?>Sorry, low reserve for this currency.<?php } ?>
						<?php if ($_GET['err'] == "wrong_amount") { ?>Sorry, amount is wrong. Only numbers and dot (.) allowed.<?php } ?>
					</div>
					<?php } ?>	
									
					<?php /*if (SITE_FEE > 0) { ?>
					 	<p class="pull-right">Fee: <b><?php echo SITE_FEE; ?>%</b></p>
					<?php }*/ ?>
					
					<div id="max_amount" class="alert alert-danger" style="display: none">Max Amount: <b><span id="mmax"></span></b> <?php echo $send_row['currency_code']; ?>.<!-- <a data-toggle="modal" data-id="<?php echo $row['to_currency']; ?>" data-id2="<?php echo $receive_row['currency_name']." ".$receive_row['currency_code']; ?>" class="open-ReserveDialog" href="#ResDialog">Want more?</a>--></div>
					
					<div id="max_reserve" class="alert alert-danger" style="display: none">Max Amount: <b><span id="mmax"></span></b> <?php echo $receive_row['currency_code']; ?>. <a data-toggle="modal" data-id="<?php echo $row['to_currency']; ?>" data-id2="<?php echo $receive_row['currency_name']." ".$receive_row['currency_code']; ?>" class="open-ReserveDialog" href="#ResDialog">Want more?</a></div>
				
							
					<?php if ($receive_row['reserve'] > 0 || $receive_row['reserve'] == "") { ?>
					<form action="<?php echo SITE_URL; ?>exchange_step2.php" method="post">
						<div class="row">
						<div class="col-sm-6">
							<h3><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $send_row['image']; ?>" width="40" height="40" /> Amount Send <i class="fa fa-arrow-right" aria-hidden="true"></i></h3>
							<div class="input-group">
							  <input type="text" id="child" name="from_amount" class="form-control input-lg" aria-describedby="inputHelpInline" value="<?php echo isset($default_from) ? $default_from : getPostParameter('from_amount'); ?>" required>
							  <span class="input-group-addon"><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $send_row['image']; ?>" width="29" height="29" style="border-radius: 50%" /> <?php echo $send_row['currency_code']; ?></span>							  
							</div>
							<?php if ($row['min_amount'] > 0 || $row['max_amount'] > 0) { ?>
							<small id="inputHelpInline" class="text-muted">
							<?php if ($row['min_amount'] > 0) { ?>Min Amount: <a href="#" class="income limit"><?php echo $row['min_amount']; ?></a> <?php echo $send_row['currency_code']; ?><?php } ?>
							<?php if ($row['max_amount'] > 0) { ?>&nbsp;&nbsp;&nbsp; Max Amount: <a href="#" class="income limit"><?php echo $row['max_amount']; ?></a> <?php echo $send_row['currency_code']; ?><?php } ?>
							</small>
							<?php } ?>
							
						</div>
						<div class="col-sm-6">
							<h3><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $receive_row['image']; ?>" width="40" height="40" />  <i class="fa fa-arrow-left" aria-hidden="true"></i> Amount Receive</h3>
							<div class="input-group">
							  <input type="text" id="child2" name="to_amount" class="form-control input-lg" aria-describedby="inputHelpInline2" value="<?php echo isset($default_to) ? $default_to : getPostParameter('to_amount'); ?>" required>
<span class="input-group-addon"><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $receive_row['image']; ?>" width="29" height="29" style="border-radius: 50%" /> <?php echo $receive_row['currency_code']; ?></span>							  
							</div>
							<?php if ($receive_row['reserve'] > 0) { ?><small id="inputHelpInline2" class="text-muted">Max Amount: <a href="#" class="outgo limit"><?php echo GetCurrencyReserve($to_id); ?></a> <?php echo $receive_row['currency_code']; ?></small><?php } ?>
						</div>
						</div>
						
						<div class="row">
						<div class="col-sm-12">
		
							<div class="progress-bar-x">
							<div class="progress-note"><a id="refresh_link" href="#"><i class="itooltip fa fa-refresh" aria-hidden="true" style="color: #000" title="Refresh exchange rate"></i></a> <span class="hidden-xs">Exchange</span> Rate: <span id="exrate"><?php echo $row['from_rate']; ?> <?php echo $send_row['currency_code']; ?> = <?php echo $row['to_rate']; ?> <?php echo $receive_row['currency_code']; ?></span></div>
							
							    <span class="progress-bar-fill" style="width: 0%"></span>
							</div>
							<br>

							<input type="hidden" name="currency_send" value="<?php echo @$from_id; ?>" />
							<input type="hidden" name="currency_receive" value="<?php echo @$to_id; ?>" />
							<hr>
							<?php if (EXCHANGE_CAPTCHA == 1) { ?><hr><?php } //dev ?>
							<p class="text-center">
							<a class="btn btn-default btn-lg pull-left" href="<?php echo SITE_URL; ?>"><i class="fa fa-angle-left" aria-hidden="true"></i> Go Back</a>
							<?php if (REQUIRE_LOGIN == 1 && !isLoggedIn()) { ?>
								<a class="btn btn-success btn-lg pull-right" data-toggle="modal" data-target="#signin">Next Step <i class="fa fa-angle-right" aria-hidden="true"></i></a>
							<?php }else{ ?>
								<button type="submit" class="btn btn-success btn-lg pull-right">Next Step <i class="fa fa-angle-right" aria-hidden="true"></i></button>
							<?php } ?>
							</p>
							
						</div>
						</div>
					</form>
					<?php } ?>
					
					
				</div>
				</div>
				<br>
				
			</div>
			

			<?php if ($row['description'] != "") { ?>
				<div class="widget">
					<h3>About <?php echo $send_row['currency_name']." ".$send_row['currency_code']; ?> to <?php echo $receive_row['currency_name']." ".$receive_row['currency_code']; ?> Exchange</h3>
					<p class="description" style="font-size: 14px;"><?php echo stripslashes($row['description']); ?></p>
				</div>
			<?php } ?>

		</div>
		<div class="col-md-4" <?php if (REQUIRE_LOGIN == 1 && !isLoggedIn()) { ?>style="opacity: 0.4"<?php } ?>>
			
			<div class="widget">
				<h1><i class="fa fa-info-circle" aria-hidden="true"></i> Exchange Info</h1>
				
				<?php if ($receive_row['reserve'] > 0 || $receive_row['reserve'] == "") { ?><h3 style="color: #777">Reserve</h3><h3> &nbsp; <?php echo GetCurrencyReserve($to_id); ?> <?php echo $receive_row['currency_code']; ?> &nbsp; <?php if ($receive_row['reserve'] != "") { ?><sup><a data-toggle="modal" data-id="<?php echo $row['to_currency']; ?>" data-id2="<?php echo $receive_row['currency_name']." ".$receive_row['currency_code']; ?>" class="open-ReserveDialog" href="#ResDialog">want more?</a></sup><?php } ?></h3><?php } ?>
				
				<?php if ($receive_row['reserve'] <= 0 && $receive_row['reserve'] != "") { ?><h3 style="color: #777">Reserve</h3> <div class="alert alert-danger"><h3 style="color: #a94442"><i class="fa fa-frown-o fa-lg" aria-hidden="true"></i> Reserve is out.</h3><br>Sorry, we have out of reserve for this currency. Please check back soon or <a data-toggle="modal" data-id="<?php echo $row['to_currency']; ?>" data-id2="<?php echo $receive_row['currency_name']." ".$receive_row['currency_code']; ?>" class="open-ReserveDialog" href="#ResDialog">contact us</a>.</div><?php } ?>			
				<?php if ($row['min_amount'] > 0) { ?><h3 style="color: #777">Min Amount</h3><h3> &nbsp; <?php echo $row['min_amount']; ?> <?php echo $send_row['currency_code']; ?></h3><?php } ?>
				<?php if ($row['max_amount'] > 0) { ?><h3 style="color: #777">Max Amount</h3><h3> &nbsp; <?php echo $row['max_amount']; ?> <?php echo $send_row['currency_code']; ?></h3><?php } ?>
				<?php /*if ($row['fee'] != "" && $row['fee'] > 0) { ?><h3 style="color: #777">Fee</h3><h3> &nbsp; <?php echo strstr($row['fee'], "%") ? $row['fee'] : $row['fee']." ".$send_row['currency_code']; ?></h3><?php }*/ ?>
			</div>
			
			<div class="widget">
				<b><i class="fa fa-lock" aria-hidden="true"></i> Secure Exchange</b><br>
				Your exchange is always safe and secure.
			</div>
					
		</div>
	</div>

	
	
	<script type="text/javascript" src="<?php echo SITE_URL; ?>js/jquery.min.js"></script>
	<script type="text/javascript">
	 $(document).ready(function(){
	    var qty=$("#child");
	    qty.keyup(function(){
		    $("#errors").hide();
		    $("#max_amount").hide();
		    $("#max_reserve").hide();
	        //var total=isNaN(parseInt(qty.val()* $("#child").val())) ? 0 :(qty.val()* $("#child").val())
	        var total=isNaN(parseInt(qty.val()* <?php echo floatval($row['exchange_rate']); ?>)) ? 0 :(qty.val()* <?php echo floatval($row['exchange_rate']); ?>)
	        total = parseFloat(total.toFixed(3));	        

	        <?php if ($receive_row['reserve'] > 0) { /*$max_am = floatval($receive_row['reserve']/$row['exchange_rate']);*/ ?>if (total > <?php echo $receive_row['reserve']; ?>) { var rrr = (<?php echo $receive_row['reserve']; ?>/<?php echo floatval($row['exchange_rate']); ?>); rrr = parseFloat(rrr.toFixed(3)); $("#child").val(rrr); $("#child2").val(<?php echo $receive_row['reserve']; ?>); $("#max_amount #mmax").html(rrr); $("#max_amount").show(); }else{ $("#child2").val(total); } <?php }else{ ?>
	        $("#child2").val(total);
	        <?php } ?>

	        <?php if ($row['max_amount'] > 0) { ?>
	        	var total2=isNaN(parseInt(<?php echo $row['max_amount']; ?>* <?php echo floatval($row['exchange_rate']); ?>)) ? 0 :(<?php echo$row['max_amount']; ?>* <?php echo floatval($row['exchange_rate']); ?>)
	        	total2 = parseFloat(total2.toFixed(3));
	        	if (qty.val() > <?php echo $row['max_amount']; ?>) { $("#child").val(<?php echo $row['max_amount']; ?>); $("#child2").val(total2); $("#max_amount #mmax").html(<?php echo $row['max_amount']; ?>); $("#max_amount").show(); }else{ $("#child2").val(total); }
	        <?php } ?>	        
	        
	    });
	    
	    var qty2=$("#child2");
	    qty2.keyup(function(){
		    $("#errors").hide();
		    $("#max_amount").hide();
		    $("#max_reserve").hide();
	        <?php if ($receive_row['reserve'] > 0) { ?>if (qty2.val() > <?php echo $receive_row['reserve']; ?>) { $("#child2").val(<?php echo $receive_row['reserve']; ?>); $("#max_reserve #mmax").html(<?php echo $receive_row['reserve']; ?>); $("#max_reserve").show(); } <?php } ?>
	        //var total=isNaN(parseInt(qty.val()* $("#child").val())) ? 0 :(qty.val()* $("#child").val())
	        var total=isNaN(parseInt(qty2.val()/ <?php echo floatval($row['exchange_rate']); ?>)) ? 0 :(qty2.val()/ <?php echo floatval($row['exchange_rate']); ?>)
	        total = Math.ceil(total);
	        $("#child").val(total);
	    });   
	});	

			$(document).on('click', '.income', function(e) {
			    e.preventDefault();
			    e = $(this).text();    
				var totals = isNaN(parseInt(e* <?php echo floatval($row['exchange_rate']); ?>)) ? 0 :(e* <?php echo floatval($row['exchange_rate']); ?>)
				totals = parseFloat(totals.toFixed(3));
			   $("#child").val(e);
			   $("#child2").val(totals);
			});
			
			$(document).on('click', '.outgo', function(e) {
			    e.preventDefault();
			    e = $(this).text();
				var totals = isNaN(parseInt(e/ <?php echo floatval($row['exchange_rate']); ?>)) ? 0 :(e/ <?php echo floatval($row['exchange_rate']); ?>)
				totals = parseFloat(totals.toFixed(3));	
				//ftotal = totals.toFixed(4); //totals = (Math.round(totals * 100)/100).toFixed(3);		    
			   $("#child2").val(e);
			   $("#child").val(totals);
			});			
				

	    	$(document).ready(function() {
				//$("#loader").show();
				setInterval(function () {
					$('#exrate').each(function(){
						$(this).hide().load('<?php SITE_URL; ?>getdata.php?what=rate&direction=<?php echo $row['exdirection_id']; ?>').fadeIn('slow');
						//$("#loader").hide();
					});
				}, 60000);
				
				$('#refresh_link').click(function (e) {
					e.preventDefault();
				  $('#exrate').hide().load('<?php SITE_URL; ?>getdata.php?what=rate&direction=<?php echo $row['exdirection_id']; ?>').fadeIn('slow');
				  $('#refresh_link').delay(300).hide();
				  $('#refresh_link').delay(30000).fadeIn('slow');
				});				
				
			   /*function restartprogressBar() {
			       // $(".progress-bar-fill").removeClass("transition");
			        
					//$(".progress-bar-fill").addClass("transition");
					//$('.progress-bar-fill').css('width', '0%');
					$(".progress-bar-fill").addClass("reseting");
					$('.progress-bar-fill').css('width', '0%');
					$(".progress-bar-fill").removeClass("reseting");
					
					$('.progress-bar-fill').css('transition', 'width 60s ease-in-out');
			    };
			    setInterval(function() {
			        restartprogressBar();
			    }, 10000);
			    */			
				
			}); 		
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
					<input type="hidden" name="exdirId" id="exdirId" value="<?php echo $row['exdirection_id']; ?>" />
					<input type="hidden" name="currId" id="currId" value="<?php echo $row['to_currency']; ?>" />
					<button type="submit" onClick="SendRequest()" class="btn btn-success btn-lg">Submit Reserve Notification</button>			        
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
		<h1><i class="fa fa-refresh"></i> Exchange</h1>
		<div class="alert alert-info"><i class="fa fa-info-circle fa-lg"></i> <?php echo CBE1_NOT_FOUND2; ?></div>
		<p align="center"><a class="btn btn-default" href="#" onclick="history.go(-1);return false;"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo CBE1_GO_BACK; ?></a></p>
	<?php } ?>		  	
	
	

<?php require_once ("inc/footer.inc.php"); ?>