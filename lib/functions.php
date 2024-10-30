<?php

/**
 * Kaya Login Captcha - Main Functions.
 * Functions to automatically add captcha on login form, lost password form and register form.
 * Disables the XML-RPC feature and set HTTP status header on blocked request
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

/**
 * Adds the plugin features with the custom settings.
 */
if (!function_exists('wpklc_initPluginFeatures'))
{
	function wpklc_initPluginFeatures()
	{
		// include the WPKLC_login_captcha class
		require_once(WPKLC_PLUGIN_PATH . 'lib/class.crud_login_captcha.php');
		// init WPKLC_login_captcha object
		$kayaLoginCaptcha = new WPKLC_login_captcha();
		// get captcha settings
		$isCaptchaOnLoginFormEnabled		= (!empty($kayaLoginCaptcha->data->captcha_on_login_form) ? true : false);
		$isCaptchaOnLostpasswordFormEnabled	= (!empty($kayaLoginCaptcha->data->captcha_on_lost_password_form) ? true : false);
		$isCaptchaOnRegisterFormEnabled		= (!empty($kayaLoginCaptcha->data->captcha_on_register_form) ? true : false);
		$isWordpressXmlRpcDisabled			= (!empty($kayaLoginCaptcha->data->wordpress_xml_rpc_disabled) ? true : false);

		// Check for GD library
		if (!function_exists('gd_info'))
		{
			$isCaptchaOnLoginFormEnabled		= false;
			$isCaptchaOnLostpasswordFormEnabled	= false;
			$isCaptchaOnRegisterFormEnabled		= false;
		}

		if ($isCaptchaOnLoginFormEnabled)
		{
			// Adds the captcha to the login form.
			add_action('login_form', 'wpklc_login_formCaptcha', 999999999);
			// Adds the captcha to the woocommerce login form.
			add_action('woocommerce_login_form', 'wpklc_login_woocommerceFormCaptcha', 999999999);
			// Validates the login captcha before wordpress checks the user and pass
			add_filter('authenticate', 'wpklc_login_validateRequest', 10, 3);
		}

		if ($isCaptchaOnLostpasswordFormEnabled)
		{
			// Adds the captcha to the lost password form.
			add_action('lostpassword_form', 'wpklc_lostpassword_formCaptcha', 999999999);
			// Adds the captcha to the woocommerce lost password form.
			add_action('woocommerce_lostpassword_form', 'wpklc_lostpassword_woocommerceFormCaptcha', 999999999);
			// Validates the lost password captcha before errors are returned from a password reset request
			add_filter('lostpassword_post', 'wpklc_lostpassword_validateRequest', 10, 1);
		}

		if ($isCaptchaOnRegisterFormEnabled)
		{
			// Adds the captcha to the register form.
			add_action('register_form', 'wpklc_register_formCaptcha', 999999999);
			// Adds the captcha to the woocommerce register form.
			add_action('woocommerce_register_form', 'wpklc_register_woocommerceFormCaptcha', 999999999);
			// Validates the register captcha before user information is saved to the database
			add_filter('registration_errors', 'wpklc_register_validateRequest', 10, 3);
			add_filter('woocommerce_process_registration_errors', 'wpklc_register_woocommerceValidateRequest', 10, 4);
		}

		if ($isWordpressXmlRpcDisabled)
		{
			add_filter('xmlrpc_enabled', '__return_false', 10);
		}
	}
	add_action('init', 'wpklc_initPluginFeatures', 10);
}

/**
 * Set HTTP status header on blocked request.
 */
if (!function_exists('wpklc_setHttpStatusHeader'))
{
	function wpklc_setHttpStatusHeader()
	{
		// include the WPKLC_login_captcha class
		require_once(WPKLC_PLUGIN_PATH . 'lib/class.crud_login_captcha.php');
		// init WPKLC_login_captcha object
		$kayaLoginCaptcha = new WPKLC_login_captcha();
		// get captcha settings
		$captchaBlockedRequestHttpStatus = (!empty($kayaLoginCaptcha->data->captcha_blocked_request_http_status) ? esc_attr($kayaLoginCaptcha->data->captcha_blocked_request_http_status) : 'none');

		if ('unauthorized' == $captchaBlockedRequestHttpStatus)
		{
			$httpStatusCode = '401';
		}
		elseif ('forbidden' == $captchaBlockedRequestHttpStatus)
		{
			$httpStatusCode = '403';
		}

		if ('none' != $captchaBlockedRequestHttpStatus && !empty($httpStatusCode))
		{
			status_header($httpStatusCode, 'Captcha Error');
		}
	}
}

/**
 * Validates the captcha request
 *
 * @param array $p_postData Data from captcha $_POST request
 *
 * @return array
 */
if (!function_exists('wpklc_validateCaptchaRequest'))
{
	function wpklc_validateCaptchaRequest($p_postData)
	{
		// init response array to the request.
		$requestResponse = array();

		// The request data is empty or missing
		if (empty($p_postData['wpklc_captcha_request']))
		{
			// Add and set error response
			$requestResponse['error'] = __('Missing request ID.', WPKLC_TEXT_DOMAIN);

			return $requestResponse;
		}

		// The answer data is empty or missing
		if (empty($p_postData['wpklc_captcha_answer']))
		{
			// Add and set error response
			$requestResponse['error'] = __('Please enter the security code.', WPKLC_TEXT_DOMAIN);

			return $requestResponse;
		}

		// get sent request id and answer
		$requestID	= sanitize_key($p_postData['wpklc_captcha_request']);
		$answer		= sanitize_text_field($p_postData['wpklc_captcha_answer']);

		// get the captcha request
		require_once(WPKLC_PLUGIN_PATH . 'lib/functions-database.php');
		$requestResult = wpklc_database_getRequest($requestID);

		// The request ID return nothing, it is invalid or has expired.
		if (empty($requestResult))
		{
			// Add and set error response
			$requestResponse['error'] = __('Invalid or expired request.', WPKLC_TEXT_DOMAIN);

			return $requestResponse;
		}

		// The answer is incorrect or does not match the request ID
		if (strtolower($requestResult['request_code']) !==  strtolower($answer))
		{
			// Add and set error response
			$requestResponse['error'] = __('The security code entered is incorrect.', WPKLC_TEXT_DOMAIN);
			// delete the database data for this request
			wpklc_database_deleteRequest($requestID);

			return $requestResponse;
		}

		// The answer is correct, delete the database data for this request
		wpklc_database_deleteRequest($requestID);

		// Add and set passed response to true
		$requestResponse['passed'] = true;

		// Return $requestResponse to validate the request
		return $requestResponse;
	}
}
