<?php

class TN3_Main
{

    function __construct()
    {
	if ( is_admin() ) {

	    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	
		require_once (TN3::$dir.'includes/class-tn3-ajax.php');
		new TN3_Ajax();
	
	    } else {

		TN3::$page = self::get_page();

		if ( TN3::$page ) {

		    if ( isset($_GET['sort']) ) TN3::$page = "sort";

		    require_once (TN3::$dir.'includes/admin/class-tn3-admin-'.TN3::$page.'.php');
		    $splitn = explode( "-", TN3::$page );
		    $splitn = array_map('ucfirst', $splitn);
		    $cname = 'TN3_Admin_'.implode("_", $splitn);
		    new $cname();

		} else {
		    
		    require_once (TN3::$dir.'includes/admin/class-tn3-admin.php');
		    new TN3_Admin();

		}

	    }

	} else {

	    require_once (TN3::$dir.'includes/class-tn3-post.php');
	    new TN3_Post();

	}

    }

    public static function get_page()
    {
	$par = isset($_GET['page'])? $_GET['page'] : (isset($_GET['tn3'])? $_GET['tn3'] : null);
	if ($par && substr($par, 0, 4) == "tn3-") return substr($par, 4);
	return null;
    }

}

?>
