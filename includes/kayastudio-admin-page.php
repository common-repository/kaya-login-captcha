<?php

/**
 * Kaya Studio - Admin Main Page
 * Displays Kaya Studio admin main page.
 *
 * @version 1.0.1
 */

/**
 * Displays Kaya Studio page.
 */
if (!function_exists('kayastudio_plugins_admin_doMainPage'))
{
	function kayastudio_plugins_admin_doMainPage()
	{
		if (!current_user_can('edit_posts'))
		{
			wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
		}

		global $wp_kayastudio_dashboard_pluginsList;
		$pluginsList = $wp_kayastudio_dashboard_pluginsList->getPluginList();

?>
		<div class="ks-wp-dashboard-page-container">
			<div class="ks-wp-dashboard-page-row">

				<div class="ks-wp-dashboard-page-header">
					<div class="ks-wp-dashboard-page-header-title">
						<h1>Kaya Studio</h1>
					</div>
				</div>

				<div class="ks-wp-dashboard-page-content">

					<?php

					foreach ($pluginsList as $i_plugin)
					{

					?>
						<div class="ks-wp-dashboard-page-content-card">
							<h6 class="ks-wp-dashboard-page-content-card-title"><?php echo esc_html($i_plugin['title']); ?></h6>
							<p>
								<?php echo esc_html($i_plugin['page_text']); ?>
							</p>
							<p>
								<a href="<?php echo esc_url(admin_url('admin.php?page=' . $i_plugin['page_slug'])); ?>" class="ks-wp-dashboard-page-btn ks-wp-dashboard-page-btn-primary" title="<?php echo esc_attr($i_plugin['page_name']); ?>"><?php echo esc_html($i_plugin['page_name']); ?></a>
							</p>
						</div>
					<?php

					}

					?>
					<div class="ks-wp-dashboard-page-content-card">
						<h6 class="ks-wp-dashboard-page-content-card-title"><?php esc_html_e('WordPress plugins by Kaya Studio', WPKLC_TEXT_DOMAIN); ?></h6>
						<p>
							<?php esc_html_e('Kaya Studio is managed by a freelance web developer. These plugins are developed in his free time and for the love of making useful open source softwares.', WPKLC_TEXT_DOMAIN); ?>
						</p>
						<p>
							<?php esc_html_e('Discover all the WordPress plugins developed by Kaya Studio.', WPKLC_TEXT_DOMAIN); ?>
						</p>
						<p>
							<a href="<?php echo esc_url('https://profiles.wordpress.org/kayastudio#content-plugins'); ?>" class="ks-wp-dashboard-page-btn ks-wp-dashboard-page-btn-primary" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e('All plugins by Kaya Studio', WPKLC_TEXT_DOMAIN); ?>"><?php esc_html_e('All plugins', WPKLC_TEXT_DOMAIN); ?></a>
						</p>
					</div>

				</div>

				<div class="ks-wp-dashboard-page-sidebar">
					<?php

					if (is_file(WPKLC_PLUGIN_PATH . 'includes/kayastudio-admin-page-sidebar.php'))
					{
						include_once WPKLC_PLUGIN_PATH . 'includes/kayastudio-admin-page-sidebar.php';
						kayastudio_plugins_admin_doMainPageSidebar();
					}

					?>
				</div>

			</div>
		</div>
<?php

	}
}
