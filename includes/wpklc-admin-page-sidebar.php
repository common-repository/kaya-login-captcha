<?php

/**
 * Kaya Login Captcha - Admin Page Sidebar
 * Displays Kaya Login Captcha admin page sidebar.
 */

/**
 * Displays Login Captcha page sidebar.
 */
if (!function_exists('wpklc_admin_doPageSidebar'))
{
	function wpklc_admin_doPageSidebar()
	{
		if (!current_user_can('manage_options'))
		{
			wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
		}

?>
		<div class="ks-wp-dashboard-page-card">
			<div class="ks-wp-dashboard-page-card-header">
				<?php esc_html_e('Reviews', WPKLC_TEXT_DOMAIN); ?>
			</div>
			<div class="ks-wp-dashboard-page-card-body">
				<h5 class="ks-wp-dashboard-page-card-title"><?php esc_html_e('Rate and review this plugin at WordPress.org', WPKLC_TEXT_DOMAIN); ?>&nbsp;&#9733;</h5>
				<p class="ks-wp-dashboard-page-card-text">
					<?php esc_html_e('Please take the time to let me know about your experience and rate this plugin.', WPKLC_TEXT_DOMAIN); ?>
				</p>
				<p class="ks-wp-dashboard-page-card-text">
					<a href="<?php echo esc_url('https://wordpress.org/support/plugin/kaya-login-captcha/reviews/?rate=5#new-post'); ?>" class="ks-wp-dashboard-page-btn ks-wp-dashboard-page-btn-primary" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e('Rate and review this plugin at WordPress.org', WPKLC_TEXT_DOMAIN); ?>"><?php esc_html_e('Rate this plugin', WPKLC_TEXT_DOMAIN); ?></a>
				</p>
			</div>
		</div>
<?php

	}
}
