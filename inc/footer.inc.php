
</div>
</div>

	
</div>


	<footer id="footer">
	<div class="container">
	<div class="row">	

		<div class="col-md-3">
			<br><a href="<?php echo SITE_URL; ?>"><img src="<?php echo SITE_URL; ?>images/logo.png" width="200"></a><br>
	        <?php if (CONTACT_PHONE != "") { ?><br><i class="fa fa-phone-square fa-2x" aria-hidden="true"></i> <?php echo CONTACT_PHONE; ?><?php } ?>
	        <?php if (CONTACT_PHONE2 != "") { ?><br><i class="fa fa-phone-square fa-2x" aria-hidden="true"></i> <?php echo CONTACT_PHONE2; ?><?php } ?>
	        <?php if (CONTACT_PHONE3 != "") { ?><br><i class="fa fa-phone-square fa-2x" aria-hidden="true"></i> <?php echo CONTACT_PHONE3; ?><?php } ?>
	        <?php if (SHOW_OPERATOR_HOURS == 1) { ?><br>Working Time: <?php echo OPERATOR_HOURS; ?> <?php echo OPERATOR_TIMEZONE; ?><?php } ?>		
		</div>	

		<div class="col-md-3">
			<h3 class="hidden-xs"><?php echo CBE1_BOX_FOLLOW; ?></h3>
			<?php if (FACEBOOK_PAGE != "") { ?><a href="<?php echo FACEBOOK_PAGE; ?>" target="_blank" rel="nofollow"><i class="fa fa-facebook-square fa-2x" aria-hidden="true"></i></a><?php } ?>
			<?php if (TWITTER_PAGE != "") { ?><a href="<?php echo TWITTER_PAGE; ?>" target="_blank" rel="nofollow"><i class="fa fa-twitter-square fa-2x" aria-hidden="true"></i></a><?php } ?>
			<?php if (GOOGLEPLUS_PAGE != "") { ?><a href="<?php echo GOOGLEPLUS_PAGE; ?>" target="_blank" rel="nofollow"><i class="fa fa-google-plus-square fa-2x" aria-hidden="true"></i></a><?php } ?>
			<?php if (PINTEREST_PAGE != "") { ?><a href="<?php echo PINTEREST_PAGE; ?>" target="_blank" rel="nofollow"><i class="fa fa-pinterest-square fa-2x" aria-hidden="true"></i></a><?php } ?>
			<a href="<?php echo SITE_URL; ?>xml_rates.php"><i class="fa fa-rss-square fa-2x" aria-hidden="true"></i></a>		
		</div>

		<div class="col-md-3">
			<h3><?php echo CBE1_FMENU_ABOUT; ?></h3>
			<ul style="list-style: none; margin: 0;">
				<?php echo ShowFooterPages(); ?>
				<li><a href="<?php echo SITE_URL; ?>aboutus.php"><?php echo CBE1_FMENU_ABOUT; ?></a></li>
				<li><a href="<?php echo SITE_URL; ?>news.php"><?php echo CBE1_FMENU_NEWS; ?></a></li>
				<li><a href="<?php echo SITE_URL; ?>terms.php"><?php echo CBE1_FMENU_TERMS; ?></a></li>
				<li><a href="<?php echo SITE_URL; ?>privacy.php"><?php echo CBE1_FMENU_PRIVACY; ?></a></li>
				<li><a href="<?php echo SITE_URL; ?>contact.php"><?php echo CBE1_FMENU_CONTACT; ?></a></li>
			</ul>		
		</div>
		
		<div class="col-md-3">
			<p class="copyright" style="white-space: nowrap">&copy; <?php echo date("Y"); ?> <?php echo SITE_TITLE; ?>. <?php echo CBE1_FMENU_RIGHTS; ?>.</p>
			<!-- Do not remove this copyright notice! -->
			<div class="powered-by-exchangerix">Powered by <a href="http://www.exchangerix.com" title="e-currency exchange script" target="_blank"><span style="color: #94D802">Exchangerix</span></a><div>
			<!-- Do not remove this copyright notice! -->				
		</div>
	
	</div>
	</div>
	</footer>


	<!-- modal boxes -->
	<div id="signup" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
	    <div class="modal-header">
	        <button class="close" aria-hidden="true" data-dismiss="modal" type="button"><i class="fa fa-times"></i></button>
	    </div>
	    <div class="modal-body">
	        <form class="signupForm">
	            <fieldset>
	                <legend class="text-center">Create an Account</legend>
	
	                <label for="signupUsername">Username</label>
	                <input id="signupUsername" type="text" name="usr" placeholder="Enter your desired username" class="form-control">
	
	                <label for="signupEmail">Email address</label>
	                <input id="signupEmail" type="text" name="email" placeholder="What's your email address?" class="form-control">
	
	                <label for="signupPassword">Password</label>
	                <input id="signupPassword" type="password" name="pwd" placeholder="5 characters or more!" class="form-control">
	
	                <button type="submit" class="btn btn-primary btn-block">Create my account</button>
	            </fieldset>
	
	            <!--<div class="errors alert alert-error hide" style="margin-top: 15px;"></div>-->
	        </form>
	    </div>
	</div>
	
	
