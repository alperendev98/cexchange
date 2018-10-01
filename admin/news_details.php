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

	$cpage = 21;

	CheckAdminPermissions($cpage);

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$news_id = (int)$_GET['id'];
		$query = "SELECT *, DATE_FORMAT(modified, '".DATE_FORMAT." %h:%i %p') AS modify_date FROM exchangerix_news WHERE news_id='$news_id' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "News Details";
	require_once ("inc/header.inc.php");

?>   
    
      <?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

          <h2><i class="fa fa-newspaper-o" aria-hidden="true"></i> News Details</h2>

          <table width="100%" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td align="left" valign="top">
				<b><?php echo stripslashes($row['news_title']); ?></b>
            </td>
          </tr>
          <tr>
            <td><div class="sline"></div></td>
          </tr>
          <tr>
            <td valign="top" style="height: 370px"><?php echo stripslashes($row['news_description']); ?></td>
          </tr>
          <tr>
            <td><div class="sline"></div></td>
          </tr>
          <tr>
            <td height="25" align="right" valign="middle" style="background: #F7F7F7">
				Status: <?php echo ($row['status'] == "inactive") ? "<span class='inactive_s'>inactive</span>" : "<span class='active_s'>active</span>"; ?> &nbsp;
				Last modified: <span class="date"><?php echo $row['modify_date']; ?></span></td>
          </tr>
          <tr>
            <td align="center" valign="bottom">
				<input type="button" class="btn btn-success" name="edit" value="Edit News" onClick="javascript:document.location.href='news_edit.php?id=<?php echo $row['news_id']; ?>'" />
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='news.php'" />
            </td>
          </tr>

          </table>

      <?php }else{ ?>
				<div class="alert alert-info">Sorry, no news found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>