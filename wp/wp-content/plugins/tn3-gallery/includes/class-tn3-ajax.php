<?php

require_once (TN3::$dir . 'includes/class-tn3-options.php');

class TN3_Ajax
{
    var $db;

    function __construct()
    {
	add_action('init', array($this, 'wp_init'));
	add_action('wp_ajax_tn3_admin', array($this, 'init'));	
	add_action('wp_ajax_tn3_alt', array($this, 'return_image'));	
	add_action('wp_ajax_nopriv_tn3_alt', array($this, 'return_image'));	
	add_action('wp_ajax_tn3_post_dialog', array($this, 'print_tn3_dialog'));	
    }
    function wp_init()
    {
	load_plugin_textdomain( 'tn3-gallery', false, 'tn3-gallery/lang/' );
	TN3::$o = TN3_Options::get( is_admin() );
    }
    function init()
    {
	extract($_POST);

	if ( isset($bulk_type) ) {
	    if (! wp_verify_nonce($_wpnonce, 'bulk-tn3-'.$bulk_type) )
		$this->jsonit("Not Authorized");

	    //$wpdb->hide_errors();
	    if ( $tn3_action == "save_data" ) $this->save_data($_POST['data']);
	    else if ( $tn3_action == "make_thumb" ) $this->make_thumb($_POST);
	    else call_user_func(array($this, 'do_' . $bulk_type), $_POST);
	} else if ( $tn3_action == 'select' ) {
	    set_current_screen();
	    $this->list_table($doc_type, isset($multi), isset($no_sel_btns), isset($noempty));
	} else if ( $tn3_action == 'load_form' ) {
	    $this->load_form($form);
	}
    }
    function jsonit($v, $err = true)
    {
	$a = array('jsonrpc' => '2.0');
	$a[$err? "error" : "result"] = $v;//$err? TN3::$db->db->last_query : $v;
	die(json_encode($a));
    }
    function do_images($a)
    {
	switch ($a['tn3_action']) {
	case 'add':
	    $r = TN3::$db->relate($a['id'], $a['parent']);
	    $this->jsonit(__( (false === $r)? "DB Error" : "OK" ), (false === $r));
	    break;
	case 'arem':
	    $r = TN3::$db->unrelate($a['id'], $a['aid']);
	    $this->jsonit(__( (false === $r)? "DB Error" : "OK" ), (false === $r));
	    break;
	case 'del':
	    $r = TN3::$db->delete($a['id'], true);
	    if (false !== $r) {
		//tn3log::w('paths:');
		$s = range(0,5);
		foreach ($r as $path) {
		    $ex = ( ABSPATH . TN3::$o['general']['path'] . $path );
		    if (file_exists($ex)) 
			if (! unlink($ex)) $this->jsonit(__( "Error Deleting File", 'tn3-gallery' ) . ": $ex");
		    foreach ($s as $k) {
			$ex = ( ABSPATH . TN3::$o['general']['path'] . "/$k$path" );
			if (file_exists($ex)) 
			    if (! unlink($ex)) $this->jsonit(__( "Error Deleting File", 'tn3-gallery' ) . ": $ex");
		    }
		}
	    }
	    $this->jsonit(__( (false === $r)? "DB Error" : "OK" ), (false === $r));
	    break;
	default:
	    break;
	}
    }
    function do_albums($a)
    {
	switch ($a['tn3_action']) {
	case 'add':
	    $r = TN3::$db->relate($a['id'], $a['parent']);
	    $this->jsonit(__( (false === $r)? "DB Error" : "OK" ), (false === $r));
	    break;
	case 'grem':
	    $r = TN3::$db->unrelate($a['id'], $a['gid']);
	    $this->jsonit(__( (false === $r)? "DB Error" : "OK" ), (false === $r));
	    break;
	case 'del':
	    $r = TN3::$db->delete($a['id']);
	    $this->jsonit(__( (false === $r)? "DB Error" : "OK" ), (false === $r));
	    break;
	default:
	    break;
	}
    }
    function do_galleries($a)
    {
	switch ($a['tn3_action']) {
	case 'del':
	    $r = TN3::$db->delete($a['id']);
	    $this->jsonit(__( (false === $r)? "DB Error" : "OK" ), (false === $r));
	    break;
	default:
	    break;
	}
    }
    // $data - array with keys equal to ids
    function save_data($data)
    {
	$flds = array();
	foreach( $data as $k => $v ) {
	    $flds[$k] = array();
	    foreach( $v as $fname => $d ) {
		$flds[$k][$fname] = $d;
	    }
	}
	$r = TN3::$db->insert_fields( $flds, true );
	$this->jsonit(__( (false === $r)? "DB Error" : "OK" ), (false === $r));
    }
    function make_thumb($data)
    {
	$path = ABSPATH.TN3::$o['general']['path'].$data['path'];
	
	    require_once (TN3::$dir.'includes/admin/class-tn3-image-creator.php');
	    $imgr = new TN3_Image_Creator($path);
	    $imgr->createWithCrop($data['size'], $data['data']);

	    $imgr->destroy();

	$this->jsonit(__( "OK" ), false);
    }
    function do_sort($a)
    {
	$r = TN3::$db->update_rels( $a['parentID'], $a['data'] );
	$this->jsonit(__( (false === $r)? "DB Error" : "OK" ), (false === $r));
    }
    function list_table($doc_type, $multi = false, $no_btns = false, $noempty)
    {
	require_once (TN3::$dir . 'includes/admin/class-tn3-select-list-table.php');
	$sel = new TN3_Select_List_Table($doc_type, $multi, $noempty);
	$sel->prepare_items();
	$sel->search_box( __( 'Search' ), 'tn3' );
	$sel->display();
	$sel->print_tn3_js();
	die();
    }
    function load_form($name)
    {
	$f = file_get_contents(TN3::$dir."includes/forms/$name");
	if ($f === FALSE) $this->jsonit(__('Form file reading error', 'tn3-gallery'), true);
	else $this->jsonit( $f, false );
    }


