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

	if (isset($_POST['action']) && $_POST['action'] == "add_news")
	{
		unset($errs);
		$errs = array();

		$news_title			= mysqli_real_escape_string($conn, getPostParameter('news_title'));
		$news_description	= mysqli_real_escape_string($conn, $_POST['news_description']);
		//$for_members		= (int)getPostParameter('for_members');//dev
		$status				= mysqli_real_escape_string($conn, getPostParameter('status'));

		if(!($news_title && $news_description))
		{
			$errs[] = "Please fill in all fields";
		}

		if (count($errs) == 0)
		{
			$sql = "INSERT INTO exchangerix_news SET news_title='$news_title', news_description='$news_description', status='$status', added=NOW(), modified=NOW()";

			if (smart_mysql_query($sql))
			{
				header("Location: news.php?msg=added");
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

	$title = "Add News";
	require_once ("inc/header.inc.php");

?>
 
        <h2><i class="fa fa-newspaper-o" aria-hidden="true"></i> Add News</h2>

		<?php if (isset($allerrors) && $allerrors != "") { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>

        <form action="" method="post">
          <table style="background:#F9F9F9" width="100%" align="center" cellpadding="2" cellspacing="3"  border="0">
          <tr>
            <td valign="middle" align="right" class="tb1">Title:</td>
            <td valign="top"><input type="text" name="news_title" id="news_title" value="<?php echo getPostParameter('news_title'); ?>" size="55" class="form-control" /></td>
          </tr>
          <tr>
            <td valign="middle" align="right" class="tb1">&nbsp;</td>
            <td valign="top"><textarea name="news_description" cols="75" rows="12" id="editor" class="form-control"><?php echo stripslashes($_POST['news_description']); ?></textarea></td>
          </tr>
		  <script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
		  <script>
				CKEDITOR.replace( 'editor' );
		  </script>
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
            <td align="center" valign="bottom">&nbsp;</td>
			<td align="left" valign="bottom">
				<input type="hidden" name="action" id="action" value="add_news" />
				<input type="submit" name="add" id="add" class="btn btn-success" value="Add News" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onClick="javascript:document.location.href='news.php'" />
            </td>
          </tr>
        </table>
      </form>


<?php require_once ("inc/footer.inc.php"); ?>