<?php

if( ! defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

// ============================================================================================================================
# "List" Page
function wpcsw_admin_page_list()
{
	$msg = '';
	$table = '';
	$files = _get_wpcsw_uploadfile_list();

	if (!empty($_POST))
	{
		$wpcsw_options = get_option('wpcsw_settings');

		$wp_upload_dir = wp_upload_dir();
		$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);
		if (!empty($wpcsw_options['settings']['upload_path'])) {
			$target_dir = $wp_upload_dir_path . '/' . $wpcsw_options['settings']['upload_path'];
		} else {
			$target_dir = $wp_upload_dir_path;
		}

		// Check if image file is a actual image or fake image
		if (isset($_POST["copysafe-web-class-submit"]))
		{
			if (wp_verify_nonce($_POST['wpcopysafeweb_wpnonce'], 'wpcopysafeweb_settings'))
			{
				$target_file = $target_dir . basename($_FILES["copysafe-web-class"]["name"]);
				$uploadOk = 1;
				$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
				
				// Allow only .class file formats
				if ($_FILES["copysafe-web-class"]["name"] == "")
				{
					$msg .= '<div class="error"><p><strong>' . esc_html(__('Please upload file to continue.', 'wp-copysafe-web')) . '</strong></p></div>';
					$uploadOk = 0;
				}
				else if ($imageFileType != "class")
				{
					$msg .= '<div class="error"><p><strong>' . esc_html(__('Sorry, only .class files are allowed.', 'wp-copysafe-web')) . '</strong></p></div>';
					$uploadOk = 0;
				}
				// Check if $uploadOk is set to 0 by an error
				else if ($uploadOk == 0)
				{
					$msg .= '<div class="error"><p><strong>' . esc_html(__('Sorry, your file was not uploaded.', 'wp-copysafe-web')) . '</strong></p></div>';
					// if everything is ok, try to upload file
				}
				else
				{
					$upload_file = $_FILES["copysafe-web-class"];

					//Register path override
					add_filter('upload_dir', 'wpcsw_upload_dir');

					//Move file
					$movefile = wp_handle_upload($upload_file, [
						'test_form' => false,
						'test_type' => false,
						'mimes' => [
							'class' => 'application/octet-stream'
						],
					]);

					//Remove path override
					remove_filter('upload_dir', 'wpcsw_upload_dir');

					if ($movefile && ! isset($movefile['error']))
					{
						$base_url = get_site_url();
						$msg .= '<div class="updated"><p><strong>' . 'The file ' . esc_html(basename($_FILES["copysafe-web-class"]["name"])) . ' has been uploaded. Click <a href="' . esc_attr($base_url) . '/wp-admin/admin.php?page=wpcsw_list">here</a> to update below list.' . '</strong></p></div>';
					}
					else
					{
						$msg .= '<div class="error"><p><strong>' . esc_html(__('Sorry, there was an error uploading your file.', 'wp-copysafe-web')) . '</strong></p></div>';
					}
				}
			} //nounce
		}
	}

	if (!empty($files))
	{
		foreach ($files as $file)
		{
			$bare_url = 'admin.php?page=wpcsw_list&cswfilename=' . $file["filename"] . '&action=cswdel';

			$complete_url = wp_nonce_url($bare_url, 'cswdel', 'cswdel_nonce');

			$link = "<div class='row-actions'>
					<span><a href='" . esc_attr($complete_url) . "' title=''>Delete</a></span>
				</div>";
			// prepare table row
			$table .= "<tr><td></td><td>" . esc_html($file["filename"]) . " " . $link . "</td><td>" . esc_html($file["filesize"]) . "</td><td>" . esc_html($file["filedate"]) . "</td></tr>";
		}
	}

	if (!$table) {
		$table .= '<tr><td colspan="3">' . esc_html(__('No file uploaded yet.', 'wp-copysafe-web')) . '</td></tr>';
	}

	$wpcsw_options = get_option('wpcsw_settings');
	if ($wpcsw_options["settings"]) {
		extract($wpcsw_options["settings"], EXTR_OVERWRITE);
	}

	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);
	$upload_dir = $wp_upload_dir_path . '/' . $upload_path;

	$display_upload_form = !is_dir($upload_dir) ? FALSE : TRUE;

	if (!$display_upload_form) {
		$msg = '<div class="updated"><p><strong>' .
			esc_html(__('Upload directory doesn\'t exist. Please configure upload directory to upload class files.', 'wp-copysafe-web')) . '</strong></p></div>';
	}
  ?>
    <div class="wrap">
        <div class="icon32" id="icon-file"><br/></div>
        <?php echo wp_kses($msg, wpcsw_kses_allowed_options()); ?>
        <h2>List Class Files</h2>
        <?php if ($display_upload_form): ?>
            <form action="" method="post" enctype="multipart/form-data">
                <?php echo wp_kses(wp_nonce_field('wpcopysafeweb_settings', 'wpcopysafeweb_wpnonce'), wpcsw_kses_allowed_options()); ?>
                <input type="file" name="copysafe-web-class" value=""/>
                <input type="submit" name="copysafe-web-class-submit"
                       value="Upload"/>
            </form>
        <?php endif; ?>
        <!--<div><?php // echo wpcsw_media_buttons('');
        ?></div>-->
        <div id="col-container" style="width:700px;">
            <div class="col-wrap">
                <h3>Uploaded Class Files</h3>
                <table class="wp-list-table widefat">
                    <thead>
                    <tr>
                        <th width="5px">&nbsp;</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php echo wp_kses($table, wpcsw_kses_allowed_options()); ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>&nbsp;</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Date</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="clear"></div>
    </div>
  <?php
}

