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

	$cpage = 14;

	CheckAdminPermissions($cpage);

	// delete ////////////////////////////////////////
	if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['action'] == "delete")
	{
		$reviewid	= (int)$_GET['id'];
		DeleteReview($reviewid);
		header("Location: reviews.php?msg=deleted");
		exit();
	}

	// results per page
	if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show'] > 0)
		$results_per_page = (int)$_GET['show'];
	else
		$results_per_page = 10;


		// Approve reviews //
		if (isset($_POST['approve']) && $_POST['approve'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$rid = (int)$v;
					smart_mysql_query("UPDATE exchangerix_reviews SET status='active' WHERE review_id='$rid'");
				}

				header("Location: reviews.php?msg=approved");
				exit();
			}	
		}


		// Delete reviews //
		if (isset($_POST['delete']) && $_POST['delete'] != "")
		{
			$ids_arr	= array();
			$ids_arr	= $_POST['id_arr'];

			if (count($ids_arr) > 0)
			{
				foreach ($ids_arr as $v)
				{
					$reviewid = (int)$v;
					DeleteReview($reviewid);
				}

				header("Location: reviews.php?msg=deleted");
				exit();
			}
		}

		$where = "1=1";

		////////////////// filter  //////////////////////
			if (isset($_GET['column']) && $_GET['column'] != "")
			{
				switch ($_GET['column'])
				{
					case "user_id": $rrorder = "user_id"; break;
					case "added": $rrorder = "added"; break;
					case "exchange_id": $rrorder = "exchange_id"; break;
					case "rating": $rrorder = "rating"; break;
					case "review": $rrorder = "review"; break;
					case "status": $rrorder = "status"; break;
					default: $rrorder = "added"; break;
				}
			}
			else
			{
				$rrorder = "added";
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

		if (isset($_GET['store']) && is_numeric($_GET['store']))
		{
			$store = (int)$_GET['store'];
			$where .= " AND exchange_id='$store' ";
			$title2 = GetStoreName($store);
		}

		if (isset($_GET['user']) && is_numeric($_GET['user']))
		{
			$user = (int)$_GET['user'];
			$where .= " AND user_id='$user' ";
			$title2 = GetUsername($user)."'s";
		}

		$query = "SELECT *, DATE_FORMAT(added, '".DATE_FORMAT." %h:%i %p') AS date_added FROM exchangerix_reviews WHERE $where ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
		
		$result = smart_mysql_query($query);
		$total_on_page = mysqli_num_rows($result);

		$query2 = "SELECT * FROM exchangerix_reviews WHERE ".$where;
		$result2 = smart_mysql_query($query2);
        $total = mysqli_num_rows($result2);

		$cc = 0;

		$title = $title2." Testimonials";
		require_once ("inc/header.inc.php");

?>

       <div id="addnew">
			<a class="addnew" href="review_add.php">Add Testimonial</a>
       </div>

		<h2><i class="fa fa-comments-o" aria-hidden="true"></i> <?php echo $title2; ?> Testimonials <?php if ($total > 0) { ?><sup class="badge" style="background: #73b9d1"><?php echo number_format($total); ?></sup><?php } ?></h2>
		<!-- //dev admin reply -->		

        <?php if ($total > 0) { ?>


			<?php if (isset($_GET['msg']) && $_GET['msg'] != "") { ?>
			<div class="alert alert-success">
				<?php

					switch ($_GET['msg'])
					{
						case "added": echo "Testimonial has been successfully added"; break;
						case "approved": echo "Testimonials have been successfully approved"; break;
						case "updated": echo "Testimonial has been successfully edited"; break;
						case "deleted": echo "Testimonial has been successfully deleted"; break;
					}

				?>
			</div>
			<?php } ?>


		<form id="form1" name="form1" method="get" action="">
		<div class="row" style="background:#F9F9F9; margin: 10px 0; padding: 7px 0;">
		<div class="col-md-8" style="white-space: nowrap">
           Sort by: 
          <select name="column" id="column" class="form-control" onChange="document.form1.submit()">
			<option value="added" <?php if ($_GET['column'] == "added") echo "selected"; ?>>Newest</option>
			<option value="user_id" <?php if ($_GET['column'] == "user_id") echo "selected"; ?>>Member</option>
			<option value="exchange_id" <?php if ($_GET['column'] == "exchange_id") echo "selected"; ?>>Exchange Direction</option>
			<option value="rating" <?php if ($_GET['column'] == "rating") echo "selected"; ?>>Rating</option>
			<option value="review" <?php if ($_GET['column'] == "review") echo "selected"; ?>>Review</option>
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
          </select>
          <select name="order" id="order" class="form-control" onChange="document.form1.submit()">
			<option value="desc" <?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>
		  &nbsp;&nbsp;Results:  
          <select name="show" id="show" class="form-control" onChange="document.form1.submit()">
			<option value="10" <?php if ($_GET['show'] == "10") echo "selected"; ?>>10</option>
			<option value="50" <?php if ($_GET['show'] == "50") echo "selected"; ?>>50</option>
			<option value="100" <?php if ($_GET['show'] == "100") echo "selected"; ?>>100</option>
			<option value="111111111" <?php if ($_GET['show'] == "111111111") echo "selected"; ?>>ALL</option>
          </select>
			<?php if ($user) { ?><input type="hidden" name="user" value="<?php echo $user; ?>" /><?php } ?>
			<?php if ($store) { ?><input type="hidden" name="store" value="<?php echo $store; ?>" /><?php } ?>
		</div>
		<div class="col-md-4 text-right" style="white-space: nowrap; padding-top: 8px;">
			   Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_on_page, $total); ?> of <?php echo $total; ?>
		</div>
		</div>
			</form>

			<form id="form2" name="form2" method="post" action="">
			<table align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<th width="3%"><center><input type="checkbox" name="selectAll" onclick="checkAll();" class="checkbox" /></center></th>
				<!--<th width="22%">Exchange</th>-->
				<th width="42%">Testimonial</th>
				<th width="11%">Status</th>
				<th width="10%">Actions</th>
			</tr>
			<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>				  
				  <tr class="<?php if (($cc%2) == 0) echo "even"; else echo "odd"; ?>">
					<td align="center" valign="middle" nowrap="nowrap"><input type="checkbox" class="checkbox" name="id_arr[<?php echo $row['review_id']; ?>]" id="id_arr[<?php echo $row['review_id']; ?>]" value="<?php echo $row['review_id']; ?>" /></td>
					<!--<td align="left" valign="middle">
						<a href="exchange_details.php?id=<?php echo $row['exchange_id']; ?>"></a>
						<i class="fa fa-long-arrow-right" aria-hidden="true"></i>
					</td>-->
					<td align="left" valign="middle" class="row_title" style="padding: 10px">
						<div style="margin: 5px 0;">
							<?php if ($row['user_id'] > 0) { ?>
								<i class="fa fa-user-circle" aria-hidden="true"></i> <a href="user_details.php?id=<?php echo $row['user_id']; ?>"><?php echo GetUsername($row['user_id']); ?></a>
							<?php }else{ ?>
								<i class="fa fa-user-o" aria-hidden="true"></i> <?php echo $row['author']; ?><!--Visitor-->
							<?php } ?>							
							<div style="float: right; font-size: 12px; color: #BABABA;"><i class="fa fa-clock-o"></i> <?php echo $row['date_added']; ?></div>
						</div>
						<?php if ($row['exchange_id'] > 0) { ?>
							<span style="color: #BBB; font-size: 13px;"><i class="fa fa-refresh"></i> exchange <a href="exchange_details.php?id=<?php echo $row['exchange_id']; ?>">#<?php echo $row['exchange_id']; ?></a></span><br>
						<?php } ?>
						<?php for ($i=0; $i<5;$i++) { ?><i class="fa fa-star fa-lg" style="color: <?php echo ($i<$row['rating']) ? "#89b601" : "#CCC"; ?>"></i> <?php } ?> <!-- x of 5 -->
						<br/><b><?php echo $row['review_title']; ?></b>
						<p style="text-align: justify"><?php if (strlen($row['review']) > 350) echo substr($row['review'], 0, 350)."..."; else echo $row['review']; ?></p>
					</td>
					<td align="center" valign="middle" style="padding-left: 10px">
					<?php
						switch ($row['status'])
						{
							case "pending": echo "<span class='label label-warning'>pending</span>"; break;
							case "active": echo "<span class='label label-success'>".$row['status']."</span>"; break;
							case "inactive": echo "<span class='label label-default'>".$row['status']."</span>"; break;
							default: echo "<span class='label label-default'>".$row['status']."</span>"; break;
						}
					?>
					</td>
					<td align="center" valign="middle" nowrap="nowrap">
						<a href="review_details.php?id=<?php echo $row['review_id']; ?>" title="View"><img src="images/view.png" border="0" alt="View" /></a>
						<a href="review_edit.php?id=<?php echo $row['review_id']; ?>" title="Edit"><img src="images/edit.png" border="0" alt="Edit" /></a>
						<a href="#" onclick="if (confirm('Are you sure you really want to delete this testimonial?') )location.href='reviews.php?id=<?php echo $row['review_id']; ?>&action=delete'" title="Delete"><img src="images/delete.png" border="0" alt="Delete" /></a>
					</td>
				  </tr>
			<?php } ?>
				<tr>
				<td style="border-top: 1px solid #F5F5F5" colspan="4" align="left">
					<input type="hidden" name="column" value="<?php echo $rrorder; ?>" />
					<input type="hidden" name="order" value="<?php echo $rorder; ?>" />
					<input type="hidden" name="page" value="<?php echo $page; ?>" />
					<input type="submit" class="btn btn-success" name="approve" id="GoButton1" value="Approve Selected" disabled="disabled" />
					<input type="submit" class="btn btn-danger" name="delete" id="GoButton2" value="Delete Selected" disabled="disabled" />
				</td>
				</tr>
            </table>
			</form>
					<?php
							$params = "";

							if (@$_GET['column'])	$params .= "column=".$_GET['column']."&";
							if (@$_GET['order'])	$params .= "order=".$_GET['order']."&";
							if (@$user)				$params .= "user=$user&";
							if (@$store)			$params .= "store=$store&";
							if (@$_GET['show'])		$params .= "show=$results_per_page&";
							if (@$_GET['page'])		$params .= "page=$page&";

							echo ShowPagination("reviews",$results_per_page,"reviews.php?".$params, "WHERE ".$where);
					?>

          <?php }else{ ?>
				<div class="alert alert-info">There are no testimonials at this time.</div>	
				<?php if ($_GET['store'] || $_GET['user']) { ?>
					<p align="center"><input type="button" class="btn btn-default" name="cancel" value="Go Back" onclick="history.go(-1);return false;" /></p>
				<?php } ?>
          <?php } ?>

<?php require_once ("inc/footer.inc.php"); ?>