<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ------------ Exchangerix IS NOT FREE SOFTWARE --------------
\*******************************************************************/



/**
 * Returns total of pending orders
 * @return	string	total
*/

if (!function_exists('GetPendingETotal')) {
	function GetPendingETotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_exchanges WHERE status='pending'");
		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}


/**
 * Returns total of member's requested money
 * @return	string	total
*/

if (!function_exists('GetRequestsTotal')) {
	function GetRequestsTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_transactions WHERE status='request'");
		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}


/**
 * Returns total of member's reserves request
 * @return	string	total
*/

if (!function_exists('GetVerificationRequestsTotal')) {
	function GetVerificationRequestsTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_users WHERE length(verified_document) > 10 OR length(verified_address) > 10");
		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}


/**
 * Returns total of member's reserves request
 * @return	string	total
*/

if (!function_exists('GetReserveRequestsTotal')) {
	function GetReserveRequestsTotal()
	{
		$result = smart_mysql_query("SELECT COUNT(*) AS total FROM exchangerix_reserve_requests WHERE status='pending'");
		$row = mysqli_fetch_array($result);
		return $row['total'];
	}
}


if (!function_exists('NetworkTotalRetailers')) {
function NetworkTotalRetailers ($network_id)
{
	$result = smart_mysql_query("SELECT COUNT(retailer_id) as total FROM exchangerix_retailers WHERE network_id='$network_id'");
	$row = mysqli_fetch_array($result);
	return $row['total'];
}
}


if (!function_exists('ApproveUser')) {
function ApproveUser ($user_id)
{
	$userid = (int)$user_id;
	smart_mysql_query("UPDATE exchangerix_users SET status='active' WHERE user_id='$userid'");
}
}


if (!function_exists('DeleteUser')) {
function DeleteUser ($user_id)
{
	$userid = (int)$user_id;
	smart_mysql_query("DELETE FROM exchangerix_users WHERE user_id='$userid'");
	smart_mysql_query("DELETE FROM exchangerix_transactions WHERE user_id='$userid'");
	smart_mysql_query("DELETE FROM exchangerix_exchanges WHERE user_id='$userid'");
	smart_mysql_query("DELETE FROM exchangerix_reviews WHERE user_id='$userid'");
}
}

if (!function_exists('DeleteCountry')) {
function DeleteCountry ($country_id)
{
	$countryid = (int)$country_id;
	smart_mysql_query("DELETE FROM exchangerix_countries WHERE country_id='$countryid'");
}
}


if (!function_exists('DeleteNews')) {
function DeleteNews ($news_id)
{
	$newsid = (int)$news_id;
	smart_mysql_query("DELETE FROM exchangerix_news WHERE news_id='$newsid'");
}
}


if (!function_exists('DeleteReview')) {
function DeleteReview ($review_id)
{
	$reviewid = (int)($review_id);
	smart_mysql_query("DELETE FROM exchangerix_reviews WHERE review_id='$reviewid'");
}
}


if (!function_exists('ConfirmPayment')) {
function ConfirmPayment ($payment_id)
{
	$pid = (int)$payment_id;
	smart_mysql_query("UPDATE exchangerix_transactions SET status='confirmed' WHERE transaction_id='$pid'");
}
}


if (!function_exists('DeletePayment')) {
function DeletePayment ($payment_id)
{
	$pid = (int)$payment_id;
	smart_mysql_query("DELETE FROM exchangerix_transactions WHERE transaction_id='$pid'");
}
}


if (!function_exists('BlockUnblockUser')) {
function BlockUnblockUser ($user_id, $unblock=0)
{
	$userid = (int)$user_id;

	if ($unblock == 1)
		smart_mysql_query("UPDATE exchangerix_users SET status='active' WHERE user_id='$userid'");
	else
		smart_mysql_query("UPDATE exchangerix_users SET status='inactive' WHERE user_id='$userid'");
}
}


if (!function_exists('GetETemplateTitle')) {
	function GetETemplateTitle($template_name)
	{
		switch ($template_name)
		{
			case "signup": $template_title = "Sign Up email"; break;
			case "activate": $template_title = "Registration Confirmation email"; break;
			case "activate2": $template_title = "Account Activation email"; break;
			case "forgot_password": $template_title = "Forgot Password email"; break;
			case "invite_friend": $template_title = "Invite a Friend email"; break;
			case "cashout_paid": $template_title = "Cash Out paid email"; break;
			case "cashout_declined": $template_title = "Cash Out declined email"; break;
			case "manual_credit": $template_title = "Manual Payment email"; break;
			case "email2users": $template_title = "Email Members email"; break;
		}

		return $template_title;
	}
}


if (!function_exists('GetGatewayName')) {
function GetGatewayName($gateway_id)
{
	$sql = "SELECT gateway_name FROM exchangerix_gateways WHERE gateway_id='$gateway_id' LIMIT 1";
	$result = smart_mysql_query($sql);
	if (mysqli_num_rows($result) > 0)
	{
		$row = mysqli_fetch_array($result);
		return $row['gateway_name'];
	}
	else
	{
		//return "---";
	}
}
}


if (!function_exists('ShowReferralsTree')) {
function ShowReferralsTree($user_id)
    {
        $q = smart_mysql_query("SELECT * FROM exchangerix_users WHERE ref_id='".(int)$user_id."' ORDER BY user_id ASC"); // ORDER BY user_id ASC
        if (!mysqli_num_rows($q))
            return;
        echo '<ul>';
        while ($arr = mysqli_fetch_array($q))
        {
            echo '<li>';
            echo "<a class='user' href=\"user_details.php?id=".$arr['user_id']."\">".$arr['fname']." ".$arr['lname']."</a>"; //you can add another output there 
            ShowReferralsTree($arr['user_id']);
            echo '</li>';
        }
        echo '</ul>';
    }
}


if (!function_exists('CheckAdminPermissions')) {
function CheckAdminPermissions($cpage)
{
	if (!@in_array($cpage, $_SESSION['adm']['pages']))
	{
		header("Location: index.php");
		exit();
	}
}
}


if (!function_exists('isSuperAdmin')) {
	function isSuperAdmin()
	{
		if (!(isset($_SESSION['adm']['role']) && $_SESSION['adm']['role'] == "superadmin"))
			return false;
		else
			return true;
	}
}


if (!function_exists('PageAllowed')) {
function PageAllowed($cpage)
{
	if (@in_array($cpage, $_SESSION['adm']['pages']))
		return true;
	else
		return false;
}
}

if (!function_exists('ShowCBEInfo')) {
function ShowCBEInfo()
{
	$i = 0;
	$feed = @simplexml_load_file('http://www.exchangerix.com/rss.xml');

	if ($feed)
	{
		foreach ($feed->channel->item as $item)
		{
			if ($i == 0)  echo "<h3>Exchangerix News</h3>";
			if (++$i == 10) break;
			
			$title       = (string) $item->title;
			$link       = (string) $item->link;
			$description = (string) $item->description;
			$ndate = (string) $item->pubDate;
			
			echo '<div style="margin: 5px 0 ;">';
			echo "<small>".$ndate."</small><br/>";
			echo "<a href=\"".$link."\"><b>".$title."</b></a>";
			echo "<p>".$description."</p>";
			echo '</div>';
		}
	}
}
}

?>