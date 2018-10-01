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

	
	if (!(isset($_SESSION['transaction_id']) && is_numeric($_SESSION['transaction_id'])))
	{
		header ("Location: index.php");
		exit();	
	}

		//// ADD TESTIMONIAL ///////////////////////////////////////////////////////////////////////
		if (isset($_POST['action']) && $_POST['action'] == "addreview") // && isLoggedIn()
		{
			$userid			= (int)$_SESSION['userid'];
			$exchange_id	= (int)$_SESSION['transaction_id'];
			$author 		= mysqli_real_escape_string($conn, getPostParameter('author'));
			$author			= ucwords(strtolower($author));
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
					$review_query = "INSERT INTO exchangerix_reviews SET exchange_id='$exchange_id', rating='$rating', user_id='$userid', author='$author', review_title='$review_title', review='$review', status='$status', added=NOW()";
					$review_result = smart_mysql_query($review_query);
					$review_added = 1;
					
					unset($_SESSION['transaction_id']);

					// send email notification //
					if (NEW_REVIEW_ALERT == 1) 
					{
						SendEmail(SITE_ALERTS_MAIL, CBE1_EMAIL_ALERT2, CBE1_EMAIL_ALERT2_MSG);
					}
					/////////////////////////////
				}
				else
				{
					$errormsg = CBE1_REVIEW_ERR5;
				}

				unset($_POST['review']);
			}
			else
			{
				$errormsg = "";
				foreach ($errs as $errorname)
					$errormsg .= $errorname."<br/>";
			}
		}
		//////////////////////////////////////////////////////////////////////////////////////////


	$content = GetContent('payment_success');

	///////////////  Page config  ///////////////
	$PAGE_TITLE			= $content['title'];

	$bg_dark = 1;
	require_once ("inc/header.inc.php");

?>

	<div class="widget">

	<h1 style="color: #6b9e46"><i class="fa fa-check-circle"></i> <?php echo $content['title']; ?> #<?php echo $_SESSION['transaction_id']; ?></h1>
	
	<?php if (@$review_added != 1) { ?>
	<div class="alert alert-success">
		<?php echo $content['text']; ?>
		
		<!-- <h3>Thank you for choosing us!</h3> -->
		
		<?php if (isset($_GET['manual']) && $_GET['manual'] == 1) { ?>
			<p>Operator will review and confirm your payment soon and you will receive email notification.</p>
		<?php }else{ ?>			
			<p>Your payment was successfully confirmed and exchange completed.</p>
		<?php } ?>
	</div>
	
	<h2><i class="fa fa-comment-o fa-lg" aria-hidden="true"></i> Leave your feedback</h2>
	
	<?php } ?>

	
	<div class="row">
	<div class="col-md-6 col-md-push-0">
	
			<?php if (isset($errormsg) && $errormsg != "") { ?>
				<div class="alert alert-danger"><?php echo $errormsg; ?></div>
			<?php } ?>
			<?php if (REVIEWS_APPROVE == 1 && $review_added == 1) { ?>
				<div class="alert alert-success"><?php echo CBE1_REVIEW_SENT; ?></div>
			<?php }else{ ?>		
		
				<form action="" method="post">
						<div class="form-group">
						<select name="rating" class="selectpicker show-menu-arrow show-tick form-control" required>
							<option value=""><?php echo CBE1_REVIEW_RATING_SELECT; ?></option>
							<option value="5" <?php if (@$rating == 5) echo "selected"; ?>>&#9733;&#9733;&#9733;&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING1; ?></option>
							<option value="4" <?php if (@$rating == 4) echo "selected"; ?>>&#9733;&#9733;&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING2; ?></option>
							<option value="3" <?php if (@$rating == 3) echo "selected"; ?>>&#9733;&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING3; ?></option>
							<option value="2" <?php if (@$rating == 2) echo "selected"; ?>>&#9733;&#9733; - <?php echo CBE1_REVIEW_RATING4; ?></option>
							<option value="1" <?php if (@$rating == 1) echo "selected"; ?>>&#9733; - <?php echo CBE1_REVIEW_RATING5; ?></option>
						</select>
						</div>
						<div class="form-group">
							<label><?php echo CBE1_CONTACT_NAME; ?></label>
							<input type="text" name="author" id="author" value="<?php echo getPostParameter('author'); ?>" size="47" class="form-control" required /></div>						
						<div class="form-group">
							<label><?php echo CBE1_REVIEW_RTITLE; ?></label>
							<input type="text" name="review_title" id="review_title" value="<?php echo getPostParameter('review_title'); ?>" size="47" class="form-control" required /></div>
						<div class="form-group">
							<label><?php echo CBE1_REVIEW_REVIEW; ?></label>
							<textarea name="review" rows="6" class="form-control" placeholder="please leave your feedback about us..." required><?php echo getPostParameter('review'); ?></textarea>
						</div>			
						<input type="hidden" name="action" value="addreview" />
						<button type="submit" class="btn btn-success btn-lg">Add Feedback</button>
				</form>
			<?php } ?>
	</div>
	
	</div>
	
	<?php 	
	
	// delete exchange id
	unset($_SESSION['rid']); 
	
	?>

<?php require_once("inc/footer.inc.php"); ?>