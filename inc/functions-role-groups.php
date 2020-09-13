<?php
/**
 * Role groups API. Offers a standardized method for creating role groups.
 *
 * @package    user_roles
 * @subpackage Admin
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

# Registers default groups.
add_action( 'init',                         'user_roles_register_role_groups',         95 );
add_action( 'user_roles_register_role_groups', 'user_roles_register_default_role_groups',  5 );

/**
 * Fires the role group registration action hook.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function user_roles_register_role_groups() {

	do_action( 'user_roles_register_role_groups' );
}


/**
 * Registers the default role groups.
 *
 * @since  2.0.0
 * @access public
 * @return void
 */
function user_roles_register_default_role_groups() {

	// Register the WordPress group.
	user_roles_register_role_group( 'wordpress',
		array(
			'label'       => esc_html__( 'WordPress', 'user_roles' ),
			'label_count' => _n_noop( 'WordPress %s', 'WordPress %s', 'user_roles' ),
			'roles'       => user_roles_get_wordpress_roles(),
		)
	);
}

/**
 * Returns the instance of the role group registry.
 *
 * @since  2.0.0
 * @access public
 * @return object
 */
function user_roles_role_group_registry() {

	return \user_roles\Registry::get_instance( 'role_group' );
}

/**
 * Function for registering a role group.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @param  array   $args
 * @return void
 */
function user_roles_register_role_group( $name, $args = array() ) {

	user_roles_role_group_registry()->register( $name, new \user_roles\Role_Group( $name, $args ) );
}

/**
 * Unregisters a group.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @return void
 */
function user_roles_unregister_role_group( $name ) {

	user_roles_role_group_registry()->unregister( $name );
}

/**
 * Checks if a group exists.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $name
 * @return bool
 */
function user_roles_role_group_exists( $name ) {

	return user_roles_role_group_registry()->exists( $name );
}

/**
 * Returns an array of registered group objects.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function user_roles_get_role_groups() {

	return user_roles_role_group_registry()->get_collection();
}

/**
 * Returns a group object if it exists.  Otherwise, `FALSE`.
 *
 * @since  1.0.0
 * @access public
 * @param  string      $name
 * @return object|bool
 */
function user_roles_get_role_group( $name ) {

	return user_roles_role_group_registry()->get( $name );
}
