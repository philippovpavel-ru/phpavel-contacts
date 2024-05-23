<?php

/**
 * Plugin Name: [ Контакты сайта ]
 * Description: Добавление раздела с контактами сайта
 *
 * Author URI:  https://philippovpavel.ru
 * Author:      Филиппов Павел
 *
 * Requires at least: 5.7
 * Requires PHP: 7.0
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Version:     1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;
// Page
add_action( 'admin_menu', 'phpavel_contact_menu_page', 5 );
function phpavel_contact_menu_page(){
	add_submenu_page(
		'options-general.php',
		'Контакты',
		'Контакты',
		'manage_options',
		'phpavel_contacts',
		'phpavel_contact_page_callback'
	);
}

function phpavel_contact_page_callback(){
	echo '<div class="wrap">
		<h1>' . get_admin_page_title() . '</h1>
		<form method="post" action="options.php">';
 
			settings_fields( 'phpavel_contacts_settings' );
			do_settings_sections( 'phpavel_contacts' );
			submit_button();

		echo '</form>
	</div>';
}

// Fields
function fields() {
	return array(
		[
			'id'       => 'phpavel_contact_phone',
			'title'    => 'Телефон',
			'type'     => 'tel',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'phpavel_contact_vk',
			'title'    => 'Ссылка VK',
			'type'     => 'url',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'phpavel_contact_inst',
			'title'    => 'Ссылка Instagram',
			'type'     => 'url',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'phpavel_contact_wa',
			'title'    => 'Ссылка WhatsApp',
			'type'     => 'url',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'phpavel_contact_address',
			'title'    => 'Адрес',
			'type'     => 'text',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'phpavel_contact_timeline',
			'title'    => 'График работы',
			'type'     => 'text',
			'function' => 'sanitize_text_field'
		],
		[
			'id'       => 'phpavel_contact_map',
			'title'    => 'Карта координаты',
			'type'     => 'text',
			'function' => 'sanitize_text_field'
		],

	);
}

add_action( 'admin_init', 'phpavel_contacts_fields' );
function phpavel_contacts_fields(){
	$fields = fields();

	add_settings_section( 'phpavel_contacts_settings_section_id', '', '', 'phpavel_contacts' );

	foreach ($fields as $field) {
		register_setting(
			'phpavel_contacts_settings',
			$field['id'],
			$field['function']
		);

		add_settings_field(
			$field['id'],
			$field['title'],
			'phpavel_'. $field['type'] .'_field',
			'phpavel_contacts',
			'phpavel_contacts_settings_section_id',
			array(
				'label_for' => $field['id'],
				'class'     => $field['id'] .'-item',
				'name'      => $field['id'],
			)
		);
	}
}

// Sanitize functions
function phpavel_text_field( $args ){
	$value = get_option( $args[ 'name' ] );

	printf(
		'<input type="text" id="%s" name="%s" value="%s" class="regular-text" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		esc_html( $value )
	);
}

function phpavel_number_field( $args ){
	$value = get_option( $args[ 'name' ] );

	printf(
		'<input type="number" min="1" id="%s" name="%s" value="%d" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		absint( $value )
	);
}

function phpavel_tel_field( $args ){
	$value = get_option( $args[ 'name' ] );

	printf(
		'<input type="tel" id="%s" name="%s" value="%s" class="regular-text" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		esc_html( $value )
	);
}

function phpavel_email_field( $args ){
	$value = get_option( $args[ 'name' ] );

	printf(
		'<input type="email" id="%s" name="%s" value="%s" class="regular-text" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		esc_html( $value )
	);
}

function phpavel_url_field( $args ){
	$value = get_option( $args[ 'name' ] );

	printf(
		'<input type="url" id="%s" name="%s" value="%s" class="regular-text" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		esc_url( $value )
	);
}

function phpavel_textarea_field( $args ){
	$value = get_option( $args[ 'name' ] );

	printf(
		'<textarea id="%s" name="%s" class="regular-text" rows="4">%s</textarea>',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		esc_textarea( $value )
	);
}

// Scripts for Contact Page
add_action( 'admin_enqueue_scripts', 'phpavel_scripts_contact_page' );
function phpavel_scripts_contact_page() {
	if ( 'settings_page_phpavel_contacts' !== get_current_screen()->id ) return;

	wp_enqueue_script( 'phpavel-mask_phone', plugin_dir_url( __FILE__ ) .'mask_phone.js', [], plugin_dir_path( __FILE__ ) .'mask_phone.js', true );
}