<?php

/**
 * Kaya Login Captcha - Login Functions.
 * Managing login captcha features.
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

/**
 * Adds the captcha to the login form.
 */
if (!function_exists('wpklc_login_formCaptcha'))
{
	function wpklc_login_formCaptcha()
	{
		// Include the captcha functions
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-captcha.php');
?>
		<!-- START Kaya Login Captcha - Login Form -->
		<p>
			<span><?php esc_html_e('Security Code:', WPKLC_TEXT_DOMAIN); ?></span><br />
			<?php wpklc_captcha_displayImage(); ?>
		</p>
		<p>
			<label for="wpklc_captcha_answer"><?php esc_html_e('Enter the security code:', WPKLC_TEXT_DOMAIN); ?></label>
			<input id="wpklc_captcha_answer" autocomplete="off" name="wpklc_captcha_answer" size="20" type="text" />
		</p>
		<!-- END Kaya Login Captcha - Login Form -->
	<?php
	}
}

/**
 * Adds the captcha to the WooCommerce login form.
 */
if (!function_exists('wpklc_login_woocommerceFormCaptcha'))
{
	function wpklc_login_woocommerceFormCaptcha()
	{
		// Include the captcha functions
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-captcha.php');
	?>
		<!-- START Kaya Login Captcha - WooCommerce Login Form -->
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label><?php esc_html_e('Security Code:', WPKLC_TEXT_DOMAIN); ?></label>
			<?php wpklc_captcha_displayImage(); ?>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wpklc_captcha_answer"><?php esc_html_e('Enter the security code:', WPKLC_TEXT_DOMAIN); ?>&nbsp;<span class="required">*</span></label>
			<input id="wpklc_captcha_answer" class="woocommerce-Input woocommerce-Input--text input-text" autocomplete="off" name="wpklc_captcha_answer" size="20" type="text" />
		</p>
		<!-- END Kaya Login Captcha - WooCommerce Login Form -->
<?php
	}
}

/**
 * Validates the login captcha before wordpress checks the user and pass
 *
 * @param object $user
 * @param string $username
 * @param string $password
 *
 * @return object
 */
if (!function_exists('wpklc_login_validateRequest'))
{
	function wpklc_login_validateRequest($user, $username, $password)
	{
		// If the username or password are not sent, return $user to avoid errors
		if (empty($username) || empty($password))
		{
			return $user;
		}

		// get the captcha request validation
		$requestResponse = wpklc_validateCaptchaRequest($_POST);

		// init WP error object
		$error = new WP_Error();

		// The request response is empty or missing
		if (empty($requestResponse))
		{
			wpklc_login_blockAuthenticationRequest();
			$error->add('authentication_failed', '<strong>' . esc_html__('Error', WPKLC_TEXT_DOMAIN) . '</strong>: ' . esc_html__('Request error.', WPKLC_TEXT_DOMAIN));

			return $error;
		}

		// The request response contains an error
		if (!empty($requestResponse['error']))
		{
			wpklc_login_blockAuthenticationRequest();
			$error->add('authentication_failed', '<strong>' . esc_html__('Error', WPKLC_TEXT_DOMAIN) . '</strong>: ' . esc_html($requestResponse['error']));

			return $error;
		}

		// The request response is valid
		if (!empty($requestResponse['passed']) && $requestResponse['passed'])
		{
			// increase the passed login statistics
			wpklc_database_increaseStatistics('login', 'passed');

			// Return $user to allow wordpress to check password and username
			return $user;
		}

		return $error;
	}
}

/**
 * Blocks authentication
 * Remove following authentication actions, increase the blocked requests count and set the http status header.
 */
if (!function_exists('wpklc_login_blockAuthenticationRequest'))
{
	function wpklc_login_blockAuthenticationRequest()
	{
		// remove following authentication actions
		remove_action('authenticate', 'wp_authenticate_username_password', 20);
		remove_action('authenticate', 'wp_authenticate_email_password', 20);
		// increase the blocked requests count
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-database.php');
		wpklc_database_increaseStatistics('login', 'blocked');
		// set the http status header
		wpklc_setHttpStatusHeader();
	}
}
