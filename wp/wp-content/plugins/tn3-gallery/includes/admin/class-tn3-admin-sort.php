<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin-page.php');

class TN3_Admin_Sort extends TN3_Admin_Page
{
    var $r;
    var $get_type;

    function print_page()
    {
	$parent = TN3::$db->getID( $_GET['sort'] );
	if ($parent->type == "album") {
	    $this->get_type = "image";
	    $upage = "tn3-images&album=".$parent->id;
	    $tit = __("Album", 'tn3-gallery');
	} else {
	    $this->get_type = "album";
	    $upage = "tn3-albums&gallery=".$parent->id;
	    $tit = __("Gallery", 'tn3-gallery');
	}

	$this->print_header(parent::$t['sort']);
	echo "<p>$tit: <b>$parent->title</b> | <a href='admin.php?page=$upage'>Back</a></p>";
	$this->r = TN3::$db->get($this->get_type, 0, 999, 'modify_time', 'ASC', $_GET['sort'], true);
	$this->print_grid();
	add_action( 'admin_footer', array( &$this, '_js_vars' ) );
    }
    function admin_init()
    {

    }
    function enqueue_scripts()
    {
	parent::enqueue_scripts();
	wp_enqueue_script('jquery-ui-sortable');
    }
    function print_grid()
    {
	$hidden = array('id', 'create_time', 'modify_time', 'width', 'height', 'filesize', 'title', 'description', 'dorder');
	
	echo '<input type="hidden" id="_wpnonce" name="_wpnonce" value="'.wp_create_nonce('bulk-tn3-sort').'" />';
	echo '<input type="submit" name="" id="save_sort" class="alignleft button-primary" value="Save">';
	echo '<select name="presort">';
	echo '<option value="-1" selected="selected">'.__('Presort', 'tn3-gallery').'</option>';
	echo '<option value="create_time">'.__('Time of Creation', 'tn3-gallery').'</option>';
	echo '<option value="modify_time">'.__('Time of Modification', 'tn3-gallery').'</option>';
	echo '<option value="width">'.__('Width', 'tn3-gallery').'</option>';
	echo '<option value="height">'.__('Height', 'tn3-gallery').'</option>';
	echo '<option value="filesize">'.__('File size', 'tn3-gallery').'</option>';
	echo '<option value="title">'.__('Title', 'tn3-gallery').'</option>';
	echo '<option value="description">'.__('Description', 'tn3-gallery').'</option>';
	echo '</select>';
	echo '<input type="submit" name="" id="reverse" class="button-secondary" value="'.__('Reverse', 'tn3-gallery').'">';


	echo '<div class="tn3-sorting">';
	foreach ( $this->r as $k => $item ) {
	    $siteurl = get_option( 'siteurl' );
	    $img_fld = ($this->get_type == "image")? $item->path : $item->thumb;
	    $img = '<img src="'.trailingslashit( $siteurl ).TN3::$o['general']['path']."/2".$img_fld.'" />';
	    $tit = $item->title;
	    $hid = '<div class="tn3-sort-hidden">';
	    foreach ($hidden as $i => $v) if ( isset($item->$v) ) {
		$hid .= '<span class="tn3-sort-info-'.$v.'">'.$item->$v.'</span>';
	    }
	    $hid .= '</div>';
	    echo '<div class="tn3-sort-item" id="img'.$item->id.'" title="'.$tit.'">'.$img.$hid.'</div>';
	}
	echo '</div>';
    }
    /**
     * Send required variables to JavaScript land
     *
     * @access private
     */
    function _js_vars() {
	    $args = array(
		    'class' => get_class( $this ),
		    'screen' => get_current_screen()
	    );

	    printf( "<script type='text/javascript'>list_args = %s;</script>\n", json_encode( $args ) );
    }

}

?>
