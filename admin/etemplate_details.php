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

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$eid	= (int)$_GET['id'];
		$query = "SELECT * FROM exchangerix_email_templates WHERE template_id='$eid' LIMIT 1";
		$result = smart_mysql_query($query);
		$total = mysqli_num_rows($result);
	}

	$title = "View Email Template";
	require_once ("inc/header.inc.php");

?>   
    
      <?php if ($total > 0) { $row = mysqli_fetch_array($result); ?>

          <h2><i class="fa fa-envelope" aria-hidden="true"></i> View Email Template</h2>

          <br/>
          <table width="80%" align="center" cellpadding="5" cellspacing="3"  border="0">
            <tr>
              <td align="left" valign="top"><b><?php echo stripslashes($row['email_subject']); ?></b></td>
            </tr>
           <tr>
            <td style="background: #F9F9F9; border-top: 1px solid #EEE; border-bottom: 1px solid #EEE; line-height: 14px;" valign="top"><?php echo stripslashes($row['email_message']); ?></td>
          </tr>
           <tr>
            <td height="25" valign="middle" align="right" style="background: #F7F7F7">Language: <b><?php echo $row['language']; ?></b></td>
          </tr>
          <tr>
            <td align="center" valign="bottom">
				<input type="button" class="btn btn-success" name="edit" value="Edit Email Template" onClick="javascript:document.location.href='etemplate_edit.php?id=<?php echo $row['template_id']; ?>'" /> 
				<input type="button" class="btn btn-default" name="cancel" value="Go Back" onClick="javascript:document.location.href='etemplates.php'" />
            </td>
          </tr>
          </table>

      <?php }else{ ?>
			<div class="alert alert-info">Sorry, no email template found.</div>
			<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
      <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>