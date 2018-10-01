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


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$content_id = (int)$_GET['id'];
		$content = GetContent($content_id);
	}
	else
	{		
		header ("Location: index.php");
		exit();
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE			= $content['title'];
	$PAGE_DESCRIPTION	= $content['meta_description'];
	$PAGE_KEYWORDS		= $content['meta_keywords'];

	require_once ("inc/header.inc.php");

?>

	<h1><?php echo $content['title']; ?></h1>

	<div class="breadcrumbs"><a href="<?php echo SITE_URL; ?>" class="home_link"><?php echo CBE1_BREADCRUMBS_HOME; ?></a> &#155; <?php echo ($content['link_title'] != "") ? $content['link_title'] : $content['title']; ?></div>

	<p><?php echo $content['text']; ?></p>


<?php require_once("inc/footer.inc.php"); ?>