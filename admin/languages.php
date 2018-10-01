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

	$cpage = 26;

	CheckAdminPermissions($cpage);

	$languages_dir = "../language/";
	$languages = scandir($languages_dir); 
	$array = array(); 
	foreach ($languages as $file)
	{
		if (is_file($languages_dir.$file) && strstr($file, ".inc.php"))
		{
			$language = mysqli_real_escape_string($conn, str_replace(".inc.php","",$file));
			$check_query = smart_mysql_query("SELECT * FROM exchangerix_languages WHERE language='$language'");
			if (mysqli_num_rows($check_query) == 0)
			{
				smart_mysql_query("INSERT INTO exchangerix_languages SET language='$language', status='inactive'");
			}
		}
	}

	$query = "SELECT * FROM exchangerix_languages ORDER BY status, language_id";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	// set default language
	if (MULTILINGUAL == 1)
	{
		$cresult = smart_mysql_query("SELECT * FROM exchangerix_languages WHERE status='active'");
		if (mysqli_num_rows($cresult) == 1)
		{
			$crow = mysqli_fetch_array($cresult);
			smart_mysql_query("UPDATE exchangerix_settings SET setting_value='".mysqli_real_escape_string($conn, $crow['language'])."' WHERE setting_key='website_language' LIMIT 1");
		}
	}

	$cc = 0;

	$title = "Site Languages";
	require_once ("inc/header.inc.php");

?>

		<div id="addnew"><a class="addnew" href="#" onclick="$('#add_new_form').toggle('fast');">Add Language</a></div>

		<h2>Site Languages</h2>		

        <?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div style="width:44%;" class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "updated": echo "Language has been successfully updated"; break;
					}
				?>
			</div>
			<?php } ?>

			<div id="add_new_form" class="alert alert-info" style="width: 44%; display: none">
				<b>How do add new language to your site?</b><br/>
				Simply upload new language file in <b>/language/</b> directory.
			</div>

			<table align="center" width="44%" class="tbl" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="15%">&nbsp;</th>
				<th width="45%">Language</th>
				<th width="20%">Status</th>
				<th width="25%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>		  
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" nowrap="nowrap"><img src="<?php echo SITE_URL; ?>images/flags/<?php echo $row['language_code']; ?>.png" align="absmiddle" /></td>
					<td align="left" valign="middle" class="row_title"><?php echo $row['language']; ?></td>
					<td align="left" valign="middle" style="padding-left: 10px"><?php if ($row['status'] == "inactive") echo "<span class='inactive_s'>".$row['status']."</span>"; else echo "<span class='active_s'>".$row['status']."</span>"; ?></td>
					<td align="center" valign="middle" nowrap="nowrap"><a href="language_edit.php?id=<?php echo $row['language_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a></td>
				  </tr>
			<?php } ?>
            </table>

          <?php }else{ ?>
				<div class="alert alert-info">There are no languages found.</div>
				<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
          <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>