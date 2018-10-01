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
	require_once("../inc/pagination.inc.php");
	require_once("./inc/admin_funcs.inc.php");

	$cpage = 21;

	CheckAdminPermissions($cpage);

	$results_per_page = 20;

	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
	{
		$newsid = (int)$_GET['id'];
		smart_mysql_query("DELETE FROM exchangerix_news WHERE news_id='$newsid'");
		header("Location: news.php?msg=deleted");
		exit();
	}

		// Delete News //
		if (isset($_POST['delete']) && $_POST['delete'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$nid = (int)$v;
					DeleteNews($nid);
				}

				header("Location: news.php?msg=deleted");
				exit();
			}
		}


		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "news_id": $rrorder = "news_id"; break;
					case "added": $rrorder = "added"; break;
					case "modified": $rrorder = "modified"; break;
					case "status": $rrorder = "status"; break;
					default: $rrorder = "news_id"; break;
				}
			}
			else
			{
				$rrorder = "news_id";
			}

			if (isset($_GET['order']) && $_GET['order'] != "")
			{
				switch ($_GET['order'])
				{
					case "asc": $rorder = "asc"; break;
					case "desc": $rorder = "desc"; break;
					default: $rorder = "desc"; break;
				}
			}
			else
			{
				$rorder = "desc";
			}
		///////////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$query = "SELECT *, DATE_FORMAT(added, '".DATE_FORMAT." %h:%i %p') AS news_date FROM exchangerix_news ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
	
		$total_result = smart_mysql_query("SELECT * FROM exchangerix_news ORDER BY news_title ASC");
		$total = mysqli_num_rows($total_result);

		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$cc = 0;


	$title = "News";
	require_once ("inc/header.inc.php");

?>

		<div id="addnew"><a class="addnew" href="news_add.php">Add News</a></div>

		<h2><i class="fa fa-newspaper-o" aria-hidden="true"></i> News</h2>

        <?php if ($total > 0) { ?>

			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php
					switch ($_GET['msg'])
					{
						case "added": echo "News has been successfully added"; break;
						case "updated": echo "News has been successfully edited"; break;
						case "deleted": echo "News has been successfully deleted"; break;
					}
				?>
			</div>
			<?php } ?>

		<table style="background:#F9F9F9" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
		<td valign="middle" align="left" width="50%">
            <form id="form1" name="form1" method="get" action="">
           Sort by: 
          <select name="column" id="column" class="form-control" onChange="document.form1.submit()">
			<option value="added" <?php if ($_GET['column'] == "ids") echo "selected"; ?>>Date</option>
			<option value="modified" <?php if ($_GET['column'] == "modified") echo "selected"; ?>>Modified</option>
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
          </select>
          <select name="order" id="order" class="form-control" onChange="document.form1.submit()">
			<option value="desc" <?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>
            </form>
			</td>
			<td  valign="middle" width="45%" align="right">
			   Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?>
			</td>
			</tr>
			</table>

			<form id="form2" name="form2" method="post" action="">
            <table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<th width="60%">News</th>
				<th width="17%">Date</th>
				<th width="10%">Status</th>
				<th width="12%">Actions</th>
			</tr>
             <?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['news_id']; ?>]" id="id_arr[<?php echo $row['news_id']; ?>]" value="<?php echo $row['news_id']; ?>" /></td>
					<td align="left" valign="middle"><a href="news_details.php?id=<?php echo $row['news_id']; ?>"><?php echo $row["news_title"]; ?></a></td>
					<td align="center" valign="middle" nowrap="nowrap"><?php echo $row['news_date']; ?></td>
					<td align="center" valign="middle" nowrap="nowrap">
						<?php if ($row['status'] == "inactive") echo "<span class='inactive_s'>".$row['status']."</span>"; else echo "<span class='active_s'>".$row['status']."</span>"; ?>
					</td>
					<td align="center" valign="middle" nowrap="nowrap">
						<a href="news_details.php?id=<?php echo $row['news_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="news_edit.php?id=<?php echo $row['news_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="news.php?id=<?php echo $row['news_id']; ?>&action=delete" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
				<td style="border-top: 1px solid #F5F5F5" colspan="5" align="left">
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="submit" class="btn btn-danger" name="delete" id="GoButton1" value="Delete Selected" disabled="disabled" />
				</td>
				</tr>
				  <tr>
				  <td align="center" colspan="5">
					<?php echo ShowPagination("news",$results_per_page,"news.php?&author=$author_id&column=$rrorder&order=$rorder&"); ?>
				  </td>
				  </tr>
            </table>
			</form>		

		</table>

        <?php }else{ ?>
					<div class="alert alert-info">There are no news at this time.</div>
        <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>