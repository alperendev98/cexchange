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

	$content = GetContent('contact');


	if (isset($_POST['action']) && $_POST['action'] == 'contact')
	{
		unset($errs);
		$errs = array();

		$fname		= getPostParameter('fname');
		$email		= getPostParameter('email');
		$subject	= trim(getPostParameter('subject'));
		$umessage	= nl2br(getPostParameter('umessage'));
		//$_GET['ref'] //dev

		if (!($fname && $email && $subject && $umessage))
		{
			$errs[] = CBE1_CONTACT_ERR1;
		}
		else
		{
			if (isset($email) && $email !="" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
			{
				$errs[] = CBE1_CONTACT_ERR2;
			}
		}

		if (count($errs) == 0)
		{
			$from = 'From: '.$fname.' <'.$email.'>';
			SendEmail(SITE_MAIL, $subject, $umessage, $noreply_mail = 1, $from);
				
			header("Location: contact.php?msg=1");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>\n";
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE			= $content['title'];
	$PAGE_DESCRIPTION	= $content['meta_description'];
	$PAGE_KEYWORDS		= $content['meta_keywords'];

	require_once ("inc/header.inc.php");
	
?>

		<h1><?php echo $content['title']; ?></h1>

		<div class="row">
		  <div class="col-md-6 col-md-push-6">
		      
		      <?php if (SHOW_OPERATOR_HOURS == 1) { ?><br><i class="fa fa-clock-o fa-lg"></i> Working time: <?php echo OPERATOR_HOURS; ?> <?php echo OPERATOR_TIMEZONE; ?><br><?php } ?>
		      <?php if (CONTACT_PHONE != "") { ?><br><i class="fa fa-phone-square fa-2x" aria-hidden="true" style="color: #92b453"></i> <?php echo CONTACT_PHONE; ?><?php } ?>
		      <?php if (CONTACT_PHONE2 != "") { ?><br><i class="fa fa-phone-square fa-2x" aria-hidden="true" style="color: #92b453"></i> <?php echo CONTACT_PHONE2; ?><?php } ?>
		      <?php if (CONTACT_PHONE3 != "") { ?><br><i class="fa fa-phone-square fa-2x" aria-hidden="true" style="color: #92b453"></i> <?php echo CONTACT_PHONE3; ?><?php } ?>
		      <br>
		      <?php if (WHATSAPP != "") { ?><br><i class="fa fa-whatsapp fa-2x" aria-hidden="true" style="color: #30ad1e"></i> <?php echo WHATSAPP; ?><?php } ?>
		      <?php if (SKYPE != "") { ?><br><i class="fa fa-skype fa-2x" aria-hidden="true" style="color: #00b0f1"></i> <?php echo SKYPE; ?><?php } ?>
		      <?php if (TELEGRAM != "") { ?><br><i class="fa fa-telegram fa-2x" aria-hidden="true" style="color: #279ed2"></i> <?php echo TELEGRAM; ?><?php } ?>	
		      <?php if (VIBER != "") { ?><br><i class="fa fa-whatsapp fa-2x" aria-hidden="true" style="color: #834996"></i> <?php echo VIBER; ?><?php } ?>		      	  			  
			  <p><?php echo $content['text']; ?></p>
			  
		  </div>			
		  <div class="col-md-6 col-md-pull-6">
			<br>

			<h3 class="text-center visible-xs"><?php echo CBE1_CONTACT_TITLE; ?></h3>

			<?php if (isset($_GET['msg']) && $_GET['msg'] == 1) { ?>
				<div class="alert alert-success"><?php echo CBE1_CONTACT_SENT; ?></div>
			<?php }?>
	
			<?php if (isset($allerrors) && $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php } ?>	
	
		  <form action="" method="post">
		  <div class="form-group">
			  <label><?php echo CBE1_CONTACT_NAME; ?></label>
			  <input name="fname" class="form-control" type="text" value="<?php echo getPostParameter('fname'); ?>" required="required">
			</div>
			<div class="form-group">
			  <label><?php echo CBE1_CONTACT_EMAIL; ?></label>
			  <input name="email" class="form-control" type="email" value="<?php echo getPostParameter('email'); ?>" required="required">
			</div>
			<div class="form-group">
			  <label><?php echo CBE1_CONTACT_SUBJECT; ?></label>
			  <input name="subject" class="form-control" type="text" value="<?php echo getPostParameter('subject'); ?>" required="required">
			</div>
			<div class="form-group">
			  <label><?php echo CBE1_CONTACT_MESSAGE; ?></label>
			  <textarea cols="50" rows="8" class="form-control" required="required" name="umessage"><?php echo getPostParameter('umessage'); ?></textarea>
			</div>
			<input type="hidden" name="action" id="action" value="contact" />
			<button type="submit" class="btn btn-success btn-lg" name="Submit"><i class="fa fa-paper-plane" aria-hidden="true"></i> <?php echo CBE1_CONTACT_BUTTON; ?></button>
		  </form>
	  
	  </div>
	  </div>
	
<?php require_once ("inc/footer.inc.php"); ?>