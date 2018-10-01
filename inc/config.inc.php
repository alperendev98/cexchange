<?php
/*******************************************************************\
 * Exchangerix v2.0
 * http://www.exchangerix.com
 *
 * Copyright (c) 2018 Exchangerix Software. All rights reserved.
 * ---------- Exchangerix IS NOT FREE SOFTWARE ----------
\*******************************************************************/

	// Error Reporting
	//@error_reporting(0);

	date_default_timezone_set(SITE_TIMEZONE); // America/New_York

	/// MySQL Settings ///
	define('DB_NAME', 'exchangerix');	// MySQL database name
	define('DB_USER', 'jonedwards');			// MySQL database user
	define('DB_PASSWORD', 'NoPass007');		// MySQL database password
	define('DB_HOST', 'localhost');				// MySQL database host name (in most cases, it's localhost)


	define("Exchangerix", true);
	define('PUBLIC_HTML_PATH', $_SERVER['DOCUMENT_ROOT']);
	define('IMAGES_PATH', $_SERVER['DOCUMENT_ROOT']."/img/");
	define('DOCS_ROOT', $_SERVER['DOCUMENT_ROOT']);
	define('CBengine_ROOT', dirname(__FILE__) . '/');
	define('CBengine_PAGE', true);

	require_once(CBengine_ROOT."db.inc.php");
	require_once(CBengine_ROOT."functions.inc.php");

	if (!defined('is_Setup'))
	{
		require_once(CBengine_ROOT."siteconfig.inc.php");
		require_once(CBengine_ROOT."timezone.inc.php");

		// setup time zone
		if (in_array(SITE_TIMEZONE, $timezone))
		{
			date_default_timezone_set(SITE_TIMEZONE);
		}

		$lang = $_COOKIE['site_lang'];

		if (MULTILINGUAL !=0 && !empty($lang) && file_exists(DOCS_ROOT."/language/".$lang.".inc.php"))
		{
			define('USER_LANGUAGE', $lang);
			require_once(DOCS_ROOT."/language/".$lang.".inc.php");
		}
		else
		{
			define('USER_LANGUAGE', SITE_LANGUAGE);
			require_once(DOCS_ROOT."/language/".SITE_LANGUAGE.".inc.php");
		}

		// maintenance mode //
		if (SITE_MODE == 'maintenance' && !$admin_panel && !isset($_SESSION['adm']['id']) && !isset($_SESSION['operator']['id']))
		{
			require_once(DOCS_ROOT."/maintenance.php");
			die();
		}
	}

?>
