<?php
/**
 * General admin functionality.
 *
 * @package    user_roles
 * @subpackage Admin
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

# Register scripts/styles.
add_action( 'admin_enqueue_scripts', 'user_roles_admin_register_scripts', 0 );
add_action( 'admin_enqueue_scripts', 'user_roles_admin_register_styles',  0 );

/**
 * Get an Underscore JS template.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @return bool
 */
function user_roles_get_underscore_template( $name ) {
	require_once( user_roles_plugin()->dir . "admin/tmpl/{$name}.php" );
}

/**
 * Registers custom plugin scripts.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function user_roles_admin_register_scripts() {

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_register_script( 'user_roles-settings',  user_roles_plugin()->uri . "js/settings{$min}.js",  array( 'jquery'  ), '', true );
	wp_register_script( 'user_roles-edit-post', user_roles_plugin()->uri . "js/edit-post{$min}.js", array( 'jquery'  ), '', true );
	wp_register_script( 'user_roles-edit-role', user_roles_plugin()->uri . "js/edit-role{$min}.js", array( 'postbox', 'wp-util' ), '', true );

	// Localize our script with some text we want to pass in.
	$i18n = array(
		'button_role_edit' => esc_html__( 'Edit',                'user_roles' ),
		'button_role_ok'   => esc_html__( 'OK',                  'user_roles' ),
		'label_grant_cap'  => esc_html__( 'Grant %s capability', 'user_roles' ),
		'label_deny_cap'   => esc_html__( 'Deny %s capability',  'user_roles' ),
		'ays_delete_role'  => esc_html__( 'Are you sure you want to delete this role? This is a permanent action and cannot be undone.', 'user_roles' ),
		'hidden_caps'      => user_roles_get_hidden_caps()
	);

	wp_localize_script( 'user_roles-edit-role', 'user_roles_i18n', $i18n );
}

/**
 * Registers custom plugin scripts.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function user_roles_admin_register_styles() {

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_register_style( 'user_roles-admin', user_roles_plugin()->uri . "css/admin{$min}.css" );
}

/**
 * Function for safely deleting a role and transferring the deleted role's users to the default
 * role.  Note that this function can be extremely intensive.  Whenever a role is deleted, it's
 * best for the site admin to assign the user's of the role to a different role beforehand.
 *
 * @since  0.2.0
 * @access public
 * @param  string  $role
 * @return void
 */
function user_roles_delete_role( $role ) {

	// Get the default role.
	$default_role = get_option( 'default_role' );

	// Don't delete the default role. Site admins should change the default before attempting to delete the role.
	if ( $role == $default_role )
		return;

	// Get all users with the role to be deleted.
	$users = get_users( array( 'role' => $role ) );

	// Check if there are any users with the role we're deleting.
	if ( is_array( $users ) ) {

		// If users are found, loop through them.
		foreach ( $users as $user ) {

			// If the user has the role and no other roles, set their role to the default.
			if ( $user->has_cap( $role ) && 1 >= count( $user->roles ) )
				$user->set_role( $default_role );

			// Else, remove the role.
			else if ( $user->has_cap( $role ) )
				$user->remove_role( $role );
		}
	}

	// Remove the role.
	remove_role( $role );

	// Remove the role from the role factory.
	user_roles_unregister_role( $role );
}

/**
 * Returns an array of all the user meta keys in the $wpdb->usermeta table.
 *
 * @since  0.2.0
 * @access public
 * @global object  $wpdb
 * @return array
 */
function user_roles_get_user_meta_keys() {
	global $wpdb;

	return $wpdb->get_col( "SELECT meta_key FROM $wpdb->usermeta GROUP BY meta_key ORDER BY meta_key" );
}
