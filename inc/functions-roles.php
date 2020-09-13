<?php
/**
 * Role-related functions that extend the built-in WordPress Roles API.
 *
 * @package    user_roles
 * @subpackage Includes
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

# Register roles.
add_action( 'wp_roles_init',          'user_roles_register_roles',         95 );
add_action( 'user_roles_register_roles', 'user_roles_register_default_roles',  5 );

/**
 * Fires the role registration action hook.
 *
 * @since  2.0.0
 * @access public
 * @param  object  $wp_roles
 * @return void
 */
function user_roles_register_roles( $wp_roles ) {

	do_action( 'user_roles_register_roles', $wp_roles );
}

/**
 * Registers any roles stored globally with WordPress.
 *
 * @since  2.0.0
 * @access public
 * @param  object  $wp_roles
 * @return void
 */
function user_roles_register_default_roles( $wp_roles ) {

	foreach ( $wp_roles->roles as $name => $object ) {

		$args = array(
			'label' => $object['name'],
			'caps'  => $object['capabilities']
		);

		user_roles_register_role( $name, $args );
	}

	// Unset any roles that were registered previously but are not currently available.
	foreach ( user_roles_get_roles() as $role ) {

		if ( ! isset( $wp_roles->roles[ $role->name ] ) )
			user_roles_unregister_role( $role->name );
	}
}

/**
 * Returns the instance of the role registry.
 *
 * @since  2.0.0
 * @access public
 * @return object
 */
function user_roles_role_registry() {

	return \user_roles\Registry::get_instance( 'role' );
}

/**
 * Returns all registered roles.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function user_roles_get_roles() {

	return user_roles_role_registry()->get_collection();
}

/**
 * Registers a role.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $name
 * @param  array   $args
 * @return void
 */
function user_roles_register_role( $name, $args = array() ) {

	user_roles_role_registry()->register( $name, new \user_roles\Role( $name, $args ) );
}

/**
 * Unregisters a role.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $name
 * @return void
 */
function user_roles_unregister_role( $name ) {

	user_roles_role_registry()->unregister( $name );
}

/**
 * Returns a role object.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $name
 * @return object
 */
function user_roles_get_role( $name ) {

	return user_roles_role_registry()->get( $name );
}

/**
 * Checks if a role object exists.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $name
 * @return bool
 */
function user_roles_role_exists( $name ) {

	return user_roles_role_registry()->exists( $name );
}

/* ====== Multiple Role Functions ====== */

/**
 * Returns an array of editable roles.
 *
 * @since  2.0.0
 * @access public
 * @global array  $wp_roles
 * @return array
 */
function user_roles_get_editable_roles() {
	global $wp_roles;

	$editable = function_exists( 'get_editable_roles' ) ? get_editable_roles() : apply_filters( 'editable_roles', $wp_roles->roles );

	return array_keys( $editable );
}

/**
 * Returns an array of uneditable roles.
 *
 * @since  2.0.0
 * @access public
 * @return array
 */
function user_roles_get_uneditable_roles() {

	return array_diff( array_keys( user_roles_get_roles() ), user_roles_get_editable_roles() );
}

/**
 * Returns an array of core WP roles.  Note that we remove any that are not registered.
 *
 * @since  2.0.0
 * @access public
 * @return array
 */
function user_roles_get_wordpress_roles() {

	$roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

	return array_intersect( $roles, array_keys( user_roles_get_roles() ) );
}

/**
 * Returns an array of the roles that have users.
 *
 * @since  2.0.0
 * @access public
 * @return array
 */
function user_roles_get_active_roles() {

	$has_users = array();

	foreach ( user_roles_get_role_user_count() as $role => $count ) {

		if ( 0 < $count )
			$has_users[] = $role;
	}

	return $has_users;
}

/**
 * Returns an array of the roles that have no users.
 *
 * @since  2.0.0
 * @access public
 * @return array
 */
function user_roles_get_inactive_roles() {

	return array_diff( array_keys( user_roles_get_roles() ), user_roles_get_active_roles() );
}

/**
 * Returns a count of all the available roles for the site.
 *
 * @since  1.0.0
 * @access public
 * @return int
 */
function user_roles_get_role_count() {

	return count( $GLOBALS['wp_roles']->role_names );
}

/* ====== Single Role Functions ====== */

/**
 * Sanitizes a role name.  This is a wrapper for the `sanitize_key()` WordPress function.  Only
 * alphanumeric characters and underscores are allowed.  Hyphens are also replaced with underscores.
 *
 * @since  1.0.0
 * @access public
 * @return int
 */
function user_roles_sanitize_role( $role ) {

	$_role = strtolower( $role );
	$_role = preg_replace( '/[^a-z0-9_\-\s]/', '', $_role );

	return apply_filters( 'user_roles_sanitize_role', str_replace( ' ', '_', $_role ), $role );
}

