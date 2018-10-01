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
	require_once("inc/pagination.inc.php");

	define('FRIENDS_INVITATIONS_LIMIT', 5);
	
	$ReferralLink	= SITE_URL."?ref=".$userid;
	$umessage		= CBE1_INVITE_EMAIL_MESSAGE;
	$umessage		= str_replace("<br/>", "&#13;&#10;", $umessage);
	$umessage		= str_replace("%site_title%", SITE_TITLE, $umessage);
	$umessage		= str_replace("%referral_link%", $ReferralLink, $umessage);


	if (isset($_POST['action']) && $_POST['action'] == "friend")
	{
		unset($errs);
		$errs = array();

		$uname		= $_SESSION['FirstName'];
		$fname		= array();
		$fname		= $_POST['fname'];
		$femail		= array();
		$femail		= $_POST['femail'];
		$umessage	= nl2br(getPostParameter('umessage'));

		if(!($fname[1] && $femail[1]))
		{
			$errs[] = CBE1_INVITE_ERR;
		}
		else
		{
			foreach ($fname as $k=>$v)
			{
				if ($femail[$k] != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $femail[$k]))
				{
					$errs[] = CBE1_INVITE_ERR2." #".$k;
				}
			}
		}

		if (count($errs) == 0)
		{
			$etemplate = GetEmailTemplate('invite_friend');
				
			$recipients = "";

			foreach ($fname as $k=>$v)
			{
				if (isset($v) && $v != "" && isset($femail[$k]) && $femail[$k] != "")
				{
					$friend_name	= substr(htmlentities(trim($v)), 0, 25);
					$friend_email	= substr(htmlentities(trim($femail[$k])), 0, 70);
						
					$esubject = $etemplate['email_subject'];

					if ($umessage != "")
					{
						$emessage = $umessage;
						$emessage = str_replace("%friend_name%", $friend_name, $emessage);
						$emessage = str_replace("%referral_link%", $ReferralLink, $emessage);
						$emessage = preg_replace('/((www|http:\/\/)[^ ]+)/', '<a href="\1" target="_blank">\1</a>', $emessage);
						$emessage .= "<p><a href='$ReferralLink' target='_blank'>".$ReferralLink."</a></p>";
					}
					else
					{
						$emessage = $etemplate['email_message'];
						$emessage = str_replace("{friend_name}", $friend_name, $emessage);
						$emessage = str_replace("{first_name}", $uname, $emessage);
						$emessage = str_replace("{referral_link}", $ReferralLink, $emessage);
					}

					$recipients .= $friend_name." <".$friend_email.">||";

					$to_email = $friend_name.' <'.$friend_email.'>';					

					SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
				}
			}

			// save invitations info //
			smart_mysql_query("INSERT INTO exchangerix_invitations SET user_id='".(int)$userid."', recipients='".mysqli_real_escape_string($conn, $recipients)."', message='".mysqli_real_escape_string($conn, $umessage)."', sent_date=NOW()");

			header("Location: invite.php?msg=1");
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
	$PAGE_TITLE = CBE1_INVITE_TITLE;

	require_once ("inc/header.inc.php");

?>

	<div class="row">
		<div class="col-md-12 hidden-xs">
		<div id="acc_user_menu">
			<ul><?php require("inc/usermenu.inc.php"); ?></ul>
		</div>
	</div>


	<h1><i class="fa fa-user"></i> <?php echo CBE1_INVITE_TITLE; ?></h1>

	<div class="referral_link_share">
		<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo str_replace("/?","/index.php?",$ReferralLink); ?>&t=<?php echo SITE_TITLE; ?>" target="_blank"><i class="fa fa-facebook-square fa-2x itooltip" title="<?php echo CBE1_SHARE_FACEBOOK; ?>" style="color: #3b5898"></i></a>
		<a href="https://twitter.com/intent/tweet?text=<?php echo urlencode(SITE_TITLE); ?>&url=<?php echo urlencode($ReferralLink); ?>" target="_blank"><i class="fa fa-twitter-square fa-2x itooltip" title="<?php echo CBE1_SHARE_TWITTER; ?>" style="color: #41cbf6"></i></a>
		<a href="https://plus.google.com/share?url=<?php echo urlencode($ReferralLink); ?>" onclick="javascript:window.open(this.href,'','menubar=no,toolbar=no,resizable=yes,scrollbars=yes,top=100,left=400,height=600,width=600');return false;" title="<?php echo CBE1_SHARE_GOOGLE; ?>"><i class="fa fa-google fa-2x itooltip" title="<?php echo CBE1_SHARE_GOOGLE; ?>" style="color: #d34835"></i></a>
	</div>

	<div class="referral_link">
	<b><i class="fa fa-external-link"></i> <?php echo CBE1_INVITE_LINK; ?>:</b>
	<input type="text" id="invite_link" class="reflink_textbox" size="50" readonly="readonly" onfocus="this.select();" onclick="this.focus();this.select();" value="<?php echo $ReferralLink; ?>" />
	<button class="btn btn-success clipboard" data-clipboard-target="#invite_link"><i class="fa fa-files-o" aria-hidden="true"></i> <?php echo CBE1_CLIPBOARD_COPY; ?></button>
	</div>


	<?php

		$results_per_page = 10;
		$cc = 0;

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$refs_query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS signup_date FROM exchangerix_users WHERE ref_id='$userid' ORDER BY created DESC LIMIT $from, $results_per_page";
		$total_refs_result = smart_mysql_query("SELECT * FROM exchangerix_users WHERE ref_id='$userid'");
		$total_refs = mysqli_num_rows($total_refs_result);

		$refs_result = smart_mysql_query($refs_query);
		$total_refs_on_page = mysqli_num_rows($refs_result);

	?>
		<br>
		<h1><i class="fa fa-users"></i> <?php echo CBE1_INVITE_REFERRALS; ?> <?php if ($total_refs > 0) { ?><sup class="badge" style="background: #73b9d1"><?php echo number_format($total_refs); ?></sup><?php } ?></h1>
		<a name="referrals"></a>

		<?php if ($total_refs > 0) { ?>

			<div class="table-responsive">
			<table align="center" class="btb" width="100%" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<th width="3%">&nbsp;</th>
				<th width="40%"><?php echo CBE1_INVITE_SNAME; ?></th>
				<th width="17%"><?php echo CBE1_INVITE_SCOUNTRY; ?></th>
				<th width="20%"><?php echo CBE1_INVITE_SDATE; ?></th>
				<th width="20%"><?php echo CBE1_INVITE_STATUS; ?></th>
			</tr>
			<?php while ($refs_row = mysqli_fetch_array($refs_result)) { $cc++; ?>
			<tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
				<td align="center" valign="middle"><img src="<?php echo SITE_URL; ?>images/<?php echo ($refs_row['status'] == "active") ? "areferral_icon.png" : "referral_icon.png"; ?>" align="absmiddle" /></td>
				<td align="left" valign="middle"><?php echo $refs_row['fname']." ".substr($refs_row['lname'], 0, 1)."."; ?></td>
				<td align="center" valign="middle"><?php echo GetCountry($refs_row['country'], 1); ?></td>
				<td nowrap="nowrap" align="center" valign="middle"><?php echo $refs_row['signup_date']; ?></td>
				<td nowrap="nowrap" align="center" valign="middle"><?php if ($refs_row['status'] == "active") echo CBE1_INVITE_STATUS_ACTIVE; else echo CBE1_INVITE_STATUS_INACTIVE; ?></td>
			</tr>
			<?php } ?>
			</table>
			</div>

			<?php echo ShowPagination("users",$results_per_page,"invite.php?", "WHERE ref_id='".(int)$userid."'"); ?>
		
		<?php }else{ ?>
			<p><?php echo CBE1_INVITE_NOREFS; ?></p>
		<?php } ?>
		


	<?php if (REFERRAL_COMMISSION > 0) { ?>
     <?php

		$cc = 0;
		$results_per_page = 10;

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT *, DATE_FORMAT(created, '".DATE_FORMAT." %h:%i %p') AS date_created, DATE_FORMAT(updated, '".DATE_FORMAT."') AS updated_date FROM exchangerix_exchanges WHERE ref_id='$userid' ORDER BY created DESC LIMIT $from, $results_per_page";

		$total_result = smart_mysql_query("SELECT * FROM exchangerix_exchanges WHERE ref_id='$userid' ORDER BY created DESC");
		$total = mysqli_num_rows($total_result);

		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		
     ?>
     		<br>

     		<p class="pull-right"><i class="fa fa-money fa-lg"></i> <b>Your Earnings</b>: <span class="label label-success"><b>$<?php echo CalculatePercentage(GetReferralEarningTotal($userid), REFERRAL_COMMISSION); ?></b></span></p>

			<h1><i class="fa fa-refresh"></i> Referrals Exchanges <?php if ($total > 0) { ?><sup class="badge" style="background: #5bbc2e"><?php echo number_format($total); ?></sup><?php } ?></h1>	

			<div class="table-responsive">
            <table align="center" class="btb" width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
				<th width="15%"><i class="fa fa-clock-o"></i> <?php echo CBE1_BALANCE_DATE; ?></th>
				<th width="17%"><?php echo CBE1_PAYMENTS_ID; ?></th>
				<th width="22%"><i class="fa fa-arrow-up" aria-hidden="true"></i> Amount Send</th>
                <th width="22%">Amount Receive <i class="fa fa-arrow-down" aria-hidden="true"></i></th>
                <th width="15%"><i class="fa fa-money"></i> Your Commission</th>
                <th width="15%"><?php echo CBE1_BALANCE_STATUS; ?></th>
              </tr>
			<?php if ($total > 0) { ?>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
                <tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
                  <td valign="middle" align="center"><?php echo $row['date_created']; ?></td>
				  <td valign="middle" align="center"><i class="fa fa-eye-slash"></i> hidden<?php //echo $row['reference_id']; ?></td>
                  <td valign="middle" align="left" style="padding-left: 10px"><b><?php echo number_format($row['exchange_amount'], 2, '.', ''); ?></b> <?php echo $row['from_currency']; ?></td>
                  <td valign="middle" align="left" style="padding-left: 10px"><b><?php echo number_format($row['receive_amount'], 2, '.', ''); ?></b> <?php echo $row['to_currency']; ?></td>
                  <td valign="middle" align="left" style="padding-left: 30px"><span style="font-size: 18px; color: #5bbc2e"><b>$<?php echo CalculatePercentage(($row['exchange_amount']), REFERRAL_COMMISSION); ?></b></span></td>
                  <td valign="middle" align="left" style="padding-left: 10px;">
					<?php
							switch ($row['status'])
							{
								case "confirmed":	echo "<span class='label label-success'>".STATUS_CONFIRMED."</span>"; break;
								case "pending":		echo "<span class='label label-warning'>".STATUS_PENDING."</span>"; break;
								case "waiting":		echo "<span class='label label-warning'>waiting</span>"; break;
								case "declined":	echo "<span class='label label-danger'>".STATUS_DECLINED."</span>"; break;
								case "failed":		echo "<span class='label label-danger'>".STATUS_FAILED."</span>"; break;
								case "request":		echo "<span class='label label-default'>".STATUS_REQUEST."</span>"; break;
								case "paid":		echo "<span class='label label-success'>".STATUS_PAID."</span>"; break;
								default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
							}

							if ($row['status'] == "declined" && $row['reason'] != "")
							{
								echo " <span class='exchangerix_tooltip' title='".$row['reason']."'><img src='".SITE_URL."images/info.png' align='absmiddle' /></span>";
							}
					?>
				  </td>
                </tr>
			<?php } ?>
           
					<?php echo ShowPagination("exchanges",$results_per_page,"invite.php?","WHERE ref_id='$userid'"); ?>
			
			<?php }else{ ?>
				<tr height="30"><td colspan="5" align="center" valign="middle"><br><p>No exchanges made by your referrals at this time.</p></td></tr>
			<?php } ?>
		   </table>
			</div>
			<br><br>
			
	<?php } ?>
		

<?php require_once ("inc/footer.inc.php"); ?>