<?php

/**
 * Kaya Login Captcha Admin Page.
 * Displays the admin plugin page.
 */

/**
 * Displays Login Captcha Page.
 */
if (!function_exists('wpklc_admin_doOptionPage'))
{
	function wpklc_admin_doOptionPage()
	{
		if (!current_user_can('manage_options'))
		{
			wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
		}

		// Include the forms class.
		require_once(WPKLC_PLUGIN_PATH . 'lib/class.forms.php');
		// Include the Captcha forms class
		require_once(WPKLC_PLUGIN_PATH . 'lib/class.forms_captcha.php');

		$wpklc_footer = sprintf(/* translators: 1: Plugin Name 2: Plugin Version */__('%1$s - Version %2$s', WPKLC_TEXT_DOMAIN), 'Kaya Login Captcha', WPKLC_VERSION);

?>
		<div class="ks-wp-dashboard-page-container">
			<div class="ks-wp-dashboard-page-row">

				<div class="ks-wp-dashboard-page-header">
					<div class="ks-wp-dashboard-page-header-title">
						<h1>Kaya Login Captcha</h1>
					</div>
				</div>

				<div class="ks-wp-dashboard-page-content">

					<div class="ks-wp-dashboard-page-content-card">
						<h6 class="ks-wp-dashboard-page-content-card-title"><?php esc_html_e('Login Captcha', WPKLC_TEXT_DOMAIN); ?></h6>
						<p>
							<?php esc_html_e('Adds a simple captcha on the login form, the register form and the lost-password form.', WPKLC_TEXT_DOMAIN); ?>
						</p>
						<p>
							<?php esc_html_e('You can choose here the captcha settings and the forms on which to display it.', WPKLC_TEXT_DOMAIN); ?><br />
							<?php esc_html_e('The blocked request HTTP status can be customized and the XML-RPC feature can be disabled (Warning: Some plugins need XML-RPC feature, like the Jetpack plugin).', WPKLC_TEXT_DOMAIN); ?>
						</p>
					</div>

					<div class="ks-wp-dashboard-page-content-card">
						<h6 class="ks-wp-dashboard-page-content-card-title"><?php esc_html_e('Login Captcha settings', WPKLC_TEXT_DOMAIN); ?></h6>
						<?php

						// include the WPKLC_login_captcha class
						require_once(WPKLC_PLUGIN_PATH . 'lib/class.crud_login_captcha.php');
						// init WPKLC_login_captcha object
						$kayaLoginCaptcha = new WPKLC_login_captcha();

						// get form fields and default values
						$formFieldsHTML = WPKLC_Forms_Captcha::display_form_fields_options($kayaLoginCaptcha->data);

						// Login Captcha variables texts
						$wpklc_textSave			= __('Save Changes', WPKLC_TEXT_DOMAIN);
						$wpklc_textReset		= __('Reset settings', WPKLC_TEXT_DOMAIN);
						$wpklc_textResetConfirm	= __('Do you want to delete the current settings?', WPKLC_TEXT_DOMAIN);

						// Login Captcha save panel
						$wpklc_admin_panel = '<table class="form-table"><tbody><tr>';
						$wpklc_admin_panel .= '<td><input class="ks-wp-dashboard-page-btn ks-wp-dashboard-page-btn-primary" class="left" type="submit" name="save_login_captcha" value="' . esc_attr($wpklc_textSave) . '" /></td>';
						$wpklc_admin_panel .= '</tr></tbody></table>';

						// Login Captcha delete panel
						$wpklc_delete_panel = '<table class="form-table"><tbody><tr>';
						$wpklc_delete_panel .= '<td><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
						$wpklc_delete_panel .= '<input type="hidden" name="wpklc[id]" value="' . esc_attr($kayaLoginCaptcha->id) . '" />';
						$wpklc_delete_panel .= '<input type="hidden" name="wpklc_action" value="delete" />';
						$wpklc_delete_panel .= '<input type="hidden" name="action" value="wpklc_login_captcha_form">';
						$wpklc_delete_panel .= '<input type="hidden" name="wpklc_target" value="login_captcha" />';
						$wpklc_delete_panel .= wp_nonce_field('wpklc_crud_' . get_current_user_id(), 'wpklc_crud_delete', true, false);
						$wpklc_delete_panel .= '<input class="ks-wp-dashboard-page-btn ks-wp-dashboard-page-btn-warning" class="left" type="submit" name="delete_login_captcha" value="' . esc_attr($wpklc_textReset) . '" onclick="return confirm(\'' . esc_js($wpklc_textResetConfirm) . '\');" />';
						$wpklc_delete_panel .= '</form></td>';
						$wpklc_delete_panel .= '</tr></tbody></table>';

						// Check font usage
						$useFont = (function_exists('imagettftext'));

						if ($useFont === FALSE) :
						?>
							<p>
								<b><?php esc_html_e('GD and FreeType support:', WPKLC_TEXT_DOMAIN); ?></b><br />
								<?php esc_html_e('To have an optimal user experience with this plugin, it is recommended to have installed the GD library and the FreeType library. Please check your PHP configuration.', WPKLC_TEXT_DOMAIN); ?>
							</p>
						<?php endif; ?>
						<p>
							<b><?php esc_html_e('Captcha preview with current saved settings:', WPKLC_TEXT_DOMAIN); ?></b><br />
							<?php

							// Include the captcha class
							require_once(WPKLC_PLUGIN_PATH . 'lib/class.captcha.php');
							// Include the captcha functions
							require_once(WPKLC_PLUGIN_PATH . 'lib/functions-captcha.php');

							// get settings config
							$settingsConfig = wpklc_captcha_getCustomConfig();
							// get captcha config
							$captchaConfig = WPKLC_Captcha::getConfig($settingsConfig);

							if (isset($captchaConfig['error']))
							{
								echo esc_html($captchaConfig['error']);
							}
							elseif (isset($captchaConfig['code']) && isset($captchaConfig['settings']))
							{
								$captcha = WPKLC_Captcha::getCaptcha($captchaConfig['settings']);
								echo '<img src="data:image/png;base64,' . esc_attr($captcha) . '" />';
							}

							?>
						</p>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
							<p>
								<b><?php esc_html_e('Captcha settings:', WPKLC_TEXT_DOMAIN); ?></b><br /><br />
								<?php echo $formFieldsHTML; ?>
							</p>
							<?php wp_nonce_field('wpklc_crud_' . get_current_user_id(), 'wpklc_crud_edit', true, true); ?>
							<input type="hidden" name="wpklc[id]" value="<?php echo esc_attr($kayaLoginCaptcha->id); ?>" />
							<input type="hidden" name="wpklc_action" value="edit" />
							<input type="hidden" name="action" value="wpklc_login_captcha_form">
							<input type="hidden" name="wpklc_target" value="login_captcha" />
							<?php echo $wpklc_admin_panel; ?>
						</form>

						<?php echo $wpklc_delete_panel; ?>
					</div>

					<div class="ks-wp-dashboard-page-content-card">
						<h6 class="ks-wp-dashboard-page-content-card-title"><?php esc_html_e('Captcha statistics', WPKLC_TEXT_DOMAIN); ?></h6>
						<?php

						require_once(WPKLC_PLUGIN_PATH . 'lib/functions-database.php');
						$loginStatistics = wpklc_database_getStatistics();

						if (!empty($loginStatistics['login']))
						{

						?>
							<br /><b><?php esc_html_e('Login form statistics', WPKLC_TEXT_DOMAIN); ?></b>
							<?php

							krsort($loginStatistics['login']);
							foreach ($loginStatistics['login'] as $i_yearKey => $i_yearValue)
							{

							?>
								<table class="widefat fixed" style="margin: 8px 0;">
									<thead>
										<tr>
											<th><?php echo esc_html($i_yearKey); ?></th>
											<th><?php esc_html_e('Passed', WPKLC_TEXT_DOMAIN); ?></th>
											<th><?php esc_html_e('Blocked', WPKLC_TEXT_DOMAIN); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php

										krsort($i_yearValue);
										foreach ($i_yearValue as $i_monthKey => $i_monthValue)
										{

										?>
											<tr>
												<td><?php echo esc_html(sprintf('%02d', $i_monthKey) . '&nbsp;-&nbsp;' . date_i18n('F', mktime(0, 0, 0, $i_monthKey, 1))); ?></td>
												<td><?php echo esc_html($i_monthValue["passed"]); ?></td>
												<td><?php echo esc_html($i_monthValue["blocked"]); ?></td>
											</tr>
										<?php

										}

										?>
									</tbody>
								</table>
							<?php

							}
						}

						if (!empty($loginStatistics['lost_password']))
						{

							?>
							<br /><b><?php esc_html_e('Lost-Password form statistics', WPKLC_TEXT_DOMAIN); ?></b>
							<?php

							krsort($loginStatistics['lost_password']);
							foreach ($loginStatistics['lost_password'] as $i_yearKey => $i_yearValue)
							{

							?>
								<table class="widefat fixed" style="margin: 8px 0;">
									<thead>
										<tr>
											<th><?php echo esc_html($i_yearKey); ?></th>
											<th><?php esc_html_e('Passed', WPKLC_TEXT_DOMAIN); ?></th>
											<th><?php esc_html_e('Blocked', WPKLC_TEXT_DOMAIN); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php

										krsort($i_yearValue);
										foreach ($i_yearValue as $i_monthKey => $i_monthValue)
										{

										?>
											<tr>
												<td><?php echo esc_html(sprintf('%02d', $i_monthKey) . '&nbsp;-&nbsp;' . date_i18n('F', mktime(0, 0, 0, $i_monthKey, 1))); ?></td>
												<td><?php echo esc_html($i_monthValue["passed"]); ?></td>
												<td><?php echo esc_html($i_monthValue["blocked"]); ?></td>
											</tr>
										<?php

										}

										?>
									</tbody>
								</table>
							<?php

							}
						}

						if (!empty($loginStatistics['register']))
						{

							?>
							<br /><b><?php esc_html_e('Register form statistics', WPKLC_TEXT_DOMAIN); ?></b>
							<?php

							krsort($loginStatistics['register']);
							foreach ($loginStatistics['register'] as $i_yearKey => $i_yearValue)
							{

							?>
								<table class="widefat fixed" style="margin: 8px 0;">
									<thead>
										<tr>
											<th><?php echo esc_html($i_yearKey); ?></th>
											<th><?php esc_html_e('Passed', WPKLC_TEXT_DOMAIN); ?></th>
											<th><?php esc_html_e('Blocked', WPKLC_TEXT_DOMAIN); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php

										krsort($i_yearValue);
										foreach ($i_yearValue as $i_monthKey => $i_monthValue)
										{

										?>
											<tr>
												<td><?php echo esc_html(sprintf('%02d', $i_monthKey) . '&nbsp;-&nbsp;' . date_i18n('F', mktime(0, 0, 0, $i_monthKey, 1))); ?></td>
												<td><?php echo esc_html($i_monthValue["passed"]); ?></td>
												<td><?php echo esc_html($i_monthValue["blocked"]); ?></td>
											</tr>
										<?php

										}

										?>
									</tbody>
								</table>
							<?php

							}
						}

						if (empty($loginStatistics['login']) && empty($loginStatistics['lost_password']) && empty($loginStatistics['register']))
						{

							?>
							<br /><b><?php esc_html_e('No statistics', WPKLC_TEXT_DOMAIN); ?></b>
						<?php

						}
						else
						{
							$wpklc_textResetStatistics			= __('Reset all statistics', WPKLC_TEXT_DOMAIN);
							$wpklc_textResetStatisticsConfirm	= __('Do you want to delete all statistics?', WPKLC_TEXT_DOMAIN);

						?>
							<table class="form-table">
								<tbody>
									<tr>
										<td>
											<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
												<input type="hidden" name="wpklc[id]" value="<?php echo esc_attr($kayaLoginCaptcha->id); ?>" />
												<input type="hidden" name="wpklc_action" value="delete_statistics" />
												<input type="hidden" name="action" value="wpklc_login_captcha_form">
												<input type="hidden" name="wpklc_target" value="login_captcha_statistics" />
												<?php wp_nonce_field('wpklc_crud_' . get_current_user_id(), 'wpklc_crud_delete_statistics', true, true); ?>
												<input class="ks-wp-dashboard-page-btn ks-wp-dashboard-page-btn-warning" class="left" type="submit" name="delete_login_captcha_statistics" value="<?php echo esc_attr($wpklc_textResetStatistics); ?>" onclick="return confirm('<?php echo esc_js($wpklc_textResetStatisticsConfirm); ?>');" />
											</form>
										</td>
									</tr>
								</tbody>
							</table>
						<?php

						}

						?>
					</div>

				</div>

				<div class="ks-wp-dashboard-page-sidebar">
					<?php

					if (is_file(WPKLC_PLUGIN_PATH . 'includes/wpklc-admin-page-sidebar.php'))
					{
						include_once WPKLC_PLUGIN_PATH . 'includes/wpklc-admin-page-sidebar.php';
						wpklc_admin_doPageSidebar();
					}
					if (is_file(WPKLC_PLUGIN_PATH . 'includes/kayastudio-admin-page-sidebar.php'))
					{
						include_once WPKLC_PLUGIN_PATH . 'includes/kayastudio-admin-page-sidebar.php';
						kayastudio_plugins_admin_doMainPageSidebar();
					}

					?>
				</div>

				<div class="ks-wp-dashboard-page-footer">
					<div class="ks-wp-dashboard-page-footer-version">
						<p><?php echo esc_html($wpklc_footer); ?></p>
					</div>
				</div>

			</div>
		</div>
<?php

	}
}
