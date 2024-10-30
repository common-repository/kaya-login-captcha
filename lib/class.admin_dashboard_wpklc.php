<?php

/** 
 * Kaya Login Captcha - Admin Dashboard Class
 * Manages Kaya Login Captcha admin page.
 */

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly
}

if (!class_exists('WPKLC_Admin_Dashboard'))
{
	class WPKLC_Admin_Dashboard
	{
		/**
		 * Menu Page Slug
		 */
		public static $_pageSlug = 'wpklc-kaya-login-captcha-admin-settings-page';

		/**
		 * Notice Option Name
		 */
		public static $_noticeOptionName = 'wpklc_kaya_login_captcha_admin_notices';

		/**
		 * Main Initialisation
		 * Adds admin menu, enqueue scripts, manage post requests and admin notices.
		 */
		public static function init()
		{
			add_action('admin_menu', array(__CLASS__, 'addAdminMenuPage'));
			add_action('admin_enqueue_scripts', array(__CLASS__, 'addAdminCssJs'));
			add_action('admin_post_wpklc_login_captcha_form', array(__CLASS__, 'doAdminPostRequests'));
			add_action('admin_notices', array(__CLASS__, 'doAdminNotices'));
		}

		/**
		 * Adds admin menu
		 * Adds a submenu for Kaya Login Captcha admin page.
		 */
		public static function addAdminMenuPage()
		{
			// add plugin features page
			add_submenu_page(
				WP_KayaStudio_Plugins_Admin_Dashboard::$_pageSlug,
				esc_html__('Login Captcha', WPKLC_TEXT_DOMAIN),
				esc_html__('Login Captcha', WPKLC_TEXT_DOMAIN),
				'manage_options',
				self::$_pageSlug,
				array(__CLASS__, 'doAdminPage')
			);
		}

		/**
		 * Return the plugin informations to be added in Plugins List
		 *
		 * @return array
		 */
		public static function getPluginInfos()
		{
			return array(
				'title'		=> sanitize_text_field('Kaya Login Captcha'),
				'page_name'	=> sanitize_text_field(__('Login Captcha settings', WPKLC_TEXT_DOMAIN)),
				'page_slug'	=> self::$_pageSlug,
				'page_text'	=> sanitize_text_field(__('Adds a simple captcha on the login form, the register form and the lost-password form.', WPKLC_TEXT_DOMAIN)),
			);
		}

		/**
		 * Displays admin page
		 * Includes the page and display it.
		 */
		public static function doAdminPage()
		{
			if (is_file(WPKLC_PLUGIN_PATH . 'includes/wpklc-admin-page.php'))
			{
				include_once WPKLC_PLUGIN_PATH . 'includes/wpklc-admin-page.php';
				wpklc_admin_doOptionPage();
			}
		}

		/**
		 * Adds admin menu styles and scripts
		 * Registers and enqueue styles and scripts for Kaya Login Captcha admin page.
		 *
		 * @param int $hook Hook suffix for the current admin page.
		 */
		public static function addAdminCssJs($hook)
		{
			if (isset($hook) && !empty($hook) && get_plugin_page_hookname(self::$_pageSlug, WP_KayaStudio_Plugins_Admin_Dashboard::$_pageSlug) === $hook)
			{
				wp_register_style('kayastudio_wp_admin_css', WPKLC_PLUGIN_URL . 'css/kayastudio-admin-page-pkg.min.css', false, '1.0.0');
				wp_enqueue_style('kayastudio_wp_admin_css');
			}
		}

		/**
		 * Manages admin page requests actions
		 * Edit or reset the WPKLC_login_captcha object and redirect.
		 *
		 * @return bool
		 */
		public static function doAdminPostRequests()
		{
			if (empty($_POST) || empty($_POST['wpklc_action']) || empty($_POST['wpklc']))
			{
				return false;
			}

			if (!current_user_can('manage_options'))
			{
				return false;
			}

			$wpklc_target = sanitize_key($_POST['wpklc_target']);

			if (!empty($wpklc_target) && ('login_captcha' === $wpklc_target))
			{
				// include the WPKLC_login_captcha class
				require_once(WPKLC_PLUGIN_PATH . 'lib/class.crud_login_captcha.php');

				$wpklc_action = sanitize_key($_POST['wpklc_action']);
				if ('edit' === $wpklc_action)
				{
					// init WPKLC_login_captcha object for update
					$kayaLoginCaptcha = new WPKLC_login_captcha('update');
				}
				elseif ('delete' === $wpklc_action)
				{
					// init WPKLC_login_captcha object for reset
					$kayaLoginCaptcha = new WPKLC_login_captcha('delete');
					// reset with default settings
					require_once(WPKLC_PLUGIN_PATH . 'lib/functions-database.php');
					wpklc_database_defaultValues();
				}

				// set admin url query with the page and the action message
				$adminUrlQuery = array(
					'page'		=> self::$_pageSlug,
					'message'	=> '1'
				);
				// set the full admin url for redirection
				$redirectURL = esc_url_raw(admin_url('admin.php?' . http_build_query($adminUrlQuery)));

				if (wp_redirect($redirectURL))
				{
					return true;
				}
			}

			if (!empty($wpklc_target) && ('login_captcha_statistics' === $wpklc_target))
			{
				$wpklc_action = sanitize_key($_POST['wpklc_action']);
				if ('delete_statistics' === $wpklc_action)
				{
					// reset all the captcha statistics
					require_once(WPKLC_PLUGIN_PATH . 'lib/functions-database.php');
					$deleted = wpklc_database_resetStatistics();
					WPKLC_Admin_Dashboard::addAdminNotice(__('Reset captcha statistics', WPKLC_TEXT_DOMAIN), $deleted);
				}

				// set admin url query with the page and the action message
				$adminUrlQuery = array(
					'page'		=> self::$_pageSlug,
					'message'	=> '1'
				);
				// set the full admin url for redirection
				$redirectURL = esc_url_raw(admin_url('admin.php?' . http_build_query($adminUrlQuery)));

				if (wp_redirect($redirectURL))
				{
					return true;
				}
			}

			return false;
		}

		/**
		 * Adds notice to admin page.
		 *
		 * @param string	$p_message The notice message.
		 * @param boolean	$p_success Set this to TRUE for a success notice.
		 */
		public static function addAdminNotice($p_message = '', $p_success = false)
		{
			// get all notices
			$notices = get_option(self::$_noticeOptionName, array());
			// add the notice to the actual list
			array_push($notices, array(
				'message'	=> sanitize_text_field($p_message),
				'type'		=> (($p_success) ? '1' : '0')
			));
			// save notices
			update_option(self::$_noticeOptionName, $notices);
		}

		/**
		 * Displays admin page notices
		 * Prints admin screen notices about form requests.
		 */
		public static function doAdminNotices()
		{
			$currentScreen = get_current_screen();
			if (get_plugin_page_hookname(self::$_pageSlug, WP_KayaStudio_Plugins_Admin_Dashboard::$_pageSlug) !== $currentScreen->id)
			{
				return false;
			}

			// get all notices
			$notices = get_option(self::$_noticeOptionName, array());

			$output = '';
			foreach ($notices as $i_notice)
			{
				// get notice message
				$noticeMessage = $i_notice['message'];

				// set default error notice data
				$noticeClasses	= 'notice-error';
				$noticeTitle	= __('Error!', WPKLC_TEXT_DOMAIN);

				// set success notice data
				if ('1' === $i_notice['type'])
				{
					$noticeClasses	= 'notice-success';
					$noticeTitle	= __('Success!', WPKLC_TEXT_DOMAIN);
				}
				// set notice HTML structure
				$output .= '<div class="notice ' . esc_attr($noticeClasses) . ' is-dismissible">';
				$output .= '<p><b>' . esc_html($noticeTitle) . '</b><br />' . esc_html($noticeMessage) . '</p>';
				$output .= '</div>';
			}

			if (!empty($notices))
			{
				// display the notices
				echo $output;
				// delete notices to prevent other displaying
				delete_option(self::$_noticeOptionName, array());
			}
		}
	}
}
