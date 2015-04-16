<?php
/*
Plugin Name: TN3 Gallery
Plugin URI: http://tn3gallery.com
Description: Advanced gallery plugin
Version: 1.2.0.25
Author: tn3gallery.com
Author URI: http://tn3gallery.com
License: http://tn3gallery.com/license
Date: 30 Apr, 2014 16:43:05 +0300
Text Domain: tn3-gallery
Domain Path: /lang
*/

// enable for dev
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

class TN3 {

    public static $info, $dir, $url, $o, $db, $page, 
	$db_version = "1.0",
	$wp_version = "1.2.0.25";

    function __construct() 
    {
	add_action('plugins_loaded', array($this, 'check_update'));

	require_once ( WP_PLUGIN_DIR.'/tn3-gallery/includes/class-tn3-db.php' );
	require_once ( WP_PLUGIN_DIR.'/tn3-gallery/includes/class-tn3-main.php' );
	TN3::$dir = WP_PLUGIN_DIR.'/tn3-gallery/';
	TN3::$url = WP_PLUGIN_URL."/tn3-gallery/";
	TN3::$db = new TN3_DB();
	new TN3_Main();
    }

    function check_update()
    {
	if ( version_compare( TN3::$db_version, TN3::$info['db'], ">" ) ) 
	    TN3::do_db(true);
	if ( version_compare( TN3::$wp_version, TN3::$info['wp'], ">" ) )
	    TN3::update_options();
    }


    function activate()
    {
	if ( TN3::requirements() ) {
	    TN3::do_db();
	    TN3::update_options();
	}
    }
    function update_options()
    {
	TN3::$info['wp'] = TN3::$wp_version;
	TN3::$info['db'] = TN3::$db_version;
	TN3::$info['jq'] = TN3::get_jq();
	TN3::$info['active'] = 'yes';
	$skinpop = get_option('tn3_presets_skin');

	$skinp = TN3::$info['lite'] == 'yes' || (false == $skinpop);
	TN3::$info['lite'] = 'no';


	update_option('tn3_info', TN3::$info);

	update_option('tn3_installed_skins', TN3::get_skins());
	if ( $skinp ) {
	    require_once (WP_PLUGIN_DIR.'/tn3-gallery/includes/class-tn3-presets.php');
	    update_option('tn3_presets_skin', $tn3_presets_skin);
	}
	$tranp = get_option('tn3_presets_transition');
	if ( false == $tranp ) {
	    require_once (WP_PLUGIN_DIR.'/tn3-gallery/includes/class-tn3-presets.php');
	    update_option('tn3_presets_transition', $tn3_presets_transition);
	}
    }
    function do_db($update = false)
    {
	global $wpdb;
	// for dbdelta to work: each field on its own line, do not use more then one space, no space after comma, no ` around names
	// 2x space after PRIMARY KEY, use word KEY never INDEX
	$struct = array('documents'	    => " (
			    id bigint(20) NOT NULL AUTO_INCREMENT,
			    type varchar(20) NOT NULL,
			    create_time datetime NOT NULL,
			    create_user bigint(20) NOT NULL,
			    modify_time datetime NOT NULL,
			    modify_user bigint(20) NOT NULL,
			    contained bigint(20) NOT NULL DEFAULT '0',
			    contains bigint(20) NOT NULL DEFAULT '0',
			    PRIMARY KEY  (id),
			      KEY ind_type (type),
			      KEY ind_ctime (create_time),
			      KEY ind_mtime (modify_time),
			      KEY ind_cuser (create_user),
			      KEY ind_muser (modify_user)
					  ) DEFAULT CHARSET=utf8;",
			'fields'	    => " (
			    docid bigint(20) NOT NULL,
			    name varchar(20) NOT NULL,
			    value_type varchar(10) NOT NULL,
			    value_text text DEFAULT NULL,
			    value_date datetime DEFAULT NULL,
			    value_number decimal(20,8) DEFAULT NULL,
			    value_bool char(1) DEFAULT NULL,
			    value_comp text DEFAULT NULL,
			    PRIMARY KEY  (docid,name),
			      KEY ind_val_text (value_text(100)),
			      KEY ind_val_date (value_date),
			      KEY ind_val_number (value_number),
			      KEY ind_val_bool (value_bool),
			      FULLTEXT KEY ind_ft (value_text)
					  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;",
			'relations'	    => " (
			    docid1 bigint(20) NOT NULL,
			    docid2 bigint(20) NOT NULL,
			    dorder int(11) NOT NULL AUTO_INCREMENT,
			    PRIMARY KEY  (docid1,docid2),
			      KEY ind_dorder (dorder),
			      KEY ind_doc1 (docid1,dorder),
			      KEY ind_doc2 (docid2,dorder)
			  ) DEFAULT CHARSET=utf8;");

