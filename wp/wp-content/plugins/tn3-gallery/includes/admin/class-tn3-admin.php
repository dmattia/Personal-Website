<?php

require_once (TN3::$dir.'includes/class-tn3-options.php');

class TN3_Admin
{
    public static $t;
    var $plugin_slug = 'tn3-gallery';

    var $tn3_url = 'http://www.tn3gallery.com/gallery-plugin-api';



    function __construct()
    {
	add_action('init', array($this, 'wp_init'));
	add_action('admin_menu', array($this, 'admin_menu'));
	add_action('admin_init', array($this, 'admin_init'));
	add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
	add_action('admin_print_styles', array($this, 'print_styles'));
	add_action('pre_set_site_transient_update_plugins', array($this, 'on_set_plugin_transient'));
	add_filter('plugins_api', array($this, 'tn3_plugin_api_call'), 10, 3);
    }
    function wp_init()
    {
	load_plugin_textdomain( 'tn3-gallery', false, 'tn3-gallery/lang/' );
	TN3_Admin::$t = array(   "overview"  => __("Overview", "tn3-gallery"),
			    "images"    => __("Images", "tn3-gallery"),
			    "albums"    => __("Albums", "tn3-gallery"),
			    "galleries" => __("Galleries", "tn3-gallery"),
			    "sort" => __("Sort", "tn3-gallery"),
			    "settings"  => __("Settings", "tn3-gallery"),
			    "general"	=> __("General", "tn3-gallery"),
			    "skin"	=> __("Skin", "tn3-gallery"),
			    "transition"	=> __("Transitions", "tn3-gallery"),
			    "plugins"	=> __("Plugins", "tn3-gallery"),
			);
	// set options here as we need translation
	TN3::$o = TN3_Options::get( is_admin() );
    }
    function admin_menu()
    {
	$t = TN3_Admin::$t;
	$pre = 'TN3 Gallery ';
	$f = array($this, 'print_admin_page');
	// $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position

	add_menu_page($pre.$t['general'], 'TN3 Gallery', 'manage_options', 'tn3-general', $f, TN3::$url."images/tn3-btn.png", 99);


	// $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function
	add_submenu_page( 'tn3-general', $pre.$t['general'], $t['general'], 'manage_options', 'tn3-general', $f);
	add_submenu_page( 'tn3-general', $pre.$t['images'], "&#0761;&nbsp;&nbsp;&nbsp;&nbsp;".$t['images'], 'manage_options', 'tn3-images', $f);

	add_submenu_page( 'tn3-general', $pre.$t['albums'], "&nbsp;&nbsp;&nbsp;".$t['albums'], 'manage_options', 'tn3-albums', $f);
	add_submenu_page( 'tn3-general', $pre.$t['galleries'], "&#0763;".$t['galleries'], 'manage_options', 'tn3-galleries', $f);


	add_submenu_page( 'tn3-general', $pre.$t['settings'], $t['settings'], 'manage_options', 'tn3-settings-general', $f);
	//add_submenu_page( 'tn3-overview', $pre.$t['general'], '&nbsp;&nbsp;&nbsp;'.$t['general'], 'manage_options', 'tn3-settings-general', $f);
	add_submenu_page( 'tn3-general', $pre.$t['skin'], '&nbsp;&nbsp;&nbsp;'.$t['skin'], 'manage_options', 'tn3-settings-skin', $f);

	add_submenu_page( 'tn3-general', $pre.$t['plugins'], '&nbsp;&nbsp;&nbsp;'.$t['plugins'], 'manage_options', 'tn3-settings-plugins', $f);
	add_submenu_page( 'tn3-general', $pre.$t['transition'], '&nbsp;&nbsp;&nbsp;'.$t['transition'], 'manage_options', 'tn3-settings-transition', $f);

    }
    function print_styles()
    {
    }
    function enqueue_scripts()
    {
	global $editing, $tinymce_version;
	wp_enqueue_script(  'tn3-log',
			    TN3::$url."js/jquery.log.js",
			    array("jquery")
			);
	if ($editing) {
	    if (version_compare($tinymce_version, "4", ">")) {
		wp_enqueue_script('tn3-admin', TN3::$url."js/tn3-admin.js", array("wpdialogs"));
		wp_enqueue_style('wp-jquery-ui-dialog');
	    } else {
		wp_enqueue_script('tn3-admin', TN3::$url."js/tn3-admin.js");
	    }
	    wp_enqueue_script('jquery-ui-tabs', null, null, null, true);
	    wp_enqueue_style('tn3-dialog', TN3::$url."css/tn3-dialog.css");
	}
    }
    function admin_init()
    {
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
	    return;
	
	if ( get_user_option('rich_editing') == 'true') {
	    add_filter("mce_external_plugins", array($this, "add_tn3button_tinymce_plugin"));
	    add_filter('mce_buttons', array($this, 'register_tn3_button'));
	}
    }
    function register_tn3_button($buttons) 
    {
	
	array_push($buttons, "|", "tn3button");
	return $buttons;
    }
 
