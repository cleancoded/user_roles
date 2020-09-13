<?php
/**
 * Functions for handling add-on plugin registration and integration for the Add-Ons
 * view on the settings screen.
 *
 * @package    user_roles
 * @subpackage Includes
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

# Register addons.
add_action( 'user_roles_register_addons', 'user_roles_register_default_addons', 5 );

/**
 * Registers any addons stored globally with WordPress.
 *
 * @since  2.0.0
 * @access public
 * @param  object  $wp_addons
 * @return void
 */
function user_roles_register_default_addons() {

	$data = include user_roles_plugin()->dir . 'admin/config/addons.php';

	// If we have an array of data, let's roll.
	if ( ! empty( $data ) && is_array( $data ) ) {

		foreach ( $data as $addon => $options ) {
			user_roles_register_addon( $addon, $options );
		}
	}
}

/**
 * Returns the instance of the addon registry.
 *
 * @since  2.0.0
 * @access public
 * @return object
 */
function user_roles_addon_registry() {

	return \user_roles\Registry::get_instance( 'addon' );
}

/**
 * Returns all registered addons.
 *
 * @since  2.0.0
 * @access public
 * @return array
 */
function user_roles_get_addons() {

	return user_roles_addon_registry()->get_collection();
}

/**
 * Registers a addon.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $name
 * @param  array   $args
 * @return void
 */
function user_roles_register_addon( $name, $args = array() ) {

	user_roles_addon_registry()->register( $name, new \user_roles\Addon( $name, $args ) );
}

/**
 * Unregisters a addon.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $name
 * @return void
 */
function user_roles_unregister_addon( $name ) {

	user_roles_addon_registry()->unregister( $name );
}

/**
 * Returns a addon object.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $name
 * @return object
 */
function user_roles_get_addon( $name ) {

	return user_roles_addon_registry()->get( $name );
}

/**
 * Checks if a addon object exists.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $name
 * @return bool
 */
function user_roles_addon_exists( $name ) {

	return user_roles_addon_registry()->exists( $name );
}
