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

	$exchange_id = (int)$_GET['id'];
	

		//// ADD TESTIMONIAL ///////////////////////////////////////////////////////////////////////
		if (isset($_POST['action']) && $_POST['action'] == "addreview") // && isLoggedIn()
		{
			$exchange_id	= (int)getPostParameter('exchangeid');
			$rating			= (int)getPostParameter('rating');
			$review_title	= mysqli_real_escape_string($conn, getPostParameter('review_title'));
			$review			= mysqli_real_escape_string($conn, nl2br(trim(getPostParameter('review'))));
			$review			= ucfirst(strtolower($review));

			unset($errs);
			$errs = array();

			if (!($exchange_id && $rating && $review_title && $review))
			{
				$errs[] = CBE1_REVIEW_ERR;
			}
			else
			{
				$number_lines = count(explode("<br />", $review));
				
				if (strlen($review) > MAX_REVIEW_LENGTH)
					$errs[] = str_replace("%length%",MAX_REVIEW_LENGTH,CBE1_REVIEW_ERR2);
				else if ($number_lines > 5)
					$errs[] = CBE1_REVIEW_ERR3;
				else if (stristr($review, 'http'))
					$errs[] = CBE1_REVIEW_ERR4;
			}

			if (count($errs) == 0)
			{
				$review = substr($review, 0, MAX_REVIEW_LENGTH);
				
				if (ONE_REVIEW == 1)
					$check_review = mysqli_num_rows(smart_mysql_query("SELECT * FROM exchangerix_reviews WHERE exchange_id='$exchange_id' AND user_id='$userid'"));
				else
					$check_review = 0;

				if ($check_review == 0)
				{
					(REVIEWS_APPROVE == 1) ? $status = "pending" : $status = "active";
					$review_query = "INSERT INTO exchangerix_reviews SET exchange_id='$exchange_id', rating='$rating', user_id='$userid', review_title='$review_title', review='$review', status='$status', added=NOW()";
					$review_result = smart_mysql_query($review_query);
					$review_added = 1;

					// send email notification //
					if (NEW_REVIEW_ALERT == 1) 
					{
						SendEmail(SITE_ALERTS_MAIL, CBE1_EMAIL_ALERT2, CBE1_EMAIL_ALERT2_MSG);
					}
					/////////////////////////////
					
					header("Location: myreviews.php?msg=added");
					exit();
			
				}
				else
				{
					$errormsg = CBE1_REVIEW_ERR5;
				}
			}
			else
			{
				$errormsg = "";
				foreach ($errs as $errorname)
					$errormsg .= $errorname."<br/>";
			}
		}
		//////////////////////////////////////////////////////////////////////////////////////////	


	if (isset($_GET['act']) && $_GET['act'] == "del")
	{
		$del_query = "DELETE FROM exchangerix_reviews WHERE user_id='$userid' AND exchange_id='$exchange_id'";
		if (smart_mysql_query($del_query))
		{
			header("Location: myreviews.php?msg=deleted");
			exit();
		}
	}

	
	$query = "SELECT r.*, e.* FROM exchangerix_reviews r, exchangerix_exchanges e WHERE r.user_id='$userid' AND r.exchange_id=e.exchange_id AND e.status='confirmed' ORDER BY e.created DESC";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = CBE1_MYREVIEWS_TITLE;

	require_once ("inc/header.inc.php");
	
