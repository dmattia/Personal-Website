<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin-page.php');

class TN3_Admin_Images extends TN3_Admin_Page
{

    function __construct()
    {
	parent::__construct();
	add_action( 'admin_head', array($this, 'print_admin_head' ));
    }
    function admin_load_page()
    {
	add_screen_option( 'per_page', array('label'=>__('Images', 'tn3-gallery'), 'default'=>20, 'option'=>'tn_images_per_page'));
    }
    function print_styles()
    {
	parent::print_styles();
	wp_enqueue_style('plupload-queue', TN3::$url."css/jquery.plupload.queue.css");
    }
    function enqueue_scripts()
    {
	parent::enqueue_scripts();
	//wp_enqueue_script( "browserplus", "http://bp.yahooapis.com/2.4.21/browserplus-min.js" );
	wp_enqueue_script( "plupload-moxie", TN3::$url."js/plupload/moxie.js" );
	wp_enqueue_script( "plupload-handlers", TN3::$url."js/plupload/plupload.full.min.js" );
	wp_enqueue_script( "plupload-queue", TN3::$url."js/plupload/jquery.plupload.queue.js" );
	wp_enqueue_script( "Jcrop", TN3::$url."js/jquery.Jcrop.min.js" );
    }

    function print_page()
    {
	$this->print_header(TN3_Admin::$t['images'], __("Upload Images", 'tn3-gallery'));
	require_once (TN3::$dir . 'includes/admin/class-tn3-images-list-table.php');
	$imgs = new TN3_Images_List_Table(TN3::$o);
	$imgs->prepare_items();
	$imgs->views();
	$imgs->search_box( __( 'Search Images', 'tn3-gallery' ), 'tn3' );
	$imgs->display();
    }
    function print_add_form()
    {
	$fsm = get_filesystem_method();
	if ($fsm != "direct") _e("Your server doesn't use direct filesystem method.");
?>
<form id="tn3-upload-form" method="post" action="examples_dump.php">
    <div id="tn3-uploader">
    <p><?php 
	_e("You browser doesn't have Flash, Silverlight or HTML5 support.", "tn3-gallery");
    ?></p>
    </div>
</form>
<?php
    }
    function print_admin_head()
    {
	$mynon = wp_create_nonce('tn3_upload_nonce');
	$size0 = TN3::$o['general']['size_0'];
	    
?>
<script type="text/javascript">
(function ($) {
    $('document').ready(function () {

	var plugURL = "<?php echo TN3::$url; ?>",
	    $uploader = $("#tn3-uploader"),
	    upQueue;

	if ($uploader.length > 0) {
	    
	    $uploader.pluploadQueue({
		// General settings
		runtimes : '<?php echo TN3::$o['general']['runtimes']; ?>',
		url : 'admin.php?tn3=tn3-upload&_wpnonce=<?php echo $mynon; if (isset($_GET['album'])) echo "&album=".$_GET['album']; ?>',
<?php if (TN3::$o['general']['max_file_size']): ?>
		max_file_size : '<?php echo TN3::$o['general']['max_file_size']; ?>',
<?php endif; ?>
		//chunk_size : '<?php //echo TN3::$o['upload']['chunk_size']; ?>',
		unique_names : true,
<?php if (TN3::$o['general']['resize']): ?>
		// Resize images on clientside if we can
		resize : {width : <?php echo $size0['w']; ?>, height : <?php echo $size0['h']; ?>, quality : <?php echo $size0['q']; ?>},
<?php endif; ?>
		// Specify what files to browse for
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
			{title : "Zip files", extensions : "zip"}
		],

		// Flash settings
		flash_swf_url : plugURL + 'js/plupload/Moxie.swf',

		// Silverlight settings
		silverlight_xap_url : plugURL + 'js/plupload/Moxie.xap',
		multipart: true,
		multiple_queues:true
	    });

	    upQueue = $uploader.pluploadQueue();
	    upQueue.bind('UploadComplete', function() {
		location.reload();
	    });
	};
	
    });

})(jQuery);

</script>
<?php	
    }
}

?>
