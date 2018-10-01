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

	$content = GetContent('payment_declined');

	///////////////  Page config  ///////////////
	$PAGE_TITLE			= $content['title'];
	$PAGE_DESCRIPTION	= $content['meta_description'];
	$PAGE_KEYWORDS		= $content['meta_keywords'];

	require_once ("inc/header.inc.php"); 

?>

	<h1 style="color: #982727"><i class="fa fa-times-circle"></i> <?php echo $content['title']; ?> &nbsp; <!--#<?php echo $_SESSION['rid']; ?>--></h1>
	
	<div class="alert alert-danger">
		<p><?php echo $content['text']; ?></p>
		<?php if (isset($_GET['reason']) && $_GET['reason'] != "") { ?>
			<?php if ($_GET['reason'] == "timeout") { ?><h3><i class="fa fa-info-circle" style="color: #982727"></i> Reason: Exchange time is expired</h3><?php } ?>
			<?php } ?>
	</div>
	
	<?php
			// delete exchange id
			unset($_SESSION['rid'], $_SESSION['transaction_id']);	
	?>

<?php require_once("inc/footer.inc.php"); ?>