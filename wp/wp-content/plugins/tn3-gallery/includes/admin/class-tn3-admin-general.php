<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin-page.php');

class TN3_Admin_General extends TN3_Admin_Page
{

    var $out = "";
    function print_page()
    {
	$this->print_header(parent::$t["general"]);
	$this->get_server_vars();
	$this->get_tn3_info();

	$this->print_dashboard();
    }
    function get_server_vars()
    {
	$out = array();

	$out['mySQL'] = TN3::$db->db->get_var("SELECT VERSION() AS version");
	$out['PHP version'] = phpversion();

	$inis = array(	"safe_mode",
			"max_execution_time",
			"upload_max_filesize",
			"post_max_size"
		    );
	foreach ($inis as $k => $v) $out["PHP ".$v] = ini_get($v)? ini_get($v) : "false";

	if (function_exists("gd_info")) {
	    $gdi = gd_info();
	    $out['GD'] = 'v'.$gdi['GD Version'];
	} else {
	    $out['GD'] = "Not Available";
	}

	$hout = '<ul>';
	foreach ($out as $k => $v) $hout .= '<li><b>' . $k . ':</b> ' . $v . '</li>';
	$hout .= '</ul>';

	$this->out['Server Info'] = $hout;
    }
    function get_tn3_info()
    {
	$out = array();
	$out['Total Images'] = TN3::$db->count('image');
	$out['Total Albums'] = TN3::$db->count('album');
	$out['WP plugin version'] = TN3::$info['wp'];
	$out['jQuery plugin version'] = TN3::$info['jq'];
	$out['Database version'] = TN3::$info['db'];


	$hout = '<ul>';
	foreach ($out as $k => $v) $hout .= '<li><b>' . $k . ':</b> ' . $v . '</li>';
	$hout .= '</ul>';

	$this->out['TN3 Info'] = $hout;
	
    }


    function print_dashboard()
    {

?>

<div id="dashboard-widgets" class="metabox-holder">
				<div id="post-body">
					<div id="dashboard-widgets-main-content">
						<div class="postbox-container" style="width:50%;">
							<div id="left-sortables" class="meta-box-sortables">

<?php
	foreach ($this->out as $title => $inside) {
?>
<div id="dashboard_right_now" class="postbox">
<h3><span><?php echo $title; ?></span></h3>
<div class="inside"><p>
<?php echo $inside; ?>
</p></div>
</div>
<?php }; ?>
</div>
</div>
</div>						
</div>
</div>
</div>
<?php
	
    }

}

?>
