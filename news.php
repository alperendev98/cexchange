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


	$results_per_page = NEWS_PER_PAGE;
	$cc = 0;
	
	
	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$news_id = (int)$_GET['id'];

		$result2 = smart_mysql_query("SELECT *, DATE_FORMAT(added, '".DATE_FORMAT."') AS news_date FROM exchangerix_news WHERE news_id='$news_id' AND status='active' LIMIT 1");
		$total2 = mysqli_num_rows($result2);
		
	}

	$result = smart_mysql_query("SELECT *, DATE_FORMAT(added, '".DATE_FORMAT."') AS news_date FROM exchangerix_news WHERE news_id='$news_id' AND status='active' LIMIT 1");
	$total = mysqli_num_rows($result);	
	

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;
	
	$result = smart_mysql_query("SELECT *, DATE_FORMAT(added, '".DATE_FORMAT."') AS news_date FROM exchangerix_news WHERE status='active' ORDER BY added DESC LIMIT $from, $results_per_page");
	
	$total_result = smart_mysql_query("SELECT * FROM exchangerix_news WHERE status='active' ORDER BY added DESC");
	$total = mysqli_num_rows($total_result);
	$total_on_page = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = CBE1_NEWS_TITLE;

	require_once ("inc/header.inc.php");

?>

	<h1><i class="fa fa-newspaper-o"></i> <?php echo CBE1_NEWS_TITLE; ?></h1>
	
	<?php if ($total2 > 0) { $row2 = mysqli_fetch_array($result2); ?>
	
		<ul class="breadcrumb">
			<li><a href="<?php echo SITE_URL; ?>" class="home_link"><?php echo CBE1_BREADCRUMBS_HOME; ?></a>  <span class="divider">/</span</li>
			<li><a href="<?php echo SITE_URL; ?>news.php"><?php echo CBE1_NEWS_TITLE; ?></a> <span class="divider">/</span</li>
			<li class="active"><?php echo $row2['news_title']; ?></li>
		</ul>
	
		<div class="news_date"><?php echo $row2['news_date']; ?></div>
		<div class="news_title"><h3><?php echo $row2['news_title']; ?></h3></div>
		<div class="news_description"><?php echo stripslashes($row2['news_description']); ?></div>
		<p align="right"><a class="more" href="<?php echo SITE_URL; ?>news.php"><?php echo CBE1_NEWS_OTHER; ?></a></p>	
	
	<?php }else{ ?>

	<?php if ($total > 0) { ?>

		<?php while ($row = mysqli_fetch_array($result)) { ?>
		<div class="news_info">
			<div class="news_date"><?php echo $row['news_date']; ?></div>
			<div class="news_title"><h3><a href="<?php echo SITE_URL; ?>news.php?id=<?php echo $row['news_id']; ?>"><?php echo $row['news_title']; ?></a></h3></div>
			<div class="news_description">
			<?php
				$description = TruncateText($row['news_description'], 500);
				if (strlen($description) > 500) $description .= " <a class='more' href='".SITE_URL."news.php?id=".$row['news_id']."'>".CBE1_NEWS_MORE."</a>";
				echo $description;
			?>
			</div>
		</div>
		<?php } ?>

		<?php echo ShowPagination("news",$results_per_page,"news.php?","WHERE status='active'"); ?>

	<?php }else{ ?>
				<p><?php echo CBE1_NEWS_NO; ?></p>
	<?php } ?>
	
	<?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>