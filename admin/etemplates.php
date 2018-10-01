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

	$cpage = 23;

	CheckAdminPermissions($cpage);

	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 8 && $_GET['action'] == "delete")
	{
		$etemplate_id = (int)$_GET['id'];
		smart_mysql_query("DELETE FROM exchangerix_email_templates WHERE template_id='$etemplate_id'");	
		header("Location: etemplates.php?msg=deleted");
		exit();
	}

	$query = "SELECT * FROM exchangerix_email_templates WHERE email_name!='email2users' GROUP BY email_name ORDER BY template_id";
	$result = smart_mysql_query($query);
	$total = mysqli_num_rows($result);

	$query2 = "SELECT * FROM exchangerix_email_templates WHERE email_name='email2users' ORDER BY template_id";
	$result2 = smart_mysql_query($query2);
	$total2 = mysqli_num_rows($result2);

	$cc = $cc2 = 0;

	$title = "Email Templates";
	require_once ("inc/header.inc.php");

?>

		<div id="addnew"><a class="addnew" href="etemplate_add.php">New Email Template</a></div>

		<h2><i class="fa fa-envelope" aria-hidden="true"></i> Email Templates</h2>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div style="width:60%;" class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "added":	echo "Email template was successfully added"; break;
						case "updated": echo "Email template has been successfully edited"; break;
						case "deleted": echo "Email template has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

			<table align="center" class="tbl" width="60%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th class="noborder" width="5%">&nbsp;</th>
				<th width="55%">Email Template</th>
				<th width="25%"><i class="fa fa-globe" aria-hidden="true"></i> Language</th>
			</tr>
             <?php if ($total > 0) { while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center">&nbsp; <i class="fa fa-at fa-lg" style="color: #5cb85c; font-weight: bold;"></i></td>
					<td align="left" valign="middle" class="row_title"><?php echo GetETemplateTitle($row['email_name']); ?></td>
					<td align="center" valign="middle" style="line-height:19px">
						<?php
							$lquery = "SELECT * FROM exchangerix_email_templates WHERE email_name='".mysqli_real_escape_string($conn, $row['email_name'])."' ORDER BY language ASC";
							$lresult = smart_mysql_query($lquery);
							$ltotal = mysqli_num_rows($lresult);
							if ($ltotal > 0)
							{
								while ($lrow = mysqli_fetch_array($lresult))
								{
									echo "<a href='etemplate_edit.php?id=".$lrow['template_id']."'>".$lrow['language']."</a>";
									//if ($lrow['template_id'] > 8) echo "<a href=\"etemplates.php?id=".$lrow['template_id']."&action=delete\" title=\"Delete\"><img src=\"images/delete.png\" border=\"0\" width='12' align='absmiddle' alt=\"Delete\" /></a>";
									echo "<br/>";
								}
							}
						?>
					</td>
				  </tr>
			<?php } } ?>
             <?php if ($total2 > 0) { while ($row2 = mysqli_fetch_array($result2)) { $cc2++; ?>
				  <tr class="<?php if (($cc2%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center"><img src="images/icons/etemplate.png" border="0" /></td>
					<td align="left" valign="middle" class="row_title">
						<?php echo GetETemplateTitle($row2['email_name']); ?>
						<br/><span style="color: #CCC"><?php echo substr($row2['email_subject'],0,55); ?></span>
					</td>
					<td align="center" valign="middle" style="line-height:15px">
						<a href="etemplate_edit.php?id=<?php echo $row2['template_id']; ?>"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="etemplates.php?id=<?php echo $row2['template_id']; ?>&action=delete" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } } ?>
			<?php if ($total == 0 && $total2 == 0) { ?>
				<tr>
					<td height="30" colspan="3" align="center" valign="middle"><div class="alert alert-info">Sorry, no email templates found.</div></td>
				</tr>
			<?php } ?>
            </table>
          

<?php require_once ("inc/footer.inc.php"); ?>