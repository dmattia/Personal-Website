<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin-page.php');

class TN3_Admin_Albums extends TN3_Admin_Page
{

    function print_page()
    {
	$this->print_header(parent::$t['albums'], __("Add Album"));
	require_once (TN3::$dir . 'includes/admin/class-tn3-albums-list-table.php');
	$albs = new TN3_Albums_List_Table(TN3::$o);
	$albs->prepare_items();
	$albs->search_box( __( 'Search Albums', 'tn3-gallery' ), 'tn3' );
	$albs->views();
	$albs->display();
    }
    function admin_init()
    {
	parent::admin_init();

	if ( isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'tn3_albums_nonce') ) {

	    $tit = sanitize_title($_POST['tn3_album_title']);
	    if (!$tit) {
		$this->notices[] = array('error', __('Album is not created. Title is missing.', 'tn3-gallery'));
		return;
	    }
	    
	    require_once (TN3::$dir . 'includes/class-tn3-db.php');
	    $this->db->insert_album($tit, $_POST['tn3_album_desc'], $_POST['thumb_tn3_album']);
	    $this->notices[] = array('updated', __('Album created succesfully.', 'tn3-gallery'));
	}
    }
    function admin_load_page()
    {
	add_screen_option( 'per_page', array('label'=>__('Albums'), 'default'=>20, 'option'=>'tn_albums_per_page'));
    }
    function print_add_form()
    {
	$mynon = wp_create_nonce('tn3_albums_nonce');
?>
<form id="tn3-albums-form" method="post" action="admin.php?page=tn3-albums&_wpnonce=<?php echo $mynon; ?>">
    <table class="form-table">
	<tbody>
	    <tr valign="top">
	    <th scope="row"><label for="posts_per_page"><?php _e("Icon:", "tn3-gallery"); ?></label></th>
		<td>
		    <input name="thumb_tn3_album" type="hidden" value="" />
		    <div class="tn3-album-thumb"></div>		    
		</td>
	    </tr>
	    <tr valign="top">
	    <th scope="row"><label for="posts_per_page"><?php _e("Title", "tn3-gallery"); ?><span> *</span>:</label></th>
		<td>
		    <input name="tn3_album_title" type="text" id="tn3_album_title" value="" class="regular-text" />
		</td>
	    </tr>
	    <tr valign="top">
	    <th scope="row"><label for="posts_per_page"><?php _e("Description:", "tn3-gallery"); ?></label></th>
		<td>
		    <input name="tn3_album_desc" type="text" id="tn3_album_desc" value="" class="regular-text" />
		</td>
	    </tr>
	</tbody>
    </table>
    <br />
    <input name="Submit" type="submit" class="button-primary" value="<?php _e("Create Album", "tn3-gallery"); ?>" />
</form>
<?php
    }

}

?>
