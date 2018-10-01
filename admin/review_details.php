<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ------------ Exchangerix IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("../inc/adm_auth.inc.php");
	require_once("../inc/config.inc.php");
	require_once("./inc/admin_funcs.inc.php");

	$cpage = 14;

	CheckAdminPermissions($cpage);

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$reviewid	= (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(added, '".DATE_FORMAT." %h:%i %p') AS date_added FROM exchangerix_reviews WHERE review_id='$reviewid' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}


	$title = "Testimonial Details";
	require_once ("inc/header.inc.php");

?>
    <?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>
    
     <h2><i class="fa fa-comment-o" aria-hidden="true"></i> Testimonial Details #<?php echo $row['review_id']; ?></h2>

        <table style="background:#F9F9F9" width="100%" cellpadding="2" cellspacing="3"  border="0" align="center">
          <tr><td colspan="2">&nbsp;</td></tr>
		  <tr>
			<tr>
				<td width="10%" valign="middle" align="left" class="tb1">By:</td>
				<td valign="middle">
					<?php if ($row['user_id'] == 0) { ?>
						<i class="fa fa-user-o" aria-hidden="true"></i> <?php echo $row['author']; ?><!--Visitor-->
					<?php }else{ ?>
						<i class="fa fa-user-circle" aria-hidden="true"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo GetUsername($row['user_id']); ?></a>
					<?php } ?>
				</td>
			</tr>
			<?php if ($row['exchange_id'] > 0) { ?>
			<tr>
				<td valign="middle" align="left" class="tb1">Exchange #:</td>
				<td valign="middle"><a href="exchange_details.php?id=<?php echo $row['exchange_id']; ?>"><?php echo $row['exchange_id']; ?></a></td>
			</tr>
			<?php } ?>
			<tr>
				<td valign="top" align="left" class="tb1">&nbsp;</td>
				<td valign="top">
					<div style="width: 98%; background: #FFF; background: #FFF; border-radius: 8px; padding: 15px 10px;">
					<?php for ($i=0; $i<5;$i++) { ?><i class="fa fa-star" style="color: <?php echo ($i<$row['rating']) ? "#89b601" : "#CCC"; ?>"></i> <?php } ?> <!-- x of 5 -->
					<br/>
					<p><b><?php echo $row['review_title']; ?></b></p>
					<?php echo $row['review']; ?>
					</div>
				</td>
            </tr>
			<tr>
				<td valign="middle" align="left" class="tb1"><i class="fa fa-clock-o"></i> Date:</td>
				<td valign="middle"><?php echo $row['date_added']; ?></td>
            </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">Status:</td>
				<td valign="middle">
				<?php
						switch ($row['status'])
						{
							case "pending": echo "<span class='pending_status'>awaiting approval</span>"; break;
							case "active": echo "<span class='active_s'>".$row['status']."</span>"; break;
							case "inactive": echo "<span class='inactive_s'>".$row['status']."</span>"; break;
							default: echo "<span class='default_status'>".$row['status']."</span>"; break;
						}
				?>
				</td>
            </tr>
			<tr><td colspan="2">&nbsp;</td></tr>
          </table>

			<p align="center">
		  		<input type="button" class="btn btn-success" name="edit" value="Edit Testimonial" onClick="javascript:document.location.href='review_edit.php?id=<?php echo $row['review_id']; ?>'" /> 
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onclick="history.go(-1);return false;" />
			</p>
      
	  <?php }else{ ?>
	  		<h2><i class="fa fa-comment-o" aria-hidden="true"></i> Testimonial Details</h2>
			<div class="alert alert-info">Sorry, no testimonial found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>