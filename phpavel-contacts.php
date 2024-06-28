<?php
/**
Plugin Name: [ PHPavel Contact Page ]
Description: Страница с полями контактов
Author: PhilippovPavel
Author URI: https://philippovpavel.ru/
Version: 1.0.0
Requires at least: 5.9
Requires PHP: 8.1

License:
Released under the GPL license
http://www.gnu.org/copyleft/gpl.html
 **/

if (!defined('ABSPATH')) exit;

register_uninstall_hook(__FILE__, ['phpavelContactPage', 'uninstall']);

class phpavelContactPage
{
	public static function uninstall()
	{
		if ( !current_user_can('activate_plugins') ) return;

		$fields = self::fields;
		if ( !$fields ) return;

		foreach ($fields as $field) {
			$option_name = $field['id'];

			delete_option($option_name);
		}
	}

	private const fields = [
		[
			'id'       => 'contact_phone',
			'title'    => 'Телефон',
			'type'     => 'tel',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'contact_email',
			'title'    => 'Email',
			'type'     => 'email',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'contact_vk',
			'title'    => 'Ссылка VK',
			'type'     => 'url',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'contact_wa',
			'title'    => 'Ссылка WhatsApp',
			'type'     => 'url',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'contact_tg',
			'title'    => 'Ссылка Telegram',
			'type'     => 'url',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'contact_wb',
			'title'    => 'Ссылка Wildberries',
			'type'     => 'url',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'contact_address',
			'title'    => 'Адрес',
			'type'     => 'text',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'contact_map_xy',
			'title'    => 'Карта, координаты',
			'type'     => 'text',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'contact_timeline',
			'title'    => 'Время работы, описание',
			'type'     => 'textarea',
			'function' => 'sanitize_textarea_field'
		],
		[
			'id'       => 'contact_cf7',
			'title'    => 'Контактая форма',
			'type'     => 'select_cf7',
			'function' => 'sanitize_text_field'
		],
	];

	public function __construct()
	{
		add_action('admin_menu', [$this, 'contact_menu_page'], 5);
		add_action('admin_init', [$this, 'contacts_fields']);
		add_action('admin_enqueue_scripts', [$this, 'scripts_contact_page']);
	}

	function contact_menu_page()
	{
		add_submenu_page(
			'options-general.php',
			'Контакты',
			'Контакты',
			'manage_options',
			'contacts',
			[$this, 'contact_page_callback']
		);
	}

	function contact_page_callback()
	{
		echo '<div class="wrap">
			<h1>' . get_admin_page_title() . '</h1>
			<form method="post" action="options.php">';

		settings_fields('contacts_settings');
		do_settings_sections('contacts');
		submit_button();

		echo '</form>
		</div>';
	}

	function contacts_fields()
	{
		$fields = self::fields;

		add_settings_section('contacts_settings_section_id', '', '', 'contacts');

		foreach ($fields as $field) {
			register_setting(
				'contacts_settings',
				$field['id'],
				$field['function']
			);

			add_settings_field(
				$field['id'],
				$field['title'],
				[$this, $field['type'] . '_field'],
				'contacts',
				'contacts_settings_section_id',
				array(
					'label_for' => $field['id'],
					'class'     => $field['id'] . '-item',
					'name'      => $field['id'],
				)
			);
		}
	}

	// Sanitize functions
	function text_field($args)
	{
		$value = get_option($args['name']);

		printf(
			'<input type="text" id="%s" name="%s" value="%s" class="regular-text" />',
			esc_attr($args['name']),
			esc_attr($args['name']),
			esc_html($value)
		);
	}

	function number_field($args)
	{
		$value = get_option($args['name']);

		printf(
			'<input type="number" min="1" id="%s" name="%s" value="%d" />',
			esc_attr($args['name']),
			esc_attr($args['name']),
			absint($value)
		);
	}

	function tel_field($args)
	{
		$value = get_option($args['name']);

		printf(
			'<input type="tel" id="%s" name="%s" value="%s" class="regular-text" />',
			esc_attr($args['name']),
			esc_attr($args['name']),
			esc_html($value)
		);
	}

	function email_field($args)
	{
		$value = get_option($args['name']);

		printf(
			'<input type="email" id="%s" name="%s" value="%s" class="regular-text" />',
			esc_attr($args['name']),
			esc_attr($args['name']),
			esc_html($value)
		);
	}

	function url_field($args)
	{
		$value = get_option($args['name']);

		printf(
			'<input type="url" id="%s" name="%s" value="%s" class="regular-text" />',
			esc_attr($args['name']),
			esc_attr($args['name']),
			esc_url($value)
		);
	}

	function textarea_field($args)
	{
		$value = get_option($args['name']);

		printf(
			'<textarea id="%s" name="%s" class="regular-text" rows="4">%s</textarea>',
			esc_attr($args['name']),
			esc_attr($args['name']),
			esc_textarea($value)
		);
	}

	function select_cf7_field($args)
	{
		$get_contact_forms = get_posts([
			'post_type'   => 'wpcf7_contact_form',
			'numberposts' => -1,
			'post_status' => 'publish'
		]);

		if ( !class_exists('WPCF7') ) {
			echo 'Плагин Contact Form 7 не установлен';
			return;
		}

		$optionsArray = [];
		$value = get_option($args['name']);

		foreach ($get_contact_forms as $contact_form) {
			$cfID = esc_attr($contact_form->ID);
			$cfTitle = esc_html($contact_form->post_title);
			$isSelected = selected($value, $cfID, false);

			$optionsArray[] = "<option value='$cfID'$isSelected>$cfTitle</option>";
		}

		$optionsString = implode('', $optionsArray);

		// echo '<pre>'. print_r( $get_contact_forms , 1) .'</pre>';

		printf(
			'<select id="%s" name="%s" class="regular-text">%s</select>',
			esc_attr($args['name']),
			esc_attr($args['name']),
			$optionsString
		);
	}

	function scripts_contact_page()
	{
		if ('settings_page_contacts' !== get_current_screen()->id) return;

		wp_enqueue_script('phpavel-mask_phone', plugin_dir_url(__FILE__) . 'mask_phone.js', [], '1.0.0', true);
	}
}

$phpavelContactPage = new phpavelContactPage();
