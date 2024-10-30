<?php

/** 
 * Kaya Login Captcha - Captcha Forms Class
 * Set Captcha Forms.
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

if (!class_exists('WPKLC_Forms_Captcha'))
{
	class WPKLC_Forms_Captcha
	{
		/**
		 * return Array of fields strucutre
		 */
		private static function get_fields()
		{
			//	Fields setting
			$fields = array(
				array(
					'_meta_type'		=> 'checkbox',
					'_meta_slug'		=> 'captcha_on_login_form',
					'_label'			=> __('Enable Captcha on the login form (Dashboard and WooCommerce)', WPKLC_TEXT_DOMAIN),
					'_value_default'	=> 1
				),
				array(
					'_meta_type'		=> 'checkbox',
					'_meta_slug'		=> 'captcha_on_lost_password_form',
					'_label'			=> __('Enable Captcha on the lost-password form (Dashboard and WooCommerce)', WPKLC_TEXT_DOMAIN),
					'_value_default'	=> 0
				),
				array(
					'_meta_type'		=> 'checkbox',
					'_meta_slug'		=> 'captcha_on_register_form',
					'_label'			=> __('Enable Captcha on the register form (Dashboard and WooCommerce)', WPKLC_TEXT_DOMAIN),
					'_value_default'	=> 0
				),
				array(
					'_meta_type'		=> 'select',
					'_meta_slug'		=> 'captcha_code_length',
					'_label'			=> __('Captcha length:', WPKLC_TEXT_DOMAIN),
					'_description'		=> __('Length of the displayed captcha code.', WPKLC_TEXT_DOMAIN),
					'_options'			=> array(
						'3' => '3',
						'4' => '4',
						'5' => '5'
					),
					'_value_default'	=> '3'
				),
				array(
					'_meta_type'		=> 'select',
					'_meta_slug'		=> 'captcha_code_format',
					'_label'			=> __('Captcha format:', WPKLC_TEXT_DOMAIN),
					'_description'		=> __('Format of the displayed captcha code: numeric, alphabetic or alphanumeric.', WPKLC_TEXT_DOMAIN),
					'_options'			=> array(
						'num'		=> __('Numeric', WPKLC_TEXT_DOMAIN),
						'alpha'		=> __('Alphabetic', WPKLC_TEXT_DOMAIN),
						'alphanum'	=> __('Alphanumeric', WPKLC_TEXT_DOMAIN)
					),
					'_value_default'	=> 'num'
				),
				array(
					'_meta_type'		=> 'checkbox',
					'_meta_slug'		=> 'captcha_lines',
					'_label'			=> __('Enable random lines in the background of the Captcha', WPKLC_TEXT_DOMAIN),
					'_value_default'	=> 1
				),
				array(
					'_meta_type'		=> 'select',
					'_meta_slug'		=> 'captcha_blocked_request_http_status',
					'_label'			=> __('Blocked request HTTP status:', WPKLC_TEXT_DOMAIN),
					'_description'		=> __('Choose the HTTP status code when a request is blocked by a captcha error.', WPKLC_TEXT_DOMAIN),
					'_options'			=> array(
						'none'			=> __('No change (code: 200)', WPKLC_TEXT_DOMAIN),
						'unauthorized'	=> __('Unauthorized (code: 401)', WPKLC_TEXT_DOMAIN),
						'forbidden'		=> __('Forbidden (code: 403)', WPKLC_TEXT_DOMAIN),
					),
					'_value_default'	=> 'none'
				),
				array(
					'_meta_type'		=> 'checkbox',
					'_meta_slug'		=> 'wordpress_xml_rpc_disabled',
					'_label'			=> __('Disable the XML-RPC WordPress API', WPKLC_TEXT_DOMAIN),
					'_description'		=> __('Bots can try to login with the XML-RPC feature. Warning: Some plugins need this, like the Jetpack plugin.', WPKLC_TEXT_DOMAIN),
					'_value_default'	=> 0
				)
			);

			return $fields;
		}

		/**
		 * return Array of fields default values
		 */
		public static function get_fields_default_value()
		{
			$fields_default = array();
			//	get form fields array
			$fields_Tab = self::get_fields();
			//	get default values
			foreach ($fields_Tab as $field)
			{
				$fields_default[$field['_meta_slug']] = $field['_value_default'];
			}

			return $fields_default;
		}

		/**
		 * return Array field by slug
		 */
		private static function get_field_by_slug($p_slug)
		{
			//	get form fields array
			$fields_Tab = self::get_fields();
			//	get field by slug
			foreach ($fields_Tab as $field)
			{
				if ($field['_meta_slug'] == $p_slug)
				{
					return $field;
				}
			}

			return false;
		}

		/**
		 * return options Object
		 */
		private static function get_form_options()
		{
			$options_Obj = new stdClass();
			//	Options setting
			$options_Tab = array(
				// '_form_onchange' => '',
			);
			//	switch from array to Object
			foreach ($options_Tab as $attr => $val)
			{
				$options_Obj->$attr = $val;
			}

			return $options_Obj;
		}

		/**
		 * return Array accepted options
		 */
		private static function get_accepted_options($p_slug)
		{
			$field = self::get_field_by_slug($p_slug);

			if (isset($field['_options'])) return $field['_options'];

			return false;
		}

		/**
		 * Display HTML form fields
		 */
		public static function display_form_fields($p_fields_val = false, $p_function_ids = false)
		{
			//	get form fields
			$form_fields = self::get_form_fields($p_fields_val, $p_function_ids);
			//	display form fields
			$o = WPKLC_Forms::display_form($form_fields, false);

			return $o;
		}

		/**
		 * Display HTML form fields with form options
		 */
		public static function display_form_fields_options($p_fields_val = false, $p_function_ids = false)
		{
			//	get form fields
			$form_fields = self::get_form_fields($p_fields_val, $p_function_ids);
			//	get form options
			$form_options = self::get_form_options();
			//	display form fields
			$o = WPKLC_Forms::display_form($form_fields, $form_options);

			return $o;
		}

		/**
		 * return Array with fields Object
		 */
		private static function get_form_fields($p_fields_val = false, $p_function_ids = false)
		{
			$fields_Obj = array();
			//	get form fields array
			$fields_Tab = self::get_fields();

			foreach ($fields_Tab as $field)
			{
				$form_field = new stdClass();
				//	switch from array to Object
				foreach ($field as $attr => $val)
				{
					$form_field->$attr = $val;
				}
				//	get field value
				if (empty($p_fields_val))
				{
					$field_val = false;
				}
				elseif (is_array($p_fields_val))
				{
					$field_val = isset($p_fields_val[$form_field->_meta_slug]) ? $p_fields_val[$form_field->_meta_slug] : false;
				}
				elseif (is_object($p_fields_val))
				{
					$field_val = isset($p_fields_val->{$form_field->_meta_slug}) ? $p_fields_val->{$form_field->_meta_slug} : false;
				}
				//	validate field value
				$form_field->_value = self::validate_field($form_field->_meta_slug, $field_val, $form_field->_value_default);
				//	get callable functions to set ids values
				if (!$p_function_ids)
				{
					$function_ids_id	= false;
					$function_ids_name	= false;
				}
				else
				{
					$function_ids_id	= $p_function_ids['_id'];
					$function_ids_name	= $p_function_ids['_name'];
				}
				//	set ids values : field id and name
				$form_field->_id	= self::set_field_id($form_field->_meta_slug, $function_ids_id);
				$form_field->_name	= self::set_field_name($form_field->_meta_slug, $function_ids_name);
				//	Add object to the list
				$fields_Obj[] = $form_field;
			}

			return $fields_Obj;
		}

		/**
		 * Set one field id
		 */
		private static function set_field_id($p_meta_slug, $p_function_id = false)
		{
			$field_id = '';
			if (!empty($p_function_id) && method_exists($p_function_id[0], $p_function_id[1]))
			{
				//	get id through callable function
				$field_id = call_user_func(array($p_function_id[0], $p_function_id[1]), $p_meta_slug);
			}
			else
			{
				//	generate unique field id
				$field_unique_id = rand(0, 99) . uniqid() . rand(0, 99);
				$field_id = 'wpklc_data_' . $p_meta_slug . '_' . $field_unique_id;
			}

			return $field_id;
		}

		/**
		 * Set one field name
		 */
		private static function set_field_name($p_meta_slug, $p_function_name = false)
		{
			$field_name = '';
			if (!empty($p_function_name) && method_exists($p_function_name[0], $p_function_name[1]))
			{
				//	get name through callable function
				$field_name = call_user_func(array($p_function_name[0], $p_function_name[1]), $p_meta_slug);
			}
			else
			{
				//	generate field name
				$field_name = 'wpklc[data][' . $p_meta_slug . ']';
			}

			return $field_name;
		}

		/**
		 * validate all fields values
		 */
		public static function validate_fields($p_fields_values)
		{
			$fields_valid = array();
			//	get form fields array
			$fields_Tab = self::get_fields();

			foreach ($fields_Tab as $field)
			{
				$_meta_slug		= $field['_meta_slug'];
				$_value_default	= $field['_value_default'];
				//	validate field value
				$fields_valid[$_meta_slug] = self::validate_field($_meta_slug, (isset($p_fields_values[$_meta_slug]) ? $p_fields_values[$_meta_slug] : NULL), $_value_default);
			}

			return $fields_valid;
		}

		/**
		 * validate one field value
		 */
		private static function validate_field($p_field_slug, $p_field_value, $p_field_value_default)
		{
			$field_valid = '';

			if ($p_field_slug == 'captcha_on_login_form')
			{
				//	captcha_on_login_form is checkbox, check is 1 or 0
				$field_valid = (!empty($p_field_value) && $p_field_value == '1') ? 1 : $p_field_value_default;
				$field_valid = (empty($p_field_value) && $p_field_value == '0') ? 0 : $p_field_value_default;
			}
			elseif ($p_field_slug == 'captcha_on_lost_password_form')
			{
				//	captcha_on_lost_password_form is checkbox, check is 1 or 0
				$field_valid = (!empty($p_field_value) && $p_field_value == '1') ? 1 : $p_field_value_default;
				$field_valid = (empty($p_field_value) && $p_field_value == '0') ? 0 : $field_valid;
			}
			elseif ($p_field_slug == 'captcha_on_register_form')
			{
				//	captcha_on_register_form is checkbox, check is 1 or 0
				$field_valid = (!empty($p_field_value) && $p_field_value == '1') ? 1 : $p_field_value_default;
				$field_valid = (empty($p_field_value) && $p_field_value == '0') ? 0 : $field_valid;
			}
			elseif ($p_field_slug == 'captcha_code_length')
			{
				//	captcha_code_length is string and key
				$accepted_options = self::get_accepted_options($p_field_slug);
				$field_valid = (!empty($p_field_value) && is_string($p_field_value) && array_key_exists($p_field_value, $accepted_options)) ? $p_field_value : $p_field_value_default;
				$field_valid = sanitize_text_field($field_valid);
			}
			elseif ($p_field_slug == 'captcha_code_format')
			{
				//	captcha_code_format is string and key
				$accepted_options = self::get_accepted_options($p_field_slug);
				$field_valid = (!empty($p_field_value) && is_string($p_field_value) && array_key_exists($p_field_value, $accepted_options)) ? $p_field_value : $p_field_value_default;
				$field_valid = sanitize_text_field($field_valid);
			}
			elseif ($p_field_slug == 'captcha_lines')
			{
				//	captcha_lines is checkbox, check is 1 or 0
				$field_valid = (!empty($p_field_value) && $p_field_value == '1') ? 1 : $p_field_value_default;
				$field_valid = (empty($p_field_value) && $p_field_value == '0') ? 0 : $field_valid;
			}
			elseif ($p_field_slug == 'captcha_blocked_request_http_status')
			{
				//	captcha_blocked_request_http_status is string and key
				$accepted_options = self::get_accepted_options($p_field_slug);
				$field_valid = (!empty($p_field_value) && is_string($p_field_value) && array_key_exists($p_field_value, $accepted_options)) ? $p_field_value : $p_field_value_default;
				$field_valid = sanitize_text_field($field_valid);
			}
			elseif ($p_field_slug == 'wordpress_xml_rpc_disabled')
			{
				//	wordpress_xml_rpc_disabled is checkbox, check is 1 or 0
				$field_valid = (!empty($p_field_value) && $p_field_value == '1') ? 1 : $p_field_value_default;
				$field_valid = (empty($p_field_value) && $p_field_value == '0') ? 0 : $field_valid;
			}

			return $field_valid;
		}
	}
}
