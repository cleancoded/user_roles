<?php
/**
 * Handles settings functionality.
 *
 * @package    user_roles
 * @subpackage Admin
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

# Register settings views.
add_action( 'user_roles_register_settings_views', 'user_roles_register_default_settings_views', 5 );

/**
 * Registers the plugin's built-in settings views.
 *
 * @since  2.0.0
 * @access public
 * @param  object  $manager
 * @return void
 */
function user_roles_register_default_settings_views( $manager ) {

	// Bail if not on the settings screen.
	if ( 'user_roles-settings' !== $manager->name )
		return;

	// Register general settings view (default view).
	$manager->register_view(
		new \user_roles\Admin\View_General(
			'general',
			array(
				'label'    => esc_html__( 'General', 'user_roles' ),
				'priority' => 0
			)
		)
	);

	// Register add-ons view.
	$manager->register_view(
		new \user_roles\Admin\View_Addons(
			'add-ons',
			array(
				'label'    => esc_html__( 'Add-Ons', 'user_roles' ),
				'priority' => 95
			)
		)
	);

	// Register add-ons view.
	$manager->register_view(
		new \user_roles\Admin\View_Donate(
			'donate',
			array(
				'label'    => esc_html__( 'Help Fund Version 3.0', 'user_roles' ),
				'priority' => 100
			)
		)
	);
}

/**
 * Conditional function to check if on the plugin's settings page.
 *
 * @since  2.0.0
 * @access public
 * @return bool
 */
function user_roles_is_settings_page() {

	$screen = get_current_screen();

	return is_object( $screen ) && 'settings_page_user_roles-settings' === $screen->id;
}

/**
 * Returns the URL to the settings page.
 *
 * @since  2.0.0
 * @access public
 * @return string
 */
function user_roles_get_settings_page_url() {

	return add_query_arg( array( 'page' => 'user_roles-settings' ), admin_url( 'options-general.php' ) );
}

/**
 * Returns the URL to a settings view page.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $view
 * @return string
 */
function user_roles_get_settings_view_url( $view ) {

	return add_query_arg( array( 'view' => sanitize_key( $view ) ), user_roles_get_settings_page_url() );
}

/**
 * Returns the current settings view name.
 *
 * @since  2.0.0
 * @access public
 * @return string
 */
function user_roles_get_current_settings_view() {

	if ( ! user_roles_is_settings_page() )
		return '';

	return isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'general';
}

/**
 * Conditional function to check if on a specific settings view page.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $view
 * @return bool
 */
function user_roles_is_settings_view( $view = '' ) {

	return user_roles_is_settings_page() && $view === user_roles_get_current_settings_view();
}
