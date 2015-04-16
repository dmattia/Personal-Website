<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin-settings.php');

class TN3_Admin_Settings_General extends TN3_Admin_Settings
{

    var $section = "general";	    

    function validate_field( $name, $value, $type )
    {
	if ( $type == "image_size" ) {

	    $current = TN3::$o['general'][$name]; 
	    $deleteCache = false;
	    $do_check = true;

	    // value required exists only for optional image sizes
	    // if it is not enabled write defaults
	    if ( isset($value['required']) ) {
		$value['required'] = ( $value['required'] == "1" )? 1 : 0;

		if ( $current['required'] !== $value['required'] ) $deleteCache = true;
		
		if ( $value['required'] == 0 ) {
		    $value = array('w' => '', 'h' => '', 'q' => 90, 'crop' => 0, 'required' => 0);
		    $do_check = false;
		}
	    }

	    if ($do_check) {

		$value['crop'] = ($value['crop'] == "1")? 1 : 0;

		// define minimums and range
		$valid = array('w' => 20, 'h' => 20, 'q' => array(0, 100));
		
		foreach ($valid as $k => $v) {
		    $cv = $value[$k];
		    if ($cv != $current[$k]) $deleteCache = true;//tn3log::a($name."->".$k."->".$current[$k]."=".$cv);
		    // check quality
		    if ( is_array($v) ) {
			if ($cv >= $v[0] && $cv <= $v[1]) continue;
			else return null;
		    } else {  
			if ($cv == '') {
			    if ( $value['crop'] === 0 ) {
				if ( $k == 'w' && $value['h'] < $v ) return null;
				if ( $k == 'h' && $value['w'] < $v ) return null;
			    } else
				return null;
			}
			else if ( $cv < $v ) {
			    
			    if ( $value['crop'] === 0 ) {
				if ( $k == 'w' && $value['h'] != '' ) return null;
				if ( $k == 'h' && $value['w'] != '' ) return null;
			    } else
				return null;
			}
		    }
		}
		if ($value['crop'] != $current['crop']) $deleteCache = true;//tn3log::a($name."->crop->".$current['crop']."=".$value['crop']);

	    }

	    if ( $deleteCache ) {
		
		$path = ABSPATH.TN3::$o['general']['path'].DIRECTORY_SEPARATOR.substr($name, 5);
		if ( !is_dir($path) ) return $value;

		$dir = dir( $path );
		while( false !== ($file = $dir->read()) ) { 
		    
		    if ($file != "." && $file != "..") {
			//tn3log::a($file);
			unlink($path.DIRECTORY_SEPARATOR.$file);
		    } 
		} 
		$dir->close();
		
		
	    }
	}
	if ($name == "license_key" && trim($value) != '') {
	    $ekey = TN3::$o['general']['license_key'];
	    if ( $ekey == trim($value) ) return $value;
	    $reqa = array( 'license_key' => trim($value), 'slug' => $this->plugin_slug );
	    $req = $this->prepare_request('check_license', $reqa);
	    $request = wp_remote_post($this->tn3_url, $req);//tn3log::w($req);tn3log::a($request);
	    
	    if (is_wp_error($request)) {
		return null;
	    } else {
		$res = unserialize($request['body']);
		
		if ($res === false) {
		    return null;
		} else {
		    if (is_object($res) && $res->is_valid == 1 ) { 
			if ( TN3::$info['lite'] == 'yes' ) {
			    add_filter('wp_redirect', array($this, 'tn3_update_redirect'), 10, 2);
			    TN3::$info['lite'] = 'upgrade';
			    update_option('tn3_info', TN3::$info);
			}
			set_site_transient('update_plugins', null);
			
			return $value;
		    } else return null;
		}		    
	    }
	    
	}
	return $value;
    }
    function tn3_update_redirect($location, $status)
    {
	//rename(WP_PLUGIN_DIR."/tn3-lite
	$nonce = wp_create_nonce('upgrade-plugin_tn3-gallery/tn3-gallery.php');
	$url = get_bloginfo('url');
	$url .= "/wp-admin/update.php?action=upgrade-plugin&plugin=tn3-gallery%2Ftn3-gallery.php&_wpnonce=".$nonce;
	return $url;
    }

}

?>
