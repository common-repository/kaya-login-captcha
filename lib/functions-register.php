<?php

/**
 * Kaya Login Captcha - Register Functions.
 * Managing register captcha features.
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

/**
 * Adds the captcha to the register form.
 */
if (!function_exists('wpklc_register_formCaptcha'))
{
	function wpklc_register_formCaptcha()
	{
		// Include the captcha functions
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-captcha.php');
?>
		<!-- START Kaya Login Captcha - Register Form -->
		<p>
			<span><?php esc_html_e('Security Code:', WPKLC_TEXT_DOMAIN); ?></span><br />
			<?php wpklc_captcha_displayImage(); ?>
		</p>
		<p>
			<label for="wpklc_captcha_answer"><?php esc_html_e('Enter the security code:', WPKLC_TEXT_DOMAIN); ?></label>
			<input id="wpklc_captcha_answer" autocomplete="off" name="wpklc_captcha_answer" size="20" type="text" />
		</p>
		<!-- END Kaya Login Captcha - Register Form -->
	<?php
	}
}

/**
 * Adds the captcha to the WooCommerce register form.
 */
if (!function_exists('wpklc_register_woocommerceFormCaptcha'))
{
	function wpklc_register_woocommerceFormCaptcha()
	{
		// Include the captcha functions
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-captcha.php');
	?>
		<!-- START Kaya Login Captcha - WooCommerce Register Form -->
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label><?php esc_html_e('Security Code:', WPKLC_TEXT_DOMAIN); ?></label>
			<?php wpklc_captcha_displayImage(); ?>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wpklc_captcha_answer"><?php esc_html_e('Enter the security code:', WPKLC_TEXT_DOMAIN); ?>&nbsp;<span class="required">*</span></label>
			<input id="wpklc_captcha_answer" class="woocommerce-Input woocommerce-Input--text input-text" autocomplete="off" name="wpklc_captcha_answer" size="20" type="text" />
		</p>
		<!-- END Kaya Login Captcha - WooCommerce Register Form -->
<?php
	}
}

/**
 * Validates the register captcha before user information is saved to the database
 *
 * @param WP_Error $errors
 * @param string $sanitized_user_login
 * @param string $user_email
 *
 * @return WP_Error $errors
 */
if (!function_exists('wpklc_register_validateRequest'))
{
	function wpklc_register_validateRequest($errors, $sanitized_user_login, $user_email)
	{
		// If the user login or user email are not sent, return $errors to avoid errors
		if (empty($sanitized_user_login) || empty($user_email))
		{
			return $errors;
		}

		// get the captcha request validation
		$requestResponse = wpklc_validateCaptchaRequest($_POST);

		// The request response is empty or missing
		if (empty($requestResponse))
		{
			wpklc_register_blockInsertRequest();
			$errors->add('register_failed', '<strong>' . esc_html__('Error', WPKLC_TEXT_DOMAIN) . '</strong>: ' . esc_html__('Request error.', WPKLC_TEXT_DOMAIN));

			return $errors;
		}

		// The request response contains an error
		if (!empty($requestResponse['error']))
		{
			wpklc_register_blockInsertRequest();
			$errors->add('register_failed', '<strong>' . esc_html__('Error', WPKLC_TEXT_DOMAIN) . '</strong>: ' . esc_html($requestResponse['error']));

			return $errors;
		}

		// The request response is valid
		if (!empty($requestResponse['passed']) && $requestResponse['passed'])
		{
			// increase the passed register statistics
			wpklc_database_increaseStatistics('register', 'passed');

			// Return $errors to allow wordpress to check new user data
			return $errors;
		}

		// Return $errors to allow wordpress to check new user data
		return $errors;
	}
}

/**
 * Validates the register captcha from WooCommerce before user information is saved to the database
 *
 * @param WP_Error $validation_error
 * @param string $username
 * @param string $password
 * @param string $email
 *
 * @return WP_Error $validation_error
 */
if (!function_exists('wpklc_register_woocommerceValidateRequest'))
{
	function wpklc_register_woocommerceValidateRequest($validation_error, $username, $password, $email)
	{
		// If the user email is not sent, return $errors to avoid errors
		if (empty($email))
		{
			return $validation_error;
		}

		// get the captcha request validation
		$requestResponse = wpklc_validateCaptchaRequest($_POST);

		// The request response is empty or missing
		if (empty($requestResponse))
		{
			wpklc_register_blockInsertRequest();
			$validation_error->add('register_failed', esc_html__('Request error.', WPKLC_TEXT_DOMAIN));

			return $validation_error;
		}

		// The request response contains an error
		if (!empty($requestResponse['error']))
		{
			wpklc_register_blockInsertRequest();
			$validation_error->add('register_failed', esc_html($requestResponse['error']));

			return $validation_error;
		}

		// The request response is valid
		if (!empty($requestResponse['passed']) && $requestResponse['passed'])
		{
			// increase the passed register statistics
			wpklc_database_increaseStatistics('register', 'passed');

			// Return $validation_error to allow WooCommerce to check new user data
			return $validation_error;
		}

		// Return $validation_error to allow WooCommerce to check new user data
		return $validation_error;
	}
}

/**
 * Blocks register request
 * Filter following register request actions, increase the blocked requests count and set the http status header.
 */
if (!function_exists('wpklc_register_blockInsertRequest'))
{
	function wpklc_register_blockInsertRequest()
	{
		// Filter following register request actions
		add_filter('wp_pre_insert_user_data', function ()
		{
			return null;
		}, 10);
		// increase the blocked requests count
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-database.php');
		wpklc_database_increaseStatistics('register', 'blocked');
		// set the http status header
		wpklc_setHttpStatusHeader();
	}
}
