<?php

	require_once (TN3::$dir . 'includes/class-tn3-presets.php');
class TN3_Post
{
    var $count;

    function __construct()
    {
	$this->count = 0;
	// use 'wp' action and parse shortcode on your own!!!
	add_action('wp', array($this, 'on_wp'));
	add_shortcode( 'tn3', array($this, 'shortcode'));
    }
    function on_wp($wp)
    {
	global $wp_the_query;
	$posts = $wp_the_query->posts;
	if (empty($posts)) return;
 
	// find skin preset names
	$skin_presets = array(); 
	$plugins = array();
	foreach ($posts as $post) {
	    $b = stripos($post->post_content, '[tn3');
	    while ( $b !== false) {
		$e = stripos($post->post_content, ']', $b) + 1;
		$sc = shortcode_parse_atts(substr($post->post_content, $b+5, $e-$b-6));
		if ( !isset($sc['skin']) ) $sc['skin'] = "default";
		$skin_presets[ $sc['skin'] ] = true;

		if ( isset( $sc['history'] ) ) $plugins['history'] = true;
		if ( isset( $sc['mediaelement'] ) ) $plugins['mediaelement'] = true;
		if ( isset( $sc['touch'] ) ) $plugins['touch'] = true;
		if ( isset( $sc['origin'] ) ) {
		    if ( $sc['origin'] == "flickr" ) $plugins['flickr'] = true;
		    else if ( $sc['origin'] == "picasa" ) $plugins['picasa'] = true;
		    else if ( $sc['origin'] == "facebook" ) $plugins['facebook'] = true;
		}

		$b = stripos($post->post_content, '[tn3', $e);
	    }
	}
	// enqueue js and css
	if (count($skin_presets) > 0) {

	    if (isset($plugins['mediaelement'])) {
		wp_enqueue_style( "mediaelement", TN3::$url."js/mediaelement/mediaelementplayer.css" );
		wp_enqueue_script( "mediaelement", TN3::$url."js/mediaelement/mediaelement-and-player.min.js", array('jquery') );
	    }
	    wp_enqueue_script( "tn3", TN3::$url."js/jquery.tn3.min.js", array('jquery') );
	    foreach ($plugins as $k => $v) wp_enqueue_script( "tn3_".$k, TN3::$url."js/tn3.".$k.".min.js", array('tn3') );

	    $opts = get_option('tn3_presets_skin');
	    foreach ($skin_presets as $k => $v) {
		if (count($opts[$k]['skin']) == 3) {
		    $gopts = get_option("tn3_admin_general");
		    $css = site_url($gopts['path'])."/skins/";
		    array_pop($opts[$k]['skin']);
		} else $css = TN3::$url."skins/";
		$css .= implode("/", $opts[$k]['skin']).".css";
		wp_enqueue_style( "tn3-skin-".implode("-", $opts[$k]['skin']), $css );
	    }
	    if (isset($plugins['touch'])) wp_enqueue_style( "tn3-skin-tn3t-tn3t-css", TN3::$url."skins/tn3t/tn3t.css" );


	}
    }
    function shortcode( $sc, $content = null )
    {
	$this->count++;
	$div_name = "tn3gallery".$this->count;

	if ( isset($sc['touch']) ) {
	    $gopts = get_option("tn3_admin_general");
	    $sc['touch'] = array("fsMode" => TN3::$url."tn3_touch_fullscreen.html");
	    //unset($sc['touch']);
	}
	$this->array_change_key($sc, 'startwithalbums', 'startWithAlbums');
	if ( !isset($sc['skin']) ) $sc['skin'] = "default";
	$sopts = get_option('tn3_presets_skin');
	$sopts = $sopts[$sc['skin']];

	$trans = get_option('tn3_presets_transition');
	if ( isset($sc['transitions']) ) {
	    $sopts['image']['transitions'] = (array)$trans[$sc['transitions']];
	    unset($sc['transitions']);
	}
	$sopts['image']['defaultTransition'] = (array)$trans['default'];
	$this->parse_plugins($sc);

	$tn3_html = '';
	if ( isset($sc['origin']) ) {
	    $tn3_html = $this->render_albums($sc['origin'], $sc['ids'], $sopts);
	    unset($sc['origin']);
	    unset($sc['ids']);
	}

	unset($sopts['imageSize']);
	unset($sopts['thumbnailSize']);
	$sc = array_merge($sopts, $sc);
	$sc['skin'] = $sopts['skin'];

	$tn3_params = $this->render_parameters($sc);
	

	$o = "<script type=\"text/javascript\">\n";
	if ($this->count == 1) $o .= "\tvar tn3s = [];\n";
	$o .= "\tjQuery(document).ready(function() {\n";
	$o .= "\tjQuery.fn.tn3.altLink = \"".admin_url("admin-ajax.php")."?action=tn3_alt&u=\";\n";
	$o .= "\tvar params = $tn3_params\n";
	$o .= "\tif (typeof tn3ini$this->count != 'undefined') jQuery.extend(true, params, tn3ini$this->count);\n";
	$o .= "\tvar tn3 = jQuery('.$div_name').tn3(params);\n";
	$o .= "\ttn3s.push(tn3);\n";
	$o .= "\t});\n";
	$o .= "</script>\n";
	$o .= "<div class=\"$div_name\">\n";
	$o .= $tn3_html;
	$o .= "</div>";
	return $o;
    }
    function parse_plugins(&$sc)
    {
	global $tn3_plugin_defaults;

	// fix shortcode lowercase
	$this->array_change_key($sc, 'imageclick', 'imageClick');
	$this->array_change_key($sc, 'thumbsize', 'thumbSize');
	$this->array_change_key($sc, 'imagesize', 'imageSize');
	$this->array_change_key($sc, 'userid', 'userID');
	$this->array_change_key($sc, 'id', 'ID');
	$this->array_change_key($sc, 'albumid', 'albumID');

	$pps = get_option('tn3_admin_plugins');
	switch ($sc['origin']) {
	case 'xml':
	    $sc['external'] = array( 'origin' => 'xml', 'url' => $sc['url'] );
	    unset($sc['origin']);
	    unset($sc['url']);
	    break;
	case "flickr":
	    $sc['external'] = array( 'api_key' => $pps['flickr-api_key'], 'user_id' => $pps['flickr-user_id'] );
	    foreach ($tn3_plugin_defaults['flickr'] as $k => $v) {
		if ( isset($sc[$k]) ) {
		    $sc['external'][$k] = $sc[$k];
		    unset($sc[$k]);
		} else $sc['external'][$k] = $v;
	    }
	    if ( isset($sc['external']['page']) || isset($sc['external']['per_page']) ) {
		$sc['external']['photos'] = array (
		    'extras'	=> 'description',
		    'media'	=> 'photos',
		    'page'	=> $sc['external']['page'],
		    'per_page'	=> $sc['external']['per_page']
		);
		unset($sc['external']['page']);
		unset($sc['external']['per_page']);
		if ($sc['external']['user_id'] == '') unset($sc['external']['user_id']);
	    }
	    break;
	case "picasa":
	    $sc['external'] = array( 'userID' => $pps['picasa-userID'] );
	    foreach ($tn3_plugin_defaults['picasa'] as $k => $v) {
		if ( isset($sc[$k]) ) {
		    $sc['external'][$k] = $sc[$k];
		    unset($sc[$k]);
		} else $sc['external'][$k] = $v;
	    }


	    if ( isset($sc['external']['page']) || isset($sc['external']['per_page']) || isset($sc['external']['thumbSize']) || isset($sc['external']['imageSize']) ) {
		$sc['external']['params'] = array (
		    'thumbsize'		=> $sc['external']['thumbSize'],
		    'imgmax'		=> $sc['external']['imageSize'],
		    '"start-index"'	=> $sc['external']['page'],
		    '"max-results"'	=> $sc['external']['per_page']
		);
		unset($sc['external']['thumbSize']);
		unset($sc['external']['imageSize']);
		unset($sc['external']['page']);
		unset($sc['external']['per_page']);
	    }
	    if ($sc['external']['source'] != 'album') unset($sc['external']['albumID']);

	    break;
	case "facebook":
	    $sc['external'] = array( 'ID' => $pps['facebook-ID'] );
	    foreach ($tn3_plugin_defaults['facebook'] as $k => $v) {
		if ( isset($sc[$k]) ) {
		    $sc['external'][$k] = $sc[$k];
		    unset($sc[$k]);
		} else $sc['external'][$k] = $v;
	    }


	    if ( isset($sc['external']['page']) || isset($sc['external']['per_page']) || isset($sc['external']['thumbSize']) || isset($sc['external']['imageSize']) ) {
		$sc['external']['params'] = array (
		    'offset'	=> $sc['external']['page'],
		    'limit'	=> $sc['external']['per_page']
		);
		unset($sc['external']['page']);
		unset($sc['external']['per_page']);
	    }

	    break;
	default:

	    break;
	}
	if (isset($sc['history'])) {
	    $sc['history'] = array(
		'slugField' => $pps['history-slugField'],
		'from' => $pps['history-from'],
		'to' => $pps['history-to'],
		'key' => $pps['history-key']
	    );
	}
	if (isset($sc['mediaelement'])) {
	    $sc['content'] = array( 'plugin'	=> 'mediaelement',
				    'options'	=> array(
					'features'	=> explode(",", $pps['mediaelement-features']),
					'pluginPath'	=> TN3::$url."js/mediaelement/"
				    )
				);
	    unset($sc['mediaelement']);
	}

	//if (isset($sc['external'])) $sc['external'] = array($sc['external']);
    }
    function array_change_key(&$a, $old, $new)
    {
	if ( array_key_exists($old, $a) ) {
	    $a[$new] = $a[$old];
	    unset($a[$old]);
	}
    }
    function render_albums($typ, $ids, $sopts)
    {
	$opts = get_option('tn3_admin_general');	
    
	if ($typ == "gallery") {
	    $r = TN3::$db->get("album", 0, 999, '', '', $ids);
	    $alb_ids = array();
	    foreach ($r as $k => $v) $alb_ids[] = $v->id;
	} else if ($typ == "album") {
	    $alb_ids = explode(",", $ids);
	} else {
	    $imgs = array();
	    $img_ids = explode(",", $ids);
	    foreach ($img_ids as $k => $v) $imgs[] = TN3::$db->getID($v);
	    $alb = array('title' => "Album");
	    return $this->render_album( $alb, $opts['path'], $imgs, $sopts );
	}
	$o = "";
	foreach ($alb_ids as $a_id) {
	    $alb = TN3::$db->getID($a_id);

	    $o .= $this->render_album( $alb, $opts['path'], null, $sopts );
	}
	return $o;
    }
    function render_album($alb, $path, $imgs = null, $sopts)
    {
	$path = site_url($path);
	$o = "\t<div class=\"tn3 album\">\n";
	if ( isset($alb->title) ) $o .= "\t\t<h4>$alb->title</h4>\n";
	if ( isset($alb->description) ) $o .= "\t\t<div class=\"tn3 description\">$alb->description</div>\n";
	if ( isset($alb->thumb) ) $o .= "\t\t<div class=\"tn3 thumb\">".$path."/2$alb->thumb</div>\n";
	$o .= "\t\t<ul>\n";

	if ($imgs == null) $imgs = TN3::$db->get("image", 0, 999, '', '', $alb->id);
	$o .= $this->render_images( $imgs, $path, $sopts );
	
	$o .= "\t\t</ul>\n\t</div>\n";
	return $o;
    }
    function render_images($imgs, $path, $sopts)
    {
	$o = "";
	foreach ( $imgs as $k => $v ) {

	    $o .= "\t\t\t<li>\n";
	    $o .= "\t\t\t\t<h4>$v->title</h4>\n";
	    if ( isset($v->description) ) $o .= "\t\t\t\t<div class=\"tn3 description\">$v->description</div>\n";
	    if ( isset($v->content_type) && $v->content_type != "image" ) {
		$yt = (stristr($v->content, "youtube") == FALSE)? 'poster="'.$path.$sopts['imageSize'].$v->path.'" ' : 'type="video/youtube" ';
		if ($v->content_type == "video") $v->content = '<video '.$yt.'src="'.$v->content.'" ></video>';
		$o .= "\t\t\t\t<div class=\"tn3 content\">$v->content</div>\n";
	    }
	    if ( isset($v->url) ) $o .= "\t\t\t\t<div class=\"tn3 url\">$v->url</div>\n";
	    $o .= "\t\t\t\t<a href=\"$path".$sopts['imageSize']."$v->path\">\n";
	    $o .= "\t\t\t\t\t<img src=\"$path".$sopts['thumbnailSize']."$v->path\" />\n";
	    $o .= "\t\t\t\t</a>\n";
	    $o .= "\t\t\t</li>\n";
	}
	
	return $o;

    }
    function render_parameters($parray, $tab = "\t")
    {
	if ($tab == "\t") {
	    if ( count($parray['skin']) == 3 ) {
		$gopts = get_option("tn3_admin_general");
		$a = array( 'skinDir: "'.site_url($gopts['path']).'/skins"' );
		array_pop($parray['skin']);
	    } else
		$a = array( 'skinDir: "'.TN3::$url.'skins"' );
	} else {
	    $a = array();
	};
	foreach ($parray as $k => $v) {
	    if (is_array($v)) {

		    foreach ($v as $vk => $vv) {
			if (is_array($vv)) {
			    if ($vk == "transitions") $a[$k][$vk] = $vk.": [".$this->render_parameters($vv, $tab."\t\t")."]";
			    else $a[$k][$vk] = $vk.": ".$this->render_parameters($vv, $tab."\t\t");
			} else $a[$k][$vk] = is_int($vk)? $this->get_js_value($vv) :
							$vk.": ".$this->get_js_value($vv);
		    };
		    $a[$k] = $this->is_assoc($v)? (($k == "content" || $k == "external" )? 
							$k.": [{\n\t$tab".implode(",\n\t$tab", $a[$k])."\n$tab}]" :
							$k.": {\n\t$tab".implode(",\n\t$tab", $a[$k])."\n$tab}") :	
						  $k.": [\n\t$tab".implode(",\n\t$tab", $a[$k])."\n$tab]";

	    } else {
		if ( ($k == 'width' || $k == 'height') && $v == 0 ) continue;
		$a[$k] = $k.": ".$this->get_js_value($v);
	    }
	}
	//$a['autoplay'] = "autoplay: ".$this->get_js_value($autoplay);
	//$a['startWithAlbums'] = "startWithAlbums: ".$this->get_js_value($albumstart);
	$o = implode(",\n$tab", $a);
	$o = $this->is_assoc($parray)? "{\n$tab".$o."\n$tab}" : "[".$o."]";
	//if ($tab == "\t") tn3log::a("-----------------------------------\n".$o);
	return $o;
    }
    function get_js_value($v)
    {
	if (is_string($v)) return "\"$v\"";
	else if (is_null($v) || $v === false) return 0;
	else return $v;
    }
    function is_assoc ($arr) {
        return (is_array($arr) && count(array_filter(array_keys($arr),'is_string')) == count($arr));
    }
    



}




?>
