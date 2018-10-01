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


	$results_per_page = 9; //dev REVIEWS_PER_PAGE NEWS_PER_PAGE
	$cc = 0;

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;
	
	$result = smart_mysql_query("SELECT *, DATE_FORMAT(added, '".DATE_FORMAT."') AS review_date FROM exchangerix_reviews r LEFT JOIN exchangerix_exchanges t ON r.exchange_id=t.exchange_id WHERE r.status='active' ORDER BY r.added DESC LIMIT $from, $results_per_page");
	
	$total_result = smart_mysql_query("SELECT * FROM exchangerix_reviews r LEFT JOIN exchangerix_exchanges t ON r.exchange_id=t.exchange_id WHERE r.status='active' ORDER BY r.added DESC");
	$total = mysqli_num_rows($total_result);
	$total_on_page = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Testimonials"; //CBE1_TESTIMONIALS_TITLE

	require_once ("inc/header.inc.php");

?>

	<h1><i class="fa fa-comments-o" aria-hidden="true"></i> Testimonials <?php if ($total > 0) { ?><span class="testimonials-count" style="font-size: 19px; padding: 3px 15px; margin: 3px; position: relative;
  top: -0.5em; color: #328813; border: 1px solid #328813; border-radius: 5px;"><?php echo $total; ?></span><?php } ?></h1>

	<?php if ($total > 0) { ?>

		<h3 class="text-center">Here's what some of our clients think about us.</h4>
		<br>

		<div class="row" id="testimonials">
		<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
		<div class="col-md-4 col-sm-6 col-xs-12<?php if ($cc%3 == 0) echo 'last'; ?>" style="margin: 15px 0px;"><!-- offset-md-3 -->
			<span class="label label-default pull-right" style="background: #ccc; font-size: 12px;"><i class="fa fa-clock-o"></i> <?php echo findTimeAgo($row['added']); ?></span>
			<p> by <i class="fa fa-user-o fa-lg" aria-hidden="true"></i> <b style="font-size: 18px"><?php echo ($row['author'] == "") ? GetUsername($row['user_id'], $hide_lastname = 1) : $row['author']; ?></b></p>
			<!--<img src="<?php echo SITE_URL; ?>images/icons/rating-<?php echo $row['rating']; ?>.png" />-->
			<?php for ($i=0; $i<5;$i++) { ?><i class="fa fa-star" style="font-size: 22px; margin-right: 3px; color: <?php echo ($i<$row['rating']) ? "#89b601" : "#CCC"; ?>"></i><?php } ?><br><br>
			<?php if ($row['from_currency'] != "" && $row['to_currency'] != "") { ?><p><a href="<?php echo SITE_URL; ?>index.php?currency_send=<?php echo $row['from_currency_id']; ?>&currency_receive=<?php echo $row['to_currency_id']; ?>" style="color: #777"><?php echo GetCurrencyImg($row['from_currency_id'], $width=27); ?> <?php echo $row['from_currency']; ?> <i class="fa fa-long-arrow-right" aria-hidden="true" style="color: #000"></i> <?php echo GetCurrencyImg($row['to_currency_id'], $width=27); ?> <?php echo $row['to_currency']; ?></a></p><?php } ?>				
			<h3><?php echo $row['review_title']; ?></h3>
			<p style="margin: 8px 0; text-align: justify;"><?php echo $row['review']; ?></p>					
		</div>
		<?php } ?>
		</div>

		<?php echo ShowPagination("reviews",$results_per_page,"testimonials.php?","WHERE status='active'"); ?>

	<?php }else{ ?>
			<div class="alert alert-info"><i class="fa fa-info-circle fa-lg"></i> No testimonials at this time.</div>
	<?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>