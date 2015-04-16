<?php

    $tn3_presets_skin = array();

    $tn3_presets_skin['default'] = array(
	'skin'		    => array('tn3', 'tn3'),
	'width'		    => 0,
	'height'	    => 0,
	'imageClick'	    => 'next',
	'delay'		    => 7000,
	'startWithAlbums'   => false,
	'useNativeFullScreen'=> true,
	'image'		    => array(
	    'crop'	    => false,
	    'idleDelay'	    => 3000,
	    'maxZoom'	    => 2,
	    'clickEvent'    => 'click'),
	'thumbnailer'	    => array(
	    'align'	    => 1,
	    'buffer'	    => 20,
	    'overMove'	    => true,
	    'mode'	    => 'thumbs',
	    'speed'	    => 8,
	    'slowdown'	    => 50,
	    'shaderColor'   => '#000000',
	    'shaderOpacity' => .5,
	    'shaderDuration'=> 200,
	    'useTitle'	    => false),
	'imageSize'	    => "/0",
	'thumbnailSize'	    => "/2"
	);
    
    $tn3_presets_skin['eris-horizontal'] = array(
	'skin'		    => array('tn3e', 'tn3e'),
	'width'		    => 0,
	'height'	    => 0,
	'imageClick'	    => 'next',
	'delay'		    => 7000,
	'startWithAlbums'   => false,
	'useNativeFullScreen'=> true,
	'image'		    => array(
	    'crop'	    => false,
	    'idleDelay'	    => 3000,
	    'maxZoom'	    => 2,
	    'clickEvent'    => 'click'),
	'thumbnailer'	    => array(
	    'align'	    => 1,
	    'buffer'	    => 20,
	    'overMove'	    => true,
	    'mode'	    => 'thumbs',
	    'speed'	    => 8,
	    'slowdown'	    => 50,
	    'shaderColor'   => '#000000',
	    'shaderOpacity' => .5,
	    'shaderDuration'=> 200,
	    'useTitle'	    => false),
	'imageSize'	    => "/0",
	'thumbnailSize'	    => "/1"
	);

    $tn3_presets_skin['eris-vertical'] = array(
	'skin'		    => array('tn3e', 'vertical'),
	'width'		    => 0,
	'height'	    => 0,
	'imageClick'	    => 'next',
	'delay'		    => 7000,
	'startWithAlbums'   => false,
	'useNativeFullScreen'=> true,
	'image'		    => array(
	    'crop'	    => false,
	    'idleDelay'	    => 3000,
	    'maxZoom'	    => 2,
	    'clickEvent'    => 'click'),
	'thumbnailer'	    => array(
	    'align'	    => 1,
	    'buffer'	    => 20,
	    'overMove'	    => true,
	    'mode'	    => 'thumbs',
	    'speed'	    => 8,
	    'slowdown'	    => 50,
	    'shaderColor'   => '#000000',
	    'shaderOpacity' => .5,
	    'shaderDuration'=> 200,
	    'useTitle'	    => false),
	'imageSize'	    => "/0",
	'thumbnailSize'	    => "/1"
	);

    $tn3_presets_skin['eris-bullets'] = array(
	'skin'		    => array('tn3e', 'bullets'),
	'width'		    => 0,
	'height'	    => 0,
	'imageClick'	    => 'next',
	'delay'		    => 7000,
	'startWithAlbums'   => false,
	'useNativeFullScreen'=> true,
	'image'		    => array(
	    'crop'	    => false,
	    'idleDelay'	    => 3000,
	    'maxZoom'	    => 2,
	    'clickEvent'    => 'click'),
	'thumbnailer'	    => array(
	    'align'	    => 1,
	    'buffer'	    => 20,
	    'overMove'	    => true,
	    'mode'	    => 'bullets',
	    'speed'	    => 8,
	    'slowdown'	    => 50,
	    'shaderColor'   => '#000000',
	    'shaderOpacity' => .5,
	    'shaderDuration'=> 200,
	    'useTitle'	    => false),
	'imageSize'	    => "/0",
	'thumbnailSize'	    => "/1"
	);

    $tn3_presets_skin['aura-horizontal'] = array(
	'skin'		    => array('tn3a', 'tn3a'),
	'width'		    => 0,
	'height'	    => 0,
	'imageClick'	    => 'next',
	'delay'		    => 7000,
	'startWithAlbums'   => false,
	'useNativeFullScreen'=> true,
	'image'		    => array(
	    'crop'	    => true,
	    'idleDelay'	    => 3000,
	    'maxZoom'	    => 2,
	    'clickEvent'    => 'click'),
	'thumbnailer'	    => array(
	    'align'	    => 1,
	    'buffer'	    => 20,
	    'overMove'	    => true,
	    'mode'	    => 'thumbs',
	    'speed'	    => 8,
	    'slowdown'	    => 50,
	    'shaderColor'   => '#000000',
	    'shaderOpacity' => .5,
	    'shaderDuration'=> 200,
	    'useTitle'	    => false),
	'imageSize'	    => "/0",
	'thumbnailSize'	    => "/1"
	);

    global $tn3_plugin_defaults;
    $tn3_plugin_defaults = array( 
    'general' => array(
	'width'		    => 0,
	'height'	    => 0,
	'skin'		    => 'default',
	'imageClick'	    => 'next'),
    'xml' => array(
	'origin'	    => 'xml',
	'url'		    => ''),
    'flickr' => array(
	'origin'	    => 'flickr',
	'user_id'	    => '',
	'source'	    => 'search',
	'photoset_id'	    => '',
	'gallery_id'	    => '',
	'page'		    => 1,
	'per_page'	    => 20,
	'imageSize'	    => 'z',
	'thumbSize'	    => 's'),
    'picasa' => array(
    	'origin'	    => 'picasa',
	'userID'	    => 'default',
	'source'	    => 'albums',
	'albumID'	    => '',
	'page'		    => 1,
	'per_page'	    => 20,
	'thumbSize'	    => '72c',
	'imageSize'	    => '1024'),
    'facebook' => array(
    	'origin'	    => 'facebook',
	'ID'		    => '',
	'source'	    => 'albums',
	'page'		    => 1,
	'per_page'	    => 20,
	'thumbSize'	    => 'thumbnail',
	'imageSize'	    => 'normal'),
    'history' => array(
	'slugField'	    => 'title',
	'from'		    => '·/_,:;',
	'to'		    => '------',
	'key'		    => 'tn3',
	'delimiter'	    => '/')
    );
    
    

    $tn3_presets_transition = array();

    $tn3_presets_transition['default'] = array(
	'type'		    => "fade",
	'easing'	    => "easeInQuad",
	'duration'	    => 300
    );
    $tn3_presets_transition['normal slide'] = array(
	'type'		    => "slide",
	'easing'	    => "easeInOutQuad",
	'direction'	    => "auto",
	'duration'	    => 330
    );
    $tn3_presets_transition['normal blinds'] = array(
	'type'		    => "blinds",
	'cross'		    => true,
	'partDirection'	    => "auto",
	'method'	    => "fade",
	'partEasing'	    => "easeInQuad",
	'partDuration'	    => 100,
	'parts'		    => 12,
	'easing'	    => "easeInQuad",
	'direction'	    => "vertical",
	'duration'	    => 240
    );
    $tn3_presets_transition['normal grid'] = array(
	'type'		    => "grid",
	'partDirection'	    => "left",
	'method'	    => "fade",
	'partEasing'	    => "easeOutSine",
	'partDuration'	    => 300,
	'diagonalStart'	    => "bl",
	'sortReverse'	    => false,
	'sort'		    => "diagonal",
	'gridY'		    => 5,
	'gridX'		    => 7,
	'easing'	    => "easeInQuad",
	'duration'	    => 260
    );


?>
