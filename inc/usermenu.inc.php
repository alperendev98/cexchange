
	<!--<li><a href="<?php echo SITE_URL; ?>myaccount.php"><i class="fa fa-home" aria-hidden="true"></i> <?php echo CBE1_ACCOUNT_HOME; ?></a></li>-->
	<!-- <li class="hidden-xs"><a href="<?php echo SITE_URL; ?>"><i class="fa fa-plus" aria-hidden="true"></i> Start Exchange</a></li> -->
	<li><a href="<?php echo SITE_URL; ?>mybalance.php#exchanges"><i class="fa fa-refresh" aria-hidden="true"></i> My Exchanges</a></li>
	<li><a href="<?php echo SITE_URL; ?>mybalance.php"><i class="fa fa-exchange" aria-hidden="true"></i> <?php echo CBE1_ACCOUNT_BALANCE; ?></a></li>
	<li><a href="<?php echo SITE_URL; ?>invite.php"><i class="fa fa-users" aria-hidden="true"></i> <?php echo CBE1_ACCOUNT_INVITE; ?></a></li>
	<li><a href="<?php echo SITE_URL; ?>myreviews.php"><i class="fa fa-comments-o" aria-hidden="true"></i> <?php echo CBE1_ACCOUNT_REVIEWS; ?></a></li>	
	<?php if ((EMAIL_VERIFICATION == 1 || PHONE_VERIFICATION == 1 || DOCUMENT_VERIFICATION == 1 || ADDRESS_VERIFICATION == 1)) { ?>
		<li><a href="<?php echo SITE_URL; ?>myaccount.php#verification"><i class="fa fa-address-card-o" aria-hidden="true"></i> Account Verification</a></li>
	<?php } ?>
	
	<li><a href="<?php echo SITE_URL; ?>withdraw.php"><i class="fa fa-money" aria-hidden="true"></i> <?php echo CBE1_ACCOUNT_WITHDRAW; ?></a></li>
	<li><a href="<?php echo SITE_URL; ?>myprofile.php"><i class="fa fa-edit" aria-hidden="true"></i> <?php echo CBE1_ACCOUNT_PROFILE; ?></a></li>
	<li class="divider"></li>
	<li><a href="<?php echo SITE_URL; ?>logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i> <?php echo CBE1_ACCOUNT_LOGOUT; ?></a></li>
					