?>

	<div class="row">
		<div class="col-md-12 hidden-xs">
		<div id="acc_user_menu">
			<ul><?php require("inc/usermenu.inc.php"); ?></ul>
		</div>
	</div>

	<h1><i class="fa fa-comments-o" aria-hidden="true"></i> <?php echo CBE1_MYREVIEWS_TITLE; ?></h1>

	<p style="text-align: right"><a class="btn btn-info" href="#" onclick="$('#add_new_form').toggle('fast');$('.alert-success').hide();$('#all_list').toggle('fast');"><i class="fa fa-plus"></i> Post Feedback</a></p>

	<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
		<div class="alert alert-success alert-dismissible fade in">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<i class="fa fa-check"></i>
			<?php
				switch ($_GET['msg'])
				{
					case "added": echo "Thank you. Your feedback was added."; break;
					case "deleted": echo CBE1_MYREVIEWS_DELETED; break;
				}
			?>
		</div>
	<?php } ?>

	<?php 
			$ex_result = smart_mysql_query("SELECT *, DATE_FORMAT(created, '".DATE_FORMAT."') AS date_created FROM exchangerix_exchanges WHERE user_id='$userid' AND status!='unknown' AND exchange_id NOT IN (SELECT exchange_id FROM exchangerix_reviews WHERE user_id='$userid') ORDER BY created DESC");
			if (mysqli_num_rows($ex_result) > 0 && !$_GET['msg'])
			{					
	?>

	<div class="row">
	<div class="col-md-6 col-md-push-3">
	
			<?php if (REVIEWS_APPROVE == 1 && $review_added == 1) { ?>
				<div class="alert alert-success"><?php echo CBE1_REVIEW_SENT; ?></div>
			<?php }else{ ?>		
		
			<div id="add_new_form" style="background: #F9F9F9; padding: 20px; margin: 10px 0; border-radius: 7px; display: <?php echo ($_POST['action']) ? "" : "none"; ?>">
	
				<?php if (isset($errormsg) && $errormsg != "") { ?>
					<div class="alert alert-danger"><?php echo $errormsg; ?></div>
				<?php } ?>				
				
				<h2><i class="fa fa-comment-o"></i> Add Feeback</h2>
				<p>Please add your testimonial about our service.</p>
				<form action="" method="post">
						<div class="form-group">
						<select name="exchangeid" class="form-control" required>
							<option value="">-- select exchange --</option>
							<?php while ($ex_row = mysqli_fetch_array($ex_result)) { ?>
								<option value="<?php echo $ex_row['exchange_id']; ?>" <?php if ($exchange_id == $ex_row['exchange_id']) echo "selected"; ?>><?php echo $ex_row['date_created']." &nbsp;&middot;&nbsp; "; ?>Exchange #<?php echo $ex_row['exchange_id']; ?> &nbsp;&middot;&nbsp; <?php echo floatval($ex_row['exchange_amount'])." ".$ex_row['from_currency']." &rarr; ".floatval($ex_row['receive_amount'])." ".$ex_row['to_currency']; ?></option>
							<?php } ?>
						</select>
						<br>							
						<select name="rating" class="form-control" requred>
							<option value=""><?php echo CBE1_REVIEW_RATING_SELECT; ?></option>
							<option value="5" <?php if ($rating == 5) echo "selected"; ?>>&#9733;&#9733;&#9733;&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING1; ?></option>
							<option value="4" <?php if ($rating == 4) echo "selected"; ?>>&#9733;&#9733;&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING2; ?></option>
							<option value="3" <?php if ($rating == 3) echo "selected"; ?>>&#9733;&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING3; ?></option>
							<option value="2" <?php if ($rating == 2) echo "selected"; ?>>&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING4; ?></option>
							<option value="1" <?php if ($rating == 1) echo "selected"; ?>>&#9733; - <?php echo CBE1_REVIEW_RATING5; ?></option>
						</select>
						</div>
						<div class="form-group">
							<label><?php echo CBE1_REVIEW_RTITLE; ?></label>
							<input type="text" name="review_title" id="review_title" value="<?php echo getPostParameter('review_title'); ?>" size="47" class="form-control" required /></div>
						<div class="form-group">
							<label><?php echo CBE1_REVIEW_REVIEW; ?></label>
							<textarea id="review" name="review" cols="45" rows="7" class="form-control" required><?php echo getPostParameter('review'); ?></textarea>
						</div>			
						<input type="hidden" name="action" value="addreview" />
						<button type="submit" class="btn btn-success btn-lg">Add Feeback</button>
				</form>
			</div>
			<?php } ?>
	</div>
	</div>

	<?php }else{ ?>
		<?php if (GetUserExchangesTotal($userid) == 0) { ?>
			<div class="alert alert-info text-center" id="add_new_form" style="display: none">
				<i class="fa fa-info-circle fa-lg"></i> You do not have exchanges at this time.
			</div>
		<?php }else{ ?>
			<div class="alert alert-info text-center" id="add_new_form" style="display: none">
				<i class="fa fa-info-circle fa-lg"></i> You can submit only one feedback for each exchange.
			</div>
		<?php } ?>
	<?php } ?>



	<div class="table-responsive" id="all_list" style="display: <?php echo ($_POST['action']) ? "none" : ""; ?>">
	<table align="center" class="btb" width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<th width="40%"><?php echo CBE1_MYREVIEWS_EXCHANGE; ?></th>
		<th width="10%"><?php echo CBE1_MYREVIEWS_RATING; ?></th>
		<th width="15%"><?php echo CBE1_MYREVIEWS_DATE; ?></th>
		<th width="10%"><?php echo CBE1_MYREVIEWS_STATUS; ?></th>
		<th width="5%"></th>
	</tr>
	<?php
			$cc = 0;
			$query_reviews = "SELECT *, DATE_FORMAT(added, '".DATE_FORMAT."') AS date_added FROM exchangerix_reviews WHERE user_id='$userid' ORDER BY added DESC";
			$result_reviews = smart_mysql_query($query_reviews);
			$total_reviews = mysqli_num_rows($result_reviews);

			if ($total_reviews > 0) {
	?>
		<!--<p><?php echo CBE1_MYREVIEWS_TEXT; ?></p>-->

		<?php while ($row_reviews = mysqli_fetch_array($result_reviews)) { $cc++; ?>
		 <tr>
			<td valign="middle" align="left" style="padding-left: 10px"><h3>Exchange #<?php echo $row_reviews['exchange_id']; //reference_id ?> <!--<i class="fa fa-long-arrow-right" aria-hidden="true"></i>--> </h3><br></td>
			<td valign="middle" align="center">
					<!--<img src="<?php echo SITE_URL; ?>images/icons/rating-<?php echo $row_reviews['rating']; ?>.png" />-->
					<?php for ($i=0; $i<5;$i++) { ?><i class="fa fa-star" style="font-size: 22px; margin-right: 3px; color: <?php echo ($i<$row_reviews['rating']) ? "#ecb801" : "#CCC"; ?>"></i><?php } ?>					
			</td>
			<td valign="middle" align="center"><?php echo $row_reviews['date_added']; ?></td>
			<td valign="middle" align="center">
				<?php
						switch ($row_reviews['status'])
						{
							case "pending":		echo "<span class='label label-warning'><i class='fa fa-clock-o'></i> ".CBE1_STATUS_REVIEW."</span>"; break;
							case "active":		echo "<span class='label label-success'>".CBE1_STATUS_ACTIVE."</span>"; break;
							case "inactive":	echo "<span class='label label-default'>".CBE1_STATUS_INACTIVE."</span>"; break;
							default:			echo "<span class='label label-default'>".$row_reviews['status']."</span>"; break;
						}
				?>			
			</td>
			<td valign="middle" align="center"><a href="#" onclick="if (confirm('<?php echo CBE1_MYREVIEWS_DELETE; ?>') )location.href='<?php echo SITE_URL; ?>myreviews.php?act=del&id=<?php echo $row_reviews['exchange_id']; ?>'" title="Delete"><i class="fa fa-times fa-2x itooltip" title="Delete Feedback" style="color: #d34835"></i></a></td>
		</tr>
		<tr>
			<td class="review_brd" colspan="5" align="left" valign="top">
				<div class="myreview">
					<h4><?php echo $row_reviews['review_title']; ?></h4>
					<p><?php echo $row_reviews['review']; ?></p>
				</div>
			</td>
		</tr>
		<?php } ?>

	<?php }else{ ?>
			<tr height="35"><td colspan="5" align="center"><?php echo CBE1_MYREVIEWS_NO; ?></td></tr>
	<?php } ?>
	</table>
	</div>




<?php require_once ("inc/footer.inc.php"); ?>