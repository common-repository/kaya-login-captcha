<?php

/**
 * Kaya Login Captcha - Database Functions.
 * Functions for the plugin database management.
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

/**
 * Setup default settings on plugin activation.
 */
if (!function_exists('wpklc_database_installSetup'))
{
	function wpklc_database_installSetup()
	{
		if (!current_user_can('manage_options'))
		{
			wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
		}

		if (!get_option(WPKLC_LOGIN_CAPTCHA_SETTINGS_DB))
		{
			// loading localisation before the first setup.
			wpklc_plugin_loadLocalisation();
			// set default settings
			wpklc_database_defaultValues();
		}

		// setup default settings for multisite
		if (is_multisite())
		{
			$subSites = get_sites();
			foreach ($subSites as $i_site)
			{
				switch_to_blog($i_site->blog_id);
				if (!get_option(WPKLC_LOGIN_CAPTCHA_SETTINGS_DB))
				{
					// loading localisation before the setup.
					wpklc_plugin_loadLocalisation();
					// set default settings
					wpklc_database_defaultValues();
				}
				restore_current_blog();
			}
		}
	}
}

/**
 * Setup default settings on new site creation in multisite.
 *
 * @param WP_Site $new_site New site object.
 */
if (!function_exists('wpklc_database_installSetupNewSite'))
{
	function wpklc_database_installSetupNewSite($new_site)
	{
		if (!current_user_can('manage_options'))
		{
			wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
		}

		// setup default settings for multisite
		if (is_multisite())
		{
			switch_to_blog($new_site->id);
			if (!get_option(WPKLC_LOGIN_CAPTCHA_SETTINGS_DB))
			{
				// loading localisation before the setup.
				wpklc_plugin_loadLocalisation();
				// set default settings
				wpklc_database_defaultValues();
			}
			restore_current_blog();
		}
	}
}

/**
 * Set default database values.
 */
if (!function_exists('wpklc_database_defaultValues'))
{
	function wpklc_database_defaultValues()
	{
		if (!current_user_can('manage_options'))
		{
			wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
		}

		require_once(WPKLC_PLUGIN_PATH . 'lib/class.crud_login_captcha.php');
		$kayaLoginCaptcha = new WPKLC_login_captcha('new');
	}
}

/**
 * Adds a captcha request and delete old ones
 *
 * @param string $requestID
 * @param string $requestCode
 *
 * @return bool
 */
if (!function_exists('wpklc_database_addRequest'))
{
	function wpklc_database_addRequest($requestID, $requestCode)
	{
		if (empty($requestID) || empty($requestCode))
		{
			return false;
		}

		// get all captcha requests
		$captchaRequests = get_option(WPKLC_LOGIN_CAPTCHA_REQUESTS_DB, array());
		// set request expiration time to 600s in normal mode or 1s if spam suspected to prevent database overflow
		$expirationTime = ((count($captchaRequests) < 150) ? 600 : 1);

		// delete old requests
		$cleanedRequests = array();
		foreach ($captchaRequests as $i_request)
		{
			if ($i_request['request_time'] > time() - $expirationTime)
			{
				array_push($cleanedRequests, $i_request);
			}
		}

		// add the request to the actual list
		array_push($cleanedRequests, array(
			'request_id'	=> sanitize_key($requestID),
			'request_code'	=> sanitize_text_field($requestCode),
			'request_time'	=> time()
		));
		// save requests
		return update_option(WPKLC_LOGIN_CAPTCHA_REQUESTS_DB, $cleanedRequests, false);
	}
}

/**
 * Retrieve a login request
 *
 * @param string $requestID
 *
 * @return null|array
 */
if (!function_exists('wpklc_database_getRequest'))
{
	function wpklc_database_getRequest($requestID)
	{
		// get all captcha requests
		$captchaRequests = get_option(WPKLC_LOGIN_CAPTCHA_REQUESTS_DB, array());

		// search for the selected request
		$requestResult = null;
		$requestID = sanitize_key($requestID);
		foreach ($captchaRequests as $i_request)
		{
			if ($i_request['request_id'] === $requestID)
			{
				$requestResult = $i_request;
				break;
			}
		}

		return $requestResult;
	}
}

