<?php

require_once (TN3::$dir . 'includes/admin/class-tn3-list-table.php');

class TN3_Galleries_List_Table extends TN3_List_Table 
{

    function TN3_Galleries_List_Table($opts) 
    {
	parent::__construct('gallery', $opts);
    }
    function get_sortable_columns() {
	$a = parent::get_sortable_columns();
	$a['contains'] = 'contains';
	return $a;
    }
    function get_columns() {
	return array(
	    'cb'    => '<input type="checkbox" />',
	    'title' => __( 'Title', 'tn3-gallery' ),
	    'description' => __( 'Description', 'tn3-gallery' ),
	    'contains' => __( 'Albums', 'tn3-gallery' ),
	    'create_time' => __( 'Created', 'tn3-gallery' ),
	    'modify_time' => __( 'Modified', 'tn3-gallery' ),
	);
    }
    function get_bulk_actions() {
	if (count($this->items) == 0) return array();
	return array(	'del'	=> __("Delete Gallery", 'tn3-gallery'));
    }
    function column_title( $item ) {
	$r = parent::column_title( $item );
	//$r = '<strong><a href="admin.php?page=tn3-images&album='.$item->id.'">'.$item->title.'</a></strong>';
	$actions = array();
	$actions['view'] = '<a href="admin.php?page=tn3-albums&gallery='.$item->id.'" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'tn3-gallery' ), $item->title ) ) . '" rel="permalink">' . __( 'View', 'tn3-gallery' ) . '</a>';
	$actions['sort'] = '<a href="admin.php?page=tn3-galleries&sort='.$item->id.'" title="' . esc_attr( __( 'Sort albums', 'tn3-gallery' ) ) . '">' . __( 'Sort', 'tn3-gallery' ) . '</a>';
	$r .= $this->row_actions( $actions );
	return $r;
    }
    function column_contains( $item ) {
	return $item->contains;
    }

}

?>
