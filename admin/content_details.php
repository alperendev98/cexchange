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

	$cpage = 22;

	CheckAdminPermissions($cpage);

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$cid	= (int)$_GET['id'];

		$query = "SELECT *, DATE_FORMAT(modified, '".DATE_FORMAT." %h:%i %p') AS modify_date FROM exchangerix_content WHERE content_id='$cid'";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "View Content";
	require_once ("inc/header.inc.php");

?>   
    
      <?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

          <table width="100%" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
			<td style="background:#F9F9F9" align="left" valign="top">
				<h3><i class="fa fa-file-o" aria-hidden="true"></i> <?php echo stripslashes($row['title']); ?></h3>
			</td>
          </tr>
          <tr>
            <td><div class="sline"></div></td>
          </tr>
          <tr>
            <td valign="top" style="height: 370px"><?php echo stripslashes($row['description']); ?></td>
          </tr>
          <tr>
            <td><div class="sline"></div></td>
          </tr>
			<?php if ($row['meta_description'] != "") { ?>
			<tr>
				<td valign="top" align="right" class="tb1">Meta Description:</td>
				<td valign="top"><?php echo $row['meta_description']; ?></td>
			</tr>
			<?php } ?>
			<?php if ($row['meta_keywords'] != "") { ?>
			<tr>
				<td valign="top" align="right" class="tb1">Meta Keywords:</td>
				<td valign="top"><?php echo $row['meta_keywords']; ?></td>
			</tr>
			<?php } ?>
          <tr>
            <td height="40" align="right" valign="middle" style="background: #F7F7F7">
				<?php if ($row['language'] != "") { ?>Language: <span class="badge"><?php echo $row['language']; ?></span> | <?php } ?>
				<i class="fa fa-clock-o"></i> Last modified: <?php echo $row['modify_date']; ?> | 
				Status: <?php echo ($row['status'] == "inactive") ? "<span class='inactive_s'>inactive</span>" : "<span class='active_s'>active</span>"; ?>&nbsp;
			</td>
          </tr>
		  <?php if ($row['name'] == "page") { ?>
          <tr>
            <td colspan="2" height="40" style="background:#F9F9F9" style="line-height: 17px;" align="left" valign="middle">
				<?php if ($row['page_location'] != "") { ?>
					<b>Page location</b>: 
					<?php 
						switch ($row['page_location'])
						{
							case "top": echo "Top menu"; break;
							case "footer": echo "Footer menu"; break;
							case "topfooter": echo "Top &amp; footer"; break;
							default: echo "---------"; break;
						}
					?><br/>			
				<?php } ?>
				<?php if ($row['link_title'] != "") { ?><b>Link title</b>: <?php echo $row['link_title']; ?><br/><?php } ?>
				<b>Page URL</b>: <a target="_blank" href="<?php echo SITE_URL."content.php?id=".$row['content_id']; ?>"><?php echo SITE_URL."content.php?id=".$row['content_id']; ?></a>
			</td>
          </tr>
		  <?php } ?>
		  </table>

            <p class="text-center">
				<input type="button" class="btn btn-success" name="edit" value="Edit Page" onClick="javascript:document.location.href='content_edit.php?id=<?php echo $row['content_id']; ?>'" />  
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='content.php'" />
            </p>
          
      <?php }else{ ?>
			<div class="alert alert-info">Sorry, no page found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>