<?php

/*
Plugin Name: CopySafe Web Protection
Plugin URI: https://artistscope.com/copysafe_web_protection_wordpress_plugin.asp
Description: Add copy protection from PrintScreen and screen capture. Copysafe Web uses encrypted images and domain lock to apply copy protection for all media displayed on the web page.
Author: ArtistScope
Text Domain: wp-copysafe-web
Version: 4.1
License: GPLv2
Author URI: https://artistscope.com/

	Copyright 2024 ArtistScope Pty Limited


	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// ================================================================================ //
//                                                                                  //
//  WARNING : DONT CHANGE ANYTHING BELOW IF YOU DONT KNOW WHAT YOU ARE DOING        //
//                                                                                  //
// ================================================================================ //
# set script max execution time to 5mins

if( ! defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

set_time_limit(300);

define('WPCSW_ASSET_VERSION', 1.03);

require_once __DIR__ . "/function.php";
require_once __DIR__ . "/function-common.php";
require_once __DIR__ . "/function-page.php";
require_once __DIR__ . "/function-shortcode.php";

function wpcsw_enable_extended_upload($mime_types = []) {
	// You can add as many MIME types as you want.
	$mime_types['class'] = 'application/octet-stream';
	// If you want to forbid specific file types which are otherwise allowed,
	// specify them here.  You can add as many as possible.
	return $mime_types;
}

//This filter is added to add the support for upload of .class file
add_filter('upload_mimes', 'wpcsw_enable_extended_upload');

// ============================================================================================================================
# register WordPress menus
function wpcsw_admin_menus() {
	add_menu_page('CopySafe Web', 'CopySafe Web', 'publish_posts', 'wpcsw_list');
	add_submenu_page('wpcsw_list', 'CopySafe Web List Files', 'List Files', 'publish_posts', 'wpcsw_list', 'wpcsw_admin_page_list');
	add_submenu_page('wpcsw_list', 'CopySafe Web Settings', 'Settings', 'publish_posts', 'wpcsw_settings', 'wpcsw_admin_page_settings');
}

// ============================================================================================================================
# delete short code
function wpcsw_delete_shortcode() {
	// get all posts
	$posts_array = get_posts();
	foreach ($posts_array as $post) {
		// delete short code
		$post->post_content = wpcsw_deactivate_shortcode($post->post_content);
		// update post
		wp_update_post($post);
	}
}

// ============================================================================================================================
# deactivate short code
function wpcsw_deactivate_shortcode($content) {
	// delete short code
	$content = preg_replace('/\[copysafe name="[^"]+"\]\[\/copysafe\]/s', '', $content);
	return $content;
}

// ============================================================================================================================
# search short code in post content and get post ids
function wpcsw_search_shortcode($file_name) {
	// get all posts
	$posts = get_posts();
	$IDs = FALSE;
	foreach ($posts as $post) {
		$file_name = preg_quote($file_name, '\\');
		preg_match('/\[copysafe name="' . $file_name . '"\]\[\/copysafe\]/s', $post->post_content, $matches);
		if (is_array($matches) && isset($matches[1])) {
			$IDs[] = $post->ID;
		}
	}
	return $IDs;
}

// ============================================================================================================================
# delete file options
function wpcsw_delete_file_options($file_name) {
	$file_name = trim($file_name);
	$wpcsw_options = get_option('wpcsw_settings');
	foreach ($wpcsw_options["classsetting"] as $k => $arr) {
		if ($wpcsw_options["classsetting"][$k][$file_name]) {
			unset($wpcsw_options["classsetting"][$k][$file_name]);
			if (!count($wpcsw_options["classsetting"][$k])) {
				unset($wpcsw_options["classsetting"][$k]);
			}
		}
	}
	update_option('wpcsw_settings', $wpcsw_options);
}

// ============================================================================================================================
# install media buttons
function wpcsw_media_buttons($context) {
	global $post_ID;
	// generate token for links
	$token = wp_create_nonce('wpcsw_token');
	$url = admin_url('?wpcsw-popup=copysafe&wpcsw_token=' . $token . '&post_id=' . $post_ID);
	echo wp_kses(
		"<a href='" . esc_attr($url) . "' class='thickbox' id='wpcsw_link' data-body='no-overflow' title='CopySafe Web'><img src='" . esc_attr(plugin_dir_url(__FILE__)) . "/images/copysafebutton.png'></a>",
		wpcsw_kses_allowed_options()
	);
}

// ============================================================================================================================

// ============================================================================================================================
# admin page scripts
function wpcsw_admin_load_js() {
	// load jquery suggest plugin
	wp_enqueue_script('suggest');
}

// ============================================================================================================================
# admin page styles
function wpcsw_admin_load_styles() {
	// register custom CSS file & load
	wp_register_style('wpcsw-style', plugins_url('css/wp-copysafe-web.css', __FILE__), [], WPCSW_ASSET_VERSION);
	wp_enqueue_style('wpcsw-style');
}

function wpcsw_is_admin_postpage() {
	$script_name = explode("/", $_SERVER["SCRIPT_NAME"]);
	$ppage = end($script_name);
	if ($ppage == "post-new.php" || $ppage == "post.php") {
		return TRUE;
	}
}

function wpcsw_includecss_js() {
	if (!wpcsw_is_admin_postpage()) {
		return;
	}
	global $wp_popup_upload_lib;
	if ($wp_popup_upload_lib) {
		return;
	}
	$wp_popup_upload_lib = TRUE;
	
	wp_enqueue_style('jquery-ui-1.9');

	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-progressbar');
	wp_enqueue_script('jquery.json');
}

function wpcsw_load_admin_scripts() {
	wp_register_style('jquery-ui-1.9', '//code.jquery.com/ui/1.9.2/themes/redmond/jquery-ui.css', [], WPCSW_ASSET_VERSION);

	wp_register_script('wp-copysafeweb-uploader', WPCSW_PLUGIN_URL . 'js/copysafe_media_uploader.js', [
		'jquery',
		'plupload-all',
	], WPCSW_ASSET_VERSION,
	['in_footer' => true]);
}

// ============================================================================================================================
# setup plugin
function wpcsw_setup()
{
	//----add codding----
	$options = get_option("wpcsw_settings");
	define('WPCSW_PLUGIN_PATH', str_replace("\\", "/", plugin_dir_path(__FILE__))); //use for include files to other files
	define('WPCSW_PLUGIN_URL', plugins_url('/', __FILE__));

	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);
	$upload_path = $wp_upload_dir_path . '/' . $options["settings"]["upload_path"];
	define('WPCSW_UPLOAD_PATH', $upload_path); //use for include files to other files

	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_url = str_replace("\\", "/", $wp_upload_dir['baseurl']);
	$upload_url = $wp_upload_dir_url . '/' . $options["settings"]["upload_path"];
	define('WPCSW_UPLOAD_URL', $upload_url);

	add_action('admin_head', 'wpcsw_includecss_js');
	add_action('wp_ajax_wpcsw_ajaxprocess', 'wpcsw_ajaxprocess');

	//Sanitize the GET input variables
	$pagename = !empty(@$_GET['page']) ? sanitize_key(@$_GET['page']) : '';
	$cswfilename = !empty(@$_GET['cswfilename']) ? sanitize_file_name(@$_GET['cswfilename']) : '';
	$action = !empty(@$_GET['action']) ? sanitize_key(@$_GET['action']) : '';
	$cswdel_nonce = !empty(@$_GET['cswdel_nonce']) ? sanitize_key(@$_GET['cswdel_nonce']) : '';

	if ($pagename == 'wpcsw_list' && $cswfilename && $action == 'cswdel')
	{
		//check that nonce is valid and user is administrator
		if (current_user_can('administrator') && wp_verify_nonce($cswdel_nonce, 'cswdel')) {
			wpcsw_delete_file_options($cswfilename);
			if (file_exists(WPCSW_UPLOAD_PATH . $cswfilename)) {
				wp_delete_file(WPCSW_UPLOAD_PATH . $cswfilename);
			}
			wp_redirect('admin.php?page=wpcsw_list');
		}
		else {
			wp_nonce_ays('');
		}
	}

	if (isset($_GET['wpcsw-popup']) && @$_GET["wpcsw-popup"] == "copysafe") {
		require_once(WPCSW_PLUGIN_PATH . "popup_load.php");
		exit();
	}

	//=============================
	// load js file
	add_action('wp_enqueue_scripts', 'wpcsw_load_js');

	add_action('admin_enqueue_scripts', 'wpcsw_load_admin_scripts');

	// load admin CSS
	add_action('admin_print_styles', 'wpcsw_admin_load_styles');

	// add short code
	add_shortcode('copysafe', 'wpcsw_shortcode');

	// if user logged in
	if (is_user_logged_in()) {
		// install admin menu
		add_action('admin_menu', 'wpcsw_admin_menus');

		// check user capability
		if (current_user_can('edit_posts')) {
			// load admin JS
			add_action('admin_print_scripts', 'wpcsw_admin_load_js');
			// load media button
			add_action('media_buttons', 'wpcsw_media_buttons');
		}
	}

	wp_register_script('wpcsw-plugin-script', WPCSW_PLUGIN_URL . 'js/copysafe_media_uploader.js', [], WPCSW_ASSET_VERSION, ['in_footer' => true]);
	wp_register_script('jquery.json', WPCSW_PLUGIN_URL . 'lib/jquery.json-2.3.js', [], WPCSW_ASSET_VERSION, ['in_footer' => true]);
}

// ============================================================================================================================
# runs when plugin activated
function wpcsw_activate() {
	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);

	// if this is first activation, setup plugin options
	if (!get_option('wpcsw_settings')) {
		// set plugin folder
		$upload_dir = 'copysafe-web/';
		$upload_path = $wp_upload_dir_path . '/' . $upload_dir;

		// set default options
		$wpcsw_options['settings'] = [
			'admin_only' => "checked",
			'upload_path' => $upload_dir,
			'mode' => "demo",
			'asps' => "checked",
			'ff' => "",
			'ch' => "",
		];

		update_option('wpcsw_settings', $wpcsw_options);

		if (!is_dir($upload_path)) {
			wp_mkdir_p($upload_path);
		}
		// create upload directory if it is not exist
	}
}

// ============================================================================================================================
# runs when plugin deactivated
function wpcsw_deactivate() {
	// remove text editor short code
	remove_shortcode('copysafe');
}

// ============================================================================================================================
# runs when plugin deleted.
function wpcsw_uninstall() {
	global $wp_filesystem;

	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();

	// delete all uploaded files
	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);

	$default_upload_dir = $wp_upload_dir_path . '/copysafe-web/';
	if (is_dir($default_upload_dir)) {
		$dir = scandir($default_upload_dir);
		foreach ($dir as $file) {
			if ($file != '.' || $file != '..') {
				wp_delete_file($default_upload_dir . $file);
			}
		}
		$wp_filesystem->rmdir($default_upload_dir);
	}

	// delete upload directory
	$options = get_option("wpcsw_settings");

	if ($options["settings"]["upload_path"]) {
		$upload_path = $wp_upload_dir_path . '/' . $options["settings"]["upload_path"];
		if (is_dir($upload_path)) {
			$dir = scandir($upload_path);
			foreach ($dir as $file) {
				if ($file != '.' || $file != '..') {
					wp_delete_file($upload_path . '/' . $file);
				}
			}
			// delete upload directory
			$wp_filesystem->rmdir($upload_path);
		}
	}

	// delete plugin options
	delete_option('wpcsw_settings');

	// unregister short code
	remove_shortcode('copysafe');

	// delete short code from post content
	wpcsw_delete_shortcode();
}

function wpcsw_load_js() {
	wp_register_script('wp-copysafeweb', WPCSW_PLUGIN_URL . 'js/wp-copysafe-web.js', [], WPCSW_ASSET_VERSION, ['in_footer' => true]);
}

function wpcsw_admin_head() {
	$uploader_options = [
		'runtimes' => 'html5,silverlight,flash,html4',
		'browse_button' => 'wpcsw-plugin-uploader-button',
		'container' => 'wpcsw-plugin-uploader',
		'drop_element' => 'wpcsw-plugin-uploader',
		'file_data_name' => 'async-upload',
		'multiple_queues' => TRUE,
		'max_file_size' => wp_max_upload_size() . 'b',
		'url' => admin_url('admin-ajax.php'),
		'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
		'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
		'filters' => [
			[
			'title' => __('Allowed Files'),
			'extensions' => '*',
			],
		],
		'multipart' => TRUE,
		'urlstream_upload' => TRUE,
		'multi_selection' => TRUE,
		'multipart_params' => [
			'_ajax_nonce' => '',
			'action' => 'wpcsw-plugin-upload-action',
		],
	];
	?>
<script type="text/javascript">
	var global_uploader_options = <?php echo wp_json_encode($uploader_options); ?>;
</script>
	<?php
}

add_action('admin_head', 'wpcsw_admin_head');

function wpcsw_includecss_js_to_footer(){
	if (!wpcsw_is_admin_postpage())
		return;
	
	?>
	<script>
	if( jQuery("#wpcsw_link").length > 0 ){
		if( jQuery("#wpcsw_link").data("body") == "no-overflow" ){
			jQuery("body").addClass("wps-no-overflow");
			
		}
	}
	</script>
	<?php
}

add_action('admin_footer', 'wpcsw_includecss_js_to_footer');

function wpcsw_ajax_action() {
	add_filter('upload_dir', 'wpcsw_upload_dir');
	
	// check ajax nonce
	//check_ajax_referer( __FILE__ );
	if (current_user_can('upload_files')) {
		$response = [];
		// handle file upload
		$id = media_handle_upload(
			'async-upload',
			0,
			[
				'test_form' => TRUE,
				'action' => 'wpcsw-plugin-upload-action',
			]
		);

		// send the file' url as response
		if (is_wp_error($id)) {
			$response['status'] = 'error22';
			$response['error'] = $id->get_error_messages();
		}
		else {
			$response['status'] = 'success';

			$src = wp_get_attachment_image_src($id, 'thumbnail');
			$response['attachment'] = [];
			$response['attachment']['id'] = $id;
			$response['attachment']['src'] = $src[0];
		}
	}

	remove_filter('upload_dir', 'wpcsw_upload_dir');
	echo wp_json_encode($response);
	exit;
}

add_action('wp_ajax_wpcsw-plugin-upload-action', 'wpcsw_ajax_action');

function wpcsw_upload_dir($upload) {
	$upload['subdir'] = '/copysafe-web';
	$upload['path'] = $upload['basedir'] . $upload['subdir'];
	$upload['url'] = $upload['baseurl'] . $upload['subdir'];
	return $upload;
}

// ============================================================================================================================
# register plugin hooks
register_activation_hook(__FILE__, 'wpcsw_activate'); // run when activated
register_deactivation_hook(__FILE__, 'wpcsw_deactivate'); // run when deactivated
register_uninstall_hook(__FILE__, 'wpcsw_uninstall'); // run when uninstalled

add_action('init', 'wpcsw_setup');