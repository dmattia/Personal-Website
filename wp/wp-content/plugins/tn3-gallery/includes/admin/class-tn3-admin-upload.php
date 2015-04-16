<?php
	
require_once (TN3::$dir.'includes/admin/class-tn3-admin-page.php');

class TN3_Admin_Upload extends TN3_Admin_Page
{


    function upload_dir($a)
    {
	$a['path'] = ABSPATH.TN3::$o['general']['path'];
	$siteurl = get_option( 'siteurl' );
	$a['url'] = trailingslashit( $siteurl ) . TN3::$o['general']['path'];
	return $a;
    }
    function __construct()
    {
	parent::__construct();
	define('FS_METHOD', 'direct');
    }
    function admin_init()
    {
	set_error_handler(array($this, 'on_error'));	
	if ( !empty($_FILES) ) {
	    $nonce = $_REQUEST['_wpnonce'];
	    if (! wp_verify_nonce($nonce, 'tn3_upload_nonce') )
	       $this->jsonit(array("code" => 102, "message" => "Not Authorized"));
	    $overrides = array( 'test_form' => false, 'test_type' => false );
	    add_filter('upload_dir', array($this, 'upload_dir'));
	    $file = wp_handle_upload($_FILES['file'], $overrides);

	    if ( isset($file['error']) )
	       $this->jsonit(array("code" => 102, "message" => "WP Handle File Upload Error"));

	    //$lib = $this->lib = new TN3_Image_Lib();
	    $sizes = $this->getRequiredSizes();
	    require_once (TN3::$dir.'includes/admin/class-tn3-image-creator.php');
	    $imgr = new TN3_Image_Creator($file['file']);
	    foreach ($sizes as $size => $o) {
		$imgr->create($size, $o);
	    }

	    $imgr->destroy();
		
	    $relp = explode(TN3::$o['general']['path'], $file['file']);
	    $rel_album = (isset($_REQUEST['album']))? $_REQUEST['album'] : null;
	    $this->db->insert_image($relp[1], $imgr->w, $imgr->h, filesize($file['file']), $rel_album);
	    
	    
	    $this->jsonit(array("url"	    => $file['url'],
				"size"	    => 123123
	    ), false);

	    var_dump($file);
	}
	$this->jsonit(array("code" => 102, "message" => "Upload Error. Nothing to upload."));

    }
    function on_error($code, $msg)
    {
	$this->jsonit(array("code" => 102, "message" => $code.": ".$msg));
    }
    function jsonit($v, $err = true)
    {
	restore_error_handler();
	$a = array('jsonrpc' => '2.0');
	$a[$err? "error" : "result"] = $v;
	die(json_encode($a));
    }
    function getRequiredSizes()
    {
	$ret = array();
	foreach (TN3::$o['general'] as $k => $v) {
	    if (substr($k, 0, 5) == "size_") {
		if (isset($v['required']) && !$v['required']) continue;
		$ret[(int)substr($k, 5)] = $v;
	    }
	}
	return $ret;
    }
    function getNewPath($size, $original_path)
    {
	$info = pathinfo($original_path);
	return $info['dirname']."/".$info['filename']."_$size.".$info['extension'];	
    }
	
}
?>
