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
	require_once("./inc/ce.inc.php");

	$cpage = 33;

	// check moderators
	if (!isSuperAdmin())
	{
		$check_result = smart_mysql_query("SELECT * FROM exchangerix_users WHERE user_id='".(int)$_SESSION['adm']['id']."' AND status='active' LIMIT 1");
		if (mysqli_num_rows($check_result) == 0)
		{
			header ("Location: logout.php");
			exit();
		}
	}

	// check permissions
	if (!@in_array($cpage, $_SESSION['adm']['pages']))
	{
		exit();
	}

	$today = date("Y-m-d");
	$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d") - 1, date("Y")));

	$title = "Admin Dashboard";
	require_once ("inc/header.inc.php");

?>
	<script type="text/javascript" src="js/jquery.min.js"></script>

	<h2><i class="fa fa-bar-chart" aria-hidden="true"></i> Reports</h2>


		<form id="form1" name="form1" method="get" action="">
		<table style="background:#F9F9F9" align="center" width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr>
		<td colspan="2" valign="middle" align="center">
			<div class="admin_filter" id="admin_filter" style="background: #F7F7F7; border-radius: 5px; padding: 8px;">
				Send Direction: <select name="from_filter" id="from_filter" onChange="document.form1.submit()" class="selectpicker show-menu-arrow show-tick form-control" data-width="fit">
				<option value="">------</option>
					<?php
						$sql_curr_send = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE allow_send='1' AND status='active' ORDER BY currency_name ASC"); 
						while ($row_curr_send = mysqli_fetch_array($sql_curr_send))
						{
							if ($from_filter == $row_curr_send['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
							echo "<option value=\"".$row_curr_send['currency_id']."\"".$selected.">".$row_curr_send['currency_name'];
							if ($row_curr_send['is_crypto'] != 1) echo " ".$row_curr_send['currency_code'];
							echo "</option>";
						}
					?>
				</select>
				&nbsp; Receive Direction: <select name="to_filter" id="to_filter" onChange="document.form1.submit()" class="selectpicker show-menu-arrow show-tick form-control" data-width="fit">
					<option value="">------</option>
					<?php
						$sql_curr_receive = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE allow_receive='1' AND status='active' ORDER BY currency_name ASC");
						while ($row_curr_receive = mysqli_fetch_array($sql_curr_receive))
						{
							if ($to_filter == $row_curr_receive['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
							echo "<option value=\"".$row_curr_receive['currency_id']."\"".$selected.">".$row_curr_receive['currency_name'];
							if ($row_curr_receive['is_crypto'] != 1) echo " ".$row_curr_receive['currency_code'];
							echo "</option>";							
						}
					?>
				</select>	<br>				
				<?php
					/*
					$sql_retailers = smart_mysql_query("SELECT * FROM exchangerix_currencies WHERE ORDER BY created ASC");
					if (mysqli_num_rows($sql_retailers) > 0)
					{
				?>
				<select name="store" id="store" style="width: 150px;" class="form-control">
				<option value="">--- all currencies ---</option>
				<?php
						while ($row_retailers = mysqli_fetch_array($sql_retailers))
						{
							if ($currency == $row_retailers['currency_id']) $selected = " selected=\"selected\""; else $selected = "";
							echo "<option value=\"".$row_retailers['currency_id']."\"".$selected.">".$row_retailers['currency_name']."</option>";
						}
				?>
				</select>&nbsp;
				<?php }*/ ?>
				Date: <input type="text" name="start_date" id="datetimepicker1" value="<?php echo $start_date; ?>" size="18" class="form-control" /> - <input type="text" name="end_date" id="datetimepicker2" value="<?php echo $end_date; ?>" size="18" class="form-control" />
				<input type="hidden" name="action" value="filter" />
				<input type="submit" class="btn btn-success" name="search" value="Show Report" />
				<?php if (isset($_GET['search'])) { ?><input type="hidden" name="search" value="search" /><?php } ?>
				<?php if ((isset($filter) && $filter != "") || $store || $start_date || $end_date) { ?><a title="Cancel Search" href="reports.php"><img align="absmiddle" src="images/icons/delete_filter.png" border="0" alt="Cancel Filter" /></a><?php } ?>
			</div>
		</td>
		</tr>
		<tr>
		<td  width="35%" valign="middle" align="left">
           <!--Sort by: 
          <select name="column" id="column" class="form-control" onChange="document.form1.submit()">
			<option value="ids" <?php if ($_GET['column'] == "ids") echo "selected"; ?>>Date</option>
			<option value="user_id" <?php if ($_GET['column'] == "user_id") echo "selected"; ?>>User ID</option>
			<option value="username" <?php if ($_GET['column'] == "username") echo "selected"; ?>>Member Name</option>
			<option value="retailer" <?php if ($_GET['column'] == "retailer") echo "selected"; ?>>Store</option>
			<option value="order_total" <?php if ($_GET['column'] == "order_total") echo "selected"; ?>>Order Amount</option>
			<option value="status" <?php if ($_GET['column'] == "status") echo "selected"; ?>>Status</option>
          </select>
          <select name="order" id="order" class="form-control" onChange="document.form1.submit()">
			<option value="desc" <?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
			<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
          </select>--->
		</td>
		</tr>
		</table>
		</form>
		<hr>

	

	<?php
			if (isset($_GET['stats_period']) && is_numeric($_GET['stats_period']) && $_GET['stats_period'] > 0)
				$stats_period = (int)$_GET['stats_period'];
			else
				$stats_period = 30;
	?>

	<h3 class="pull-left">Users Stats</h3>
	<div class="pull-right">
	<form id="form5" name="form5" method="get" action="">
	Period: <select name="stats_period" onChange="document.form5.submit()" class="selectpicker">
		<option value="7" <?php if (@$stats_period == 7) echo "selected='selected'"; ?>>last 7 days</option>
		<option value="30" <?php if (@$stats_period == 30) echo "selected='selected'"; ?>>last 30 days</option>
		<option value="90" <?php if (@$stats_period == 90) echo "selected='selected'"; ?>>last 90 days</option>
		<option value="180" <?php if (@$stats_period == 180) echo "selected='selected'"; ?>>last 180 days</option>
	</select>
	</form>
	</div>
	<div id="statstchart" style="height: 270px;"></div>
	<br/>
	<script src="js/raphael-min.js" language="javascript"></script>
	<script src="js/morris.min.js" language="javascript"></script>

	<?php
			for ($i=0; $i<=$stats_period; $i++)
			{
				$d = date("d M", strtotime('-'. $i .' days'));
				$days[$d] = 0;			
			}

			$chart_data = array_reverse($days);

			$chart_result = smart_mysql_query("SELECT DATE_FORMAT(created, '%d %b') as user_date, COUNT(*) as users FROM exchangerix_users WHERE created > created - ".$stats_period." GROUP BY YEAR(created), MONTH(created), DAY(created)");
			
			if (mysqli_num_rows($chart_result) > 0)
			{
				while ($chart_row = mysqli_fetch_array($chart_result))
				{
					if (array_key_exists($chart_row['user_date'], $chart_data))
						$chart_data[$chart_row['user_date']] = $chart_row['users'];
				}
			}
	?>

	<script language="javascript" type="text/javascript">
	new Morris.Bar({
	  element: 'statstchart',
	  data: [ <?php foreach ($chart_data as $k => $v) { echo "{ user_date: '$k', value: $v },"; } ?> ],
	  //{ y: '2017', a: 100, b: 90 },
	  xkey: 'user_date',
	  ykeys: ['value'],
	  labels: ['Users'],
	  barColors: ['#799cd8'],
	  barRatio: 0.4,
	  hideHover: 'auto'
	});
	</script>


	<?php
			if (isset($_GET['stats_period2']) && is_numeric($_GET['stats_period2']) && $_GET['stats_period2'] > 0)
				$stats_period2 = (int)$_GET['stats_period2'];
			else
				$stats_period2 = 22222;
	?>
	<br/><br/>
	<h3 class="pull-left"><i class="fa fa-line-chart"></i> <?php echo (@$_GET['show_for'] == "signups") ? "Sign Ups" : "Exchanges"; ?> Stats</h3>
	<div class="pull-right">
	<form id="form6" name="form6" method="get" action="#cstats">
	Show for: <select name="show_for" onChange="document.form6.submit()" class="selectpicker">
		<option value="exchanges" <?php if (@$_GET['show_for'] == "exchanges") echo "selected='selected'"; ?>>exchanges</option>
		<option value="signups" <?php if (@$_GET['show_for'] == "signups") echo "selected='selected'"; ?>>sign ups</option>
	</select>
	&nbsp;&nbsp;
	Period: <select name="stats_period2" onChange="document.form6.submit()" class="selectpicker">
		<option value="7" <?php if (@$stats_period2 == 7) echo "selected='selected'"; ?>>last 7 days</option>
		<option value="30" <?php if (@$stats_period2 == 30) echo "selected='selected'"; ?>>last 30 days</option>
		<option value="90" <?php if (@$stats_period2 == 90) echo "selected='selected'"; ?>>last 90 days</option>
		<option value="180" <?php if (@$stats_period2 == 180) echo "selected='selected'"; ?>>last 180 days</option>
		<option value="22222" <?php if (@$stats_period2 == 22222) echo "selected='selected'"; ?>>past months</option>
		<option value="11111" <?php if (@$stats_period2 == 11111) echo "selected='selected'"; ?>>past years</option>
	</select>
	</form>
	</div>
	<div id="statstchart2" style="height: 270px;"></div>
	<a name="cstats"></a>
	<br/>
	<?php
			unset($chart_data2);

			if ($stats_period2 == 11111)
			{
				for ($i=0; $i<=7; $i++)
				{
					$d = date("Y", strtotime('-'. $i .' year'));
					$years[$d] = 0;			
				}

				$eee = "YEAR(created)";
				$www = "%Y";
				$vvv = "";

				$chart_data2 = $years;
			}
			elseif ($stats_period2 == 22222)
			{
				for ($i=0; $i<=12; $i++)
				{
					$d = date("M Y", strtotime('-'. $i .' month'));
					$months[$d] = 0;			
				}

				$eee = "YEAR(created), MONTH(created)";
				$www = "%b %Y";
				$vvv = "";

				$chart_data2 = array_reverse($months);
			}
			else
			{
				for ($i=0; $i<=$stats_period2; $i++)
				{
					$d = date("d M", strtotime('-'. $i .' days'));
					$days[$d] = 0;			
				}
				
				$eee = "YEAR(created), MONTH(created), DAY(created)";
				$www = "%d %b";
				$vvv = "AND created > created - ".$stats_period2."";

				$chart_data2 = array_reverse($days);
			}

			if (isset($_GET['show_for']) && $_GET['show_for'] == "signups")
				$chart_result2 = smart_mysql_query("SELECT DATE_FORMAT(created, '".$www."') as stats_date, COUNT(*) as stats_amount FROM exchangerix_users WHERE 1=1 ".$vvv." GROUP BY ".$eee);
			else
				$chart_result2 = smart_mysql_query("SELECT DATE_FORMAT(created, '".$www."') as stats_date, SUM(amount) as stats_amount FROM exchangerix_exchanges WHERE status='confirmed' ".$vvv." GROUP BY ".$eee);
			
			if (mysqli_num_rows($chart_result2) > 0)
			{
				while ($chart_row2 = mysqli_fetch_array($chart_result2))
				{
					if (array_key_exists($chart_row2['stats_date'], $chart_data2))
						$chart_data2[$chart_row2['stats_date']] = $chart_row2['stats_amount'];
				}
			}
	?>

	<script language="javascript" type="text/javascript">
	new Morris.Bar({
	  element: 'statstchart2',
	  data: [ <?php foreach ($chart_data2 as $k => $v) { echo "{ statsdate: '$k', value: $v },"; } ?> ],
	  xkey: 'statsdate',
	  ykeys: ['value'],
	<?php if (isset($_GET['show_for']) && $_GET['show_for'] == "signups") { ?>
	  labels: ['Sign Ups'],
	  barColors: ['#999999'],
	<?php }else{ ?>
	  labels: ['Exchanges <?php echo SITE_CURRENCY; ?>'],
	  barColors: ['#8ec120'],
	<?php } ?>
	  barRatio: 0.4,
	  hideHover: 'auto'
	});
	</script>

	
	</div>
	</div>


<?php require_once ("inc/footer.inc.php"); ?>