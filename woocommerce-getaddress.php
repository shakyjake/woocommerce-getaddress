<?php

/**
 * WooCommerce GetAddress
 *
 * @wordpress-plugin
 * Plugin Name:   WooCommerce GetAddress
 * Description:   Woocommerce Checkout integration for getaddress.io
 * Version:       1.1.15
 * Text Domain:   wcgaio
 * License:       MIT
 * License URI:   https://opensource.org/license/mit/
 * Author:        Jake Nicholson
 * Author URI:    https://github.com/shakyjake
 */

if(!defined('ABSPATH')){
	exit; /* Exit if accessed directly. */
}

if(!defined('WCGAIO_ROOT')){
	define('WCGAIO_ROOT', __FILE__);
}

/**
 * Set up plugin
 */
function wcgaio_init(){
	require_once trailingslashit(plugin_dir_path(WCGAIO_ROOT)) . 'includes/options.php';
}
add_action('init', 'wcgaio_init');

/**
 * AJAX endpoint for address details
 * @return void
 */
function wcgaio_address_details(){
	
	$action = '';
	if(!empty($_POST['action'])){
		$action = $_POST['action'];
	}
	$address_id = '';
	if(!empty($_POST['address_id'])){
		$address_id = $_POST['address_id'];
	}
	$token = '';
	if(!empty($_POST['token'])){
		$token = $_POST['token'];
	}
	
	$result = '{"Message" : "Address not found"}';
	
	if(!headers_sent()){
		header('Content-Type: application/json; charset=' . get_option( 'blog_charset' ));
	}
	
	if(wp_verify_nonce($token, $action)){
	
		$curl = curl_init();
		$url = sprintf(
			'https://api.getaddress.io/get/%1$s?api-key=%2$s',
			urlencode($address_id),
			urlencode(wcgaio_token_get())
		);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		if(curl_errno($curl)){
			wp_die('{"Message" : "Address not found"}', 404);
		}
	
	} else {
		wp_die($result, 404);
	}

	wp_die($result, 200);
	
}
add_action('wp_ajax_wcgaio_address_details', 'wcgaio_address_details');
add_action('wp_ajax_nopriv_wcgaio_address_details', 'wcgaio_address_details');

/**
 * AJAX endpoint for address searches
 * @return void
 */
function wcgaio_address_search(){
	
	$action = '';
	if(!empty($_POST['action'])){
		$action = $_POST['action'];
	}
	$search = '';
	if(!empty($_POST['search'])){
		$search = $_POST['search'];
	}
	$token = '';
	if(!empty($_POST['token'])){
		$token = $_POST['token'];
	}
	
	$url = sprintf(
		'https://api.getaddress.io/autocomplete/%1$s?api-key=%2$s&all=true',
		str_replace('+', '%20', urlencode($search)),
		urlencode(wcgaio_token_get())
	);
	
	$default_result = '{"url" : "' . $url . '", "suggestions" : []}';
	
	if(!headers_sent()){
		header('Content-Type: application/json; charset=' . get_option( 'blog_charset' ));
	}
	
	if(wp_verify_nonce($token, $action)){
	
		$curl = curl_init();

		$referrer = sprintf(
			'https://%1$s/%2$s',
			$_SERVER['HTTP_HOST'],
			$_SERVER['REQUEST_URI']
		);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_REFERER, $referrer);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		if(curl_errno($curl)) {
			error_log(print_r(curl_error($curl), true));
			wp_die('{"suggestions" : []}', 200);
		}
		
	}

	wp_die($result, 200);
	
}
add_action('wp_ajax_wcgaio_address_search', 'wcgaio_address_search');
add_action('wp_ajax_nopriv_wcgaio_address_search', 'wcgaio_address_search');

/**
 * Register scripts and styles for use in the checkout page
 * @return void
 */
function wcgaio_enqueue_assets(){

	if(!defined('WCGAIO_VERSION')){ // Define version constant for use in enqueuing static assets
		define('WCGAIO_VERSION', get_plugin_data(WCGAIO_ROOT)['Version']);
	}
	
	if(function_exists('is_checkout')){
		if(is_checkout()){
			if(wcgaio_token_get()){
				wp_register_style('wcgaio', trailingslashit(plugin_dir_url(__FILE__)) . 'css/address.css', null, WCGAIO_VERSION, 'all');
				wp_register_script('wcgaio-load', trailingslashit(plugin_dir_url(__FILE__)) . 'js/address.js', ['jquery'], WCGAIO_VERSION, true);
				wp_localize_script('wcgaio-load', 'wcgaio', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'search_token' => wp_create_nonce('wcgaio_address_search'),
					'detail_token' => wp_create_nonce('wcgaio_address_details'),
					'show_manual_btn' => get_option('wcgaio_manual') ? 'true' : 'false'
				));
				wp_enqueue_style('wcgaio');
				wp_enqueue_script('wcgaio-load');
				
				$colour = get_option('wcgaio_button_color');
				$border_radius = get_option('wcgaio_border_radius');
	
				$custom_style = sprintf(
					'.address__search{%1$s%2$s}',
					($colour ? sprintf('--wcgaio-accent: %1$s;', $colour) : ''),
					($border_radius ? sprintf('--wcgaio-radius: %1$s;', $border_radius) : '')
				);
	
				wp_add_inline_style('wcgaio', $custom_style);
			}
		}
	}
}
add_action('wp_enqueue_scripts', 'wcgaio_enqueue_assets', 1, 1);

/**
 * Hook to register the address search field in WooCommerce's checkout fields
 * @return array
 */
function wcgaio_address_fields($fields){

	$fields['address_search'] = [
		'label' => 'Find your address',
		'label_class' => 'address__label',
		'clear' => true,
		'type' => 'text',
		'required' => false,
		'placeholder' => 'Enter your postcode',
		'class' => array('address__search form-row-wide'),
		'priority' => 41
	];
	
	return $fields;
	
}
add_filter('woocommerce_default_address_fields', 'wcgaio_address_fields', 9999, 1);

/**
 * Attempt to obtain a boolean value from whatever format it's been stored in
 * @return bool
 */
function wcgaio_sanitise_boolean($value) {
	
	if(is_bool($value)){
		return $value;
	}
	
	if(is_string($value)){
		if($value === '1' || $value === 'true'){
			return true;
		}
		return false;
	}
	
	if(is_int($value)){
		if($value === 1){
			return true;
		}
		return false;
	}
	
	return false;
	
}

?>