<?php
/*************************************************************************
Plugin Name:  Unattach
Plugin URI:   http://outlandishideas.co.uk/blog/2011/03/unattach/
Description:  Allows detaching images and other media from posts, pages and other content types.
Version:      1.0.2
Author:       tamlyn, Ov3rfly
**************************************************************************/

//filter to add button to media library UI
function unattach_media_row_action( $actions, $post ) {
	if ($post->post_parent) {
		// 1.0.2: add nonce to fix CSRF, Ov3rfly
		$url = wp_nonce_url( admin_url('tools.php?page=unattach&noheader=true&id=' . $post->ID), 'unattach' );
		$actions['unattach'] = '<a href="' . esc_url( $url ) . '" title="' . __( 'Unattach this media item.', 'unattach' ) . '">' . __( 'Unattach', 'unattach' ) . '</a>';
	}

	return $actions;
}

//action to set post_parent to 0 on attachment
function unattach_do_it() {
	// 1.0.2: check nonce to fix CSRF, Ov3rfly
	check_admin_referer( 'unattach' );

	global $wpdb;

	if (!empty($_REQUEST['id'])) {
		// 1.0.2: cast id to int, Ov3rfly
		$wpdb->update($wpdb->posts, array('post_parent'=>0), array('id'=>(int)$_REQUEST['id'], 'post_type'=>'attachment'));
	}
	
	wp_redirect(admin_url('upload.php'));
	exit;
}

//set it up
add_action( 'admin_menu', 'unattach_init' );
function unattach_init() {
	// 1.0.2: add filter for capability, Ov3rfly
	$capability = apply_filters( 'unattach_capability', 'upload_files' );
	if ( current_user_can( $capability ) ) {
		add_filter('media_row_actions',  'unattach_media_row_action', 10, 2);
		//this is hacky but couldn't find the right hook
		add_submenu_page('tools.php', 'Unattach Media', 'Unattach', $capability, 'unattach', 'unattach_do_it');
		remove_submenu_page('tools.php', 'unattach');
	}
}

/*
// example to restrict access via capability in functions.php, Ov3rfly
// see also http://codex.wordpress.org/Roles_and_Capabilities
function my_unattach_capability( $capability ) {
	return 'administrator';
}
add_filter( 'unattach_capability', 'my_unattach_capability' );
*/

// 1.0.2: add translation possibility, Ov3rfly
function unattach_load_textdomain() {
	load_plugin_textdomain( 'unattach', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
}
add_action( 'plugins_loaded', 'unattach_load_textdomain' );