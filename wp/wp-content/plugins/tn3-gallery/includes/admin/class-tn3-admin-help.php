<?php

class TN3_Help 
{
    static $count = 0, $is_old, $cs;

    static function print_help()
    {
	global $wp_version;
	self::$cs = get_current_screen();
	self::$is_old = !method_exists(self::$cs, 'add_help_tab');
	$page = str_replace("-", "_", TN3::$page);
	$c = call_user_func(array('self', $page));
	if (self::$is_old && $c) {
	   add_contextual_help( self::$cs, '<div id="tn3-help">' . $c . '</div>' );
	}
    }

    static function general()
    {

    }
    static function images()
    {
	$c = "";
	$c .= self::get_list( __("Adding Images", "tn3-gallery" ), array(
	    __( "Select Images.", "tn3-gallery" ),
	    __( "Click Upload Images.", "tn3-gallery" ),
	    __( "Click Browse files and select images from your hard drive.", "tn3-gallery" ),
	    __( "Click Start upload.", "tn3-gallery" ),
	    __( "Monitor the upload progress until completion.", "tn3-gallery" )
	), 'images.png');
	$c .= self::get_list( __( "Editing Images", "tn3-gallery" ), array(
	    __( "Select Images.", "tn3-gallery" ),
	    __( "Click the Edit link next to the image you want to edit.", "tn3-gallery" ),
	    __( "Enter a title, description and link.", "tn3-gallery" ),
	    __( "Click Save.", "tn3-gallery" ),
	    __( "Click OK.", "tn3-gallery" )
	), 'edit_image.png');
	$c .= self::get_list( __( "Editing Thumbnails", "tn3-gallery" ), array(
	    __( "Select Images.", "tn3-gallery" ),
	    __( "Click the View link next to the image you want to edit.", "tn3-gallery" ),
	    __( "Drag and resize the marquee so that the area you want as your thumbnail is highlighted.", "tn3-gallery" ),
	    __( "Click Save.", "tn3-gallery" )
	), 'edit_thumb.png');
	$c .= self::get_list( __( "Adding Images to Albums", "tn3-gallery" ), array(
	    __( "Select Images.", "tn3-gallery" ),
	    __( "Select the checkbox next to every image you want to add to your album.", "tn3-gallery" ),
	    __( "Select Add to Album from the Bulk Actions dropdown.", "tn3-gallery" ),
	    __( "Click Apply", "tn3-gallery" ),
	    __( "Select the checkbox next to the album(s) you want to add your images to.", "tn3-gallery" ),
	    __( "Click Save.", "tn3-gallery" ),
	    __( "Click OK.", "tn3-gallery" )
	), 'add_image.png');
	return $c;
    }
    static function albums()
    {
	$c = "";
	$c .= self::get_list( __("Adding Albums", "tn3-gallery" ), array(
	    __( "Select Albums.", "tn3-gallery" ),
	    __( "Click Add Album.", "tn3-gallery" ),
	    __( "Click the thumbnail icon to choose a thumbnail from your current images.", "tn3-gallery" ),
	    __( "Select the checkbox next to the image you'd like to use as your thumbnail.", "tn3-gallery" ),
	    __( "Enter a title for the album.", "tn3-gallery" ),
	    __( "Enter a description for the album.", "tn3-gallery" ),
	    __( "Click Create Album.", "tn3-gallery" )
	), 'albums.png');
	$c .= self::get_list( __("Adding Albums to Galleries", "tn3-gallery" ), array(
	    __( "Select Albums.", "tn3-gallery" ),
	    __( "Select the checkbox next to the album(s) you want to add to your gallery.", "tn3-gallery" ),
	    __( "Select Add to Gallery from the Bulk Actions dropdown.", "tn3-gallery" ),
	    __( "Click Apply.", "tn3-gallery" ),
	    __( "Select the checkbox next to the gallery you want to add your album to.", "tn3-gallery" ),
	    __( "Click OK.", "tn3-gallery" )
	), 'add_album.png');
	$c .= self::get_list( __("Sorting Images", "tn3-gallery" ), array(
	    __( "Select Albums.", "tn3-gallery" ),
	    __( "Click Sort next to the album you want to sort.", "tn3-gallery" ),
	    __( "Select the kind of sorting you want to apply form the dropdown list or drag and drop the images into your desired order.", "tn3-gallery" ),
	    __( "Click Save.", "tn3-gallery" )
	), 'sort_albums.png');
	return $c;
    }
    static function galleries()
    {
	$c = "";
	$c .= self::get_list( __("Adding Galleries", "tn3-gallery" ), array(
	    __( "Select Galleries.", "tn3-gallery" ),
	    __( "Click Add Gallery.", "tn3-gallery" ),
	    __( "Add a title.", "tn3-gallery" ),
	    __( "Add a description.", "tn3-gallery" ),
	    __( "Click Create Gallery.", "tn3-gallery" )
	), 'galleries.png');
	$c .= self::get_list( __("Sorting Albums", "tn3-gallery" ), array(
	    __( "Select Galleries.", "tn3-gallery" ),
	    __( "Click Sort next to the gallery you want to sort.", "tn3-gallery" ),
	    __( "Select the kind of sorting you want to apply form the dropdown list, or drag and drop the albums into your desired order.", "tn3-gallery" ),
	    __( "Click Save.", "tn3-gallery" )
	), 'sort_galleries.png');
	return $c;
    }
    static function sort()
    {
	$c = "";
	$c .= self::get_list( __("Sorting Images", "tn3-gallery" ), array(
	    __( "Select Albums.", "tn3-gallery" ),
	    __( "Click Sort next to the album you want to sort.", "tn3-gallery" ),
	    __( "Select the kind of sorting you want to apply form the dropdown list or drag and drop the images into your desired order.", "tn3-gallery" ),
	    __( "Click Save.", "tn3-gallery" )
	), 'sort_albums.png');
	$c .= self::get_list( __("Sorting Albums", "tn3-gallery" ), array(
	    __( "Select Galleries.", "tn3-gallery" ),
	    __( "Click Sort next to the gallery you want to sort.", "tn3-gallery" ),
	    __( "Select the kind of sorting you want to apply form the dropdown list, or drag and drop the albums into your desired order.", "tn3-gallery" ),
	    __( "Click Save.", "tn3-gallery" )
	), 'sort_galleries.png');
	return $c;

    }
    static function settings_general()
    {
	$c = "";
	$c .= self::parag(array(
	    __( "Entering a valid license key will update the plugin automatically. (If you are using the Lite version entering a valid license key will upgrade you to the Pro version automatically.)", "tn3-gallery" ),
	    __( "A variety of different runtimes can be used for uploading images, you can specify the order 'Upload-Runtimes' you wish to try them in.", "tn3-gallery" ),
	    __( "Some runtimes, Flash for example, will allow you to resize images to their optimum size before uploading. We recommend that you keep 'Upload-Resize' option enabled.", "tn3-gallery" )
	));
	return $c;

    }
    static function settings_skin()
    {
	$c = "";
	$c .= self::get_list( __("Creating Skin Presets", "tn3-gallery" ), array(
	    __( "Select Skin.", "tn3-gallery" ),
	    __( "Enter a name for your modified skin.", "tn3-gallery" ),
	    __( "Modify the settings however you wish.", "tn3-gallery" ),
	    __( "Click Save Changes.", "tn3-gallery" )
	), 'skins.png');
	return $c;
    }
    static function settings_plugins()
    {

    }
    static function settings_transition()
    {

    }

    static function get_list($title, $list, $img)
    {
	$c = '<div class="tn3-help-list">';
	if (self::$is_old) {
	    $c .= '<a class="tn3-help-list-title">' . $title . '</a>';
	    $c .= '<div class="hidden">';
	}
	$c .= '<img src="'.TN3::$url.'images/help/'.$img.'" />';
	$c .= '<ol>';
	foreach ($list as $v) {
	    $c .= '<li>' . $v . '</li>';
	}
	$c .= '</ol></div>';
	if (self::$is_old) {
	    $c .= '</div>';
	    return $c;
	}

	self::$cs->add_help_tab( array(
	    'id' => sanitize_title($title, 'tn3-help-list-'.self::$count),
	    'title' => $title,
	    'content' => $c,
	) );
	self::$count++;
	

    }
    static function parag( $list )
    {
	return '<p>'.implode('</p><p>', $list).'</p>';
    }
}


?>
