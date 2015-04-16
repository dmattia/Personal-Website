<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin-page.php');

class TN3_Admin_Galleries extends TN3_Admin_Page
{


    function print_page()
    {
	$this->print_header(parent::$t['galleries'], __("Add Gallery", 'tn3-gallery'));
	require_once (TN3::$dir . 'includes/admin/class-tn3-galleries-list-table.php');
	$gals = new TN3_Galleries_List_Table(TN3::$o);
	$gals->prepare_items();
	$gals->display();
    }
    function admin_init()
    {
	parent::admin_init();

	if ( isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'tn3_gallery_nonce') ) {
	    $tit = sanitize_title($_POST['tn3_gallery_title']);
	    if (!$tit) {
		$this->notices[] = array('error', __('Gallery is not created. Title is missing.', 'tn3-gallery'));		
		return;
	    }
	    
	    require_once (TN3::$dir . 'includes/class-tn3-db.php');
	    $this->db->insert_gallery($tit, $_POST['tn3_gallery_desc']);
	    $this->notices[] = array('updated', __('Gallery created succesfully.', 'tn3-gallery'));
	}
    }
    function admin_load_page()
    {
	add_screen_option( 'per_page', array('label'=>__('Galleries'), 'default'=>20, 'option'=>'tn_galleries_per_page'));
    }
    function print_add_form()
    {
	$mynon = wp_create_nonce('tn3_gallery_nonce');
?>
<form id="tn3-albums-form" method="post" action="admin.php?page=tn3-galleries&_wpnonce=<?php echo $mynon; ?>">
    <table class="form-table">
	<tbody>
	    <tr valign="top">
	    <th scope="row"><label for="posts_per_page"><?php _e("Title", "tn3-gallery"); ?><span> *</span>:</label></th>
		<td>
		    <input name="tn3_gallery_title" type="text" id="tn3_gallery_title" value="" class="regular-text" />
		</td>
	    </tr>
	    <tr valign="top">
	    <th scope="row"><label for="posts_per_page"><?php _e("Description:", "tn3-gallery"); ?></label></th>
		<td>
		    <input name="tn3_gallery_desc" type="text" id="tn3_gallery_desc" value="" class="regular-text" />
		</td>
	    </tr>
	</tbody>
    </table>
    <br />
    <input name="Submit" type="submit" class="button-primary" value="<?php _e("Create Gallery", "tn3-gallery"); ?>" />
</form>
<?php
    }


}

?>
