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

	if (isset($_POST["action"]) && $_POST["action"] == "add") //dev
	{
			unset($errors);
			$errors = array();

			$rating			= (int)getPostParameter('rating');
			$review_title	= mysqli_real_escape_string($conn, getPostParameter('review_title'));
			$review			= mysqli_real_escape_string($conn, getPostParameter('review'));

			if (!($review_title && $rating))
			{
				$errs[] = "Please fill in all fields";
			}

			if (count($errs) == 0)
			{
				smart_mysql_query("INSERT INTO exchangerix_reviews SET author='Visitor', review_title='$review_title', rating='$rating', review='$review', status='active', added=NOW()");

				header("Location: reviews.php?msg=added");
				exit();
			}
			else
			{
				$errormsg = "";
				foreach ($errs as $errorname)
					$errormsg .= $errorname."<br/>";
			}
	}


	$title = "Add Testimonial";
	require_once ("inc/header.inc.php");

?>


    <h2><i class="fa fa-comment-o" aria-hidden="true"></i> Add Testimonial</h2>


	<?php if (isset($errormsg) && $errormsg != "") { ?>
		<div class="alert alert-danger"><?php echo $errormsg; ?></div>
	<?php } ?>

      <form action="" method="post" name="form1">
        <table style="background:#F9F9F9" width="100%" cellpadding="2" cellspacing="3"  border="0" align="center">
          <tr>
            <td width="9%" valign="middle" align="left" class="tb1">By:</td>
            <td valign="middle">
							<?php if ($row['user_id'] > 0) { ?>
								<i class="fa fa-user-o" aria-hidden="true"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo GetUsername($row['user_id']); ?></a>
							<?php }else{ ?>
								<i class="fa fa-user-o" aria-hidden="true"></i> Visitor
							<?php } ?>
	        </td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Rating:</td>
            <td valign="top">
				<select class="selectpicker" id="rating" name="rating" style="width: 150px;">
					<option value="">---------</option>
					<option value="5" <?php if ($row['rating'] == 5) echo "selected"; ?>>&#9733;&#9733;&#9733;&#9733;&#9733; - Excellent</option>
					<option value="4" <?php if ($row['rating'] == 4) echo "selected"; ?>>&#9733;&#9733;&#9733;&#9733; - Very Good</option>
					<option value="3" <?php if ($row['rating'] == 3) echo "selected"; ?>>&#9733;&#9733;&#9733; - Good</option>
					<option value="2" <?php if ($row['rating'] == 2) echo "selected"; ?>>&#9733;&#9733; - Fair</option>
					<option value="1" <?php if ($row['rating'] == 1) echo "selected"; ?>>&#9733; - Poor</option>
				</select>			
			</td>
          </tr>
          <tr>
            <td valign="middle" align="left" class="tb1">Title:</td>
            <td valign="top"><input type="text" name="review_title" id="review_title" value="<?php echo $row['review_title']; ?>" size="72" class="form-control" /></td>
          </tr>
            <tr>
				<td valign="middle" align="left" class="tb1">&nbsp;</td>
				<td valign="top"><textarea name="review" cols="70" rows="10" class="form-control"><?php echo strip_tags($row['review']); ?></textarea></td>
            </tr>
            <tr>
              <td align="center" valign="bottom">&nbsp;</td>
			  <td align="left" valign="bottom">
				<input type="hidden" name="action" id="action" value="add">
				<input type="submit" class="btn btn-success" name="update" id="update" value="Add Testimonial" />
				<input type="button" class="btn btn-default" name="cancel" value="Cancel" onclick="history.go(-1);return false;" />
              </td>
            </tr>
          </table>
      </form>


<?php require_once ("inc/footer.inc.php"); ?>