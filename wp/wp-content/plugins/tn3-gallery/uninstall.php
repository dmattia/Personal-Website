<?php
    
    global $wp_filesystem, $wpdb;
    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) || !is_object($wp_filesystem) )
	exit ();

    $gopts = get_option( 'tn3_admin_general' );
    // delete option
    delete_option( 'tn3_installed_skins' );
    delete_option( 'tn3_admin_general' );
    delete_option( 'tn3_admin_plugins' );
    delete_option( 'tn3_admin_skin' );
    delete_option( 'tn3_admin_transition' );

    if ($gopts['remove_images']) {
	delete_option( 'tn3_info' );
	delete_option( 'tn3_presets_skin' );
	delete_option( 'tn3_presets_transition' );
	// image path
	$ipath = ABSPATH.$gopts['path'];
	//Protection against deleting files in any important base directories.
	if ( !in_array( $ipath, array(ABSPATH, WP_CONTENT_DIR, WP_PLUGIN_DIR, WP_CONTENT_DIR . '/plugins') ) && $wp_filesystem->exists($ipath)  ) {

	    $deleted = $wp_filesystem->delete($ipath, true);
	    if ( ! $deleted )
		return new WP_Error('could_not_remove_images', __('Could not remove images.') );
	}
    
	$tbls = array( 'documents', 'fields', 'relations' );
	$q = "DROP TABLE ".$wpdb->prefix."tn3_".implode(", ".$wpdb->prefix."tn3_", $tbls).";";
	$wpdb->query($q);
    }

?>