// ============================================================================================================================
# "Settings" page
function wpcsw_admin_page_settings()
{
	$msg = '';
	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);

	if (!empty($_POST))
	{
		if (wp_verify_nonce($_POST['wpcopysafeweb_wpnonce'], 'wpcopysafeweb_settings'))
		{
			$wpcsw_options = get_option('wpcsw_settings');
			extract($_POST, EXTR_OVERWRITE);
		
			if (!$upload_path) {
				$upload_path = 'copysafe-web/';
			}
			else
			{
				$upload_path = sanitize_text_field($upload_path);
			}

			$upload_path = str_replace("\\", "/", stripcslashes($upload_path));
			if (substr($upload_path, -1) != "/") {
				$upload_path .= "/";
			}

			$wpcsw_options['settings'] = [
				'admin_only' => sanitize_text_field($admin_only),
				'upload_path' => $upload_path,
				'mode' => sanitize_text_field($mode),
				'asps' => !empty(sanitize_text_field($asps))  ? 'checked' : '',
				'ff' => !empty(sanitize_text_field($ff)) ? 'checked' : '',
				'ch' => !empty(sanitize_text_field($ch)) ? 'checked' : '',
				'latest_version' => $latest_version,
			];

			$max_upload_size = wp_max_upload_size();
			if ( ! $max_upload_size ) {
				$max_upload_size = 0;
			}

			$wpcsw_options['settings']['max_size'] = esc_html(size_format($max_upload_size));

			$upload_path = $wp_upload_dir_path . '/' . $upload_path;
			if (!is_dir($upload_path)) {
				wp_mkdir_p($upload_path);
			}

			update_option('wpcsw_settings', $wpcsw_options);
			$msg = '<div class="updated"><p><strong>' . __('Settings Saved') . '</strong></p></div>';
		} //nounce
	}

	$wpcsw_options = get_option('wpcsw_settings');
	if ($wpcsw_options["settings"]) {
		extract($wpcsw_options["settings"], EXTR_OVERWRITE);
	}

	$upload_dir = $wp_upload_dir_path . '/' . $upload_path;

	if (!is_dir($upload_dir)) {
		$msg = '<div class="updated"><p><strong>' . __('Upload directory doesn\'t exist.') . '</strong></p></div>';
	}

	$select = '<option value="demo">Demo Mode</option><option value="licensed">Licensed</option><option value="debug">Debugging Mode</option>';
	$select = str_replace('value="' . $mode . '"', 'value="' . $mode . '" selected', $select);
	?>
    <style type="text/css">#wpcsw_page_setting img { cursor: pointer; }</style>
    <div class="wrap">
        <div class="icon32" id="icon-settings"><br/></div>
        <?php echo wp_kses($msg, wpcsw_kses_allowed_options()); ?>
        <h2> Default Settings</h2>
        <form action="" method="post">
            <?php echo wp_kses(wp_nonce_field('wpcopysafeweb_settings', 'wpcopysafeweb_wpnonce'), wpcsw_kses_allowed_options()); ?>
            <table cellpadding='1' cellspacing='0' border='0' id='wpcsw_page_setting'>
                <p><strong>Default settings applied to all protected
                        pages:</strong></p>
                <tbody>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Allow admin only for new uploads.'></td>
                    <td align="left" nowrap>Allow Admin Only:</td>
                    <td align="left"><input name="admin_only" type="checkbox"
                                            value="checked" <?php echo $admin_only ? 'checked' : ''; ?>>
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Path to the upload folder for Web.'>
                    <td align="left" nowrap>Upload Folder:</td>
                    <td align="left"><input value="<?php echo esc_attr($upload_path); ?>"
                                            name="upload_path"
                                            class="regular-text code"
                                            type="text"><br />
                        Only specify the folder name. It will be located in site's upload directory, <?php echo esc_attr($wp_upload_dir_path); ?>.
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Set the mode to use. Use Licensed if you have licensed images. Otherwise set for Demo or Debug mode.'>
                    </td>
                    <td align="left">Mode</td>
                    <td align="left"><select
                                name="mode"><?php echo wp_kses($select, wpcsw_kses_allowed_options()); ?></select></td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Enter minimum version for ArtisBrowser to allow access.'>
                    </td>
                    <td align="left">Minimum Version</td>
                    <td align="left">
                        <input type="text" size="8" name="latest_version" value="<?php echo esc_attr($latest_version ? $latest_version : 34.9); ?>" />
                        <br />
                        Enter minimum version for ArtisBrowser to check. 
                    </td>
                </tr>
                <tr class="copysafe-video-browsers">
                    <td colspan="5"><h2 class="title">Browser allowed</h2></td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Allow visitors using the ArtisBrowser to access this page.'>
                    </td>
                    <td align="left" nowrap>Allow ArtisBrowser:</td>
                    <td align="left"><input name="asps" type="checkbox"
                                            value="checked" <?php echo esc_attr($asps); ?>>
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Allow visitors using the Firefox web browser to access this page.'>
                    </td>
                    <td align="left">Allow Firefox:</td>
                    <td align="left"><input name="ff" type="checkbox"
                                            <?php echo esc_attr($ff ? 'checked': ''); ?>> ( for test only )</td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Allow visitors using the Chrome web browser to access this page.'>
                    </td>
                    <td align="left">Allow Chrome:</td>
                    <td align="left"><input name="ch" type="checkbox"
                                            <?php echo esc_attr($ch ? 'checked' : ''); ?>> ( for test only )</td>
                </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" value="Save Settings"
                       class="button-primary" id="submit" name="submit">
            </p>
        </form>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <script type='text/javascript'>
      jQuery(document).ready(function () {
        jQuery("#wpcsw_page_setting img").click(function () {
          alert(jQuery(this).attr("alt"));
        });
      });
    </script>
  <?php
}