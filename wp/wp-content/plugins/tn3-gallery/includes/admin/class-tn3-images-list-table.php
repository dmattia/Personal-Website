<?php

require_once (TN3::$dir . 'includes/admin/class-tn3-list-table.php');

class TN3_Images_List_Table extends TN3_List_Table 
{
    var $alb;

    function TN3_Images_List_Table($opts) 
    {
	parent::__construct('image', $opts);
	$this->alb = ( empty( $_REQUEST['album'] ) )? null : (int)$_REQUEST['album'];
    }
    function get_sortable_columns() {
	if (isset($this->alb)) return array();
	$a = parent::get_sortable_columns();
	$a['contained'] = 'contained';
	return $a;
    }
    function get_columns() {
	return array(
	    'cb'    => '<input type="checkbox" />',
	    'path'  => __( '', 'tn3-gallery' ),
	    'title' => __( 'Title', 'tn3-gallery' ),
	    'description' => __( 'Description', 'tn3-gallery' ),
	    'contained' => __( 'In Albums', 'tn3-gallery' ),
	    'create_time' => __( 'Created', 'tn3-gallery' ),
	    'modify_time' => __( 'Modified', 'tn3-gallery' ),
	);
    }
    
    function get_bulk_actions() {
	if (count($this->items) == 0) return array();
	$bulk = array();
	$bulk['add'] = __("Add to Album", 'tn3-gallery');
	if (isset($this->alb)) $bulk['arem'] = __("Remove from Album", 'tn3-gallery');
	$bulk['del'] = __("Delete Permanently", 'tn3-gallery');
	return $bulk;
    }
    function get_views() {
	if (count($this->items) == 0) return;
	if (isset($this->alb)) {
	    $alb = TN3::$db->getID($this->alb);
	    return array(   'foo'   => "Album: <b>$alb->title</b>", 
			    'sort'  => '<a href="admin.php?page=tn3-albums&sort='.$this->alb.'">Sort</a>'
			);
	}
    }
    function column_title( $item ) {
	//$r = parent::column_title( $item );
	$r = '<strong><a href="javascript:tn3.editImage('.$item->id.')">'.$item->title.'</a></strong>';
	$r .= "<br />".(int)$item->width." x ".(int)$item->height." px";
	$r .= "<br />".(int)($item->filesize / 1024)." kB ";
	$actions = array();
	$actions['edit'] = '<a href="javascript:tn3.editImage('.$item->id.')" title="' . esc_attr( __( 'Edit image details', 'tn3-gallery' ) ) . '">' . __( 'Edit', 'tn3-gallery' ) . '</a>';
	$actions['view'] = '<a href="javascript:tn3.showImage('.$item->id.')" title="' . esc_attr( sprintf( __( 'View image', 'tn3-gallery' ), $item->title ) ) . '" rel="permalink">' . __( 'View', 'tn3-gallery' ) . '</a>';
	$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', 'tn3-gallery' ) ) . "' href='javascript:tn3.deleteImage(".$item->id.")'>" . __( 'Delete', 'tn3-gallery' ) . "</a>";
	$r .= $this->row_actions( $actions );
	return $r;
    }
    function column_description( $item )
    {
	$desc = (isset($item->description))? $item->description : "";
	$r = $desc;
	return $r;
    }
    function column_path( $item ) {
	$img = parent::column_path( $item );
	return '<a href="javascript:tn3.showImage('.$item->id.')" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'tn3-gallery' ), $item->title ) ) . '" rel="permalink">' . $img . '</a>';
    }
    function extra_tablenav( $which )
    {
    }
    function get_items($per = 5) 
    {
	if ( isset( $_GET['s'] ) && $_GET['s'] != "" ) $s = $_GET['s'];
	else $s = null;
	return TN3::$db->get(	$this->type, 
				$this->page, 
				$per, 
				$this->alb? 'dorder' : $this->ordby, 
				$this->alb? 'ASC' : $this->ord, 
				$this->alb, 
				false,
				$s);
    }
    function get_tn3_js()
    {
	$r = parent::get_tn3_js();
	$r['sizes'] = array();
	$opts = get_option("tn3_admin_general");
	foreach ($opts as $k => $v) {
	    if (is_array($v)) {
		$v['size'] = (int)substr($k, 5);
		if (isset($v['crop']) && $v['crop'] == 1) {
		    array_push($r['sizes'], $v);
		}
	    }
	}
	return $r;
    }


}

?>
