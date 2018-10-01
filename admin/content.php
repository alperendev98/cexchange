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

	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 7 && $_GET['action'] == "delete")
	{
		$content_id = (int)$_GET['id'];
		smart_mysql_query("DELETE FROM exchangerix_content WHERE content_id='$content_id'");
		header("Location: content.php?msg=deleted");
		exit();
	}

	$query = "SELECT * FROM exchangerix_content ORDER BY name, language";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	$cc = 0;

	$title = "Content";
	require_once ("inc/header.inc.php");

?>

		<div id="addnew">
			<a class="addnew" href="content_add.php">New Page</a>
		</div>

		<h2><i class="fa fa-files-o" aria-hidden="true"></i> Content</h2>

        <?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div style="width:80%;" class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "added":	echo "Content was successfully added"; break;
						case "updated": echo "Content has been successfully edited"; break;
						case "deleted": echo "Content has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

			<table align="center" class="tbl" width="80%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th class="noborder" width="5%">&nbsp;</th>
				<th width="45%">Page Title</th>
				<th width="20%">Name</th>
				<th width="15%"><i class="fa fa-globe" aria-hidden="true"></i> Language</th>
				<th width="20%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center">&nbsp; <i class="fa fa-file-o fa-lg" aria-hidden="true" style="color: #80c1e4"></i></td>
					<td align="left" valign="middle" class="row_title">&nbsp; <a href="content_details.php?id=<?php echo $row['content_id']; ?>"><?php echo $row['title']; ?></a></td>
					<td align="left" valign="middle" style="padding-left: 15px;">
						<?php
								switch ($row['name'])
								{
									case "home": echo "Home"; break;
									case "aboutus": echo "About"; break;
									case "howitworks": echo "How it works"; break;
									case "affiliate": echo "Affiliate program"; break;
									case "payment_declined": echo "Declined payment"; break;
									case "payment_success": echo "Successful payment"; break;
									case "help": echo "Help"; break;
									case "terms": echo "Terms"; break;
									case "privacy": echo "Privacy"; break;
									case "contact": echo "Contact"; break;
									case "page": echo "New page"; break;
									default: echo $row['name']; break;
								}
						?>
					</td>
					<td align="center" valign="middle" nowrap="nowrap"><span class="badge"><?php echo ($row['language'] != "") ? $row['language'] : "-- all --"; ?></span></td>
					<td align="center" valign="middle" nowrap="nowrap">
						<a href="content_details.php?id=<?php echo $row['content_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="content_edit.php?id=<?php echo $row['content_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<?php if ($row['name'] == "page") { ?>
							<a href="content.php?id=<?php echo $row['content_id']; ?>&action=delete" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
						<?php } ?>
					</td>
				  </tr>
			<?php } ?>
            </table>

          <?php }else{ ?>
				<div class="alert alert-info">There are no pages at this time.</div>
          <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>