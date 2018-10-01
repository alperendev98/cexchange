<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	if (file_exists("./install.php"))
	{
		header ("Location: install.php");
		exit();
	}

	session_start();
	require_once("inc/config.inc.php");

	// save referral id //////////////////////////////////////////////
	if (isset($_GET['ref']) && is_numeric($_GET['ref']))
	{
		$ref_id = (int)$_GET['ref'];
		setReferral($ref_id);

		header("Location: index.php");
		exit();
	}

	// set language ///////////////////////////////////////////////////
	if (isset($_GET['lang']) && $_GET['lang'] != "")
	{
		$site_lang	= strtolower(getGetParameter('lang'));
		$site_lang	= preg_replace("/[^0-9a-zA-Z]/", " ", $site_lang);
		$site_lang	= substr(trim($site_lang), 0, 30);
		
		if ($site_lang != "")
		{
			setcookie("site_lang", $site_lang, time()+3600*24*365, '/');
		}

		header("Location: index.php");
		exit();
	}

	$content = GetContent('home');
	
	// some cron // RESERVE_MINUTES
	smart_mysql_query("UPDATE exchangerix_exchanges SET status='timeout' WHERE (created < (NOW() - INTERVAL 120 MINUTE) AND status='waiting')");


	///////////////  Page config  ///////////////
	$PAGE_TITLE			= SITE_HOME_TITLE;
	$PAGE_DESCRIPTION	= $content['meta_description'];
	$PAGE_KEYWORDS		= $content['meta_keywords'];

	$bg_dark = 1;
	require_once("inc/header.inc.php");

?>


<?php
// step2
if (isset($_GET['currency_send']))
	$from_id 	= (int)$_GET['currency_send'];
else 
	$from_id = 11;
if (isset($_GET['currency_receive']))
	$to_id 		= (int)$_GET['currency_receive'];
	else
	$to_id = 19;

							
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


?>
				