    function return_image()
    {
	$url = $_GET['u'];
	$c_dir = TN3::$o['general']['path'];
	$url = explode($c_dir, $url);
	$file = ABSPATH.$c_dir.$url[1];

	if ( ! is_file($file) ) {
	    $isize = explode("/", $url[1]);
	    $size = $isize[1];
	    $ofile = ABSPATH.$c_dir.DIRECTORY_SEPARATOR.$isize[2];
	    
	    require_once (TN3::$dir.'includes/admin/class-tn3-image-creator.php');
	    $imgr = new TN3_Image_Creator($ofile);
	    $imgr->create((int)$size, TN3::$o['general']['size_'.$size]);

	    $imgr->destroy();
	}

	$fp = fopen($file, 'rb');

	// send the right headers
	header("Content-Type: image/jpeg");
	header("Content-Length: " . filesize($file));

	// dump the picture and stop the script
	fpassthru($fp);
	exit;
    }

    function print_tn3_dialog()
    {
	$sel_skins = array();
	$r = get_option('tn3_presets_skin');
	foreach ($r as $k => $v) {
	    $sel_skins .= "<option value='$k'>$k</option>";
	}
	$sel_trans = array();
	$rt = get_option('tn3_presets_transition');
	foreach ($rt as $k => $v) {
	    $sel_trans .= "<option value='$k'>$k</option>";
	}
	$popts = get_option('tn3_admin_plugins');
?>
<div id="tn3-dialog" tabindex="-1"><div id="tn3-tabs"> 
	<ul>
	<li><a href="#tn3-tab-source"><?php _e("Source", "tn3-gallery"); ?></a></li>
	<li><a href="#tn3-tab-options"><?php _e("Options", "tn3-gallery"); ?></a></li>
	</ul>

<div id="tn3-tab-source">
    <ul class="tn3-source-nav">
	<li>Images</li>
	<li>Albums</li>

	<li>Gallery</li>
	<li>XML</li>
	<li>Flickr</li>
	<li>Picasa</li>
	<li>Facebook</li>

    </ul>
    <div class="tn3-source">
    </div>
</div>

<div id="tn3-tab-options">
<div id="tn3-post-options">
    <div class="left tn3-form-cont">
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Skin preset:", "tn3-gallery"); ?></span>
	    <select id="tn3-select-skin" name="tn3-post-skin"><?php echo $sel_skins; ?></select>
	</div>

	<div class="tn3-form-elem">
	<span class="title"><?php _e("Transition preset:", "tn3-gallery"); ?></span>
	    <select id="tn3-select-transitions" name="tn3-post-transitions"><?php echo $sel_trans; ?></select>
	</div>

	<div class="tn3-form-elem">
	<span class="title"><?php _e("Dimensions:", "tn3-gallery"); ?></span>
	    <span class="input-text-wrap">
		<input type="text" name="tn3-post-width" class="ptitle" value="<?php echo $r['default']['width']; ?>" size="4" /> x
		<input type="text" name="tn3-post-height" class="ptitle" value="<?php echo $r['default']['height']; ?>" size="4" />
	    </span>
	</div>
	<div class="tn3-form-elem">
	<input value="1" type="checkbox" name="tn3-post-responsive" id="tn3-post-responsive"><label for="tn3-post-responsive">  <?php _e("Responsive", "tn3-gallery"); ?></label>
	</div>
    </div>
    <div class="right tn3-form-cont">
	<div class="tn3-form-elem">
	<input value="1" type="checkbox" name="tn3-post-autoplay" id="tn3-post-autoplay"><label for="tn3-post-autoplay"> <?php _e("Slideshow Autoplay", "tn3-gallery"); ?></label>
	</div>
	<div class="tn3-form-elem">
	<input value="1" type="checkbox" name="tn3-post-startWithAlbums" id="tn3-post-startWithAlbums"><label for="tn3-post-startWithAlbums">  <?php _e("Display Albums First", "tn3-gallery"); ?></label>
	</div>

	<div class="tn3-form-elem">
	<input value="1" type="checkbox" name="tn3-post-history" id="tn3-post-history"><label for="tn3-post-history">  <?php _e("Enable History Plugin", "tn3-gallery"); ?></label>
	</div>
	<div class="tn3-form-elem">
	<input value="1" type="checkbox" name="tn3-post-mediaelement" id="tn3-post-mediaelement"><label for="tn3-post-mediaelement">  <?php _e("Include mediaelement.js", "tn3-gallery"); ?></label>
	</div>
	<div class="tn3-form-elem">
	<input value="1" type="checkbox" name="tn3-post-touch" id="tn3-post-touch"><label for="tn3-post-touch">  <?php _e("Enable Touchscreen plugin", "tn3-gallery"); ?></label>
	</div>

	<div class="tn3-form-elem-click">
	<span class="title"><?php _e("Image click action:", "tn3-gallery"); ?></span>
	    <select id="tn3-image-click" name="tn3-post-imageClick">
	    <option value='next'><?php _e("Next Image", "tn3-gallery"); ?></option>
	    <option value='url'><?php _e("Open URL", "tn3-gallery"); ?></option>
	    <option value='fullscreen'><?php _e("Go Full Screen", "tn3-gallery"); ?></option>
	    </select>
	</div>
    </div>
</div>
</div>

<div class="submitbox">
	<div id="wp-link-cancel" style="font-size:11px;">
	<a class="submitdelete deletion" href="#"><?php _e("Cancel", "tn3-gallery"); ?></a>
	</div>
	<div id="tn3-submit-ok">
	<input type="submit" name="tn3-ok" id="tn3-ok" class="button-primary" value="<?php _e("Insert TN3", "tn3-gallery"); ?>" tabindex="100">
	</div>
</div>
</div></div> 

<div id="tn3-xml-form" class="tn3-form-cont">
	<div class="tn3-xml-url">
	    <span class="title">XML URL:</span>
	    <span class="input-text-wrap"><input type="text" name="tn3-xml-url" class="ptitle" value="" size="80" /></span>
	</div>

</div>

<div id="tn3-flickr-form" class="tn3-form-cont">
<?php
	
	if ( !isset($popts['flickr-api_key']) || $popts['flickr-api_key'] == "") 
	    echo '<p class="tn3-error">flickr API key is missing</p>';
	else echo '<input type="hidden" name="tn3-flickr-api_key" value="'.$popts['flickr-api_key'].'" disabled="disabled" />';
	$userID = $popts['flickr-user_id']

?>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("User ID:", "tn3-gallery"); ?></span>
	    <span class="input-text-wrap"><input type="text" name="tn3-flickr-user_id" class="ptitle" value="<?php echo $userID; ?>" /></span>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Source:", "tn3-gallery"); ?></span>
	    <select id="tn3-flickr-combo-source" name="tn3-flickr-source">
		<option value='sets'>Sets</option>
		<option value='galleries'>Galleries</option>
		<option value='favorites'>Favorites</option>
		<option value='interstingness'>Interstingness</option>
		<option value='photostream'>Photostream</option>
		<option value='search'>Search</option>
	    </select>
	    <select id="tn3-flickr-combo-sets" name="tn3-flickr-sets">
		<option value='all'>All</option>
	    </select>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Image:", "tn3-gallery"); ?></span>
	    <select id="tn3-flickr-combo-image-size" name="tn3-flickr-imageSize">
		<option value='s'>small square 75x75</option>
		<option value='t'>thumbnail, 100 on longest side</option>
		<option value='m'>small, 240 on longest side</option>
		<option value='-'>medium, 500 on longest side</option>
		<option value='z' selected>medium 640, 640 on longest side</option>
		<option value='b'>large, 1024 on longest side</option>
		<option value='o'>original image</option>
	    </select>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Thumbnail:", "tn3-gallery"); ?></span>
	    <select id="tn3-flickr-combo-thumbnail-size" name="tn3-flickr-thumbSize">
		<option value='s' selected>small square 75x75</option>
		<option value='t'>thumbnail, 100 on longest side</option>
		<option value='m'>small, 240 on longest side</option>
		<option value='-'>medium, 500 on longest side</option>
		<option value='z'>medium 640, 640 on longest side</option>
		<option value='b'>large, 1024 on longest side</option>
		<option value='o'>original image</option>
	    </select>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Start page:", "tn3-gallery"); ?></span>
	    <span class="input-text-wrap"><input type="text" name="tn3-flickr-page" class="ptitle" value="1" size="4" /></span>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Per page:", "tn3-gallery"); ?></span>
	    <span class="input-text-wrap"><input type="text" name="tn3-flickr-per_page" class="ptitle" value="20" size="4" /></span>
	</div>

</div>
<div id="tn3-picasa-form" class="tn3-form-cont">
	<div class="tn3-form-elem">
	<span class="title"><?php _e("User ID:", "tn3-gallery"); ?></span>
	    <span class="input-text-wrap"><input type="text" name="tn3-picasa-userID" class="ptitle" value="<?php echo $popts['picasa-userID']; ?>" /></span>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Source:", "tn3-gallery"); ?></span>
	    <select id="tn3-picasa-combo-source" name="tn3-picasa-source">
		<option value='albums'>Albums</option>
		<option value='photos'>Photos</option>
		<option value='album'>Album</option>
		<option value='all'>All</option>
		<option value='featured'>Featured</option>
	    </select>
	    <span class="tn3-hidden">Album:&nbsp;
		<select id="tn3-picasa-combo-album" name="tn3-picasa-albumID">
		    <option value=''>Loading...</option>
		</select>
	    </span>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Image:", "tn3-gallery"); ?></span>
	    <select id="tn3-picasa-combo-image-size" name="tn3-picasa-imageSize">
		<option value="32c">square, 32 pixels</option>
		<option value="32u">32 pixels</option>
		<option value="48c">square, 48 pixels</option>
		<option value="48u">48 pixels</option>
		<option value="64c">square, 64 pixels</option>
		<option value="64u">64 pixels</option>
		<option value="72c">square, 72 pixels</option>
		<option value="72u">72 pixels</option>
		<option value="94u">94 pixels</option>
		<option value="104c">square, 104 pixels</option>
		<option value="104u">104 pixels</option>
		<option value="110u">110 pixels</option>
		<option value="128u">128 pixels</option>
		<option value="144c">square, 144 pixels</option>
		<option value="144u">144 pixels</option>
		<option value="150c">square, 150 pixels</option>
		<option value="150u">150 pixels</option>
		<option value="160c">square, 160 pixels</option>
		<option value="160u">160 pixels</option>
		<option value="200u">200 pixels</option>
		<option value="220u">220 pixels</option>
		<option value="288u">288 pixels</option>
		<option value="320u">320 pixels</option>
		<option value="400u">400 pixels</option>
		<option value="512u">512 pixels</option>
		<option value="576u">576 pixels</option>
		<option value="640u">640 pixels</option>
		<option value="720u">720 pixels</option>
		<option value="800u">800 pixels</option>
		<option value="912u">912 pixels</option>
		<option selected="selected" value="1024u">1024 pixels</option>
		<option value="1152u">1152 pixels</option>
		<option value="1280u">1280 pixels</option>
		<option value="1440u">1440 pixels</option>
		<option value="1600u">1600 pixels</option>
		<option value="d">original</option>
	    </select>

	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Thumbnail:", "tn3-gallery"); ?></span>
	    <select id="tn3-picasa-combo-thumbnail-size" name="tn3-picasa-thumbSize">
		<option value="32c">square, 32 pixels</option>
		<option value="32u">32 pixels</option>
		<option value="48c">square, 48 pixels</option>
		<option value="48u">48 pixels</option>
		<option value="64c">square, 64 pixels</option>
		<option value="64u">64 pixels</option>
		<option selected="selected" value="72c">square, 72 pixels</option>
		<option value="72u">72 pixels</option>
		<option value="94u">94 pixels</option>
		<option value="104c">square, 104 pixels</option>
		<option value="104u">104 pixels</option>
		<option value="110u">110 pixels</option>
		<option value="128u">128 pixels</option>
		<option value="144c">square, 144 pixels</option>
		<option value="144u">144 pixels</option>
		<option value="150c">square, 150 pixels</option>
		<option value="150u">150 pixels</option>
		<option value="160c">square, 160 pixels</option>
		<option value="160u">160 pixels</option>
		<option value="200u">200 pixels</option>
		<option value="220u">220 pixels</option>
		<option value="288u">288 pixels</option>
		<option value="320u">320 pixels</option>
		<option value="400u">400 pixels</option>
		<option value="512u">512 pixels</option>
		<option value="576u">576 pixels</option>
		<option value="640u">640 pixels</option>
		<option value="720u">720 pixels</option>
		<option value="800u">800 pixels</option>
		<option value="912u">912 pixels</option>
		<option value="1024u">1024 pixels</option>
		<option value="1152u">1152 pixels</option>
		<option value="1280u">1280 pixels</option>
		<option value="1440u">1440 pixels</option>
		<option value="1600u">1600 pixels</option>
		<option value="d">original</option>
	    </select>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Start page:", "tn3-gallery"); ?></span>
	    <span class="input-text-wrap"><input type="text" name="tn3-picasa-page" class="ptitle" value="1" size="4" /></span>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Per page:", "tn3-gallery"); ?></span>
	    <span class="input-text-wrap"><input type="text" name="tn3-picasa-per_page" class="ptitle" value="20" size="4" /></span>
	</div>

</div>
<div id="tn3-facebook-form" class="tn3-form-cont">
	<div class="tn3-form-elem">
	<span class="title tn3-title-id"><?php _e("User ID:", "tn3-gallery"); ?></span>
	    <span class="tn3-facebook-user-id"><input type="text" name="tn3-facebook-ID" class="ptitle" value="<?php echo $popts['facebook-ID']; ?>" /></span>
	    <span class="tn3-facebook-album-id">
		<select id="tn3-facebook-combo-album" name="tn3-facebook-aID" style="display:none" disabled="">
		    <option value=''>Loading...</option>
		</select>
	    </span>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Source:", "tn3-gallery"); ?></span>
	    <select id="tn3-facebook-combo-source" name="tn3-facebook-source">
		<option value='albums'>All Albums</option>
		<option value='album'>Specific Album</option>
	    </select>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Image:", "tn3-gallery"); ?></span>
	    <select id="tn3-facebook-combo-image-size" name="tn3-facebook-imageSize">
		<option value='thumbnail'>small, 75px</option>
		<option value='album'>medium, 130 on longest side</option>
		<option selected="selected" value='normal'>normal, 720 on longest side</option>
		<option value='original'>original</option>
	    </select>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Thumbnail:", "tn3-gallery"); ?></span>
	    <select id="tn3-facebook-combo-thumbnail-size" name="tn3-facebook-thumbSize">
		<option value='thumbnail'>small, 75px</option>
		<option value='album'>medium, 130 on longest side</option>
		<option value='normal'>normal, 720 on longest side</option>
		<option value='original'>original</option>
	    </select>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Start page:", "tn3-gallery"); ?></span>
	    <span class="input-text-wrap"><input type="text" name="tn3-facebook-page" class="ptitle" value="1" size="4" /></span>
	</div>
	<div class="tn3-form-elem">
	<span class="title"><?php _e("Per page:", "tn3-gallery"); ?></span>
	    <span class="input-text-wrap"><input type="text" name="tn3-facebook-per_page" class="ptitle" value="20" size="4" /></span>
	</div>

</div>

<?php
	$this->print_tn3_js();
	die();
    }
    function print_tn3_js() {
	$tn = array();	

	echo "\n<script type='text/javascript'>";
	foreach($tn as $k => $v) {
	    echo "tn3.".$k."=".json_encode($v).";";
	}
	echo "tn3.pluginPath=".json_encode(TN3::$url).";";
	require_once (TN3::$dir . 'includes/class-tn3-presets.php');
	echo "tn3.defaults=".json_encode($tn3_plugin_defaults).";";
	$sp = get_option('tn3_presets_skin');
	echo "tn3.skinPresets=".json_encode($sp).";";
	
	echo "</script>\n";
    }

}


?>
