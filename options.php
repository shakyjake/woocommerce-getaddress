<?php

if(!defined('ABSPATH')){
	exit; /* Exit if accessed directly. */
}

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