/**
 * WordPress provides no method of translating custom roles other than filtering the
 * `translate_with_gettext_context` hook, which is very inefficient and is not the proper
 * method of translating.  This is a method that allows plugin authors to hook in and add
 * their own translations.
 *
 * Note the core WP `translate_user_role()` function only translates core user roles.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return string
 */
function user_roles_translate_role( $role ) {
	global $wp_roles;

	return user_roles_translate_role_hook( $wp_roles->role_names[ $role ], $role );
}

/**
 * Hook for translating user roles. I needed to separate this from the primary
 * `user_roles_translate_role()` function in case `$wp_roles` was not yet available
 * but both the role and role label were.
 *
 * @since  2.0.1
 * @access public
 * @param  string  $label
 * @param  string  $role
 * @return string
 */
function user_roles_translate_role_hook( $label, $role ) {

	return apply_filters( 'user_roles_translate_role', translate_user_role( $label ), $role );
}

/**
 * Conditional tag to check if a role has any users.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function user_roles_role_has_users( $role ) {

	return in_array( $role, user_roles_get_active_roles() );
}

/**
 * Conditional tag to check if a role has any capabilities.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function user_roles_role_has_caps( $role ) {

	return user_roles_get_role( $role )->has_caps;
}

/**
 * Counts the number of users for all roles on the site and returns this as an array.  If
 * the `$role` parameter is given, the return value will be the count just for that particular role.
 *
 * @since  0.2.0
 * @access public
 * @param  string     $role
 * @return int|array
 */
function user_roles_get_role_user_count( $role = '' ) {

	// If the count is not already set for all roles, let's get it.
	if ( empty( user_roles_plugin()->role_user_count ) ) {

		// Count users.
		$user_count = count_users();

		// Loop through the user count by role to get a count of the users with each role.
		foreach ( $user_count['avail_roles'] as $_role => $count )
			user_roles_plugin()->role_user_count[ $_role ] = $count;
	}

	// Return the role count.
	if ( $role )
		return isset( user_roles_plugin()->role_user_count[ $role ] ) ? user_roles_plugin()->role_user_count[ $role ] : 0;

	// If the `$role` parameter wasn't passed into this function, return the array of user counts.
	return user_roles_plugin()->role_user_count;
}

/**
 * Returns the number of granted capabilities that a role has.
 *
 * @since  1.0.0
 * @access public
 * @param  string
 * @return int
 */
function user_roles_get_role_granted_cap_count( $role ) {

	return user_roles_get_role( $role )->granted_cap_count;
}

/**
 * Returns the number of denied capabilities that a role has.
 *
 * @since  1.0.0
 * @access public
 * @param  string
 * @return int
 */
function user_roles_get_role_denied_cap_count( $role ) {

	return user_roles_get_role( $role )->denied_cap_count;
}

/**
 * Conditional tag to check whether a role can be edited.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return bool
 */
function user_roles_is_role_editable( $role ) {

	return in_array( $role, user_roles_get_editable_roles() );
}

/**
 * Conditional tag to check whether a role is a core WordPress role.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return bool
 */
function user_roles_is_wordpress_role( $role ) {

	return in_array( $role, array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ) );
}

/* ====== URLs ====== */

/**
 * Returns the URL for the add-new role admin screen.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function user_roles_get_new_role_url() {

	return add_query_arg( 'page', 'role-new', admin_url( 'users.php' ) );
}

/**
 * Returns the URL for the clone role admin screen.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return string
 */
function user_roles_get_clone_role_url( $role ) {

	return add_query_arg( 'clone', $role, user_roles_get_new_role_url() );
}

/**
 * Returns the URL for the edit roles admin screen.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function user_roles_get_edit_roles_url() {

	return add_query_arg( 'page', 'roles', admin_url( 'users.php' ) );
}

/**
 * Returns the URL for the edit "mine" roles admin screen.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $view
 * @return string
 */
function user_roles_get_role_view_url( $view ) {

	return add_query_arg( 'view', $view, user_roles_get_edit_roles_url() );
}

/**
 * Returns the URL for the edit role admin screen.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return string
 */
function user_roles_get_edit_role_url( $role ) {

	return add_query_arg( array( 'action' => 'edit', 'role' => $role ), user_roles_get_edit_roles_url() );
}

/**
 * Returns the URL to permanently delete a role (edit roles screen).
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return string
 */
function user_roles_get_delete_role_url( $role ) {

	$url = add_query_arg( array( 'action' => 'delete', 'role' => $role ), user_roles_get_edit_roles_url() );

	return wp_nonce_url( $url, 'delete_role', 'user_roles_delete_role_nonce' );
}

/**
 * Returns the URL for the users admin screen specific to a role.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $role
 * @return string
 */
function user_roles_get_role_users_url( $role ) {

	return admin_url( add_query_arg( 'role', $role, 'users.php' ) );
}
