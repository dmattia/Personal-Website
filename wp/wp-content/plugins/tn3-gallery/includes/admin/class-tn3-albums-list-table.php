<?php

require_once (TN3::$dir . 'includes/admin/class-tn3-list-table.php');

class TN3_Albums_List_Table extends TN3_List_Table 
{
    var $gal;

    function TN3_Albums_List_Table($opts) 
    {
	parent::__construct('album', $opts);
	$this->gal = ( empty( $_REQUEST['gallery'] ) )? null : (int)$_REQUEST['gallery'];
    }
    function get_sortable_columns() {
	if (isset($this->gal)) return array();
	$a = parent::get_sortable_columns();
	$a['contains'] = 'contains';
	$a['contained'] = 'contained';
	return $a;
    }
    function get_columns() {
	return array(
	    'cb'    => '<input type="checkbox" />',
	    'path'  => __( '', 'tn3-gallery' ),
	    'title' => __( 'Title', 'tn3-gallery' ),
	    'description' => __( 'Description', 'tn3-gallery' ),
	    'contains' => __( 'Images', 'tn3-gallery' ),
	    'contained' => __( 'In Galleries', 'tn3-gallery' ),
	    'create_time' => __( 'Created', 'tn3-gallery' ),
	    'modify_time' => __( 'Modified', 'tn3-gallery' ),
	);
    }
    function get_bulk_actions() {
	if (count($this->items) == 0) return array();
	$bulk = array();
	$bulk['add'] = __("Add to Gallery");
	if (isset($this->gal)) $bulk['grem'] = __("Remove from Gallery", 'tn3-gallery');
	$bulk['del'] = __("Delete Album", 'tn3-gallery');
	return $bulk;
    }
    function get_views() {
	if (count($this->items) == 0) return;
	if ( isset($this->gal) ) {
	    $glr = TN3::$db->getID($this->gal);
	    return array(   'foo'   => "Gallery: <b>$glr->title</b>", 
			    'sort'  => '<a href="admin.php?page=tn3-galleries&sort='.$this->gal.'">Sort</a>'
			);
	}
    }
    function column_title( $item ) {
	$r = parent::column_title( $item );
	//$r = '<strong><a href="admin.php?page=tn3-images&album='.$item->id.'">'.$item->title.'</a></strong>';
	$actions = array();
	$actions['view'] = '<a href="admin.php?page=tn3-images&album='.$item->id.'" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'tn3-gallery' ), $item->title ) ) . '" rel="permalink">' . __( 'View', 'tn3-gallery' ) . '</a>';
	if ($item->contains > 0)
	$actions['sort'] = '<a href="admin.php?page=tn3-albums&sort='.$item->id.'" title="' . esc_attr( __( 'Sort images', 'tn3-gallery' ) ) . '">' . __( 'Sort', 'tn3-gallery' ) . '</a>';
	$r .= $this->row_actions( $actions );
	return $r;
    }
    function column_path( $item ) 
    {
	$ithumb = "";
	if (isset($item->thumb)) {
	    $ithumb = $item->thumb;
	    $siteurl = get_option( 'siteurl' );
	    $thumb = '<img src="'.trailingslashit( $siteurl ).TN3::$o['general']['path']."/2".$ithumb.'" width="60" height="60" />';
	} else $thumb = "";

	return	'<input name="thumb['.$item->id.']" type="hidden" value="'.$ithumb.'" />' .
		'<div class="tn3-album-thumb">' .
		$thumb .
		'</div>';
    }
    function get_items($per = 5) 
    {
	//return $this->db->get($this->type, $this->page, $per, $this->ordby, $this->ord, $this->gal);
	if ( isset( $_GET['s'] ) && $_GET['s'] != "" ) $s = $_GET['s'];
	else $s = null;
	return TN3::$db->get(	$this->type, 
				$this->page, 
				$per, 
				$this->gal? 'dorder' : $this->ordby, 
				$this->gal? 'ASC' : $this->ord, 
				$this->gal, 
				false,
				$s);
    }

}

?>
