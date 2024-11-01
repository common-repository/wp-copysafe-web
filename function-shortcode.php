<?php
if( ! defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

// ============================================================================================================================
# convert shortcode to html output
function wpcsw_shortcode($atts)
{
	wpcsw_check_artis_browser_version();
	global $post;

	$postid = $post->ID;
	$filename = $atts["name"];

	if (!file_exists(WPCSW_UPLOAD_PATH . $filename)) {
		return "<div style='padding:5px 10px;background-color:#fffbcc'><strong>File(" . esc_html($filename) . ") don't exist</strong></div>";
	}

	$settings = wpcsw_get_first_class_settings();

	// get plugin options
	$wpcsw_options = get_option('wpcsw_settings');
	if ($wpcsw_options["settings"]) {
		$settings = wp_parse_args($wpcsw_options["settings"], $settings);
	}

	if ($wpcsw_options["classsetting"][$postid][$filename]) {
		$settings = wp_parse_args($wpcsw_options["classsetting"][$postid][$filename], $settings);
	}

	$settings = wp_parse_args($atts, $settings);

	extract($settings);

	if ($asps == "checked") {
		$asps = '1';
	}
	if ($ch == "checked") {
		$chrome = '1';
	}
	if ($ff == "checked") {
		$firefox = '1';
	}

	if ($key_safe == "checked") {
		$key_safe = 1;
	}
	if ($capture_safe == "checked") {
		$capture_safe = 1;
	}
	if ($menu_safe == "checked") {
		$menu_safe = 1;
	}
	if ($remote_safe == "checked") {
		$remote_safe = 1;
	}

	$plugin_url = WPCSW_PLUGIN_URL;
	$upload_url = WPCSW_UPLOAD_URL;

	$script_tag = 'script';

	ob_start();
	?>
	<script type="text/javascript">
		var wpcsw_plugin_url = "<?php echo esc_js($plugin_url); ?>" ;
		var wpcsw_upload_url = "<?php echo esc_js($upload_url); ?>" ;
	</script>
	<script type="text/javascript">
		// hide JavaScript from non-JavaScript browsers
		var m_bpDebugging = false;
		var m_szMode = "<?php echo esc_js($mode); ?>";
		var m_szClassName = "<?php echo esc_js($name); ?>";
		var m_szImageFolder = "<?php echo esc_js($upload_url); ?>"; //  path from root with / on both ends
		var m_bpKeySafe = "<?php echo esc_js($key_safe); ?>";
		var m_bpCaptureSafe = "<?php echo esc_js($capture_safe); ?>";
		var m_bpMenuSafe = "<?php echo esc_js($menu_safe); ?>";
		var m_bpRemoteSafe = "<?php echo esc_js($remote_safe); ?>";
		var m_bpWindowsOnly = true;	
		var m_bpProtectionLayer = false;		//this page does not use layer control

		var m_bpASPS = "<?php echo esc_js($asps); ?>"; // ArtistScope web browsers version 2 and later
		var m_bpChrome = "<?php echo esc_js($chrome); ?>"; // all chrome browsers before version 32	
		var m_bpFx = "<?php echo esc_js($firefox); ?>"; // all firefox browsers from version 5 and later

		var m_szDefaultStyle = "ImageLink";
		var m_szDefaultTextColor = "<?php echo esc_js($text_color); ?>";
		var m_szDefaultBorderColor = "<?php echo esc_js($border_color); ?>";
		var m_szDefaultBorder = "<?php echo esc_js($border); ?>";
		var m_szDefaultLoading = "<?php echo esc_js($loading_message); ?>";
		var m_szDefaultLabel = "";
		var m_szDefaultLink = "<?php echo esc_js($hyperlink); ?>";
		var m_szDefaultTargetFrame = "<?php echo esc_js($target); ?>";
		var m_szDefaultMessage = "";

		if (m_szMode == "debug") {
			m_bpDebugging = true;
		}
		
		if ((m_bpCaptureSafe == "1") && (m_bpKeySafe == "1")) {
			var cswbody = document.getElementsByTagName("body")[0];
			cswbody.setAttribute("onselectstart", "return false;");
			cswbody.setAttribute("ondragstart", "return false");
			cswbody.setAttribute("onmousedown", "if (event.preventDefault){event.preventDefault();}");
			cswbody.setAttribute("onBeforePrint", "document.body.style.display = '';");
			cswbody.setAttribute("onContextmenu", "return false;");
			cswbody.setAttribute("onClick", "if(event.button==2||event.button==3){event.preventDefault();event.stopPropagation();return false;}");
		}
		else if ((m_bpCaptureSafe == "1") && (m_bpKeySafe != "1")) {
			var cswbody = document.getElementsByTagName("body")[0];
			cswbody.setAttribute("onselectstart", "return false;");
			cswbody.setAttribute("ondragstart", "return false");
			cswbody.setAttribute("onContextmenu", "return false;");
		}
	</script>
	<<?php echo esc_html($script_tag); ?> src="<?php echo esc_attr(WPCSW_PLUGIN_URL . 'js/wp-copysafe-web.js?v=' . urlencode(WPCSW_ASSET_VERSION)); ?>"></<?php echo esc_html($script_tag); ?>>
	<div>
		 <script type="text/javascript">
			//hide JavaScript from non-JavaScript browsers
			if ((m_szMode == "licensed") || (m_szMode == "debug")) {
				insertCopysafeWeb("<?php echo esc_js($name); ?>", "<?php echo esc_js($width); ?>", "<?php echo esc_js($height); ?>");
			}
			else {
				document.writeln("<img src='<?php echo esc_js($plugin_url); ?>images/image_placeholder.jpg' border='0' alt='Demo mode'>");
			}
		 </script>
	</div>
	<?php
	$output = ob_get_clean();

	return $output;
}