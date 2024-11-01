<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( ! Class_Exists('WPCSWPOPUP'))
{
	class WPCSWPOPUP
	{
		function __construct()
		{
			WPCSWPOPUP::add_popup_script();
			call_user_func_array(array('WPCSWPOPUP', 'set_media_upload'), array());
		}

		public function header_html()
		{
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php esc_attr(bloginfo('charset')); ?>"/>
<title><?php echo esc_html(__("Step Setting", 'wp-copysafe-web')); ?></title>
</head>
<body>
<div id="wrapper" class="hfeed">
<ul><?php
		}

		public function footer_html()
		{
?>
</ul>
</div>
</body>
<?php
		}

		public function set_media_upload()
		{
			include(WPCSW_PLUGIN_PATH . "media-upload.php");
		}

		public function add_popup_script()
		{
			$script_tag = 'script';
			ob_start();
			?>
			<<?php echo esc_html($script_tag); ?> type='text/javascript' src='<?php echo  esc_attr(WPCSW_PLUGIN_URL); ?>js/copysafe_media_uploader.js?v=<?php echo urlencode(WPCSW_ASSET_VERSION); ?>'></<?php echo esc_html($script_tag); ?>>
			<?php
			$html = ob_get_clean();

			echo wp_kses($html, ['script' => [
				'src' => 1,
				'type' => 1,
			]]);
		}
	}

	$popup = new WPCSWPOPUP ();
}