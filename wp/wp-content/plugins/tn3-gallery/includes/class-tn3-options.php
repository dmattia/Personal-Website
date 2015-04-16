<?php

class TN3_Options
{

    private static $definition = array();
    private static $current = array();
    public static $defaults = array();

    private static function section($id, $title, $admin = false, $form = true)
    {
	$all =& TN3_Options::$definition;
	$all[$id] = array(  'title'	=> $title,
			    'admin'	=> $admin,
			    'form'	=> $form,
			    'fields'	=> array()
			);
	TN3_Options::$defaults[$id] = array();
    }
    private static function field($section, $name, $value, $type = '', $title = '',  $desc = '', $att = array(), $e = array(), $valid = null)
    {
	$f =& TN3_Options::$definition[$section]['fields'];
	$f[$name] = array( 'id'	    => "tn3_".$section."_".$name,
			   'name'   => $name,
			   'value'  => $value,
			   'type'   => $type,
			   'title'  => $title,
			   'desc'   => $desc,
			   'att'    => $att,
			   'e'	    => $e,
			   'valid'  => $valid );
	TN3_Options::$defaults[$section][$name] = $value;
    }

    // merges defaults and currently active options and returns them
    public static function get($admin = false)
    {
	if (count(TN3_Options::$definition) == 0) {
	    TN3_Options::define();

	    foreach (TN3_Options::$definition as $sk => $sv) {
		if (!$admin && $sv['admin']) continue;

		TN3_Options::$current[$sk] = array();
		foreach ( $sv['fields'] as $k => $v ) {
		    if ( is_array($v['value']) ) {
			TN3_Options::$current[$sk][$k] = array();
			foreach ( $v['value'] as $ok => $ov ) {
			    TN3_Options::$current[$sk][$k][$ok] = $ov;
			}
		    } else TN3_Options::$current[$sk][$k] = $v['value'];
		}

		$name = $sv['admin']? "tn3_admin_$sk" : "tn3_$sk";
		$copt = get_option($name);
		if (!$copt) {
		    add_option($name, TN3_Options::$current[$sk], '', $sv['admin']? 'no':'yes');
		} else {
		    foreach ($copt as $k => $v) {
			if (is_array($v)) {
			    foreach ($v as $ok => $ov) {
				TN3_Options::$current[$sk][$k][$ok] = $ov;
			    }
			} else TN3_Options::$current[$sk][$k] = $v;
		    }
		}
	    }
	}
	//tn3log::w(TN3_Options::$defaults);
	return TN3_Options::$current;
    }
    // returns array of sections that appear in the forms
    public static function getForms()
    {
	if (count(TN3_Options::$definition) == 0) TN3_Options::define();
	$ret = array();
	foreach (TN3_Options::$definition as $k => $v) if (TN3_Options::$definition[$k]['form']) $ret[$k] = $v;
	return $ret;
    }
    private static function define()
    {

	TN3_Options::section(	'general',
				'General',
				true
	);
	TN3_Options::field( 'general',
			    'line_g0_s',
			    '<hr />',
			    'html',
			    __('<strong>License</strong>', 'tn3-gallery')
	);
	TN3_Options::field('general',
			   'license_key',
			   '',
			   'textfield',
			   'Key:',

			   __('TN3 Pro version license key.<br/><em>Used for automatic updates.</em>', 'tn3-gallery'),


			   array( 'size' => 60 )
	);
	TN3_Options::field( 'general',
			    'line_g1_s',
			    '<hr />',
			    'html',
			    __('<strong>Images</strong>', 'tn3-gallery')
	);
	TN3_Options::field('general',
			   'size_0',
			   array('w' => 1024, 'h' => 1024, 'crop' => 0, 'q' => 90, 'name' => 'default'),
			   'image_size',
			   __('Default Dimensions:', 'tn3-gallery'),
			   "","","",//"default image size", '', '',
			   array('w' => 20, 'h' => 20, 'q' => array(0, 100))
	);
	TN3_Options::field('general',
			   'size_1',
			   array('w' => '', 'h' => 90, 'crop' => 0, 'q' => 90, 'name' => 'thumbnail'),
			   'image_size',
			   __('Thumbnail Dimensions:', 'tn3-gallery'),
			   "",//"default thumbnail.<em> You can't change those settings.</em>",
			   true
	);
	TN3_Options::field('general',
			   'size_2',
			   array('w' => 90, 'h' => 90, 'crop' => 1, 'q' => 90, 'name' => 'square thumbnail'),
			   'image_size',
			   __('Square Thumbnail Dimensions:', 'tn3-gallery'),
			   "",//"default square thumbnail.<em> You can't change those settings.</em>",
			   true
	);
	
	TN3_Options::field('general',
			   'size_3',
			   array('w' => '', 'h' => '', 'crop' => 0, 'q' => 90, 'required' => 0, 'name' => 'custom 1'),
			   'image_size',
			   __('Custom Dimensions', 'tn3-gallery').' 1:',
			   "","","",//"custom size 1", '', '',
			   array('w' => 20, 'h' => 20, 'q' => array(0, 100))
	);
	TN3_Options::field('general',
			   'size_4',
			   array('w' => '', 'h' => '', 'crop' => 0, 'q' => 90, 'required' => 0, 'name' => 'custom 2'),
			   'image_size',
			   __('Custom Dimensions', 'tn3-gallery').' 2:',
			   "","","",//"custom size 2", '', '',
			   array('w' => 20, 'h' => 20, 'q' => array(0, 100))
	);
	TN3_Options::field('general',
			   'size_5',
			   array('w' => '', 'h' => '', 'crop' => 0, 'q' => 90, 'required' => 0, 'name' => 'custom 3'),
			   'image_size',
			   __('Custom Dimensions', 'tn3-gallery').' 3:',
			   "","","",//"custom size 3", '', '',
			   array('w' => 20, 'h' => 20, 'q' => array(0, 100))
		       );

	TN3_Options::field('general',
			   'path',
			   'wp-content/tn3',
			   'textfield',
			   __('Image Path:', 'tn3-gallery'),
			   __('path where images will be stored.', 'tn3-gallery'),
			   array(	'size' => 60, 'readonly' => 'readonly')
	);
	TN3_Options::field('general',
			   'remove_images',
			   1,
			   'checkbox',
			   __('Remove TN3 data:', 'tn3-gallery'),
			   __("If this option is checked, image files and TN3 presets will be deleted when plugin is uninstalled.", 'tn3-gallery')
	);
	/*
	TN3_Options::field('images',
			   'library',
			   'gd',
			   'radio',
			   'Graphic Library:',
			   "path where images will be stored <br/><em>changing this path after you uploaded images is not recomended</em>",
			   array(	 ),
			   array( 'gd' => 'GD Library', 'im' => 'Image Magick' )
		       );
	 */
	/*
	TN3_Options::field( 'general',
			    'line_g2_s',
			    '<hr />',
			    'html',
			    '<strong>Miscs</strong>'
	);
	TN3_Options::field('general',
			   'color',
			   'red',
			   'combo',
			   'Choose Color:',
			   "path where images will be stored <br/><em>changing this path after you uploaded images is not recomended</em>",
			   '',
			   array( 'red' => 'Red Color', 'blue' => 'Blue Color', 'green' => "Green Color" )
	);
	TN3_Options::field('general',
			   'media_feed',
			   0,
			   'checkbox',
			   'Media RSS Feed:',
			   "If set, media RSS feed will be added to the page header"
		       );
	 */
	TN3_Options::field( 'general',
			    'line_g3_s',
			    '<hr />',
			    'html',
			    __('<strong>Upload</strong>', 'tn3-gallery')
	);
	TN3_Options::field('general',
			   'runtimes',
			   'html5,flash,silverlight,gears,browserplus,html4',
			   'textfield',
			   __('Runtimes:', 'tn3-gallery'),
			   __('Runtimes will be attempted in the specified order. <br/><em>separate runtimes with commas</em>', 'tn3-gallery'),
			   array( 'size' => 60)
	);
	TN3_Options::field('general',
			   'max_file_size',
			   '',
			   'textfield',
			   __('Maximum permitted file size:', 'tn3-gallery'),
			   __("permitted suffixes: <b>kb, mb, gb (e.g. 200mb)</b> <br/><em>there is no default limit.</em>", 'tn3-gallery'),
			   array(	'size' => 8 )
	);
	/*
	TN3_Options::field('general',
			   'chunk_size',
			   '200kb',
			   'textfield',
			   __('Chunk Size:', 'tn3-gallery'),
			   __("allowed suffixes: <b>kb, mb, gb</b>; files will be split to chunks, if possible, and reconstructed on server <br/><em>this way one might for example overcome server limitation on maximum file upload size</em>", 'tn3-gallery'),
			   array(	'size' => 12 )
		       );*/
	TN3_Options::field('general',
			   'resize',
			   1,
			   'checkbox',
			   __('Resize:', 'tn3-gallery'),
			   __("Image will be resized before uploading if set and frontend resizing is possible.", 'tn3-gallery')
	);
	TN3_Options::section(	'skin',
				'Skin',
				true
	);
	TN3_Options::field('skin',
			   'presets',
			   'default',
			   'presets',
			   __('Preset:', 'tn3-gallery'),
			   '',
			   '',
			   'skin'
	);
	TN3_Options::field( 'skin',
			    'line_l1_s',
			    '<hr />',
			    'html',
			    __('<strong>GENERAL</strong>', 'tn3-gallery')
	);
	$sk = get_option('tn3_installed_skins');
	$sko = array();
	foreach ($sk['default'] as $k => $v) {
	    $sko["$k,$k"] = $k;
	    foreach ($v as $var) {
		if ($k != $var) $sko["$k,$var"] = " - $var";
	    }
	}
	if (isset($sk['custom'])) {
	    foreach ($sk['custom'] as $k => $v) {
		$sko["$k,$k,custom"] = $k."*";
		foreach ($v as $var) {
		    if ($k != $var) $sko["$k,$var,custom"] = " - $var";
		}
	    }
	}
	TN3_Options::field('skin',
			   'general_skin_o',
			   'tn3/tn3',
			   'combo',
			   __('Skin:', 'tn3-gallery'),
			   __('name of the html file to be loaded as a skin.', 'tn3-gallery'),
			   '',
			   $sko
	);
	$di = get_option('tn3_admin_general');
	if (!$di) $di = TN3_Options::$defaults['general'];
	$idi = array();
	foreach ($di as $k => $v) {
	    if (substr($k, 0, 4) != "size" || (isset($v['required']) && $v['required'] != 1) ) continue;
	    $idi["/".substr($k, 5, 1)] = $v['name'].' - '.$v['w'].'x'.$v['h'].'px';
	}
	$idi[''] = 'original';
	
	TN3_Options::field('skin',
			   'general_imageSize_s',
			   '/0',
			   'combo',
			   __('Image Dimensions:', 'tn3-gallery'),
			   __('Dimensions to use for the image.', 'tn3-gallery'),
			   '',
			   $idi
		       );
	TN3_Options::field('skin',
			   'general_thumbnailSize_s',
			   '/2',
			   'combo',
			   __('Thumbnail Dimensions:', 'tn3-gallery'),
			   __('Dimensions to use for the thumbnail.', 'tn3-gallery'),
			   '',
			   $idi
	);
	 
	TN3_Options::field('skin',
			   'general_width_n',
			   '0',
			   'textfield',
			   __('Default Width:', 'tn3-gallery'),
			   __("Width of the TN3 Gallery. Set to 0 to use the width defined in the CSS.", 'tn3-gallery'),
			   array(	'size' => 8 )
	);
	TN3_Options::field('skin',
			   'general_height_n',
			   '0',
			   'textfield',
			   __('Default Height:', 'tn3-gallery'),
			   __("Height of the TN3 Gallery. Set to 0 to use the height defined in the CSS.", 'tn3-gallery'),
			   array(	'size' => 8 )
		       );
	/*
	TN3_Options::field('skin',
			   'general_fullOnly_b',
			   0,
			   'checkbox',
			   __('Full Screen Only:', 'tn3-gallery'),
			   __('If set, tn3 will be visible only in fullscreen', 'tn3-gallery')
		       );
	TN3_Options::field('skin',
			   'general_imageClick_s',
			   'next',
			   'combo',
			   __('Image Click Action:', 'tn3-gallery'),
			   __("Action to take when an image is clicked", 'tn3-gallery'),
			   '',
			   array( 'next' => __('Show Next Image', 'tn3-gallery'), 'url' => __('Open URL', 'tn3-gallery'), 'fullscreen' => __("Go Full Screen", 'tn3-gallery') )
		       );
	 */
	TN3_Options::field('skin',
			   'general_delay_n',
			   '7000',
			   'textfield',
			   __('Slideshow Delay:', 'tn3-gallery'),
			   __("Slideshow delay in milliseconds.", 'tn3-gallery'),
			   array(	'size' => 8 )
		       );
	TN3_Options::field('skin',
			   'general_useNativeFullScreen_b',
			   1,
			   'checkbox',
			   __('Use Native FullScreen:', 'tn3-gallery'),
			   __("If set, native browser's fullscreen support will be used.", 'tn3-gallery')
		       );
	/*
	TN3_Options::field('skin',
			   'general_startWithAlbums_b',
			   0,
			   'checkbox',
			   __('Show Album List:', 'tn3-gallery'),
			   __('If set, tn3 will init with album list.', 'tn3-gallery')
		       );
	TN3_Options::field('skin',
			   'general_timerMode_s',
			   'bar',
			   'combo',
			   __('Timer Mode:', 'tn3-gallery'),
			   __("Type of slideshow timer animation", 'tn3-gallery'),
			   '',
			   array( 'char' => __('Character Based', 'tn3-gallery'), 'bar' => __('Bar', 'tn3-gallery') )
	);
	TN3_Options::field('skin',
			   'general_timerSteps_n',
			   '500',
			   'textfield',
			   __('Timer Steps:', 'tn3-gallery'),
			   __("The number of timer steps.", 'tn3-gallery'),
			   array(	'size' => 8 )
	);
	TN3_Options::field('skin',
			   'general_timerStepChar_s',
			   '&#8226;',
			   'textfield',
			   __('Timer Character:', 'tn3-gallery'),
			   __("Character used for timer step", 'tn3-gallery'),
			   array(	'size' => 8 )
		       );
	 */
	TN3_Options::field( 'skin',
			    'line_l2_s',
			    '<hr />',
			    'html',
			    __('<strong>IMAGE</strong>', 'tn3-gallery')
	);

	TN3_Options::field('skin',
			   'image_crop_b',
			   0,
			   'checkbox',
			   __('Image Crop:', 'tn3-gallery'),
			   __('If set, image may be cropped to fill available area up to the maxZoom value.', 'tn3-gallery')
		       );

	TN3_Options::field('skin',
			   'image_idleDelay_n',
			   '3000',
			   'textfield',
			   __('Idle Delay:', 'tn3-gallery'),
			   __("Delay in milliseconds until elements are hidden. Set to 0 to disable this feature.", 'tn3-gallery'),
			   array(	'size' => 8 )
	);
	TN3_Options::field('skin',
			   'image_maxZoom_n',
			   '2',
			   'textfield',
			   __('Maximum Zoom:', 'tn3-gallery'),
			   __("The maximum amount of scale that can be applied to the image when it’s smaller than the available area.<br /><em>2 = 200%</em>", 'tn3-gallery'),
			   array(	'size' => 8 )
	);
	TN3_Options::field('skin',
			   'image_clickEvent_s',
			   'click',
			   'combo',
			   __('Image Click Action:', 'tn3-gallery'),
			   __("Click event can be triggered on single or double mouse click.", 'tn3-gallery'),
			   '',
			   array( 'click' => __('Single Click', 'tn3-gallery'), 'dblclick' => __('Double Click', 'tn3-gallery') )
	);
	TN3_Options::field( 'skin',
			    'line_l3_s',
			    '<hr />',
			    'html',
			    __('<strong>THUMBNAILS</strong>', 'tn3-gallery')
			);
	TN3_Options::field('skin',
			   'thumbnailer_align_n',
			   1,
			   'combo',
			   __('Alignment:', 'tn3-gallery'),
			   __("Aligns the thumbnails when they do not fill the carousel area.", 'tn3-gallery'),
			   '',
			   array( '0' => __('Left or Top', 'tn3-gallery'), '1' => __('Center or Middle', 'tn3-gallery'), '2' => __('Right or Bottom', 'tn3-gallery') )
	);
	TN3_Options::field('skin',
			   'thumbnailer_buffer_n',
			   '20',
			   'textfield',
			   __('Movement Buffer:', 'tn3-gallery'),
			   __("The 'dead area' at the edges of the thumbnailer that doesn't respond to the mouse.<br /><em>In pixels</em>", 'tn3-gallery'),
			   array(	'size' => 8 )
		       );
	TN3_Options::field('skin',
			   'thumbnailer_overMove_b',
			   1,
			   'checkbox',
			   __('Move on Mouse Over:', 'tn3-gallery'),
			   __('If set, thumbnails move on mouse over.', 'tn3-gallery')
		       );
	TN3_Options::field('skin',
			   'thumbnailer_mode_s',
			   'thumbs',
			   'combo',
			   __('Mode:', 'tn3-gallery'),
			   __("Show thumbnails, bullets or numbers. Bullets and numbers require the skin to support them.", 'tn3-gallery'),
			   '',
			   array( 'thumbs' => 'thumbnails', 'bullets' => 'bullets', 'numbers' => 'numbers' )
	);
	TN3_Options::field('skin',
			   'thumbnailer_speed_n',
			   '8',
			   'textfield',
			   __('Movement Speed:', 'tn3-gallery'),
			   __("The mouse over movement speed.", 'tn3-gallery'),
			   array(	'size' => 8 )
		       );
	TN3_Options::field('skin',
			   'thumbnailer_slowdown_n',
			   '50',
			   'textfield',
			   __('Movement Slowdown:', 'tn3-gallery'),
			   __("Deceleration speed when the mouse leaves the scroll area.", 'tn3-gallery'),
			   array(	'size' => 8 )
		       );
	TN3_Options::field('skin',
			   'thumbnailer_shaderColor_s',
			   '#000000',
			   'textfield',
			   __('Color of Shader:', 'tn3-gallery'),
			   __("The color of the shader.", 'tn3-gallery'),
			   array(	'size' => 8 )
		       );
	TN3_Options::field('skin',
			   'thumbnailer_shaderOpacity_n',
			   '0.5',
			   'textfield',
			   __('Opacity of Shader:', 'tn3-gallery'),
			   __("The opacity of the shader.", 'tn3-gallery'),
			   array(	'size' => 8 )
		       );
	TN3_Options::field('skin',
			   'thumbnailer_shaderDuration_n',
			   '200',
			   'textfield',
			   __('Shader Fade Duration:', 'tn3-gallery'),
			   __("The duration for shader fadeIn.", 'tn3-gallery'),
			   array(	'size' => 8 )
	);
	TN3_Options::field('skin',
			   'thumbnailer_useTitle_b',
			   0,
			   'checkbox',
			   __('Use Title as title attribute:', 'tn3-gallery'),
			   __('If set, title of the image will be used as title attribute of img tag', 'tn3-gallery')
		       );

	TN3_Options::section(	'plugins',
				__('Plugins', 'tn3-gallery'),
				true
	);
	TN3_Options::field( 'plugins',
			    'line_l1_s',
			    '<hr />',
			    'html',
			    __('<strong>History</strong>', 'tn3-gallery')
	);
	TN3_Options::field('plugins',
			   'history-slugField',
			   'title',
			   'textfield',
			   __('Slug Field:', 'tn3-gallery'),
			   __('Field from which a slug (url friendly image name) for the permalink will be retrieved. If this field doesn’t have a value, the plugin will use the order number for the slug.', 'tn3-gallery'),
			   array(	'size' => 20 )
	);
	TN3_Options::field('plugins',
			   'history-from',
			   '·/_,:;',
			   'textfield',
			   __('Characters to replace:', 'tn3-gallery'),
			   __('A string of characters to be replaced when creating the slug.', 'tn3-gallery'),
			   array(	'size' => 40 )
	);
	TN3_Options::field('plugins',
			   'history-to',
			   '------',
			   'textfield',
			   __('Character Replacement:', 'tn3-gallery'),
			   __('A string of characters to use when replacing characters defined by the previous setting.', 'tn3-gallery'),
			   array(	'size' => 40 )
	);
	TN3_Options::field('plugins',
			   'history-key',
			   'tn3',
			   'textfield',
			   __('Hash Key:', 'tn3-gallery'),
			   __('The hash key to be used for the permalink.', 'tn3-gallery'),
			   array(	'size' => 10 )
	);



	TN3_Options::field( 'plugins',
			    'line_l2_s',
			    '<hr />',
			    'html',
			    '<strong>flickr</strong>'
	);
	TN3_Options::field('plugins',
			   'flickr-api_key',
			   '',
			   'textfield',
			   __('API Key:', 'tn3-gallery'),
			   __('The flickr API application key. <a href="http://www.flickr.com/services/api/misc.api_keys.html">See here</a> for more details.', 'tn3-gallery'),
			   array(	'size' => 40 )
	);
	TN3_Options::field('plugins',
			   'flickr-user_id',
			   '',
			   'textfield',
			   __('User ID:', 'tn3-gallery'),
			   __('The NSID of the user to retrieve a photoset list for. If none is supplied, the calling user is assumed.', 'tn3-gallery'),
			   array(	'size' => 40 )
	);
	TN3_Options::field( 'plugins',
			    'line_l3_s',
			    '<hr />',
			    'html',
			    '<strong>Picasa</strong>'
	);
	TN3_Options::field('plugins',
			   'picasa-userID',
			   '',
			   'textfield',
			   __('User ID:', 'tn3-gallery'),
			   __('Google user ID.', 'tn3-gallery'),
			   array(	'size' => 40 )
		       );
	TN3_Options::field( 'plugins',
			    'line_l4_s',
			    '<hr />',
			    'html',
			    '<strong>Facebook</strong>'
	);
	TN3_Options::field('plugins',
			   'facebook-ID',
			   '',
			   'textfield',
			   __('User ID:', 'tn3-gallery'),
			   __('Facebook user ID.', 'tn3-gallery'),
			   array(	'size' => 40 )
		       );
	TN3_Options::field( 'plugins',
			    'line_l5_s',
			    '<hr />',
			    'html',
			    '<strong>mediaelement.js</strong>'
	);
	TN3_Options::field('plugins',
			   'mediaelement-features',
			   'current,progress,duration',
			   'textfield',
			   __('Features:', 'tn3-gallery'),
			   __('The order of controls you want on the control bar', 'tn3-gallery'),
			   array(	'size' => 80 )
		       );

	TN3_Options::section(	'transition',
				'Transitions',
				true
	);
	TN3_Options::field('transition',
			   'presets',
			   'default',
			   'presets',
			   __('Preset:', 'tn3-gallery'),
			   '',
			   '',
			   'transition'
	);
	TN3_Options::field( 'transition',
			    'params',
			    '',
			    'hidden'
	);
    }
}

?>
