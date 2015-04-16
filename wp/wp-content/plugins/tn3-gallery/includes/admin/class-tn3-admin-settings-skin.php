<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin-settings.php');

class TN3_Admin_Settings_Skin extends TN3_Admin_Settings
{

    var $section = "skin";	    

    function admin_init()
    {
	parent::admin_init();
	add_action( 'admin_footer', array( &$this, 'print_tn3_js' ) );
    }
    function validate_field( $name, $value, $type )
    {
	return $value;
    }
    function print_tn3_js() {
	$tn = get_option('tn3_presets_skin');
	echo "\n<script type='text/javascript'>tn3.skins={};";
	foreach($tn as $k => $v) {
	    echo "tn3.skins['".$k."']=".json_encode($v).";";
	}
	echo "</script>\n";
    }

}

?>