<?php
	
	$cc = 0;
	$res_query = "SELECT * FROM exchangerix_currencies WHERE image!='' AND (reserve > 0 || reserve='') AND (allow_send='1' OR allow_receive='1') AND status='active' ORDER BY RAND()";
	$res_result = smart_mysql_query($res_query);
	
	if (mysqli_num_rows($res_result) > 0)
	{
?>
	
		<div class="row hidden-xs" style="background: #F9F9F9; margin: 10px 0;">
			<div class="col-md-5 col-sm-6">
				<h2 style="white-space: nowrap; padding-top: 10px;">Fast Exchange in Minutes Between</h2>
			</div>
			<div class="col-md-7 col-sm-6">
				<div id="myCarousel" class="carousel fdi-Carousel slide">
					<div class="carousel fdi-Carousel slide" id="eventCarousel" data-interval="0">
					<div class="carousel-inner onebyone-carosel">

					<?php while ($row_res = mysqli_fetch_array($res_result)) { $cc++; if ($row_res['$row_res'] != "" && $row_res['$row_res'] != "http://") $elink1 = $row_res['website']; else $elink1 = "#"; ?>
					<div class="item <?php echo ($cc == 1) ? "active" : ""; ?>">
						<div class="col-xs-3">
							<?php if ($elink1 != "#") { ?><a href="<?php echo $elink1; ?>" target="_blank"><?php } ?>
								<img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $row_res['image']; ?>" width="44" height="44" border="0" style="margin: 8px; border-radius: 50%;" class="img-responsive" />
							<?php if ($elink1 != "#") { ?></a><?php } ?>
						</div>
					</div>
					<?php } ?>

						</div>
					</div>
				</div>			
									
			</div>
		</div>

<?php }	?>				


		<div class="row">
			<div class="col-md-8">
	
	
	<div class="widget" style="background: #f1f9ed; border: 1px solid #deedd7">
	<h1 class="home_h1 text-center">Start Exchange</h1>
	
		<form action="<?php echo SITE_URL; ?>index.php" method="get" name="form3">
		<div class="row">
			<div class="col-md-4 col-md-offset-2">     
			  
				<div class="row">
					<div class="col-md-3">
					</div>
					<div class="col-md-9">
						<h3><i class="fa fa-arrow-up fa-lg" aria-hidden="true" style="color: #8dc6fb"></i> SEND</h3>
					</div>
				</div>

				<div class="row">
					<div class="col-md-3">
					<?php	echo GetCurrencyImg($from_id); ?>
					</div>
					<div class="col-md-9">
					<select class="selectpicker show-menu-arrow show-tick form-control" id="currency_send" name="currency_send" title="select" required onchange="this.form.submit()">
					<!--<option value="">--- select ---</option>-->
						<?php
								
							if (!isLoggedIn()) $asql = " hide_from_visitors!='1' AND "; else $asql = "";
							$sql = "SELECT * FROM exchangerix_currencies WHERE currency_id IN (SELECT from_currency FROM exchangerix_exdirections WHERE $asql status='active') AND (reserve > 0 || reserve='') AND allow_send='1' AND status='active' ORDER BY sort_order DESC, currency_name ASC";

							$sql_curr_send = smart_mysql_query($sql);
							if (mysqli_num_rows($sql_curr_send) > 0)
							{
								while ($row_curr_send = mysqli_fetch_array($sql_curr_send))
								{
									if ($row_curr_send['is_crypto'] != 1) $show_ccode = " ".$row_curr_send['currency_code']; else $show_ccode = "";
									if ($row_curr_send['currency_id'] == $from_id) $selected = " selected=\"selected\""; else $selected = "";

									echo "<option value=\"".$row_curr_send['currency_id']."\"".$selected." data-content=\"<span style='font-size: 15px'> ".$row_curr_send['currency_name'].$show_ccode."</span>\">".$row_curr_send['currency_name'];
									echo $show_ccode;
									echo "</option>";
								}
							}
						?>
					</select>
					</div>
				</div>
				<br>
		   </div>
		   <div class="col-md-1 text-center hidden-xs"><br><br><i class="fa fa-exchange fa-2x"></i></div>
		   <div class="col-md-4">

			  <br class="visible-xs">

				<div class="row">
					
					<div class="col-md-10">
					<h3>RECEIVE <i class="fa fa-arrow-down fa-lg" aria-hidden="true" style="color: #5cb85c"></i></h3>
					</div>
				</div>
			  
				
				<div class="row">
				<div class="col-md-10">
				<select class="selectpicker show-menu-arrow show-tick form-control" id="currency_receive" name="currency_receive" title="select" required onchange="this.form.submit()">
				<!--<option value="">--- select ---</option>-->
					<?php
						$sql_curr_receive = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE currency_id IN (SELECT to_currency FROM exchangerix_exdirections WHERE $asql status='active') AND (reserve > 0 || reserve='') AND allow_send='1' AND status='active' ORDER BY sort_order DESC, currency_name ASC");
						if (mysqli_num_rows($sql_curr_receive) > 0)
						{
							while ($row_curr_receive = mysqli_fetch_array($sql_curr_receive))
							{
								if ($row_curr_receive['is_crypto'] != 1) $show_ccode = " ".$row_curr_receive['currency_code']; else $show_ccode = "";
								if ($row_curr_receive['currency_id'] == $to_id) $selected = " selected=\"selected\""; else $selected = "";
								echo "<option value=\"".$row_curr_receive['currency_id']."\"".$selected." data-content=\"<span style='font-size: 15px'> ".$row_curr_receive['currency_name'].$show_ccode."</span>\">".$row_curr_receive['currency_name'];
								echo $show_ccode;
								echo "</option>";
							}
						}
					?>
				</select>
				</div>
				<div class="col-md-2">
				<?php	echo GetCurrencyImg($to_id); ?>
				<br><br>
				</div>
          </div>
		   </div>
          </div>
          <!-- step 2  -->

				<div class="row">
				<?php if ($total == 0) echo '<div class="col-md-9 col-md-offset-2 alert alert-info"><i class="fa fa-info-circle fa-lg"></i>' . CBE1_NOT_FOUND2 . '</div>'; ?>
						<div class="col-md-4 col-md-offset-2">

						<div class="row">
					<div class="col-md-3">
					</div>
					<div class="col-md-9">
							<div class="input-group">
							  <input type="text" id="child" name="from_amount" class="form-control input-lg" aria-describedby="inputHelpInline" value="<?php echo isset($default_from) ? $default_from : getGetParameter('from_amount'); ?>" required>
							   
							</div>
							<?php if ($row['min_amount'] > 0 || $row['max_amount'] > 0) { ?>
							<small id="inputHelpInline" class="text-muted">
							<?php if ($row['min_amount'] > 0) { ?>Min Amount: <a href="#" class="income limit"><?php echo $row['min_amount']; ?></a> <?php echo $send_row['currency_code']; ?><?php } ?>
							<?php if ($row['max_amount'] > 0) { ?>&nbsp;&nbsp;&nbsp; Max Amount: <a href="#" class="income limit"><?php echo $row['max_amount']; ?></a> <?php echo $send_row['currency_code']; ?><?php } ?>
							</small>
							<?php } ?>
							
					</div>
				</div>

							
						</div>

						<div class="col-md-1 text-center hidden-xs"><br><br></div>

						<div class="col-md-4">

							<div class="row">
						
								<div class="col-md-10">
										<div class="input-group">
											<input type="text" id="child2" name="to_amount" class="form-control input-lg" aria-describedby="inputHelpInline2" value="<?php echo isset($default_to) ? $default_to : getGetParameter('to_amount'); ?>" required>

										</div>
										<?php if ($receive_row['reserve'] > 0) { ?><small id="inputHelpInline2" class="text-muted">Max Amount: <a href="#" class="outgo limit"><?php echo GetCurrencyReserve($to_id); ?></a> <?php echo $receive_row['currency_code']; ?></small><?php } ?>
								</div>
							</div>

						</div>
						</div>
						
						<div class="row">
						<div class="col-md-9 col-md-offset-2">
	
							<div class="progress-note">
							<a id="refresh_link" href="#"><i class="itooltip fa fa-refresh" aria-hidden="true" style="color: #000" title="Refresh exchange rate"></i></a> 
							<span class="hidden-xs">Exchange</span> Rate: <span id="exrate"><?php echo $row['from_rate']; ?> <?php echo $send_row['currency_code']; ?> = <?php echo $row['to_rate']; ?> <?php echo $receive_row['currency_code']; ?></span>
							</div>
							<br>

							<hr>
							<?php if (EXCHANGE_CAPTCHA == 1) { ?><hr><?php } //dev ?>
							
						</div>
						</div>

						<!-- end step 2 -->
						</form>
						<form action="<?php echo SITE_URL; ?>exchange_step2.php" method="post" name="form4">
 		   		<hr style="margin: 0 0 8px 0">
						<input type="hidden" name="currency_send" value="<?php echo @$from_id; ?>" />
							<input type="hidden" name="currency_receive" value="<?php echo @$to_id; ?>" />
							<input id="child3" type="hidden" name="from_amount" value="<?php  getGetParameter('from_amount'); ?>" />
							<input id="child4" type="hidden" name="to_amount" value="<?php  getGetParameter('to_amount'); ?>" />
							
			  	<p class="home_btn" align="center"><button type="submit" id="exbutton" class="btn btn-success btn-lg"><i class="fa fa-refresh" id="resh" aria-hidden="true"></i> Exchange</button></p>		   
		   </form>     
			     
			<div class="widget">
				<h1><i class="fa fa-exchange" aria-hidden="true"></i> Latest Exchanges</h1>
			
				<div id="loader"><p align="center"><img src="<?php echo SITE_URL; ?>images/loading_line.gif" /></p></div>
				<div id="show"></div>
			</div>
	</div>



		<?php if ($content['text'] != "") { ?>
			<div style="background: #FFF; padding: 15px; margin: 30px 0 10px 0;">
				<?php echo $content['text']; ?>
			</div>
		<?php } ?>
		

		<?php
			$ee = 0;
			$res_query3 = "SELECT *, sum(exchange_amount) exchange_amount, sum(receive_amount) receive_amount FROM exchangerix_exchanges WHERE date_sub(curdate(), interval 7 day) <= created AND status='confirmed' GROUP BY from_currency, to_currency ORDER BY created DESC LIMIT 7";
			$res_result3 = smart_mysql_query($res_query3);
			
			if (mysqli_num_rows($res_result3) > 0)
			{	
		?>
			<div class="widget hidden-xs">
				<h2><i class="fa fa-line-chart" aria-hidden="true"></i> Tranding Directions of the Week</h2>
		
				<div class="table-responsive">
				 <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th width="56%">Direction</th>
						<th width="22%"><i class="fa fa-arrow-up" aria-hidden="true"></i> Total Sent</th>
						<th width="22%">Total Received <i class="fa fa-arrow-down" aria-hidden="true"></i></th>
					</tr>
				<?php while ($row_res3 = mysqli_fetch_array($res_result3)) { $ee++; ?>
					<tr class="href-row <?php echo ($ee%2 == 0) ? "row_even" : "row_odd"; ?>" style="cursor: pointer; margin-bottom: 5px; border-bottom: 1px solid #EEE" data-href="<?php echo SITE_URL; ?>index.php?currency_send=<?php echo $row_res3['from_currency_id']; ?>&currency_receive=<?php echo $row_res3['to_currency_id']; ?>">
					<td><p><h4><?php echo GetCurrencyImg($row_res3['from_currency_id']); ?> <?php echo $row_res3['from_currency']; ?> <i class="fa fa-long-arrow-right" aria-hidden="true" style="color: #000"></i> <?php echo GetCurrencyImg($row_res3['to_currency_id']); ?> <?php echo $row_res3['to_currency']; ?></h4></p></td>
					<td style="padding-left: 30px;"><b style="font-size: 17px;"><?php echo number_format($row_res3['exchange_amount'], 2, '.', ''); ?></b> <sup><?php echo GetCurCode($row_res3['from_currency']); ?></sup> </td>
					<td style="padding-left: 30px;"><b style="font-size: 17px;"><?php echo number_format($row_res3['receive_amount'], 2, '.', ''); ?></b> <sup><?php echo GetCurCode($row_res3['to_currency']); ?></sup></td>
					</tr>
				<?php } ?>
				</table>
				</div>
			
			</div>
			
		<?php }	?>
	
	
	<?php
	
		$news_result = smart_mysql_query("SELECT *, DATE_FORMAT(added, '".DATE_FORMAT."') AS news_date FROM exchangerix_news WHERE status='active' ORDER BY added DESC LIMIT 5");
		$news_total = mysqli_num_rows($news_result);
		
		if ($news_total > 0) {
		
		?>
		
		<div style="background: #FFF; padding: 15px; margin: 5px 0; border-radius: 7px;">
		<h3><?php echo CBE1_NEWS_TITLE; ?></h3>

		<?php while ($news_row = mysqli_fetch_array($news_result)) { ?>
		<div class="news_info">
			<div class="news_date"><?php echo $news_row['news_date']; ?></div>
			<div class="news_title"><h4><a href="<?php echo SITE_URL; ?>news.php?id=<?php echo $news_row['news_id']; ?>"><?php echo $news_row['news_title']; ?></h4></a></div>
		</div>
		<?php } ?>
		</div>
	<?php } ?>

