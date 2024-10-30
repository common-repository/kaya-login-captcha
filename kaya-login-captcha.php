<?php
/*
 * Plugin Name: Kaya Login Captcha
 * Description: Adds a simple captcha on login form, register form and lost-password form.
 * Tags: captcha, login, login security, spam, Brute force Protection
 * Author: Kaya Studio
 * Author URI:  https://kayastudio.fr
 * Donate link: http://dotkaya.org/a-propos/
 * Contributors: kayastudio
 * Requires at least: 4.6.0
 * Tested up to: 6.6
 * Stable tag: 1.0.1
 * Version: 1.0.1
 * Requires PHP: 5.3
 * Text Domain: kaya-login-captcha
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

if (!defined('WPKLC_VERSION'))							define('WPKLC_VERSION', '1.0.1');
if (!defined('WPKLC_FILE'))								define('WPKLC_FILE', plugin_basename(__FILE__));
if (!defined('WPKLC_PLUGIN_URL'))						define('WPKLC_PLUGIN_URL', plugin_dir_url(__FILE__));
if (!defined('WPKLC_PLUGIN_PATH'))						define('WPKLC_PLUGIN_PATH', plugin_dir_path(__FILE__));
if (!defined('WPKLC_TEXT_DOMAIN'))						define('WPKLC_TEXT_DOMAIN', 'kaya-login-captcha');
if (!defined('WPKLC_LOGIN_CAPTCHA_SETTINGS_DB'))		define('WPKLC_LOGIN_CAPTCHA_SETTINGS_DB', 'wpklc_kaya_login_captcha_settings');
if (!defined('WPKLC_LOGIN_CAPTCHA_REQUESTS_DB'))		define('WPKLC_LOGIN_CAPTCHA_REQUESTS_DB', 'wpklc_kaya_login_captcha_requests');
if (!defined('WPKLC_LOGIN_CAPTCHA_STATS_LOGIN_DB'))		define('WPKLC_LOGIN_CAPTCHA_STATS_LOGIN_DB', 'wpklc_kaya_login_captcha_login_statistics');
if (!defined('WPKLC_LOGIN_CAPTCHA_STATS_lOSTPASS_DB'))	define('WPKLC_LOGIN_CAPTCHA_STATS_lOSTPASS_DB', 'wpklc_kaya_login_captcha_lost_password_statistics');
if (!defined('WPKLC_LOGIN_CAPTCHA_STATS_REGISTER_DB'))	define('WPKLC_LOGIN_CAPTCHA_STATS_REGISTER_DB', 'wpklc_kaya_login_captcha_register_statistics');

// Include the main functions
require_once(WPKLC_PLUGIN_PATH . 'lib/functions.php');
// Include the login functions
require_once(WPKLC_PLUGIN_PATH . 'lib/functions-login.php');
// Include the lost password functions
require_once(WPKLC_PLUGIN_PATH . 'lib/functions-lostpassword.php');
// Include the register functions
require_once(WPKLC_PLUGIN_PATH . 'lib/functions-register.php');

// Include Kaya Studio Admin Dashboard class
require_once(WPKLC_PLUGIN_PATH . 'lib/class.admin_dashboard_kayastudio.php');
// Include Kaya Studio Admin Plugins Dashboard class
require_once(WPKLC_PLUGIN_PATH . 'lib/class.admin_dashboard_kayastudio_plugins.php');
// Include Kaya Login Captcha Dashboard class
require_once(WPKLC_PLUGIN_PATH . 'lib/class.admin_dashboard_wpklc.php');

if (is_admin())
{
	// Include the admin functions
	require_once(WPKLC_PLUGIN_PATH . 'lib/functions-admin.php');
}

/**
 * Load Localisation files.
 */
if (!function_exists('wpklc_plugin_loadLocalisation'))
{
	function wpklc_plugin_loadLocalisation()
	{
		load_plugin_textdomain(WPKLC_TEXT_DOMAIN, false, dirname(WPKLC_FILE) . '/languages/');
	}
	add_action('plugins_loaded', 'wpklc_plugin_loadLocalisation');
}

/**
 * Install database setup on plugin activation.
 */
if (!function_exists('wpklc_plugin_doActivation'))
{
	function wpklc_plugin_doActivation()
	{
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-database.php');
		wpklc_database_installSetup();
	}
	register_activation_hook(__FILE__, 'wpklc_plugin_doActivation');
}

/**
 * Install database setup on new site creation in multisite.
 *
 * @param WP_Site $new_site New site object.
 */
if (!function_exists('wpklc_plugin_doNewSite'))
{
	function wpklc_plugin_doNewSite($new_site)
	{
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-database.php');
		wpklc_database_installSetupNewSite($new_site);
	}
	add_action('wp_initialize_site', 'wpklc_plugin_doNewSite', 20, 1);
}

/**
 * Show action links on the plugin screen.
 *
 * @param mixed $links Plugin Action links.
 * @param mixed $file Plugin Base file.
 *
 * @return array
 */
if (!function_exists('wpklc_plugin_displayActionLinks'))
{
	function wpklc_plugin_displayActionLinks($links, $file)
	{
		if (WPKLC_FILE === $file)
		{
			$actionLinks = array(
				'ks_panel'		=> '<a href="' . esc_url(admin_url('admin.php?page=' . WPKLC_Admin_Dashboard::$_pageSlug)) . '" title="' . esc_attr__('Login Captcha settings', WPKLC_TEXT_DOMAIN) . '">' . esc_html__('Settings', WPKLC_TEXT_DOMAIN) . '</a>',
				'ks_plugins'	=> '<a href="' . esc_url('https://profiles.wordpress.org/kayastudio#content-plugins') . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr__('Other plugins by Kaya Studio', WPKLC_TEXT_DOMAIN) . '">' . esc_html__('Other plugins', WPKLC_TEXT_DOMAIN) . '</a>',
			);

			return array_merge($actionLinks, $links);
		}

		return (array) $links;
	}
	add_filter('plugin_action_links', 'wpklc_plugin_displayActionLinks', 10, 2);
}

/**
 * Show row meta links on the plugin screen.
 *
 * @param mixed $links Plugin Row Meta links.
 * @param mixed $file Plugin Base file.
 *
 * @return array
 */
if (!function_exists('wpklc_plugin_displayMetaLinks'))
{
	function wpklc_plugin_displayMetaLinks($links, $file)
	{
		if (WPKLC_FILE === $file)
		{
			$metaLinks = array(
				'ks_rate'	=> '<a href="' . esc_url('https://wordpress.org/support/plugin/kaya-login-captcha/reviews/?rate=5#new-post') . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr__('Rate and review this plugin at WordPress.org', WPKLC_TEXT_DOMAIN) . '">' . esc_html__('Rate this plugin', WPKLC_TEXT_DOMAIN) . '&nbsp;&#9733;</a>',
				'ks_donate'	=> '<a href="' . esc_url('http://dotkaya.org/a-propos/') . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr__('Donate to support the advancement of this plugin', WPKLC_TEXT_DOMAIN) . '">' . esc_html__('Donate to this plugin', WPKLC_TEXT_DOMAIN) . '&nbsp;&#9829;</a>',
			);

			return array_merge($links, $metaLinks);
		}

		return (array) $links;
	}
	add_filter('plugin_row_meta', 'wpklc_plugin_displayMetaLinks', 10, 2);
}