    // Load the TinyMCE plugin : editor_plugin.js (wp2.5)
    function add_tn3button_tinymce_plugin($plugin_array) 
    {
	$plugin_array['tn3button'] = TN3::$url."js/tn3-button.js";
	return $plugin_array;
    }
    // requests plugin data from the server and returns for plugin transient
    // handles basic_check action
    function on_set_plugin_transient($transient_data)
    {
	if (empty($transient_data->checked))
		return $transient_data;
	
	$request_args = new stdClass;
	$request_args->slug = $this->plugin_slug;
	if ( TN3::$info['lite'] === 'upgrade' ) {
	    $request_args->version = "0";
	    $this->tn3_url = "http://www.tn3gallery.com/gallery-plugin-api";
	} else $request_args->version = TN3::$wp_version;
	
	$request_string = $this->prepare_request('basic_check', $request_args);
	// sends slug, version, license_key  
	//tn3log::w($request_string);
	// Start checking for an update
	$raw_response = wp_remote_post($this->tn3_url, $request_string);
	
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);
	//tn3log::a("----------------------");
	//tn3log::a($response);
	
	if (is_object($response) && !empty($response) ) {
	    if ( ! isset($transient_data->response) ) $transient_data->response = array();
	    // if there is new version set it to transient
	    if (isset($response->new_version)) {
	        $transient_data->response[$this->plugin_slug .'/'. $this->plugin_slug .'.php'] = $response;
	    // or remove from transient
	    } else if ( isset($transient_data->response[$this->plugin_slug .'/'. $this->plugin_slug .'.php']) ) {
	        unset( $transient_data->response[$this->plugin_slug .'/'. $this->plugin_slug .'.php'] );
	    }
	}
	//tn3log::a("----------------------");
	//tn3log::a($transient_data);	
	return $transient_data;
    }
    function prepare_request($action, $args) 
    {
	global $wp_version;
	
	if (is_array($args)) $args = (object)$args;

	if ( $action != "check_license" ) {
	    $gopts = get_option('tn3_admin_general');
	    $args->license_key = $gopts['license_key'];
	}
	
	return array(
	    'body' => array(
		'action' => $action, 
		'request' => serialize($args),
		'api-key' => md5(get_bloginfo('url'))
	    ),
	    'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);	
    }
    // handles 'plugin_information' action from plugins api
    function tn3_plugin_api_call($def, $action, $args) 
    {
	// return if it is not our plugin
	if ($args->slug != $this->plugin_slug)
	    return false;
	
	// get the latest version number from the transient
	//$plugin_info = get_site_transient('update_plugins');
	//$current_version = $plugin_info->checked[$this->plugin_slug .'/'. $this->plugin_slug .'.php'];
	
	$request_string = $this->prepare_request($action, $args);
	//tn3log::w($request_string);
	
	$request = wp_remote_post($this->tn3_url, $request_string);
	
	if (is_wp_error($request)) {
	    $res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
	    $res = unserialize($request['body']);
	    //tn3log::a($res);
	    if ($res === false)
		$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}
	
	return $res;
    }


}
//set_site_transient('update_plugins', null);


?>
