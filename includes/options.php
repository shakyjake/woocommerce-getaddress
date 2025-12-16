<?php

if(!defined('ABSPATH')){
	exit; /* Exit if accessed directly. */
}

if(!defined('WCGAIO_ROOT')){
	exit; /* Exit if accessed directly. */
}

/**
 * Register Settings Fields
 * @return void
 */
function wcgaio_settings_fields(){
    register_setting('wcgaio_settings', 'wcgaio_api_key', [
		'type' => 'string', 
		'sanitize_callback' => 'sanitize_text_field',
		'default' => NULL,
	]);
    register_setting('wcgaio_settings', 'wcgaio_button_color', [
		'type' => 'string', 
		'sanitize_callback' => 'sanitize_hex_color',
		'default' => NULL,
	]);
    register_setting('wcgaio_settings', 'wcgaio_border_radius', [
		'type' => 'string', 
		'sanitize_callback' => 'sanitize_text_field',
		'default' => NULL,
	]);
    register_setting('wcgaio_settings', 'wcgaio_manual', [
		'type' => 'boolean', 
		'sanitize_callback' => 'wcgaio_sanitise_boolean',
		'default' => NULL,
	]);
} 
add_action('admin_init', 'wcgaio_settings_fields');

/**
 * Add options page to admin menu
 * @return void
 */
function wcgaio_settings_menu(){
	add_submenu_page('options-general.php', 'WooCommerce getaddress.io settings', 'WooCommerce getaddress.io settings', 'administrator', __FILE__, 'wcgaio_options_page');
}
add_action('admin_menu', 'wcgaio_settings_menu');

/**
 * Return the button colour defined in the settings
 * @return string
 */
function wcgaio_color_get(){
	return get_option('wcgaio_button_color');
}

/**
 * Return the API key defined in the settings
 * @return string
 */
function wcgaio_token_get(){
	return get_option('wcgaio_api_key');
}

/**
 * Echo options page HTML
 * @return void
 */

function wcgaio_options_page(){

?>
<div class="wrap">
	<h1>WooCommerce getaddress.io integration</h1>
	<form method="post" action="options.php">
<?php
		settings_fields('wcgaio_settings');
		do_settings_sections('wcgaio_settings');
?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="wcgaio_api_key">API Key</label>
				</th>
				<td>
					<input type="text" name="wcgaio_api_key" id="wcgaio_api_key" value="<?php echo esc_attr( get_option('wcgaio_api_key') ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wcgaio_button_color">Button Colour</label>
				</th>
				<td>
					<input type="color" name="wcgaio_button_color" id="wcgaio_button_color" value="<?php echo esc_attr( get_option('wcgaio_button_color') ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wcgaio_button_color">Input/Button Border Radius</label>
				</th>
				<td>
					<input type="text" name="wcgaio_border_radius" id="wcgaio_border_radius" value="<?php echo esc_attr( get_option('wcgaio_border_radius') ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wcgaio_manual">Show &quot;Enter address manually&quot; button</label>
				</th>
				<td>
					<input type="checkbox" name="wcgaio_manual" id="wcgaio_manual" value="1"<?php if(get_option('wcgaio_manual')){ echo 'checked'; } ?> />
				</td>
			</tr>
		</table>
<?php
		submit_button();
?>
	</form>
</div>
<?php
}
?>