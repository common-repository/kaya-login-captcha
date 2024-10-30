<?php

/**
 * Kaya Login Captcha - Captcha Functions.
 * Managing captcha features.
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

/**
 * creates and display the captcha base64 image for forms.
 */
if (!function_exists('wpklc_captcha_displayImage'))
{
	function wpklc_captcha_displayImage()
	{
		// Include the captcha class
		require_once(WPKLC_PLUGIN_PATH . 'lib/class.captcha.php');
		// Set a unique request ID
		$requestID = md5(mt_rand(1, 999999999) . 't4qd3btp6jj3pi54h65v0y48509p5zzwct738h725vv3vw5l2p246r5pg2o0gy1n6v0r94' . mt_rand(1, 999999999));

		// get custom config
		$settingsConfig = wpklc_captcha_getCustomConfig();
		// get captcha config
		$captchaConfig = WPKLC_Captcha::getConfig($settingsConfig);

		if (isset($captchaConfig['error']))
		{
			echo esc_html($captchaConfig['error']);
		}
		elseif (isset($captchaConfig['code']) && isset($captchaConfig['settings']))
		{
			// get the captcha base64 encoded image
			$captcha = WPKLC_Captcha::getCaptcha($captchaConfig['settings']);
			// add request to the database
			require_once(WPKLC_PLUGIN_PATH . 'lib/functions-database.php');
			if (wpklc_database_addRequest($requestID, $captchaConfig['code']))
			{
?>
				<img src="data:image/png;base64,<?php echo esc_attr($captcha); ?>" />
				<input name="wpklc_captcha_request" type="hidden" value="<?php echo esc_attr($requestID); ?>" />
<?php
			}
		}
	}
}

/**
 * Get Captcha custom configuration.
 *
 * @return array
 */
if (!function_exists('wpklc_captcha_getCustomConfig'))
{
	function wpklc_captcha_getCustomConfig()
	{
		// include the WPKLC_login_captcha class
		require_once(WPKLC_PLUGIN_PATH . 'lib/class.crud_login_captcha.php');

		// init WPKLC_login_captcha object
		$kayaLoginCaptcha = new WPKLC_login_captcha();

		// get captcha settings
		$captchaCodeLenght		= (!empty($kayaLoginCaptcha->data->captcha_code_length) ? $kayaLoginCaptcha->data->captcha_code_length : '');
		$captchaCodeFormat		= (!empty($kayaLoginCaptcha->data->captcha_code_format) ? $kayaLoginCaptcha->data->captcha_code_format : '');
		$captchaLinesEnabled	= (!empty($kayaLoginCaptcha->data->captcha_lines) ? true : false);

		// init captcha config with selected settings
		$settingsConfig = array(
			'min_length'	=> esc_attr($captchaCodeLenght),
			'max_length'	=> esc_attr($captchaCodeLenght),
			'characters'	=> esc_attr($captchaCodeFormat),
			'lines'			=> esc_attr($captchaLinesEnabled),
		);

		return $settingsConfig;
	}
}
