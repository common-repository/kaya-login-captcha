<?php

/**
 * Delete data from database during plugin uninstall.
 */
if (!defined('WP_UNINSTALL_PLUGIN'))
{
	die();
}

// delete main settings, requests, stats an notices options
delete_option('wpklc_kaya_login_captcha_settings');
delete_option('wpklc_kaya_login_captcha_requests');
delete_option('wpklc_kaya_login_captcha_login_statistics');
delete_option('wpklc_kaya_login_captcha_lost_password_statistics');
delete_option('wpklc_kaya_login_captcha_register_statistics');
delete_option('wpklc_kaya_login_captcha_admin_notices');

// delete multisite settings, requests, stats an notices options
if (is_multisite())
{
	$subSites = get_sites();
	foreach ($subSites as $i_site)
	{
		switch_to_blog($i_site->blog_id);
		if (get_option('wpklc_kaya_login_captcha_settings'))
		{
			delete_option('wpklc_kaya_login_captcha_settings');
			delete_option('wpklc_kaya_login_captcha_requests');
			delete_option('wpklc_kaya_login_captcha_login_statistics');
			delete_option('wpklc_kaya_login_captcha_lost_password_statistics');
			delete_option('wpklc_kaya_login_captcha_register_statistics');
			delete_option('wpklc_kaya_login_captcha_admin_notices');
		}
		restore_current_blog();
	}
}
