<?php
if (!defined('ABSPATH')) {
	exit;
} // Exit when accessed directly

function wpcsw_ajaxprocess()
{
	if ($_POST["fucname"] == "check_upload_nonce")
	{
		if (!wp_verify_nonce($_POST['nonce_value'], 'wpcsw_upload_nonce')) {
			echo "0";
			wp_nonce_ays('');
			exit();
		}
		//return;
	}

	if ($_POST["fucname"] == "file_upload")
	{
		$msg = wpcsw_file_upload($_POST);
		$upload_list = get_wpcsw_uploadfile_list();
		$data = [
			"message" => $msg,
			"list" => $upload_list,
		];
		echo wp_json_encode($data);
	}
	elseif ($_POST["fucname"] == "file_search")
	{
		$data = wpcsw_file_search($_POST);
		echo wp_kses($data, wpcsw_kses_allowed_options());
	}
	else if ($_POST["fucname"] == "setting_save")
	{
		$data = wpcsw_setting_save($_POST);
		echo wp_kses($data, wpcsw_kses_allowed_options());
	}
	else if ($_POST["fucname"] == "get_parameters")
	{
		$data = wpcsw_get_parameters($_POST);
		echo wp_kses($data, wpcsw_kses_allowed_options());
	}

	exit;
}

function wpcsw_get_parameters($param)
{
	$default_settings = [];
	$postid           = (int)$param["post_id"];
	$filename         = trim(sanitize_text_field($param["filename"]));
	$settings         = wpcsw_get_first_class_settings();

	$options = get_option("wpcsw_settings");
	if ($options["classsetting"][$postid][$filename])
	{
		$settings = wp_parse_args($options["classsetting"][$postid][$filename], $default_settings);
	}

	extract($settings);

	$width = sanitize_text_field($width);
	$height = sanitize_text_field($height);
	$border = sanitize_text_field($border);
	$border_color = sanitize_text_field($border_color);
	$text_color = sanitize_text_field($text_color);
	$loading_message = sanitize_text_field($loading_message);
	$hyperlink = sanitize_text_field($hyperlink);
	$target = sanitize_text_field($target);

	$key_safe = ($key_safe) ? 1 : 0;
	$capture_safe = ($capture_safe) ? 1 : 0;
	$menu_safe = ($menu_safe) ? 1 : 0;
	$remote_safe = ($remote_safe) ? 1 : 0;

	$params =
		" width='" . esc_attr( $width ) . "'" .
		" height='" . esc_attr( $height ) . "'" .
		" border='" . esc_attr( $border ) . "'" .
		" border_color='" . esc_attr( $border_color ) . "'" .
		" key_safe='" . esc_attr( $key_safe ) . "'" .
		" capture_safe='" . esc_attr( $capture_safe ) . "'" .
		" menu_safe='" . esc_attr( $menu_safe ) . "'" .
		" remote_safe='" . esc_attr( $remote_safe ) . "'" .
		" text_color='" . esc_attr( $text_color ) . "'" .
		" loading_message='" . esc_attr( $loading_message ) . "'" .
		" hyperlink='" . esc_attr( $hyperlink ) . "'" .
		" target='" . esc_attr( $target ) . "'";

	return $params;
}

function wpcsw_get_first_class_settings() {
	$settings = [
		'key_safe' => 0,
		'capture_safe' => 0,
		'menu_safe' => 0,
		'remote_safe' => 0,
		'border' => 0,
		'border_color' => '000000',
		'text_color' => 'FFFFFF',
		'loading_message' => 'Image loading...',
		'hyperlink' => '',
		'target' => "_top",
	];
	return $settings;
}

