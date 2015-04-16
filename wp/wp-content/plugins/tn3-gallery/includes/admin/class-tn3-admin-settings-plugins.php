<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin-settings.php');

class TN3_Admin_Settings_Plugins extends TN3_Admin_Settings
{

    var $section = "plugins";	    

    function validate_field( $name, $value, $type )
    {
	return $value;
    }

}

?>