/**
 * Delete a login request
 *
 * @param string $requestID
 */
if (!function_exists('wpklc_database_deleteRequest'))
{
	function wpklc_database_deleteRequest($requestID)
	{
		// get all captcha requests
		$captchaRequests = get_option(WPKLC_LOGIN_CAPTCHA_REQUESTS_DB, array());

		// delete the selected request
		$cleanedRequests = array();
		foreach ($captchaRequests as $i_request)
		{
			$requestID = sanitize_key($requestID);
			if ($i_request['request_id'] !== $requestID)
			{
				array_push($cleanedRequests, $i_request);
			}
		}
		// save requests
		update_option(WPKLC_LOGIN_CAPTCHA_REQUESTS_DB, $cleanedRequests, false);
	}
}

/**
 * Increases the captcha statistics number in options
 * Count passed and blocked requests sorted by month.
 *
 * @param string $form Form of the request (login, lost_password or register)
 * @param string $name Type of the statistics (passed or blocked)
 */
if (!function_exists('wpklc_database_increaseStatistics'))
{
	function wpklc_database_increaseStatistics($form, $name)
	{
		// select the option by form requested
		$form = sanitize_key($form);
		if ('login' == $form)
		{
			$databaseOption = WPKLC_LOGIN_CAPTCHA_STATS_LOGIN_DB;
		}
		elseif ('lost_password' == $form)
		{
			$databaseOption = WPKLC_LOGIN_CAPTCHA_STATS_lOSTPASS_DB;
		}
		elseif ('register' == $form)
		{
			$databaseOption = WPKLC_LOGIN_CAPTCHA_STATS_REGISTER_DB;
		}
		else
		{
			return false;
		}

		// get all captcha statistics
		$captchaStatistics = get_option($databaseOption, array());
		if (empty($captchaStatistics[date('Y')]))
		{
			$captchaStatistics[date('Y')] = array();
		}
		if (empty($captchaStatistics[date('Y')][date('n')]))
		{
			$captchaStatistics[date('Y')][date('n')] = array('passed' => 0, 'blocked' => 0);
		}

		// increase the stats
		$name = sanitize_key($name);
		$captchaStatistics[date('Y')][date('n')][$name]++;

		update_option($databaseOption, $captchaStatistics, false);
	}
}

/**
 * Get all the captcha statistics
 *
 * @return array
 */
if (!function_exists('wpklc_database_getStatistics'))
{
	function wpklc_database_getStatistics()
	{
		$captchaStatistics = array();
		// get all captcha statistics
		$captchaStatistics['login']			= get_option(WPKLC_LOGIN_CAPTCHA_STATS_LOGIN_DB, array());
		$captchaStatistics['lost_password']	= get_option(WPKLC_LOGIN_CAPTCHA_STATS_lOSTPASS_DB, array());
		$captchaStatistics['register']		= get_option(WPKLC_LOGIN_CAPTCHA_STATS_REGISTER_DB, array());

		return $captchaStatistics;
	}
}

/**
 * Reset all the captcha statistics
 *
 * @return bool
 */
if (!function_exists('wpklc_database_resetStatistics'))
{
	function wpklc_database_resetStatistics()
	{
		$deleted = array();
		// reset all captcha statistics
		$deleted[] = update_option(WPKLC_LOGIN_CAPTCHA_STATS_LOGIN_DB, array(), false);
		$deleted[] = update_option(WPKLC_LOGIN_CAPTCHA_STATS_lOSTPASS_DB, array(), false);
		$deleted[] = update_option(WPKLC_LOGIN_CAPTCHA_STATS_REGISTER_DB, array(), false);

		return in_array(true, $deleted);
	}
}
