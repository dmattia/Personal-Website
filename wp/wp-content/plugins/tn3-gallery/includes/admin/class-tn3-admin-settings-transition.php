<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin-settings.php');

class TN3_Admin_Settings_Transition extends TN3_Admin_Settings
{

    var $section = "transition";	    

    function admin_init()
    {
	parent::admin_init();
	add_action( 'admin_footer', array( &$this, 'print_tn3_js' ) );
    }
    function print_styles()
    {
	parent::print_styles();
	wp_enqueue_style('tn3-transition-ui', TN3::$url."css/ui-darkness/jquery-ui-1.8.13.custom.css");
    }
    function enqueue_scripts()
    {
	parent::enqueue_scripts();
	wp_enqueue_script('tn3', TN3::$url."js/jquery.tn3.min.js");
	//wp_enqueue_script('tn3-ui-custom', TN3::$url."js/jquery-ui-1.8.13.custom.min.js");
	wp_enqueue_script('jquery-ui-slider');
	wp_enqueue_script('tn3-transition-inspector', TN3::$url."js/tn3.wpTransitionInspector.js");
	wp_enqueue_script('json2');
    }
    function add_to_form()
    {
	echo '<div id="tn3-transitions"></div>';
    }
    function validate_field( $name, $value, $type )
    {
	return $value;
    }
    function print_tn3_js() {
	$siteurl = get_option( 'siteurl' );
	$r = array( "path" => trailingslashit( $siteurl ).TN3::$o['general']['path'] );
	$r[ "pluginPath" ] = TN3::$url;
	$tn = get_option('tn3_presets_transition');
	echo "\n<script type='text/javascript'>";
	foreach($r as $k => $v) {
	    echo "tn3.".$k."=".json_encode($v).";";
	}
	echo "tn3.transitions={};";
	foreach($tn as $k => $v) {
	    echo "tn3.transitions['".$k."']=".json_encode($v).";";
	}
	echo "</script>\n";
    }

}

?>