</div>
<div class="col-md-4">
	
<div class="widget">
				<h1>Track Exchange</h1>
				<form action="<?php echo SITE_URL; ?>track_order.php" method="post">
				<input type="text" class="form-control" name="id" value="" placeholder="your exchange id" />
				<input type="hidden" name="action" value="check_status" />
				<br>
				<input type="submit" class="form-control track" value="Track" />
				</form>
			</div>

<?php
	
	$res_query = "SELECT * FROM exchangerix_currencies WHERE (reserve > 0 OR reserve='') AND status='active' ORDER BY sort_order DESC, currency_name ASC"; // AND currency_id IN (SELECT to_currency FROM exchangerix_exdirections WHERE status='active')
	$res_result = smart_mysql_query($res_query);
	$cc = 0;
	
	if (mysqli_num_rows($res_result) > 0) //want more link hover //dev
	{
?>
		<div class="widget">
		<h1><i class="fa fa-bars" aria-hidden="true"></i> Our Reserves</h1>
		
		<table width="100%">
		<?php while ($row_res = mysqli_fetch_array($res_result)) { $cc++; ?>
		<tr style="background: <?php if (($cc%2) == 0) echo "#F9F9F9"; else echo "#FFF"; ?>">
			<td width="15%"><img src="<?php echo SITE_URL; ?>images/currencies/<?php echo $row_res['image']; ?>" width="33" height="33" border="0" style="margin: 3px; border-radius: 50%;" /></td>
			<td width="40%"><b><?php echo $row_res['currency_name']; ?></b><br></td>
			<td width="45%" align="right"><?php echo ($row_res['reserve'] == 0) ? "<span class='label label-success'>unlimited</span> <sup>".$row_res['currency_code']."</sup>" : "<b>".GetCurrencyReserve($row_res['currency_id'])."</b> <sup>".$row_res['currency_code']."</sup>"; ?>&nbsp;</td>
		</tr>
		<?php } ?>
		</table>
		</div>

<?php }	?>


		<script type="text/javascript" src="<?php echo SITE_URL; ?>js/jquery.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				//$("#loader").show();
				$("#show").hide().delay(3000).fadeIn();
				function update_stats() {
					$('#show').each(function(){
						$("#loader").show().delay(3000).fadeOut();
						$(this).hide().load('<?php SITE_URL; ?>getdata.php').fadeIn('slow');
						//$("#loader").hide();
					});
				}
				update_stats();
				setInterval(update_stats, 120000);
			});
		</script>

		


		<div class="widget">
		<h1><i class="fa fa-comments-o fa-lg" aria-hidden="true"></i> Testimonials <?php if (GetTestimonialsTotal() > 0) { ?><a href="<?php echo SITE_URL; ?>testimonials.php"><span class="testimonials-count" style="font-size: 19px; padding: 3px 15px; margin: 3px; position: relative;
  top: -0.5em; color: #328813; border: 1px solid #328813; border-radius: 5px;"><?php echo number_format(GetTestimonialsTotal()) ; ?></span></a><?php } ?></h1>
			
				<?php
					
					$res2_query = "SELECT * FROM exchangerix_reviews r LEFT JOIN exchangerix_exchanges e ON r.exchange_id=e.exchange_id WHERE r.status='active' ORDER BY r.added LIMIT ".HOMEPAGE_REVIEWS_LIMIT;
					$res2_result = smart_mysql_query($res2_query);
					$total2_res = mysqli_num_rows($res2_result);
					$cc = 0;
					
					if ($total2_res > 0)
					{
				?>
					<a class="pull-right" href="<?php echo SITE_URL; ?>testimonials.php" style="color: #777; padding-bottom: 11px;"><i class="fa fa-comments"></i> view all</a>
					
					<div class="carousel slide" data-ride="carousel" id="quote-carousel">
					    <ol class="carousel-indicators">
				          <li data-target="#quote-carousel" data-slide-to="0" class="active"></li>
						  <?php for ($e=1; $e<$total2_res; $e++) { ?>
				          	<li data-target="#quote-carousel" data-slide-to="<?php echo $e; ?>"></li>
				          <?php } ?>
				        </ol>
					<div class="carousel-inner">					
						<?php while ($row2_res = mysqli_fetch_array($res2_result)) { $cc++; ?>
						<div class="item <?php if ($cc == 1) echo "active"; ?>">
			            <blockquote>
			              <div class="row">
			                <div class="col-sm-12">
			                			
								<center>
									<?php for ($i=0; $i<5;$i++) { ?><i class="fa fa-star" style="font-size: 20px; margin-right: 3px; color: <?php echo ($i<$row2_res['rating']) ? "#ecb801" : "#CCC"; ?>"></i><?php } ?>							
								</center>
								 by <i class="fa fa-user-o" aria-hidden="true"></i> <b><?php echo ($row2_res['author'] == "") ? GetUsername($row2_res['user_id'], $hide_lastname = 1) : $row2_res['author']; ?></b><br><br>
								<?php if ($row2_res['from_currency'] != "" &&  $row2_res['from_currency'] != "") { ?>
								<a href="<?php echo SITE_URL; ?>index.php?currency_send=<?php echo $row2_res['from_currency_id']; ?>&currency_receive=<?php echo $row2_res['to_currency_id']; ?>"><?php echo GetCurrencyImg($row2_res['from_currency_id'], $width=27); ?> <b><?php echo $row2_res['from_currency']; ?> <i class="fa fa-long-arrow-right" aria-hidden="true" style="color: #000"></i></b> <?php echo GetCurrencyImg($row2_res['to_currency_id'], $width=27); ?> <b><?php echo $row2_res['to_currency']; ?></b></a>
								<br>
								<?php } ?>
								<b style="font-size: 17px"><?php echo $row2_res['review_title']; ?></b>
								<p style="text-align: justify"><?php echo $row2_res['review']; ?></p>
							
							</div>
			              </div>
			            </blockquote>
						</div>
						<?php } ?>	
					</div>
					</div>
				
				<?php }else{ ?>		
					<p class="text-center">No testimonias at this time.<?php //echo CBE1_TESTIMONIALS_NO; ?></p>
				<?php } ?>
				
		</div>	
		
		

			
	
	
</div>
</div>

	<?php if (SHOW_SITE_STATS == 1) { ?>
	<div class="row" style="background:#f9fcf9; padding: 15px; margin: 20px 0 10px 0; border-radius: 7px;">
		<div class="col-md-3 text-center">
			<?php $all_clients_row = mysqli_fetch_array(smart_mysql_query("SELECT count( DISTINCT(client_email) ) as total FROM exchangerix_exchanges WHERE user_id='0'")); $all_clients_num = $all_clients_row['total']; ?>
			<h2><?php echo (GetUsersTotal() + $all_clients_num); ?></h2>
			<h3><i class="fa fa-users" aria-hidden="true"></i> Clients trust us</h3>
		</div>
		<div class="col-md-3 text-center">
			<?php $all_t_stats_row = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE created > DATE_SUB(NOW(), INTERVAL 1 DAY)")); $all_t_stats = $all_t_stats_row['total']; // status='confirmed' AND ?>
			<h2><?php echo $all_t_stats; ?></h2>			
			<h3><i class="fa fa-exchange" aria-hidden="true"></i> Exchanges Today</h3>
		</div>		
		<div class="col-md-3 text-center">
			<?php $all_t_stats_row = mysqli_fetch_array(smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges")); $all_t_stats = $all_t_stats_row['total']; //  WHERE status='confirmed' ?>
			<h2><?php echo $all_t_stats; ?></h2>			
			<h3><i class="fa fa-refresh" aria-hidden="true"></i> Total Exchanges</h3>
		</div>
		<div class="col-md-3 text-center">
			<h2>$<?php echo number_format(floatval(getsetting('total_exchanges_usd'))); ?></h2>			
			<h3><i class="fa fa-money" aria-hidden="true"></i> Exchanged (in USD)</h3>
		</div>
	</div>
	<?php } ?>

</div>
</div>

<script type="text/javascript" src="<?php echo SITE_URL; ?>js/jquery.min.js"></script>
	<script type="text/javascript">
	 $(document).ready(function(){

		var qty3=$("#child3");
		var qty4=$("#child4");

			var qty=$("#child");
			var qty2=$("#child2");

	    qty.keyup(function(){
		    $("#errors").hide();
		    $("#max_amount").hide();
		    $("#max_reserve").hide();
	        
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
					
					qty3.val(qty.val());
					qty4.val(qty2.val());
	    });
	    
	    
	    qty2.keyup(function(){
		    $("#errors").hide();
		    $("#max_amount").hide();
		    $("#max_reserve").hide();
	        <?php if ($receive_row['reserve'] > 0) { ?>if (qty2.val() > <?php echo $receive_row['reserve']; ?>) { $("#child2").val(<?php echo $receive_row['reserve']; ?>); $("#max_reserve #mmax").html(<?php echo $receive_row['reserve']; ?>); $("#max_reserve").show(); } <?php } ?>
	        //var total=isNaN(parseInt(qty.val()* $("#child").val())) ? 0 :(qty.val()* $("#child").val())
	        var total=isNaN(parseInt(qty2.val()/ <?php echo floatval($row['exchange_rate']); ?>)) ? 0 :(qty2.val()/ <?php echo floatval($row['exchange_rate']); ?>)
	        total = Math.ceil(total);
					$("#child").val(total);
					
					qty3.val(qty.val());
					qty4.val(qty2.val());
	    });   
	});	

			$(document).on('click', '.income', function(e) {
			    e.preventDefault();
			    e = $(this).text();    
				var totals = isNaN(parseInt(e* <?php echo floatval($row['exchange_rate']); ?>)) ? 0 :(e* <?php echo floatval($row['exchange_rate']); ?>)
				totals = parseFloat(totals.toFixed(3));
			   $("#child").val(e);
				 $("#child2").val(totals);
				 
				 qty3.val(qty.val());
					qty4.val(qty2.val());
			});
			
			$(document).on('click', '.outgo', function(e) {
			    e.preventDefault();
			    e = $(this).text();
				var totals = isNaN(parseInt(e/ <?php echo floatval($row['exchange_rate']); ?>)) ? 0 :(e/ <?php echo floatval($row['exchange_rate']); ?>)
				totals = parseFloat(totals.toFixed(3));	
				//ftotal = totals.toFixed(4); //totals = (Math.round(totals * 100)/100).toFixed(3);		    
			   $("#child2").val(e);
				 $("#child").val(totals);
				 
				 qty3.val(qty.val());
					qty4.val(qty2.val());
			});			
				

	    	$(document).ready(function() {
				//$("#loader").show();
				// setInterval(function () {
				// 	$('#exrate').each(function(){
				// 		$(this).hide().load('<?php SITE_URL; ?>getdata.php?what=rate&direction=<?php echo $row['exdirection_id']; ?>').fadeIn('slow');
				// 		//$("#loader").hide();
				// 	});
				// }, 60000);
				
				// $('#refresh_link').click(function (e) {
				// 	e.preventDefault();
				//   $('#exrate').hide().load('<?php SITE_URL; ?>getdata.php?what=rate&direction=<?php echo $row['exdirection_id']; ?>').fadeIn('slow');
				//   $('#refresh_link').delay(300).hide();
				//   $('#refresh_link').delay(30000).fadeIn('slow');
				// });				
				
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

<?php require_once("inc/footer.inc.php"); ?>