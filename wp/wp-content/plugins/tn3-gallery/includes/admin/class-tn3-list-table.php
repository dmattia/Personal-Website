<?php

class TN3_List_Table extends WP_List_Table 
{
    var $opts, $type, $page, $ordby, $ord, $db;

    function TN3_List_Table($type) 
    {
	$this->type = $type;

	$this->page = $this->get_pagenum() - 1;
	$this->ordby = ( empty( $_REQUEST['orderby'] ) )? 'modify_time' : $_REQUEST['orderby'];
	$this->ord = ( empty( $_REQUEST['order'] ) )? 'DESC' : $_REQUEST['order'];

	parent::__construct( array(
	    'plural' => 'tn3-' . ( ($type == 'gallery')? 'galleries' : $type.'s' ),
	    'singular' => $type,
	    'ajax' => true,
	) );
	add_action( 'admin_footer', array( &$this, 'print_tn3_js' ) );
    }

    function ajax_user_can() 
    {
	return true;
    }
    function prepare_items() 
    {
	$user = get_current_user_id();
	$screen = get_current_screen();
	$option = $screen->get_option('per_page', 'option');
	if ($option == NULL) $per = $screen->get_option( 'per_page', 'default' );
	else $per = get_user_meta($user, $option, true);
	if ( ! isset($per) || $per == 0 ) $per = 20;

	$this->items = $this->get_items($per);
	$tot = TN3::$db->found_rows;

	$this->set_pagination_args( array(
		'total_items' => $tot,
		'per_page' => $per
	) );
    }
    function check_permissions() {
	if ( !current_user_can('manage_options') )
	    wp_die(__('Cheatin&#8217; uh?'));
    }

    function get_columns() {
	return array(
	    'cb'    => '<input type="checkbox" />',
	    'title' => __( 'Title', 'tn3-gallery' ),
	    'create_time' => __( 'Created', 'tn3-gallery' ),
	    'modify_time' => __( 'Modified', 'tn3-gallery' ),
	);
    }
    
    function get_sortable_columns() {
	return array(
	    'title' => 'title',
	    'create_time' => 'create_time',
	    'modify_time' => 'modify_time'
	);
    }
    function column_cb( $item ) {
	return '<input type="checkbox" name="tn3_images" value="' . $item->id . '">';
    }
    
    function column_path( $item ) {
	$siteurl = get_option( 'siteurl' );
	return '<img src="'.trailingslashit( $siteurl ).TN3::$o['general']['path']."/2".$item->path.'" width="60" height="60" />';
    }

    function column_title( $item )
    {
	//$r = '<fieldset class="tn3-fieldset">';
	//$r .= '<span class="title">' . __( "Title" ) . ': </span>';
	$r = '<input type="text" name="title['.$item->id.']" size="30" value="'.$item->title.'" class="title"> ';
	//$r .= '<span class="title">' . __( "Description" ) . ': </span>';
	//$item->description = isset($item->description)? $item->description : '';
	//$r .= '<textarea cols="28" rows="2" name="description['.$item->id.']">'.$item->description.'</textarea>';
	//$r .= '</fieldset>';
	//$r = '<strong><a href="#">'.$item->title.'</a></strong>';
	return $r;
    }
    function column_description( $item )
    {
	$desc = (isset($item->description))? $item->description : "";
	$r = '<textarea cols="28" rows="3" name="description['.$item->id.']">'.$desc.'</textarea>';
	return $r;
    }    
    function column_default( $item, $column_name ) {
	if ($column_name == 'modify_time' || $column_name == "create_time") return $this->column_date($item, $column_name);
	return $item->$column_name;
    }
    function column_date( $item, $column_name )
    {
	$time = strtotime($item->$column_name);
	$time_diff = time() - $time;
	if ( $time_diff > 0 && $time_diff < 7*24*60*60 )
	    $h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
	else
	    $h_time = mysql2date( __( 'Y/m/d' ), $item->$column_name );
	return $h_time;
    }
    
    function get_bulk_actions() {
	return array();
    }
    function get_views() {
	$types = array( 'sort'    => __('Sort', 'tn3-gallery'));return $types;
	$ret = array();
	$status = isset($_REQUEST['status'])? $_REQUEST['status'] : "all";
	foreach ($types as $class => $title) {
	    $link = 'admin.php?page=tn3-images';
	    $link = add_query_arg( 'status', $class, $link );
	    // key is class name, value is html to print
	    $ret[$class] = ($status == $class)? "<strong>$title</strong>" : "<a href='$link'>$title</a>";
	}
	return $ret;
    }
    function get_items($per = 10) 
    {
	return TN3::$db->get($this->type, $this->page, $per, $this->ordby, $this->ord);

    }
    // rewrite original with bug fixed, see commented
    function get_column_info() {
	if ( isset( $this->_column_headers ) )
	    return $this->_column_headers;

	$screen = get_current_screen();

	//$columns = get_column_headers( $screen );
	$columns = apply_filters( 'manage_' . $screen->id . '_columns', $this->get_columns() );
	$hidden = get_hidden_columns( $screen );

	$_sortable = apply_filters( "manage_{$screen->id}_sortable_columns", $this->get_sortable_columns() );

	$sortable = array();
	foreach ( $_sortable as $id => $data ) {
	    if ( empty( $data ) )
		    continue;

	    $data = (array) $data;
	    if ( !isset( $data[1] ) )
		    $data[1] = false;

	    $sortable[$id] = $data;
	}

	$this->_column_headers = array( $columns, $hidden, $sortable );

	return $this->_column_headers;
    }
    function no_items() 
    {
	_e( 'No items found yet.', 'tn3-gallery' );
    }
    function extra_tablenav( $which )
    {
	if (count($this->items) == 0) return;
	$id = ($which == "top")? "dosave" : "dosave2";
	echo '<div class="alignleft actions">';
	echo '<input type="submit" name="" id="'.$id.'" class="button-secondary action" value="'.__('Save Changes', 'tn3-gallery').'">';
	echo '</div>';
    }
    function get_tn3_js()
    {
	$siteurl = get_option( 'siteurl' );
	$r = array( "path" => trailingslashit( $siteurl ).TN3::$o['general']['path'] );
	$r[ "pluginPath" ] = TN3::$url;
	$r[ 'data' ] = array();
	$o = 0;
	foreach ($this->items as $k => $v) {
	    $v->ord = $o;
	    $o++;
	    array_push($r['data'], $v);
	}
	return $r;
    }
    function print_tn3_js() {
	$tn = $this->get_tn3_js();
	echo "\n<script type='text/javascript'>";
	foreach($tn as $k => $v) {
	    if ($k == "data") echo "tn3.data = tn3.data.concat(".json_encode($v).");";
	    else echo "tn3.".$k."=".json_encode($v).";";
	}
	echo "</script>\n";
    }
}

?>
