<?php

/** 
 * Kaya Login Captcha - Login Captcha CRUD Class
 * Loads, Saves and Reset Login Captcha settings 
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

if (!class_exists('WPKLC_login_captcha'))
{
	class WPKLC_login_captcha
	{
		public $id;
		public $data;
		public $new;
		private $saved;
		private $deleted;

		/**
		 * Creates Login Captcha Object. 
		 * Preloads with all Login Captcha through 'all'
		 *
		 * @param string $action : 'all' will load all Login Captcha records into $login_captcha
		 * @param string $action : 'new' will load new Login Captcha with defaults
		 */
		public function __construct($action = 'all')
		{
			if ($action == 'all') $this->load_all();
			elseif ($action == 'new') $this->load_new();
			elseif ($action == 'update') $this->update();
			elseif ($action == 'delete') $this->destroy();
		}

		/**
		 * Defaults data
		 *
		 * @return array
		 */
		private function default_new()
		{
			if (!current_user_can('manage_options'))
			{
				wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
			}

			// Include the forms class.
			require_once(WPKLC_PLUGIN_PATH . 'lib/class.forms.php');
			// Include the Captcha forms class
			require_once(WPKLC_PLUGIN_PATH . 'lib/class.forms_captcha.php');

			$new_lc_settings = array();
			$new_lc_settings['new']		= true;
			$new_lc_settings['id']		= WPKLC_LOGIN_CAPTCHA_SETTINGS_DB;
			$new_lc_settings['data']	= array();
			$new_lc_settings['data']	= WPKLC_Forms_Captcha::get_fields_default_value();

			return $new_lc_settings;
		}

		/**
		 * Loads defaults data into class attributes
		 */
		private function load_new()
		{
			if (!current_user_can('manage_options'))
			{
				wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
			}

			$login_captcha = stripslashes_deep($this->default_new());
			if (empty($login_captcha)) return '';

			$this->create($login_captcha);
		}

		/**
		 * Creates new Login Captcha record
		 */
		private function create($login_captcha)
		{
			if (!current_user_can('manage_options'))
			{
				wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
			}

			$this->saved = false;
			if (empty($login_captcha)) return '';

			if ($login_captcha['new'])
			{
				$this->saved = update_option(WPKLC_LOGIN_CAPTCHA_SETTINGS_DB, $this->prepare($login_captcha), false);
			}
		}

		/**
		 * Updates Login Captcha record and call update functions. 
		 * Set $this->saved to true on success.
		 */
		private function update()
		{
			if (!current_user_can('manage_options'))
			{
				wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
			}

			$this->saved = false;
			if (empty($_POST) || empty($_POST['wpklc']) || !is_array($_POST['wpklc']) || empty($_POST['wpklc_crud_edit'])) return '';

			if (wp_verify_nonce(sanitize_key($_POST['wpklc_crud_edit']), 'wpklc_crud_' . get_current_user_id()))
			{
				$login_captcha = $this->recursive_sanitize_text_field($_POST['wpklc']);
				if (empty($login_captcha) || !isset($login_captcha['id'])) return '';

				$this->saved = update_option(WPKLC_LOGIN_CAPTCHA_SETTINGS_DB, $this->prepare($login_captcha), false);
			}
			WPKLC_Admin_Dashboard::addAdminNotice(__('Saving captcha settings', WPKLC_TEXT_DOMAIN), $this->saved);
		}

		/**
		 * Delete Login Captcha record from $_POST
		 * Set $this->deleted to true on success.
		 */
		private function destroy()
		{
			if (!current_user_can('manage_options'))
			{
				wp_die('<p>' . esc_html__('You do not have sufficient permissions.') . '</p>');
			}

			if (empty($_POST) || empty($_POST['wpklc']) || !is_array($_POST['wpklc']) || empty($_POST['wpklc_crud_delete'])) return '';

			if (wp_verify_nonce(sanitize_key($_POST['wpklc_crud_delete']), 'wpklc_crud_' . get_current_user_id()))
			{
				$login_captcha = $this->recursive_sanitize_text_field($_POST['wpklc']);
				if (empty($login_captcha) || !isset($login_captcha['id'])) return '';

				$this->deleted = update_option(WPKLC_LOGIN_CAPTCHA_SETTINGS_DB, '', false);
			}
			WPKLC_Admin_Dashboard::addAdminNotice(__('Reset captcha settings', WPKLC_TEXT_DOMAIN), $this->deleted);
		}

		/**
		 * Converts array input values into values need for database storage
		 *
		 * @param array $q : raw Login Captcha associative array from $_POST
		 *
		 * @return array : array prepared for database insertion
		 */
		private function prepare($q)
		{
			if (empty($q) || !is_array($q) || empty($q['data']) || empty($q['id'])) return '';

			// Include the forms class.
			require_once(WPKLC_PLUGIN_PATH . 'lib/class.forms.php');
			// Include the Captcha forms class
			require_once(WPKLC_PLUGIN_PATH . 'lib/class.forms_captcha.php');

			$attributes = WPKLC_Forms_Captcha::validate_fields($q['data']);

			$result = base64_encode(serialize($attributes));

			return $result;
		}

		/**
		 * Load all Login Captcha
		 * Stripslashes on DB return values.
		 */
		private function load_all()
		{
			$settingsData = get_option(WPKLC_LOGIN_CAPTCHA_SETTINGS_DB);

			if (!empty($settingsData))
			{
				$login_captcha = stripslashes_deep($settingsData);
				$this->id = WPKLC_LOGIN_CAPTCHA_SETTINGS_DB;
				$data_attributes = stripslashes_deep(unserialize(base64_decode($login_captcha)));

				if (empty($data_attributes) || !is_array($data_attributes)) return '';

				if (!isset($this->data))
					$this->data = new stdClass();

				foreach ($data_attributes as $i_attr => $i_val)
				{
					$attribute = sanitize_key($i_attr);
					$value = sanitize_text_field($i_val);

					$this->data->{$attribute} = $value;
				}
				$this->new = false;
			}
		}

		/**
		 * Recursive sanitation for an array
		 * Allows an array to get sanitized properly, sanitizing each value in a key - value pair.
		 *
		 * @param $array
		 *
		 * @return mixed
		 */
		private function recursive_sanitize_text_field($p_array)
		{
			foreach ($p_array as $i_key => &$i_value)
			{
				if (is_array($i_value))
				{
					$i_value = $this->recursive_sanitize_text_field($i_value);
				}
				else
				{
					$i_value = sanitize_text_field($i_value);
				}
			}

			return $p_array;
		}
	}
}