	if (!$update) $table_names = $wpdb->get_col("SHOW TABLES");
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	foreach ($struct as $k => $v) {
	    $table_name = $wpdb->prefix . "tn3_$k";
	    if ( $update || array_search($table_name, $table_names) === FALSE) {
		dbDelta( "CREATE TABLE " . $table_name . $v );
	    }
	}
    }
    function requirements()
    {
	$errors = array();
	if ( version_compare( get_bloginfo( 'version' ), '3.1', '<' ) ) {
	    $errors[] = "WordPress version >= 3.1";
	}
	if ( version_compare( phpversion(), '5.0', '<' ) ) {
	    $errors[] = "PHP version >= 5.0";
	}
	if ( count($errors) > 0 ) {
	    TN3::$info['active'] = implode(", ", $errors);
	    update_option('tn3_info', TN3::$info);
	    return false;
	}
	return true;
    }
    function requirements_error($p)
    {
	global $current_screen;
	//$plugins['tn3-gallery/tn3-gallery.php']['Description'] = __('Requirements Error: ', 'tn3-gallery').TN3::$info['active'];
	//return $plugins;
	if ( $current_screen->id == "plugins" ) {
	    echo '<div class="error"><p>';
	    echo __( "TN3 Gallery plugin doesn't meet system requirements", 'tn3-gallery' ).": ".TN3::$info['active'];
	    echo '</p></div>';
	}
    }

    function deactivate()
    {
	TN3::$info['active'] = 'no';
	update_option('tn3_info', TN3::$info);
    }

    function get_skins($is_custom = false, $path = "")
    {
	$a = array();
	if ($is_custom) {
	    $cura = &$a;
	} else {
	    $a['default'] = array();
	    $cura = &$a['default'];
	    $path = WP_PLUGIN_DIR.'/tn3-gallery/skins';
	}
	  
	$c = scandir($path);
	if (false != $c) { 
	    foreach ($c as $k => $v) {
		if (substr($v, 0, 1) != ".") {
		    $cura[$v] = array();
		    $pathi = "$path/$v";
		    $ci = scandir($pathi);
		    foreach ($ci as $ki => $vi) {
			$i = pathinfo($pathi."/$vi");
			if ($i['extension'] == 'html' && file_exists($pathi."/".$i['filename'].".css")) array_push($cura[$v], $i['filename']);
		    }
		}
	    }
	}
	$genop = get_option("tn3_admin_general");
	if ( !$is_custom && false !== $genop ) {
	    $apath = ABSPATH.$genop['path']."/skins";
	    $a['custom'] = TN3::get_skins(true, $apath);
	    //tn3log::w($a);
	}
	return $a;
	
    }
    function get_jq()
    {
	
	$file = WP_PLUGIN_DIR.'/tn3-gallery/js/jquery.tn3.min.js';
	
	
	$fp = @fopen($file, "r");
	if ($fp) {
	    $p = 0;
	    while ( fgetc($fp) != "\n" ) {
		$p++;
		fseek($fp, $p);
	    }
	    if (($buffer = fgets($fp, 4096)) !== false) {
		fclose($fp);
		return trim(substr($buffer, 8));
	    }
	}
	fclose($fp);
	return "unknown";
		
    }

}

class tn3log
{
    public static function w($s)
    {
	$debug = WP_PLUGIN_DIR."/tn3-gallery/debug.txt";
	$fh = fopen($debug, 'w') or die("can't open debug file");
	fwrite($fh, var_export($s, true));
	fclose($fh);

    }
    public static function a($s)
    {
	$debug = WP_PLUGIN_DIR."/tn3-gallery/debug.txt";
	if (file_exists($debug)) $fh = fopen($debug, 'a');
	else $fh = fopen($debug, 'w') or die("can't open debug file");
	fwrite($fh, "\r\n".var_export($s, true));
	fclose($fh);
    }
}

TN3::$info = get_option('tn3_info', array());

if ( isset(TN3::$info['active']) && TN3::$info['active'] != 'no' ) {
    register_deactivation_hook( WP_PLUGIN_DIR.'/tn3-gallery/tn3-gallery.php', array('TN3', 'deactivate') );
    if ( TN3::$info['active'] == 'yes' ) new TN3();
    else add_action( 'admin_notices', array('TN3', 'requirements_error') );
} else
    register_activation_hook( WP_PLUGIN_DIR.'/tn3-gallery/tn3-gallery.php', array('TN3', 'activate') );

?>
