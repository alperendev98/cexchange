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

	if (isset($_POST['action']) && $_POST['action'] == "addcontent")
	{
		$language			= mysqli_real_escape_string($conn, $_POST['language']);
		//$page_name		= mysqli_real_escape_string($conn, getPostParameter('page_name'));
		$page_title			= mysqli_real_escape_string($conn, $_POST['page_title']);
		$page_text			= mysqli_real_escape_string($conn, $_POST['page_text']);
		if (isset($_POST['link_title']) && $_POST['link_title'] != "") $link_title = mysqli_real_escape_string($conn, getPostParameter('link_title')); else $linnk_title = $page_title;
		$meta_description	= mysqli_real_escape_string($conn, getPostParameter('meta_description'));
		$meta_keywords		= mysqli_real_escape_string($conn, getPostParameter('meta_keywords'));
		$page_location		= mysqli_real_escape_string($conn, getPostParameter('page_location'));
		$status				= mysqli_real_escape_string($conn, getPostParameter('status'));

		if ($_POST['add'] && $_POST['add'] != "")
		{
			unset($errs);
			$errs = array();

			if (!($page_title && $page_text))
			{
				$errs[] = "Please fill in all required fields";
			}
			else
			{
				if ($page_name != "page")
				{
					$check_query = smart_mysql_query("SELECT * FROM exchangerix_content WHERE name='$page_name' AND language='$language'");
					if (mysqli_num_rows($check_query) != 0)
					{
						$errs[] = "Sorry, that page already exists";
					}
				}
			}

			if (count($errs) == 0)
			{
				$sql = "INSERT INTO exchangerix_content SET language='$language', name='page', link_title='$link_title', title='$page_title', description='$page_text', page_location='$page_location', page_url='', meta_description='$meta_description', meta_keywords='$meta_keywords', status='$status', modified=NOW()";

				if (smart_mysql_query($sql))
				{
					header("Location: content.php?msg=added");
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
	}


	$title = "Create Page";
	require_once ("inc/header.inc.php");

?>
 
        <h2><i class="fa fa-file-o" aria-hidden="true"></i> Create Page</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

        <form action="" name="form1" method="post">
          <table style="background:#F9F9F9" width="100%" align="center" cellpadding="2" cellspacing="3"  border="0">
          <!--
		  <tr>
            <td  width="17%" valign="middle" align="left" class="tb1">Page Name:</td>
            <td valign="top">
				<select name="page_name">
					<option value="page" <?php if ($page_name == 'page') echo "selected='selected'"; ?>>New page</option>
					<option value="home" <?php if ($page_name == 'home') echo "selected='selected'"; ?>>Home page</option>
					<option value="aboutus" <?php if ($page_name == 'aboutus') echo "selected='selected'"; ?>>About Us</option>
					<option value="howitworks" <?php if ($page_name == 'howitworks') echo "selected='selected'"; ?>>How it works</option>
					<option value="help" <?php if ($page_name == 'help') echo "selected='selected'"; ?>>Help</option>
					<option value="terms" <?php if ($page_name == 'terms') echo "selected='selected'"; ?>>Terms and Conditions</option>
					<option value="privacy" <?php if ($page_name == 'privacy') echo "selected='selected'"; ?>>Privacy Policy</option>
					<option value="contact" <?php if ($page_name == 'contact') echo "selected='selected'"; ?>>Contact Us</option>			
				</select>
			</td>
          </tr>
		  -->
          <tr>
            <td width="17%" valign="middle" align="left" class="tb1"><span class="req">* </span>Page Title:</td>
            <td valign="top"><input type="text" name="page_title" id="page_title" value="<?php echo getPostParameter('page_title'); ?>" size="80" required="required" class="form-control" /></td>
          </tr>
          <tr>
            <td  valign="middle" align="right" class="tb1">&nbsp;</td>
            <td valign="top">
				<textarea cols="80" id="editor" name="page_text" rows="10"><?php echo stripslashes($_POST['page_text']); ?></textarea>
				<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
				<script>
					CKEDITOR.replace( 'editor' );
				</script>
			</td>
          </tr>
          <tr>
            <td  valign="middle" align="left" class="tb1">Language:</td>
            <td valign="top">
				<select name="language" class="form-control" onChange="document.form1.submit()">
				<option value="">-- any --</option>
				<?php

					$lang_sql = "SELECT * FROM exchangerix_languages WHERE status='active' ORDER BY sort_order, language";
					$lang_result = smart_mysql_query($lang_sql);

					if (mysqli_num_rows($lang_result) > 0) {
						while ($lang_row = mysqli_fetch_array($lang_result)) {
				?>
					<option value="<?php echo $lang_row['language']; ?>" <?php if ($language == $lang_row['language']) echo 'selected="selected"'; ?>><?php echo $lang_row['language']; ?></option>

				<?php 
					}
						}
				?>
				</select>			
			</td>
          </tr>
          <tr>
            <td  valign="middle" align="left" class="tb1">Link Title:</td>
            <td valign="top"><input type="text" name="link_title" id="link_title" value="<?php echo getPostParameter('link_title'); ?>" size="40" class="form-control" /></td>
          </tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Meta Description:</td>
				<td valign="top"><textarea name="meta_description" cols="112" rows="2" class="form-control" style="width: 99%"><?php echo getPostParameter('meta_description'); ?></textarea></td>
            </tr>
			<tr>
				<td valign="middle" align="left" class="tb1">Meta Keywords:</td>
				<td valign="top"><input type="text" name="meta_keywords" id="meta_keywords" value="<?php echo getPostParameter('meta_keywords'); ?>" size="115" class="form-control" style="width: 99%" /></td>
            </tr>
          <tr>
            <td  valign="middle" align="left" class="tb1">Add link to:</td>
            <td valign="top">
				<select name="page_location" class="form-control" style="width: 65px">
					<option value="">----------</option>
					<option value="top" <?php if ($page_location == 'top') echo "selected='selected'"; ?>>Top menu</option>
					<option value="footer" <?php if ($page_location == 'footer') echo "selected='selected'"; ?>>Footer menu</option>
					<option value="topfooter" <?php if ($page_location == 'topfooter') echo "selected='selected'"; ?>>Top &amp; footer</option>
				</select>
			</td>
          </tr>
		  <tr>
			<td  valign="middle" align="left" class="tb1">Status:</td>
            <td valign="top">
				<select name="status" class="selectpicker">
					<option value="active" <?php if ($status == "active") echo "selected"; ?>>active</option>
					<option value="inactive" <?php if ($status == "inactive") echo "selected"; ?>>inactive</option>
				</select>
			</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
			<td align="left" valign="bottom">
				<input type="hidden" name="action" id="action" value="addcontent" />
				<input type="submit" name="add" id="add" class="btn btn-success" value="Create Page" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='content.php'" />
            </td>
          </tr>
        </table>
      </form>

<?php require_once ("inc/footer.inc.php"); ?>