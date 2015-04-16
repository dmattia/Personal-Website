<?php

require_once (TN3::$dir . 'includes/admin/class-tn3-list-table.php');

class TN3_Select_List_Table extends TN3_List_Table 
{

    private $multi, $noempty;

    function __construct($type, $multi, $noempty) 
    {
	parent::__construct($type);
	$this->multi = $multi;
	$this->noempty = $noempty;
    }

    function get_sortable_columns() {
	if (isset($this->gal)) return array();
	$a = parent::get_sortable_columns();
	switch ($this->type) {
	case 'gallery':
	    $a['contains'] = 'contains';
	    break;
	case 'album':
	    $a['contains'] = 'contains';
	    break;
	case 'image':
	    $a['contained'] = 'contained';
	    break;
	}
	return $a;
    }
    function get_columns() {
	$cs = array(
	    'cb'    => $this->multi? '<input type="checkbox" />':'',
	    'path'  => __( '', 'tn3-gallery' ),
	    'title' => __( 'Title', 'tn3-gallery' )
	);
	switch ($this->type) {
	case 'gallery':
	    unset($cs['path']);
	    $cs['contains'] = __( 'Albums', 'tn3-gallery' );
	    break;
	case 'album':
	    $cs['contains'] = __( 'Images', 'tn3-gallery' );
	    break;
	case 'image':
	    $cs['contained'] = __( 'In Albums', 'tn3-gallery' );
	    break;
	}
	$cs['modify_time'] = __( 'Modified', 'tn3-gallery' );
	return $cs;
    }
    function column_cb( $item ) {
	if ($this->type != 'image' && $item->contains == '0' && $this->noempty) return '';
	return '<input type="checkbox" name="tn3_images" value="' . $item->id . '">';
    }
    function column_path( $item ) {
	$siteurl = get_option( 'siteurl' );
	$src = trailingslashit( $siteurl ).TN3::$o['general']['path']."/2".(($this->type == 'image')? $item->path : $item->thumb);
	return '<img src="'.$src.'" width="40" height="40" />';
    }
    function column_title( $item )
    {
	return $item->title;
    }
    function get_bulk_actions() {
	return array();
    }
    // which - top or bottom
    function extra_tablenav($which)
    {
	/*
	echo '<input type="submit" name="" id="select_cancel" class="button-secondary alignleft" value="Cancel">';
	if ($this->multi) {
	    echo '<input type="submit" name="" id="select_ok" class="button-secondary alignleft" value="OK">';
	}
	if ($which == "bottom") $this->print_tn3_js();
	 */
    }
    function get_items($per = 5) 
    {
	if ( isset( $_GET['s'] ) && $_GET['s'] != "" ) $s = $_GET['s'];
	else $s = null;
	return TN3::$db->get(	$this->type, 
				$this->page, 
				$per, 
				$this->ordby, 
				$this->ord, 
				null, 
				false,
				$s);
    }


}

?>