function wpcsw_file_upload($param)
{
	$file_error = $param["error"];
	$file_errors = [
		0 => __("There is no error, the file uploaded with success"),
		1 => __("The uploaded file exceeds the upload_max_filesize directive in php.ini"),
		2 => __("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
		3 => __("The uploaded file was only partially uploaded"),
		4 => __("No file was uploaded"),
		6 => __("Missing a temporary folder"),
		7 => __("Upload directory is not writable"),
		8 => __("User not logged in"),
	];

	if ($file_error == 0) {
		$msg = '<div class="updated"><p><strong>' . esc_html(__('File Uploaded. You must save "File Details" to insert post', 'wp-copysafe-web')) . '</strong></p></div>';
	}
	else {
		$msg = '<div class="error"><p><strong>' . esc_html(__('Error')) . '!</strong></p><p>' . esc_html($file_errors[$file_error]) . '</p></div>';
	}

	return $msg;
}

function wpcsw_file_search($param)
{
	if(empty($param['search']) || empty($param['post_id']))
	{
		return '';
	}

	$postid = (int)$param['post_id'];
	$search = trim(sanitize_text_field($param["search"]));

	$files = _get_wpcsw_uploadfile_list();

	$result = FALSE;
	foreach ($files as $file)
	{
		if ($search == trim($file["filename"]))
		{
			$result = TRUE;
		}
	}

	if ( ! $result)
	{
		return "<hr /><h2>No found file</h2>";
	}

	$file_options = wpcsw_get_first_class_settings();

	$wpcsw_options = get_option('wpcsw_settings');
	if ($wpcsw_options["classsetting"][$postid][$search])
	{
		$file_options = $wpcsw_options["classsetting"][$postid][$search];
	}

	extract($file_options, EXTR_OVERWRITE);

	$width = sanitize_text_field($width);
	$height = sanitize_text_field($height);
	$border = sanitize_text_field($border);
	$border_color = sanitize_text_field($border_color);
	$text_color = sanitize_text_field($text_color);
	$loading_message = sanitize_text_field($loading_message);
	$hyperlink = sanitize_text_field($hyperlink);
	$target = sanitize_text_field($target);

	$key_safe = ($key_safe) ? "checked" : 0;
	$capture_safe = ($capture_safe) ? "checked" : 0;
	$menu_safe = ($menu_safe) ? "checked" : 0;
	$remote_safe = ($remote_safe) ? "checked'" : 0;

    $str = "<hr />
      <div class='icon32' id='icon-file'><br /></div>
        <h2>Page Settings</h2>
        <div>
        <table cellpadding='0' cellspacing='0' border='0' >
            <tbody id='wpcsw_setting_body'> 
            <tr> 
              <td align='left' width='40'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Width in pixels. For auto width set 0.' /></td>
              <td align='left' nowrap>Custom Width:</td>
              <td> 
                <input name='width' id='wpcsw_width' type='text' value='" . esc_attr($width) . "' size='3' />
              </td>
              <td align='left'>&nbsp;</td>
              <td align='left' width='40'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Height in pixels. For auto height set 0.' /></td>
              <td align='left' nowrap>Custom Height:</td>
              <td> 
                <input name='height' id='wpcsw_height' type='text' value='" . esc_attr($height) . "' size='3' />
              </td>
            </tr>
            <tr> 
              <td align='left' width='40'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Border thickness in pixels. For no border set 0.' /></td>
              <td align='left'>Border size:</td>
              <td> 
                <input name='border' id='wpcsw_border' type='text' value='" . esc_attr($border) . "' size='3' />
              </td>
              <td align='left'>&nbsp;</td>
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Color of the border and image backround area. For example use FFFFFF for white and 000000 is for black... without the # symbol.' /></td>
              <td align='left'>Border color:</td>
              <td> 
                <input name='border_color' id='wpcsw_border_color' type='text' value='" . esc_attr($border_color) . "' size='7' />
              </td>
            </tr>
            <tr> 
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Check this box to disable Printscreen and screen capture when the class image loads.'></td>
              <td align='left' nowrap>Prevent Capture:</td>
              <td> 
                <input name='capture_safe' type='checkbox' value='1' $capture_safe>
              </td>
              <td align='left'>&nbsp;</td>
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Check this box to disable use of the keyboard when the class image loads.' /></td>
              <td align='left' nowrap>Prevent Keyboard:</td>
              <td> 
                <input name='key_safe' id='wpcsw_key_safe' type='checkbox' value='1' " . esc_attr($key_safe) . ">
              </td>
            </tr>
            <tr> 
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Check this box to disable use of browser menus. This option is browser dependent.'></td>
              <td align='left' nowrap>Prevent Menus:</td>
              <td> 
                <input name='menu_safe' type='checkbox' value='1' " . esc_attr($menu_safe) . ">
              </td>
              <td align='left'>&nbsp;</td>
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Check this box to prevent viewing by remote or virtual computers when the class image loads.'></td>
              <td align='left' nowrap>Prevent Remote:</td>
              <td> 
                <input name='remote_safe' type='checkbox' value='1' " . esc_attr($remote_safe) . ">
              </td>
            </tr>
            <tr> 
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Color of the text message that is displayed in the image area sas the image downloads.' /></td>
              <td align='left' nowrap>Text color:</td>
              <td> 
                <input name='text_color' id='wpcsw_text_color' type='text' value='" . esc_attr($text_color) . "' size='7' />
              </td>
              <td align='left'>&nbsp;</td>
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Set the message to display as this class image loads.' /></td>
              <td align='left' nowrap>Loading message:&nbsp;</td>
              <td> 
                <input name='loading_message' id='wpcsw_loading_message' type='text' value='" . esc_attr($loading_message) . "' size='20' />
              </td>
            </tr>
            <tr> 
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Set the target frame for the hyperlink, for example _top' /></td>
              <td align='left' nowrap>Target frame:</td>
              <td> 
                <input value='" . esc_attr($target) . "' name='target' id='wpcsw_target' type='text' size='10' />
              </td>
              <td align='left'>&nbsp;</td>
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Add a link to another page activated by clciking on the image, or leave blank for no link.' /></td>
              <td align='left' nowrap>Hyperlink:</td>
              <td> 
                <input value='" . esc_attr($hyperlink) . "' name='hyperlink' id='wpcsw_hyperlink' type='text' size='20' />
              </td>
            </tr>
            </tbody> 
          </table>
          <p class='submit'>
              <input type='button' value='Save' class='button-primary' id='setting_save' name='submit' />
              <input type='button' value='Cancel' class='button-primary' id='cancel' />
          </p>
      </div>";

	return $str;
}

function wpcsw_setting_save($param)
{
	$postid = (int)$param["post_id"];
	$name   = trim(sanitize_text_field($param["nname"]));
	$data   = (array) json_decode(stripcslashes($param["set_data"]));

	// escape user inputs
	$data = array_map("esc_attr", $data);
	extract($data);

	$border          = (empty($border) ? '0' : esc_attr($border));
	$border_color    = (empty($border_color) ? '' : esc_attr($border_color));
	$text_color      = (empty($text_color) ? '' : esc_attr($text_color));
	$loading_message = (empty($loading_message) ? '' : esc_attr($loading_message));

	$wpcsw_settings = get_option('wpcsw_settings');
	if (!is_array($wpcsw_settings))
	{
		$wpcsw_settings = [];
	}

	$width     = sanitize_text_field($width);
	$height    = sanitize_text_field($height);
	$hyperlink = sanitize_text_field($hyperlink);
	$target    = sanitize_text_field($target);

	$key_safe = ($key_safe) ? 1 : 0;
	$capture_safe = ($capture_safe) ? 1 : 0;
	$menu_safe = ($menu_safe) ? 1 : 0;
	$remote_safe = ($remote_safe) ? 1 : 0;

	$datas = [
		'border' => "$border",
		"width" => "$width",
		"height" => "$height",
		'border_color' => "$border_color",
		'text_color' => "$text_color",
		'loading_message' => "$loading_message",
		'key_safe' => "$key_safe",
		'capture_safe' => "$capture_safe",
		'menu_safe' => "$menu_safe",
		'remote_safe' => "$remote_safe",
		'hyperlink' => "$hyperlink",
		'target' => "$target",
		'postid' => "$postid",
		'name' => "$name",
	];


	$wpcsw_settings["classsetting"][$postid][$name] = $datas;
	update_option('wpcsw_settings', $wpcsw_settings);

	$msg = '<div class="updated fade">
				<strong>' . __('File Options Are Saved', 'wp-copysafe-web') . '</strong><br />
				<div style="margin-top:5px;"><a href="#" alt="' . esc_attr( $name ) . '" class="button-secondary sendtoeditor"><strong>Insert file to editor</strong></a></div>
			</div>';

	return $msg;
}

function _get_wpcsw_uploadfile_list()
{
	$listdata = [];

	if (!is_dir(WPCSW_UPLOAD_PATH)) {
		return $listdata;
	}

	$file_list = scandir(WPCSW_UPLOAD_PATH);

	foreach ($file_list as $file)
	{
		if ($file == "." || $file == "..") {
			continue;
		}
		$file_path = WPCSW_UPLOAD_PATH . $file;
		if (filetype($file_path) != "file") {
			continue;
		}
		$filename = explode('.', $file);
		$ext = end($filename);
		if ($ext != "class") {
			continue;
		}

		$file_path = WPCSW_UPLOAD_PATH . $file;
		$file_name = $file;
		$file_size = filesize($file_path);
		$file_date = filemtime($file_path);

		if (round($file_size / 1024, 0) > 1) {
			$file_size = round($file_size / 1024, 0);
			$file_size = "$file_size KB";
		}
		else {
			$file_size = "$file_size B";
		}

		$file_date = gmdate("n/j/Y g:h A", $file_date);

		$listdata[] = [
			"filename" => $file_name,
			"filesize" => $file_size,
			"filedate" => $file_date,
		];
	}

	return $listdata;
}

function get_wpcsw_uploadfile_list()
{
	$table = '';
	$files = _get_wpcsw_uploadfile_list();

	foreach ($files as $file)
	{
		//$link = "<div class='row-actions'>
		//			<span><a href='#' alt='{$file["filename"]}' class='setdetails row-actionslink' title=''>Setting</a></span>&nbsp;|&nbsp;
		//			<span><a href='#' alt='{$file["filename"]}' class='sendtoeditor row-actionslink' title=''>Insert to post</a></span>
		//		</div>" ;
		// prepare table row
		$table .=
			"<tr><td></td><td><a href='#' data-alt='" . esc_attr($file["filename"]) . "' class='sendtoeditor row-actionslink'>" . esc_attr($file["filename"]) . "</a></td>".
			"<td width='50px'>" . esc_attr($file["filesize"]) . "</td><td width='130px'>" . esc_attr($file["filedate"]) . "</td></tr>";
	}

	if ( ! $table) {
		$table .= '<tr><td colspan="3">' . __('No file uploaded yet.', 'wp-copysafe-web') . '</td></tr>';
	}

	return $table;
}

function get_wpcsw_browser_info()
{
	$u_agent  = $_SERVER['HTTP_USER_AGENT'];
	$bname    = 'Unknown';
	$platform = 'Unknown';
	$version  = "";

	//First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'linux';
	}
	else if (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'mac';
	}
	else if (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'windows';
	}

	// Next get the name of the user-agent yes seperately and for good reason
	if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
		$bname = 'Internet Explorer';
		$ub = "MSIE";
	}
	else if(preg_match('/Firefox/i',$u_agent)){
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	}
	else if(preg_match('/OPR/i',$u_agent)){
		$bname = 'Opera';
		$ub = "Opera";
	}
	else if(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
		$bname = 'Google Chrome';
		$ub = "Chrome";
	}
	else if(preg_match('/Safari/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
		$bname = 'Apple Safari';
		$ub = "Safari";
	}
	else if(preg_match('/Netscape/i',$u_agent)){
		$bname = 'Netscape';
		$ub = "Netscape";
	}
	else if(preg_match('/Edge/i',$u_agent)){
		$bname = 'Edge';
		$ub = "Edge";
	}
	else if(preg_match('/Trident/i',$u_agent)){
		$bname = 'Internet Explorer';
		$ub = "MSIE";
	}

	// finally get the correct version number
	$known = array('Version', @$ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
	}
	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if (strripos($u_agent,"Version") < strripos($u_agent,@$ub)){
			$version= $matches['version'][0];
		}
		else {
			$version = $matches['version'][1];
		}
	}
	else {
		$version = $matches['version'][0];
	}

	// check if we have a number
	if( $version == null || $version == "" ) {
		$version = "?";
	}

	return array(
		'userAgent' => $u_agent,
		'name'      => $bname,
		'version'   => $version,
		'platform'  => $platform,
		'pattern'   => $pattern
	);
}

function wpcsw_check_artis_browser_version()
{
	$wpcsv_current_browser = get_wpcsw_browser_info();
	$wpcsv_current_browser_data = $wpcsv_current_browser['userAgent'];
	
	if( $wpcsv_current_browser_data != "" )
	{
		$wpcsv_browser_data = explode("/", $wpcsv_current_browser_data);
		if (strpos($wpcsv_current_browser_data, 'ArtisBrowser') !== false)
		{
			$current_version = end($wpcsv_browser_data);
			$wpcsw_options = get_option('wpcsw_settings');
			$latest_version = $wpcsw_options["settings"]["latest_version"];
			if( $current_version < $latest_version )
			{
				$ref_url = get_permalink(get_the_ID());
			?>
				<script>
				document.location = '<?php echo esc_js(WPCSW_PLUGIN_URL."download-update.html?ref=". urlencode($ref_url)); ?>';
				</script>
				<?php
			}
		}
	}
}