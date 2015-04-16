<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin.php');
require_once (TN3::$dir.'includes/admin/class-tn3-admin-help.php');

class TN3_Admin_Page extends TN3_Admin
{
    var $opts, $db;
    protected $notices;

    function __construct()
    {
	parent::__construct();
	$this->db = TN3::$db;
	$this->notices = array();
	add_action('admin_notices', array($this, 'admin_notices'));
	// set_screen_option is not allowing numbers in screen option name
	// so we must change default screen names
	//add_action('current_screen', array($this, 'scr'));
	// only default wp will pass this f so we need to return proper value
	add_action('set-screen-option', array($this, 'set_scr'));
	add_action('admin_head', array('TN3_Help', 'print_help'));
	add_action( "load-tn3-gallery_page_tn3-".TN3::$page, array($this, 'admin_load_page') );
    }
    function scr($cs)
    {
	global $current_screen;
	$pag = TN3::$page;
	$current_screen->id = "tn_$pag";
	$current_screen->base = "tn_$pag";
    }
    function set_scr($a, $b, $c)
    {
	return $_POST['wp_screen_options']['value'];
    }
    function admin_load_page()
    {
    }
    function admin_init()
    {
    }
    /*
    function screen_options_30()
    {
	global $wp_current_screen_options;
	$c = $wp_current_screen_options['per_page'];

	$per = (int) get_user_option( $wp_current_screen_options['per_page']['option'] );
	if ( ! isset($per) || $per == 0 ) $per = $wp_current_screen_options['per_page']['default'];

	$return = "<div class='screen-options'>\n";
	$return .= "<input type='text' class='screen-per-page' name='wp_screen_options[value]' ".
	    "id='".$c['option']."' maxlength='3' value='".$per."' /> <label for='".$c['option']."'>".$c['label']."</label>\n";
	$return .= get_submit_button( __( 'Apply' ), 'button', 'screen-options-apply', false );
	$return .= "</div>\n";
	return $return;
    }
     */
	    
    function print_styles()
    {
	parent::print_styles();
	wp_enqueue_style('tn3-admin', TN3::$url."css/tn3-admin.css");
    }
    function enqueue_scripts()
    {
	parent::enqueue_scripts();
	wp_enqueue_script(  'blockUI',
			    TN3::$url."js/jquery.blockUI.js",
			    array("jquery")
			);
	wp_enqueue_script(  'tn3-admin',
			    TN3::$url."js/tn3-admin.js",
			    array("jquery")
	);
	wp_localize_script( 'tn3-admin', 'tn3L18n', array(
	    'success' => __( 'Operation Successful.', 'tn3-gallery' ),
	    'rusure' => __( 'Are you sure?', 'tn3-gallery' ),
	    'plzwait' => __( "Please wait...", 'tn3-gallery' ),
	    'cancel' => __( "Cancel", 'tn3-gallery' ),
	    'save' => __( "Save", 'tn3-gallery' ),
	    'addnew' => __( "Add New", 'tn3-gallery' ),
	    'newpreset' => __( "new preset", 'tn3-gallery' ),
	    '"delete"' => __( "Delete", 'tn3-gallery' ),
	) );
    }
    function print_admin_page()
    {
	echo '<div class="wrap">';

	$this->print_page();

	echo '</div>';
?>
<div id="tn3-dialog" style="display:none; cursor: default"> 
<h4><?php _e("Completed Successfully", "tn3-gallery"); ?></h4> 
	<input type="button" id="tn3-yes" class="button add-new-h2" value="<?php echo __('Yes', 'tn3-gallery'); ?>" /> 
        <input type="button" id="tn3-no" class="button add-new-h2" value="<?php echo __('No', 'tn3-gallery'); ?>" /> 
        <input type="button" id="tn3-ok" class="button add-new-h2" value="<?php echo __('OK', 'tn3-gallery'); ?>" /> 
</div> 
<?php
    }

    function print_header($title, $btext = '')
    {
	echo '<h2>TN3 '.$title;
	if ( $btext ) echo '<a class="button add-new-h2 tn3-admin-button-add">'.$btext.'</a>';
	echo '</h2>';
	if ( $btext ) {
	    echo '<div id="tn3-admin-add" style="display:none">';
	    $this->print_add_form();
	    echo '</div>';
	}
    }
    function admin_notices()
    {
	foreach($this->notices as $k => $v) {
	    echo '<div class="'.$v[0].'" style="padding:5px 10px">'.$v[1].'</div>';
	}
    }


}

?>
