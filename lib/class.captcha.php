<?php

/** 
 * Kaya Login Captcha - Captcha Class
 * Create Captcha configuration and base64 encoded image.
 */

if (!class_exists('WPKLC_Captcha'))
{
	class WPKLC_Captcha
	{
		/**
		 * Get the captcha configuration
		 *
		 * @param array $config
		 *
		 * @return array
		 */
		public static function getConfig($config = array())
		{
			// init config array
			$captchaConfig = array();

			// set assets paths
			$bg_path	= WPKLC_PLUGIN_PATH . 'assets/backgrounds/';
			$font_path	= WPKLC_PLUGIN_PATH . 'assets/fonts/';

			// Default settings values
			$captchaSettings = array(
				'code'				=> '',
				'min_length'		=> 3,
				'max_length'		=> 3,
				'backgrounds'		=> array(
					$bg_path . '45-degree-fabric.png',
					$bg_path . 'cloth-alike.png',
					$bg_path . 'grey-sandbag.png',
					$bg_path . 'kinda-jean.png',
					$bg_path . 'polyester-lite.png',
					$bg_path . 'stitched-wool.png',
					$bg_path . 'white-carbon.png',
					$bg_path . 'white-wave.png'
				),
				'fonts'				=> array(
					$font_path . 'times_new_yorker.ttf'
				),
				'characters'		=> 'Num',
				'min_font_size'		=> 22,
				'max_font_size'		=> 22,
				'color'				=> '#666',
				'angle_min'			=> 0,
				'angle_max'			=> 8,
				'lines'				=> true,
				'shadow'			=> true,
				'shadow_color'		=> '#fff',
				'shadow_offset_x'	=> -2,
				'shadow_offset_y'	=> 2
			);

			// Characters values
			$charactersSettings = array(
				'num'		=> '1234567890',
				'alpha'		=> 'ABCDEFGHIJKLMNPRSTUVWXYZ',
				'alphanum'	=> 'ABCDEFGHJKLMNPRSTUVWXYZ23456789',
			);

			// Overwrite defaults with custom config values
			if (is_array($config))
			{
				foreach ($config as $i_key => $i_value) $captchaSettings[$i_key] = $i_value;
			}

			// Restrict certain values
			if ($captchaSettings['min_length'] < 1) $captchaSettings['min_length'] = 1;
			if ($captchaSettings['angle_min'] < 0) $captchaSettings['angle_min'] = 0;
			if ($captchaSettings['angle_max'] > 10) $captchaSettings['angle_max'] = 10;
			if ($captchaSettings['angle_max'] < $captchaSettings['angle_min']) $captchaSettings['angle_max'] = $captchaSettings['angle_min'];
			if ($captchaSettings['min_font_size'] < 10) $captchaSettings['min_font_size'] = 10;
			if ($captchaSettings['max_font_size'] < $captchaSettings['min_font_size']) $captchaSettings['max_font_size'] = $captchaSettings['min_font_size'];

			// Generate CAPTCHA code if not set in settings
			if (empty($captchaSettings['code']))
			{
				$captchaSettings['code'] = '';
				$length = mt_rand($captchaSettings['min_length'], $captchaSettings['max_length']);
				while (strlen($captchaSettings['code']) < $length)
				{
					$captchaSettings['code'] .= substr($charactersSettings[$captchaSettings['characters']], mt_rand() % (strlen($charactersSettings[$captchaSettings['characters']])), 1);
				}
			}

			return array(
				'code'		=> $captchaSettings['code'],
				'settings'	=> serialize($captchaSettings)
			);
		}

		/**
		 * Get the captcha base64 encoded image
		 *
		 * @param string $captchaConfig serialized configuration
		 *
		 * @return string
		 */
		public static function getCaptcha($captchaConfig)
		{
			$captchaSettings = unserialize($captchaConfig);
			if (!$captchaSettings)
			{
				return '';
			}

			// Select background randomly and get info
			$background = $captchaSettings['backgrounds'][mt_rand(0, count($captchaSettings['backgrounds']) - 1)];
			list($bgWidth, $bgHeight, $bgType, $bgAttr) = getimagesize($background);

			// Init captcha image
			$captcha = imagecreatefrompng($background);

			// Set captcha code color
			$captchaColor = self::hex2rgb($captchaSettings['color']);
			$captchaColor = imagecolorallocate($captcha, $captchaColor['r'], $captchaColor['g'], $captchaColor['b']);

			// Determine text angle
			$angle = mt_rand($captchaSettings['angle_min'], $captchaSettings['angle_max']) * (mt_rand(0, 1) == 1 ? -1 : 1);

			// Select font randomly
			$font = $captchaSettings['fonts'][mt_rand(0, count($captchaSettings['fonts']) - 1)];

			// Check font usage
			$useFont = ($font !== '' && file_exists($font) && function_exists('imagettftext'));

			// Draw lines
			if ($captchaSettings['lines'])
			{
				$backgroundLines = 5;
				// Set lines thickness
				imagesetthickness($captcha, ($useFont) ? 2 : 1);
				for ($i = 0; $i <= $backgroundLines; $i++)
				{
					// Set a random color and draw line
					$lineColor = self::hex2rgb(substr(str_shuffle("ABCDEF0123456789"), 0, 6));
					$lineColor = imagecolorallocate($captcha, $lineColor['r'], $lineColor['g'], $lineColor['b']);
					imageline($captcha, rand(1, $bgWidth - 25), rand(1, $bgHeight), rand(1, $bgWidth + 25), rand(1, $bgHeight), $lineColor);
				}
				for ($i = 0; $i <= $backgroundLines; $i++)
				{
					// Draw line with the captcha color
					imageline($captcha, rand(1, $bgWidth - 25), rand(1, $bgHeight), rand(1, $bgWidth + 25), rand(1, $bgHeight), $captchaColor);
				}
			}

			// Determine shadow color
			if ($captchaSettings['shadow'])
			{
				$shadowColor = self::hex2rgb($captchaSettings['shadow_color']);
				$shadowColor = imagecolorallocate($captcha, $shadowColor['r'], $shadowColor['g'], $shadowColor['b']);
			}

			if ($useFont === FALSE)
			{
				// Set the font size.
				$fontSize = 5;
				// Determine text position
				$textPosX = mt_rand($fontSize, $fontSize * 10);
				$textPosY = mt_rand($fontSize, $fontSize * 10);

				for ($i = 0; $i < strlen($captchaSettings['code']); $i++)
				{
					// Draw shadow
					if ($captchaSettings['shadow'])
					{
						imagestring($captcha, $fontSize, $textPosX - 1, $textPosY + 1, $captchaSettings['code'][$i], $shadowColor);
						imagestring($captcha, $fontSize, $textPosX + 1, $textPosY - 1, $captchaSettings['code'][$i], $shadowColor);
					}
					// Draw text
					imagestring($captcha, $fontSize, $textPosX, $textPosY, $captchaSettings['code'][$i], $captchaColor);

					$textPosX += ($fontSize * 3);
				}
			}
			else
			{
				// Set the font size.
				$fontSize		= mt_rand($captchaSettings['min_font_size'], $captchaSettings['max_font_size']);
				$textBoxSize	= imagettfbbox($fontSize, $angle, $font, $captchaSettings['code']);

				// Determine text position
				$boxWidth		= abs($textBoxSize[6] - $textBoxSize[2]);
				$boxHeight		= abs($textBoxSize[5] - $textBoxSize[1]);
				$textPosXMin	= 0;
				$textPosXMax	= ($bgWidth) - ($boxWidth);
				$textPosX		= mt_rand($textPosXMin, $textPosXMax);
				$textPosYMin	= $boxHeight;
				$textPosYMax	= ($bgHeight) - ($boxHeight / 2);
				if ($textPosYMin > $textPosYMax)
				{
					$tempTextPosY	= $textPosYMin;
					$textPosYMin	= $textPosYMax;
					$textPosYMax	= $tempTextPosY;
				}
				$textPosY = mt_rand((int) $textPosYMin, (int) $textPosYMax);

				// Draw shadow
				if ($captchaSettings['shadow'])
				{
					imagettftext($captcha, $fontSize, $angle, $textPosX + $captchaSettings['shadow_offset_x'], $textPosY + $captchaSettings['shadow_offset_y'], $shadowColor, $font, $captchaSettings['code']);
				}

				// Draw text
				imagettftext($captcha, $fontSize, $angle, $textPosX, $textPosY, $captchaColor, $font, $captchaSettings['code']);
			}

			// Output image
			ob_start();
			imagepng($captcha);
			$imagedata = ob_get_clean();
			imagedestroy($captcha);

			return base64_encode($imagedata);
		}

		/**
		 * Return RGB value from hexadecimal color code
		 *
		 * @param string $hexCode
		 * @param bool $returnString
		 * @param string $separator
		 *
		 * @return array
		 */
		public static function hex2rgb($hexCode, $returnString = false, $separator = ',')
		{
			$hexCode = preg_replace("/[^0-9A-Fa-f]/", '', $hexCode); // Gets a proper hex string
			$rgbArray = array();
			if (strlen($hexCode) == 6)
			{
				$colorVal = hexdec($hexCode);
				$rgbArray['r'] = 0xFF & ($colorVal >> 0x10);
				$rgbArray['g'] = 0xFF & ($colorVal >> 0x8);
				$rgbArray['b'] = 0xFF & $colorVal;
			}
			elseif (strlen($hexCode) == 3)
			{
				$rgbArray['r'] = hexdec(str_repeat(substr($hexCode, 0, 1), 2));
				$rgbArray['g'] = hexdec(str_repeat(substr($hexCode, 1, 1), 2));
				$rgbArray['b'] = hexdec(str_repeat(substr($hexCode, 2, 1), 2));
			}
			else
			{
				return false;
			}

			return $returnString ? implode($separator, $rgbArray) : $rgbArray;
		}
	}
}
