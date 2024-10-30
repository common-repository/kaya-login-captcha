<?php

/** 
 *	Kaya Login Captcha - Forms Class
 *  Set and Display Forms 
 *
 *	$p_field = field object with attributes :
 *
 *	'_meta_type' = input type (text, email, number, checkbox, select)
 *	'_meta_slug' = unique field id
 *	'_label' = main field text
 *	'_description' = field description, help text
 *	'_class' = css class added to the field
 *	'_value_default' = default field value
 *	'_options' = array('key' => 'value') for multiple choices (select)
 *
 *
 *	$p_options = form options :
 *
 *	'_form_onchange' = JavaScript function called on field value change
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

if (!class_exists('WPKLC_Forms'))
{
	class WPKLC_Forms
	{
		/**
		 * Display HTML form with all fields
		 */
		public static function display_form($p_fields, $p_options)
		{
			if (empty($p_fields)) return '';

			$o  = '';
			foreach ($p_fields as $field)
			{
				$o .= self::do_form_field_line($field, $p_options);
			}

			return $o;
		}

		/**
		 * Display one field line
		 */
		private static function do_form_field_line($p_field, $p_options)
		{
			if (empty($p_field) || empty($p_field->_meta_type)) return '';

			$field_function = 'do_form_' . $p_field->_meta_type;

			$o = '';
			if (method_exists(__CLASS__, $field_function))
			{
				$o .= call_user_func_array(array(__CLASS__, $field_function), array($p_field, $p_options));
				$o .= '<br />';
			}
			else
			{
				$o .= '<br />' . esc_html__('No layout found for field type:', WPKLC_TEXT_DOMAIN) . ' ' . esc_html($p_field->_meta_type) . '<br />';
			}

			return $o;
		}

		/**
		 * Display form text input
		 */
		private static function do_form_text($p_field, $p_options)
		{
			if (empty($p_field)) return '';

			$o = self::get_field_label($p_field);
			$o .= self::get_field_text($p_field, $p_options);
			$o .= self::get_field_description($p_field);

			return $o;
		}

		/**
		 * Display form checkbox input
		 */
		private static function do_form_checkbox($p_field, $p_options)
		{
			if (empty($p_field)) return '';

			$o = self::get_field_checkbox($p_field, $p_options);
			$o .= self::get_field_description($p_field);

			return $o;
		}

		/**
		 * Display form select input
		 */
		private static function do_form_select($p_field, $p_options)
		{
			if (empty($p_field)) return '';

			$o = self::get_field_label($p_field);
			$o .= self::get_field_select($p_field, $p_options);
			$o .= self::get_field_description($p_field);

			return $o;
		}

		/**
		 *  Output field: label
		 */
		private static function get_field_label($p_field)
		{
			if (empty($p_field) || empty($p_field->_id) || empty($p_field->_label)) return '';

			$field_id = (!empty($p_field->_id)) ? esc_attr($p_field->_id) : '';
			$field_label = (!empty($p_field->_label)) ? esc_html($p_field->_label) : '';

			$o = '<label for="' . $field_id . '"><strong>' . $field_label . '</strong></label><br />';

			return $o;
		}

		/**
		 *  Output field: text
		 */
		private static function get_field_text($p_field, $p_options)
		{
			if (empty($p_field) || empty($p_field->_id) || empty($p_field->_name)) return '';

			$field_value = (!empty($p_field->_value)) ? esc_attr($p_field->_value) : '';
			$field_class = (!empty($p_field->_class)) ? esc_attr($p_field->_class) : '';
			$field_id = (!empty($p_field->_id)) ? esc_attr($p_field->_id) : '';
			$field_name = (!empty($p_field->_name)) ? esc_attr($p_field->_name) : '';
			$field_meta_slug = (!empty($p_field->_meta_slug)) ? esc_attr($p_field->_meta_slug) : '';
			$option_onchange = (isset($p_options) && !empty($p_options->_form_onchange)) ? esc_attr($p_options->_form_onchange) : '';

			$o = '<input id="' . $field_id . '"';
			$o .= ' name="' . $field_name . '"';
			$o .= ' type="text" value="' . $field_value . '" ';
			if (!empty($field_class)) $o .= ' class="' . $field_class . '"';
			if (!empty($option_onchange)) $o .= ' onkeyup="' . $option_onchange . '(this, \'' . $field_meta_slug . '\');"';
			$o .= '/><br />';

			return $o;
		}

		/**
		 *  Output field: checkbox
		 */
		private static function get_field_checkbox($p_field, $p_options)
		{
			if (empty($p_field) || empty($p_field->_id) || empty($p_field->_name) || empty($p_field->_label)) return '';

			$field_checked = (!empty($p_field->_value)) ? true : false;
			$field_class = (!empty($p_field->_class)) ? esc_attr($p_field->_class) : '';
			$field_id = (!empty($p_field->_id)) ? esc_attr($p_field->_id) : '';
			$field_name = (!empty($p_field->_name)) ? esc_attr($p_field->_name) : '';
			$field_label = (!empty($p_field->_label)) ? esc_html($p_field->_label) : '';
			$field_meta_slug = (!empty($p_field->_meta_slug)) ? esc_attr($p_field->_meta_slug) : '';
			$option_onchange = (isset($p_options) && !empty($p_options->_form_onchange)) ? esc_attr($p_options->_form_onchange) : '';

			$o = '<label for="' . $field_id . '">';
			$o .= '<input type="hidden" name="' . $field_name . '" value="0">';
			$o .= '<input id="' . $field_id . '"';
			$o .= ' name="' . $field_name . '"';
			$o .= ' value="1" type="checkbox"';
			if ($field_checked) $o .= ' checked';
			if (!empty($field_class)) $o .= ' class="' . $field_class . '"';
			if (!empty($option_onchange)) $o .= ' onclick="' . $option_onchange . '(this, \'' . $field_meta_slug . '\');"';
			$o .= '>' . $field_label;
			$o .= '</label><br />';

			return $o;
		}

		/**
		 *  Output field: select
		 */
		private static function get_field_select($p_field, $p_options)
		{
			if (empty($p_field) || empty($p_field->_id) || empty($p_field->_name)) return '';

			$field_value = (!empty($p_field->_value)) ? esc_attr($p_field->_value) : '';
			$field_class = (!empty($p_field->_class)) ? esc_attr($p_field->_class) : '';
			$field_id = (!empty($p_field->_id)) ? esc_attr($p_field->_id) : '';
			$field_name = (!empty($p_field->_name)) ? esc_attr($p_field->_name) : '';
			$field_meta_slug = (!empty($p_field->_meta_slug)) ? esc_attr($p_field->_meta_slug) : '';
			$option_onchange = (isset($p_options) && !empty($p_options->_form_onchange)) ? esc_attr($p_options->_form_onchange) : '';

			$o = '<select id="' . $field_id . '"';
			$o .= ' name="' . $field_name . '"';
			if (!empty($option_onchange)) $o .= ' onchange="' . $option_onchange . '(this, \'' . $field_meta_slug . '\');"';
			if (!empty($field_class)) $o .= ' class="' . $field_class . '"';
			$o .= '>';
			foreach ($p_field->_options as $value => $display)
			{
				$o .= '<option value="' . esc_attr($value) . '" ';
				if ($field_value == $value) $o .= 'selected="selected"';
				$o .= '>' . esc_html($display) . '</option>';
			}
			$o .= '</select><br />';

			return $o;
		}

		/**
		 *  Output field: description
		 */
		private static function get_field_description($p_field)
		{
			if (empty($p_field) || empty($p_field->_description)) return '';

			$field_description = (!empty($p_field->_description)) ? esc_html($p_field->_description) : '';
			$o = '<small>' . $field_description . '</small><br />';

			return $o;
		}
	}
}
