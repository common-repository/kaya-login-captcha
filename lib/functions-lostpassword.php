<?php

/**
 * Kaya Login Captcha - Lost Password Functions.
 * Managing lost password captcha features.
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

/**
 * Adds the captcha to the lost password form.
 */
if (!function_exists('wpklc_lostpassword_formCaptcha'))
{
	function wpklc_lostpassword_formCaptcha()
	{
		// Include the captcha functions
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-captcha.php');
?>
		<!-- START Kaya Login Captcha - Lost-Password Form -->
		<p>
			<span><?php esc_html_e('Security Code:', WPKLC_TEXT_DOMAIN); ?></span><br />
			<?php wpklc_captcha_displayImage(); ?>
		</p>
		<p>
			<label for="wpklc_captcha_answer"><?php esc_html_e('Enter the security code:', WPKLC_TEXT_DOMAIN); ?></label>
			<input id="wpklc_captcha_answer" autocomplete="off" name="wpklc_captcha_answer" size="20" type="text" />
		</p>
		<!-- END Kaya Login Captcha - Lost-Password Form -->
	<?php
	}
}

/**
 * Adds the captcha to the WooCommerce lost password form.
 */
if (!function_exists('wpklc_lostpassword_woocommerceFormCaptcha'))
{
	function wpklc_lostpassword_woocommerceFormCaptcha()
	{
		// Include the captcha functions
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-captcha.php');
	?>
		<!-- START Kaya Login Captcha - WooCommerce Lost-Password Form -->
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label><?php esc_html_e('Security Code:', WPKLC_TEXT_DOMAIN); ?></label>
			<?php wpklc_captcha_displayImage(); ?>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wpklc_captcha_answer"><?php esc_html_e('Enter the security code:', WPKLC_TEXT_DOMAIN); ?>&nbsp;<span class="required">*</span></label>
			<input id="wpklc_captcha_answer" class="woocommerce-Input woocommerce-Input--text input-text" autocomplete="off" name="wpklc_captcha_answer" size="20" type="text" />
		</p>
		<!-- END Kaya Login Captcha - WooCommerce Lost-Password Form -->
<?php
	}
}

/**
 * Validates the lost password captcha before errors are returned from a password reset request
 *
 * @param WP_Error $errors
 *
 * @return WP_Error $errors
 */
if (!function_exists('wpklc_lostpassword_validateRequest'))
{
	function wpklc_lostpassword_validateRequest($errors)
	{
		// get the captcha request validation
		$requestResponse = wpklc_validateCaptchaRequest($_POST);

		// The request response is empty or missing
		if (empty($requestResponse))
		{
			wpklc_lostpassword_blockResetRequest();
			$errors->add('retrieve_password_failed', '<strong>' . esc_html__('Error', WPKLC_TEXT_DOMAIN) . '</strong>: ' . esc_html__('Request error.', WPKLC_TEXT_DOMAIN));

			return $errors;
		}

		// The request response contains an error
		if (!empty($requestResponse['error']))
		{
			wpklc_lostpassword_blockResetRequest();
			$errors->add('retrieve_password_failed', '<strong>' . esc_html__('Error', WPKLC_TEXT_DOMAIN) . '</strong>: ' . esc_html($requestResponse['error']));

			return $errors;
		}

		// The request response is valid
		if (!empty($requestResponse['passed']) && $requestResponse['passed'])
		{
			// increase the passed lost password statistics
			wpklc_database_increaseStatistics('lost_password', 'passed');

			// Return $errors to allow wordpress to check the password reset request
			return $errors;
		}

		// Return $errors to allow wordpress to check the password reset request
		return $errors;
	}
}

/**
 * Blocks password reset request
 * Filter following password reset request actions, increase the blocked requests count and set the http status header.
 */
if (!function_exists('wpklc_lostpassword_blockResetRequest'))
{
	function wpklc_lostpassword_blockResetRequest()
	{
		// Filter following password reset request actions
		add_filter('allow_password_reset', function ()
		{
			return false;
		}, 10);
		// increase the blocked requests count
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-database.php');
		wpklc_database_increaseStatistics('lost_password', 'blocked');
		// set the http status header
		wpklc_setHttpStatusHeader();
	}
}
