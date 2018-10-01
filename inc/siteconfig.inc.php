<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	$setts_sql = "SELECT * FROM exchangerix_settings";
	$setts_result = smart_mysql_query($setts_sql);

	unset($settings);
	$settings = array();

	while ($setts_row = mysqli_fetch_array($setts_result))
	{
		$settings[$setts_row['setting_key']] = $setts_row['setting_value'];
	}

	define('SITE_TITLE', $settings['website_title']);
	define('SITE_MAIL', $settings['website_email']);
	define('EMAIL_FROM_NAME', $settings['email_from_name']);
	define('NOREPLY_MAIL', $settings['noreply_email']);
	define('SITE_ALERTS_MAIL', $settings['alerts_email']);
	define('SITE_URL', $settings['website_url']);
	define('SITE_MODE', $settings['website_mode']);
	define('SITE_HOME_TITLE', $settings['website_home_title']);
	define('SITE_LANGUAGE', $settings['website_language']);
	define('MULTILINGUAL', $settings['multilingual']);
	define('SITE_TIMEZONE', $settings['website_timezone']);
	define('DATE_FORMAT', $settings['website_date_format']);
	define('SITE_CURRENCY', $settings['website_currency']);
	define('SITE_CURRENCY_FORMAT', $settings['website_currency_format']);
	define('SIGNUP_CAPTCHA', $settings['signup_captcha']);
	define('EXCHANGE_CAPTCHA', $settings['exchange_captcha']);
	define('ACCOUNT_ACTIVATION', $settings['account_activation']);
	define('LOGIN_ATTEMPTS_LIMIT', $settings['login_attempts_limit']);
	define('LOGIN_ATTEMPTS', 5);
	define('ONE_REVIEW', $settings['one_review']);
	define('HOMEPAGE_REVIEWS_LIMIT', $settings['homepage_reviews_limit']);
	define('HOMEPAGE_EXCHANGES_LIMIT', $settings['homepage_exchanges_limit']);
	define('RESULTS_PER_PAGE', $settings['results_per_page']);
	define('MIN_PAYOUT_PER_TRANSACTION', $settings['min_transaction']);
	define('MIN_PAYOUT', $settings['min_payout']);
	define('CANCEL_WITHDRAWAL', $settings['cancel_withdrawal']);
	define('SIGNUP_BONUS', $settings['signup_credit']);
	define('REFERRAL_COMMISSION', $settings['referral_commission']);
	define('IMAGE_WIDTH', $settings['image_width']);
	define('IMAGE_HEIGHT', $settings['image_height']);
	define('SHOW_LANDING_PAGE', $settings['show_landing_page']);
	define('REVIEWS_APPROVE', $settings['reviews_approve']);
	define('MAX_REVIEW_LENGTH', $settings['max_review_length']);
	define('REVIEWS_PER_PAGE', $settings['reviews_per_page']);
	define('NEWS_PER_PAGE', $settings['news_per_page']);
	define('SHOW_SITE_STATS', $settings['show_site_statistics']);
	define('NEW_EXCHANGE_ALERT', $settings['email_new_exchange']);
	define('NEW_AMOUNT_REQUEST_ALERT', $settings['email_new_amount_request']); 
	define('NEW_REVIEW_ALERT', $settings['email_new_review']);
	define('NEW_TICKET_ALERT', $settings['email_new_ticket']);
	define('NEW_TICKET_REPLY_ALERT', $settings['email_new_ticket_reply']);
	define('NEW_REPORT_ALERT', $settings['email_new_report']);
	define('SMS_AMOUNT_REQUEST_ALERT', $settings['sms_new_amount_request']); 
	define('SMTP_MAIL', $settings['smtp_mail']);
	define('SMTP_PORT', $settings['smtp_port']);
	define('SMTP_HOST', $settings['smtp_host']);
	define('SMTP_USERNAME', $settings['smtp_username']);
	define('SMTP_PASSWORD', $settings['smtp_password']);
	define('SMTP_SSL', $settings['smtp_ssl']);
	define('FACEBOOK_CONNECT', $settings['facebook_connect']);
	define('FACEBOOK_APPID', $settings['facebook_appid']);
	define('FACEBOOK_SECRET', $settings['facebook_secret']);
	define('FACEBOOK_PAGE', $settings['facebook_page']);
	define('GOOGLEPLUS_PAGE', $settings['googleplus_page']);
	define('PINTEREST_PAGE', $settings['pinterest_page']);
	define('SHOW_FB_LIKEBOX', $settings['show_fb_likebox']);
	define('TWITTER_PAGE', $settings['twitter_page']);
	define('REG_SOURCES', $settings['reg_sources']);
	define('ADDTHIS_ID', $settings['addthis_id']);
	define('GOOGLE_ANALYTICS', stripslashes($settings['google_analytics']));
	define('TIMENOW', time());
	define('EMAIL_VERIFICATION', $settings['email_verification']);
	define('PHONE_VERIFICATION', $settings['phone_verification']);
	define('DOCUMENT_VERIFICATION', $settings['document_verification']);
	define('ADDRESS_VERIFICATION', $settings['address_verification']);
	define('PAYMENT_PROOF', $settings['payment_proof']);
	define('REQUIRE_LOGIN', $settings['require_login']);
	define('RESERVE_MINUTES', $settings['reserve_minutes']);
	define('OPERATOR_STATUS', $settings['operator_status']);
	define('CONTACT_PHONE', $settings['contact_phone']);
	define('CONTACT_PHONE2', $settings['contact_phone2']);
	define('CONTACT_PHONE3', $settings['contact_phone3']);
	define('SHOW_OPERATOR_HOURS', $settings['show_operator_hours']);
	define('OPERATOR_HOURS', $settings['operator_hours']);
	define('OPERATOR_TIMEZONE', $settings['operator_timezone']);
	define('CHAT_CODE', $settings['chat_code']);
	define('WHATSAPP', $settings['whatsapp']);
	define('SKYPE', $settings['skype']);
	define('TELEGRAM', $settings['telegram']);
	define('VIBER', $settings['viber']);
	define('SMS_API_KEY', $settings['sms_api_key']);
	define('SMS_API_SECRET', $settings['sms_api_secret']);
	define('ALLOWED_FILES', $settings['allowed_files']);
	define('FILES_MAX_SIZE', $settings['files_max_size']);
	
	if ($settings['update_rates_minutes'] <= 0) $settings['update_rates_minutes'] = 1; // min 1 min
	define('UPDATE_RATES_MINUTES', $settings['update_rates_minutes']);

	if (REG_SOURCES != "" && strstr(REG_SOURCES, ',')) $reg_sources = explode(",",REG_SOURCES);

	// results per page dropdown
	$results_on_page = array("5", "10", "12", "24", "25", "40", "50", "100", "111111");

	// site languages
	$languages = array();
	$languages_sql = "SELECT * FROM exchangerix_languages WHERE status='active' ORDER BY sort_order, language";
	$languages_result = smart_mysql_query($languages_sql);
	if (mysqli_num_rows($languages_result) > 0)
	{
		while ($languages_row = mysqli_fetch_array($languages_result))
		{
			$language_code = $languages_row['language_code'];
			$language_name = $languages_row['language'];
			$languages[$language_code] = $language_name;
		}
	}

?>