<?php
/**
 * Callback functions for outputting help tabs in the admin.
 *
 * @package    user_roles
 * @subpackage Admin
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

/**
 * Help sidebar for all of the help tabs.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function user_roles_get_help_sidebar_text() {

	// Get docs and help links.
	$docs_link = sprintf( '<li><a href="https://github.com/justintadlock/user_roles/blob/master/readme.md">%s</a></li>', esc_html__( 'Documentation',  'user_roles' ) );
	$help_link = sprintf( '<li><a href="https://Cleancoded.com/board/topics">%s</a></li>',                            esc_html__( 'Support Forums', 'user_roles' ) );

	// Return the text.
	return sprintf(
		'<p><strong>%s</strong></p><ul>%s%s</ul>',
		esc_html__( 'For more information:', 'user_roles' ),
		$docs_link,
		$help_link
	);
}

/**
 * Edit role overview help tab args.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function user_roles_get_edit_role_help_overview_args() {

	return array(
		'id'       => 'overview',
		'title'    => esc_html__( 'Overview', 'user_roles' ),
		'callback' => 'user_roles_edit_role_help_overview_cb'
	);
}

/**
 * Edit role name help tab args.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function user_roles_get_edit_role_help_role_name_args() {

	return array(
		'id'       => 'role-name',
		'title'    => esc_html__( 'Role Name', 'user_roles' ),
		'callback' => 'user_roles_edit_role_help_role_name_cb'
	);
}

/**
 * Edit role edit caps help tab args.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function user_roles_get_edit_role_help_edit_caps_args() {

	return array(
		'id'       => 'edit-capabilities',
		'title'    => esc_html__( 'Edit Capabilities', 'user_roles' ),
		'callback' => 'user_roles_edit_role_help_edit_caps_cb'
	);
}

/**
 * Edit role custom cap help tab args.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function user_roles_get_edit_role_help_custom_cap_args() {

	return array(
		'id'       => 'custom-capability',
		'title'    => esc_html__( 'Custom Capability', 'user_roles' ),
		'callback' => 'user_roles_edit_role_help_custom_cap_cb'
	);
}

/**
 * Edit role overview help tab callback function.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function user_roles_edit_role_help_overview_cb() { ?>

	<p>
		<?php esc_html_e( 'This screen allows you to edit an individual role and its capabilities.', 'user_roles' ); ?>
	<p>

	<p>
		<?php printf(
			esc_html__( 'Visit the %s page in the WordPress Codex to see a complete list of roles, capabilities, and their definitions.', 'user_roles' ),
			'<a href="https://codex.wordpress.org/Roles_and_Capabilities">' . esc_html__( 'Roles and Capabilities', 'user_roles' ) . '</a>'
		); ?>
	</p>
<?php }

/**
 * Edit role name help tab callback function.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function user_roles_edit_role_help_role_name_cb() { ?>

	<p>
		<?php esc_html_e( 'The role name field allows you to enter a human-readable name for your role.', 'user_roles' ); ?>
	</p>

	<p>
		<?php esc_html_e( 'The machine-readable version of the role appears below the name field, which you can edit. This can only have lowercase letters, numbers, or underscores.', 'user_roles' ); ?>
	</p>
<?php }

/**
 * Edit role edit caps help tab callback function.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function user_roles_edit_role_help_edit_caps_cb() { ?>

	<p>
		<?php esc_html_e( 'The capabilities edit box is made up of tabs that separate capabilities into groups. You may take the following actions for each capability:', 'user_roles' ); ?>
	</p>

	<ul>
		<li><?php _e( '<strong>Grant</strong> allows you to grant the role a capability.', 'user_roles' ); ?></li>
		<li><?php _e( '<strong>Deny</strong> allows you to explicitly deny the role a capability.', 'user_roles' ); ?></li>
		<li><?php esc_html_e( 'You may also opt to neither grant nor deny the role a capability.', 'user_roles' ); ?></li>
	</ul>
<?php }

/**
 * Edit role custom cap help tab callback function.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function user_roles_edit_role_help_custom_cap_cb() { ?>

	<p>
		<?php esc_html_e( 'The custom capability box allows you to create a custom capability for the role. After hitting the Add New button, it will add the capability to the Custom tab in the Edit Capabilities box.', 'user_roles' ); ?>
	</p>
<?php }
