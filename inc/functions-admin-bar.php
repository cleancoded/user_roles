<?php
/**
 * Functions for modifying the WordPress admin bar.
 *
 * @package    user_roles
 * @subpackage Includes
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

# Hook the user_roles admin bar to 'wp_before_admin_bar_render'.
add_action( 'wp_before_admin_bar_render', 'user_roles_admin_bar' );

/**
 * Adds new menu items to the WordPress admin bar.
 *
 * @since  0.2.0
 * @access public
 * @global object  $wp_admin_bar
 * @return void
 */
function user_roles_admin_bar() {
	global $wp_admin_bar;

	// Check if the current user can 'create_roles'.
	if ( current_user_can( 'create_roles' ) ) {

		// Add a 'Role' menu item as a sub-menu item of the new content menu.
		$wp_admin_bar->add_menu(
			array(
				'id'     => 'user_roles-new-role',
				'parent' => 'new-content',
				'title'  => esc_attr__( 'Role', 'user_roles' ),
				'href'   => esc_url( user_roles_get_new_role_url() )
			)
		);
	}
}
