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

	if (isset($_POST['action']) && $_POST['action'] == "editcontent")
	{
		//$language			= mysqli_real_escape_string($conn, $_POST['language']);
		$content_id			= (int)getPostParameter('cid');
		$link_title			= mysqli_real_escape_string($conn, getPostParameter('link_title'));
		$page_title			= mysqli_real_escape_string($conn, $_POST['page_title']);
		$page_text			= mysqli_real_escape_string($conn, $_POST['page_text']);
		$meta_description	= mysqli_real_escape_string($conn, getPostParameter('meta_description'));
		$meta_keywords		= mysqli_real_escape_string($conn, getPostParameter('meta_keywords'));
		$page_location		= mysqli_real_escape_string($conn, getPostParameter('page_location'));
		$status				= mysqli_real_escape_string($conn, getPostParameter('status'));

		unset($errs);
		$errs = array();

		if (!($page_title && $page_text))
		{
			$errs[] = "Please fill in all required fields";
		}

		if (count($errs) == 0)
		{
			$sql = "UPDATE exchangerix_content SET link_title='$link_title', title='$page_title', description='$page_text', page_location='$page_location', page_url='', meta_description='$meta_description', meta_keywords='$meta_keywords', status='$status', modified=NOW() WHERE content_id='$content_id' LIMIT 1"; //language='$language', 

			if (smart_mysql_query($sql))
			{
				header("Location: content.php?msg=updated");
				exit();
			}
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}
	}


	if (isset($_GET['id']) && is_numeric($_GET['id'])) { $cid = (int)$_GET['id']; } else { $cid = (int)$_POST['cid']; }
	
	$query = "SELECT * FROM exchangerix_content WHERE content_id='$cid' LIMIT 1";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);


	$title = "Edit Content";
	require_once ("inc/header.inc.php");

?>
 
      <?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

        <h2><i class="fa fa-file-o" aria-hidden="true"></i> Edit Content</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

        <form action="" method="post">
          <table style="background:#F9F9F9" width="100%" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td width="17%" valign="middle" align="left" class="tb1"><span class="req">* </span>Page Title:</td>
            <td valign="top"><input type="text" name="page_title" id="page_title" value="<?php echo $row['title']; ?>" size="80" class="form-control" /></td>
          </tr>
          <tr>
            <td  valign="middle" align="right" class="tb1">&nbsp;</td>
            <td valign="top">
				<textarea cols="80" id="editor" name="page_text" rows="10"><?php echo stripslashes($row['description']); ?></textarea>
				<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
				<script>
					CKEDITOR.replace( 'editor' );
				</script>		
			</td>
          </tr>
          <tr>
				<td valign="middle" align="left" class="tb1">Meta Description:</td>
				<td valign="top"><textarea name="meta_description" cols="112" rows="2" class="form-control" style="width: 99%"><?php echo strip_tags($row['meta_description']); ?></textarea></td>
          </tr>
          <tr>
				<td valign="middle" align="left" class="tb1">Meta Keywords:</td>
				<td valign="top"><input type="text" name="meta_keywords" id="meta_keywords" value="<?php echo $row['meta_keywords']; ?>" size="115" class="form-control" style="width: 99%" /></td>
          </tr>
		  <?php if ($row['language'] != "") { ?>
          <tr>
            <td  valign="middle" align="left" class="tb1">Language:</td>
            <td valign="top"><input type="text" class="form-control" value="<?php echo $row['language']; ?>" size="13" disabled="disabled" /></td>
          </tr>
		  <?php } ?>
		  <?php if ($row['name'] == "page") { ?>
          <tr>
            <td  valign="middle" align="left" class="tb1">Link Title:</td>
            <td valign="top"><input type="text" name="link_title" id="link_title" value="<?php echo $row['link_title']; ?>" size="40" class="form-control" /></td>
          </tr>
		  <?php } ?>
          <tr>
            <td  valign="middle" align="left" class="tb1">Page Name:</td>
            <td valign="top"><input type="text" class="form-control" value="<?php echo $row['name']; ?>" size="13" disabled="disabled" /></td>
          </tr>
		  <?php if ($row['content_id'] > 7) { ?>
          <tr>
            <td  valign="middle" align="left" class="tb1">Add link to:</td>
            <td valign="top">
				<select name="page_location" class="form-control" style="width: 65px">
					<option value="">----------</option>
					<option value="top" <?php if ($row['page_location'] == 'top') echo "selected='selected'"; ?>>Top menu</option>
					<option value="footer" <?php if ($row['page_location'] == 'footer') echo "selected='selected'"; ?>>Footer menu</option>
					<option value="topfooter" <?php if ($row['page_location'] == 'topfooter') echo "selected='selected'"; ?>>Top &amp; footer</option>
				</select>
			</td>
          </tr>
		  <tr>
			<td  valign="middle" align="left" class="tb1">Status:</td>
            <td valign="top">
				<select name="status" class="selectpicker">
					<option value="active" <?php if ($row['status'] == "active") echo "selected"; ?>>active</option>
					<option value="inactive" <?php if ($row['status'] == "inactive") echo "selected"; ?>>inactive</option>
				</select>
			</td>
          </tr>
		  <?php } ?>
          <tr>
            <td>&nbsp;</td>
			<td align="left" valign="bottom">
				<input type="hidden" name="cid" id="cid" value="<?php echo (int)$row['content_id']; ?>" />
				<input type="hidden" name="action" id="action" value="editcontent" />
				<input type="submit" name="update" id="update" class="btn btn-success" value="Update" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='content.php'" />
            </td>
          </tr>
        </table>
      </form>

      <?php }else{ ?>
			<div class="alert alert-info">Sorry, no page found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>