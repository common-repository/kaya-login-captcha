<?php

/**
 * Kaya Login Captcha - Administration Functions.
 * Managing administration features.
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

if (!is_admin())
{
	exit; // Exit if accessed outside dashboard
}

/**
 * Check for KayaStudio Plugins object and create it if not found.
 */
if (!isset($wp_kayastudio_dashboard_pluginsList))
{
	global $wp_kayastudio_dashboard_pluginsList;
	$wp_kayastudio_dashboard_pluginsList = new WP_KayaStudio_Plugins_List_Admin_Dashboard();
}

/**
 * Adds administration plugin menu pages.
 *
 * Adds pages to admin menu (Main page, Plugin Settings), and adds plugin infos in plugins list.
 *
 * @return bool	True if the current user has the specified capability for seeing the menu, or False if not.
 */
if (!function_exists('wpklc_admin_addMenuPages'))
{
	function wpklc_admin_addMenuPages()
	{
		if (!current_user_can('manage_options'))
		{
			return false;
		}
		global $wp_kayastudio_dashboard_pluginsList;

		// Add Kaya Studio Main page
		WP_KayaStudio_Plugins_Admin_Dashboard::init();
		// Add Kaya Login Captcha page
		WPKLC_Admin_Dashboard::init();
		// Add Kaya Login Captcha infos in plugins list
		$wp_kayastudio_dashboard_pluginsList->addPluginInList(WPKLC_Admin_Dashboard::getPluginInfos());

		return true;
	}
	add_action('init', 'wpklc_admin_addMenuPages');
}