<div class="modal fade" id="signin" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h2 class="modal-title" id="myModalLabel"><i class="fa fa-user-circle-o"></i> <?php echo CBE1_LOGIN_TITLE; ?></h2>
        <div class="pull-right"><?php echo CBE1_LOGIN_NMEMBER; ?> <a href="<?php echo SITE_URL; ?>signup.php"><b><?php echo CBE_SIGNUP; ?></b></a></div>
      </div>
      <div class="modal-body">
	      	
			<div class="login_box" style="padding: 10px; border-radius: 10px;">
			<form action="<?php echo SITE_URL; ?>login.php" method="post">
			<div class="form-group">
				<label><?php echo CBE1_LOGIN_EMAIL2; ?></label>
				<input type="text" class="form-control input-lg" name="username" value="<?php echo getPostParameter('username'); ?>" size="25" required="required" />
			  </div>
			  <div class="form-group">
				<label><?php echo CBE1_LOGIN_PASSWORD; ?></label>
				<input type="password" class="form-control input-lg" name="password" value="" size="25" required="required" />
			  </div>
			  <div class="checkbox">
			  <label>
				<input type="checkbox" class="checkboxx" name="rememberme" id="rememberme" value="1" checked="checked" /> <?php echo CBE1_LOGIN_REMEMBER; ?>
			  </label>
			  </div>
			  <div class="form-group">
					<input type="hidden" name="action" value="login" />
					<input type="submit" class="btn btn-success btn-lg" name="login" id="login" value="<?php echo CBE1_LOGIN_BUTTON; ?>" />
			  </div>
			  <div class="form-group">
					<a href="<?php echo SITE_URL; ?>forgot.php"><?php echo CBE1_LOGIN_FORGOT; ?></a>
					<?php if (ACCOUNT_ACTIVATION == 1) { ?>
						<p><a href="<?php echo SITE_URL; ?>activation_email.php"><?php echo CBE1_LOGIN_AEMAIL; ?></a></p>
					<?php } ?>
			  </div>
		  	</form>
		  	</div>

			<?php if (FACEBOOK_CONNECT == 1 && FACEBOOK_APPID != "" && FACEBOOK_SECRET != "") { ?>
				<div style="border-bottom: 1px solid #ECF0F1; margin-bottom: 15px;">
					<div style="font-weight: bold; background: #FFF; color: #CECECE; margin: 0 auto; top: 5px; text-align: center; width: 50px; position: relative;">or</div>
				</div>
				<p align="center"><a href="javascript: void(0);" onclick="facebook_login();" class="connect-f"><img src="<?php echo SITE_URL; ?>images/facebook_connect.png" /></a></p>
			<?php } ?>
		  	
      </div>
    </div>
  </div>
</div>


	<script type="text/javascript" src="<?php echo SITE_URL; ?>js/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo SITE_URL; ?>js/bootstrap.min.js"></script>
	<!--<script type="text/javascript" async src="//platform.twitter.com/widgets.js"></script>-->
	<script type="text/javascript" src="<?php echo SITE_URL; ?>js/clipboard.min.js"></script>
	<script type="text/javascript" src="<?php echo SITE_URL; ?>js/autocomplete.js"></script>
	<script type="text/javascript" src="<?php echo SITE_URL; ?>js/jsCarousel.js"></script>
	<script type="text/javascript" src="<?php echo SITE_URL; ?>js/clipboard.js"></script>
	<script type="text/javascript" src="<?php echo SITE_URL; ?>js/exchangerix.js"></script>
	<script type="text/javascript" src="<?php echo SITE_URL; ?>js/easySlider1.7.js"></script>
	<script type="text/javascript" src="<?php echo SITE_URL; ?>js/lightbox.js"></script>
	<script type="text/javascript" src="<?php echo SITE_URL; ?>js/bootstrap-select.min.js"></script>
	
	<?php if (isset($ADDTHIS_SHARE) && $ADDTHIS_SHARE == 1) { ?>
		<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=<?php echo ADDTHIS_ID; ?>"></script>
	<?php } ?>
	
	<?php if (FACEBOOK_CONNECT == 1 && FACEBOOK_APPID != "" && FACEBOOK_SECRET != "") { ?>
		<script type="text/javascript" src="http://connect.facebook.net/en_US/all.js#appId=<?php echo FACEBOOK_APPID; ?>&amp;xfbml=1"></script>
	<?php } ?>
	
	<?php echo (CHAT_CODE != "") ? CHAT_CODE : ""; ?>

</body>
</html>