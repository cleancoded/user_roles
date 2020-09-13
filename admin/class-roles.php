<?php
/**
 * Roles admin screen.
 *
 * @package    user_roles
 * @subpackage Admin
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

namespace user_roles\Admin;

/**
 * Class that displays the roles admin screen and handles requests for that page.
 *
 * @since  2.0.0
 * @access public
 */
final class Roles {

	/**
	 * Sets up some necessary actions/filters.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Set up some page options for the current screen.
		add_action( 'current_screen', array( $this, 'current_screen' ) );

		// Set up the role list table columns.
		add_filter( 'manage_users_page_roles_columns', array( $this, 'manage_roles_columns' ), 5 );

		// Add help tabs.
		add_action( 'user_roles_load_manage_roles', array( $this, 'add_help_tabs' ) );
	}

	/**
	 * Modifies the current screen object.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function current_screen( $screen ) {

		if ( 'users_page_roles' === $screen->id )
			$screen->add_option( 'per_page', array( 'default' => 20 ) );
	}

	/**
	 * Sets up the roles column headers.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  array  $columns
	 * @return array
	 */
	public function manage_roles_columns( $columns ) {

		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'title'         => esc_html__( 'Role Name', 'user_roles' ),
			'role'          => esc_html__( 'Role',      'user_roles' ),
			'users'         => esc_html__( 'Users',     'user_roles' ),
			'granted_caps'  => esc_html__( 'Granted',   'user_roles' ),
			'denied_caps'   => esc_html__( 'Denied',    'user_roles' )
		);

		return apply_filters( 'user_roles_manage_roles_columns', $columns );
	}

	/**
	 * Runs on the `load-{$page}` hook.  This is the handler for form submissions and requests.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function load() {

		// Get the current action if sent as request.
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : false;

		// Get the current action if posted.
		if ( ( isset( $_POST['action'] ) && 'delete' == $_POST['action'] ) || ( isset( $_POST['action2'] ) && 'delete' == $_POST['action2'] ) )
			$action = 'bulk-delete';

		// Bulk delete role handler.
		if ( 'bulk-delete' === $action ) {

			// If roles were selected, let's delete some roles.
			if ( current_user_can( 'delete_roles' ) && isset( $_POST['roles'] ) && is_array( $_POST['roles'] ) ) {

				// Verify the nonce. Nonce created via `WP_List_Table::display_tablenav()`.
				check_admin_referer( 'bulk-roles' );

				// Loop through each of the selected roles.
				foreach ( $_POST['roles'] as $role ) {

					$role = user_roles_sanitize_role( $role );

					if ( user_roles_role_exists( $role ) )
						user_roles_delete_role( $role );
				}

				// Add roles deleted message.
				add_settings_error( 'user_roles_roles', 'roles_deleted', esc_html__( 'Selected roles deleted.', 'user_roles' ), 'updated' );
			}

		// Delete single role handler.
		} else if ( 'delete' === $action ) {

			// Make sure the current user can delete roles.
			if ( current_user_can( 'delete_roles' ) ) {

				// Verify the referer.
				check_admin_referer( 'delete_role', 'user_roles_delete_role_nonce' );

				// Get the role we want to delete.
				$role = user_roles_sanitize_role( $_GET['role'] );

				// Check that we have a role before attempting to delete it.
				if ( user_roles_role_exists( $role ) ) {

					// Add role deleted message.
					add_settings_error( 'user_roles_roles', 'role_deleted', sprintf( esc_html__( '%s role deleted.', 'user_roles' ), user_roles_get_role( $role )->get( 'label' ) ), 'updated' );

					// Delete the role.
					user_roles_delete_role( $role );
				}
			}
		}

		// Load page hook.
		do_action( 'user_roles_load_manage_roles' );
	}

	/**
	 * Enqueue scripts/styles.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue() {

		wp_enqueue_style(  'user_roles-admin'     );
		wp_enqueue_script( 'user_roles-edit-role' );
	}

	/**
	 * Displays the page content.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function page() {

		require_once( user_roles_plugin()->dir . 'admin/class-role-list-table.php' ); ?>

		<div class="wrap">

			<h1>
				<?php esc_html_e( 'Roles', 'user_roles' ); ?>

				<?php if ( current_user_can( 'create_roles' ) ) : ?>
					<a href="<?php echo esc_url( user_roles_get_new_role_url() ); ?>" class="page-title-action"><?php echo esc_html_x( 'Add New', 'role', 'user_roles' ); ?></a>
				<?php endif; ?>
			</h1>

			<?php settings_errors( 'user_roles_roles' ); ?>

			<div id="poststuff">

				<form id="roles" action="<?php echo esc_url( user_roles_get_edit_roles_url() ); ?>" method="post">

					<?php $table = new Role_List_Table(); ?>
					<?php $table->prepare_items(); ?>
					<?php $table->display(); ?>

				</form><!-- #roles -->

			</div><!-- #poststuff -->

		</div><!-- .wrap -->
	<?php }

	/**
	 * Adds help tabs.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function add_help_tabs() {

		// Get the current screen.
		$screen = get_current_screen();

		// Add overview help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'overview',
				'title'    => esc_html__( 'Overview', 'user_roles' ),
				'callback' => array( $this, 'help_tab_overview' )
			)
		);

		// Add screen content help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'screen-content',
				'title'    => esc_html__( 'Screen Content', 'user_roles' ),
				'callback' => array( $this, 'help_tab_screen_content' )
			)
		);

		// Add available actions help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'row-actions',
				'title'    => esc_html__( 'Available Actions', 'user_roles' ),
				'callback' => array( $this, 'help_tab_row_actions' )
			)
		);

		// Add bulk actions help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'bulk-actions',
				'title'    => esc_html__( 'Bulk Actions', 'user_roles' ),
				'callback' => array( $this, 'help_tab_bulk_actions' )
			)
		);

		// Set the help sidebar.
		$screen->set_help_sidebar( user_roles_get_help_sidebar_text() );
	}

	/**
	 * Overview help tab callback function.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function help_tab_overview() { ?>

		<p>
			<?php esc_html_e( 'This screen provides access to all of your user roles. Roles are a method of grouping users. They are made up of capabilities (caps), which give permission to users to perform specific actions on the site.' ); ?>
		<p>
	<?php }

	/**
	 * Screen content help tab callback function.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function help_tab_screen_content() { ?>

		<p>
			<?php esc_html_e( 'You can customize the display of this screen&#8216;s contents in a number of ways:', 'user_roles' ); ?>
		</p>

		<ul>
			<li><?php esc_html_e( 'You can hide/display columns based on your needs and decide how many roles to list per screen using the Screen Options tab.', 'user_roles' ); ?></li>
			<li><?php esc_html_e( 'You can filter the list of roles by types using the text links in the upper left. The default view is to show all roles.', 'user_roles' ); ?></li>
		</ul>
	<?php }

	/**
	 * Row actions help tab callback function.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function help_tab_row_actions() { ?>

		<p>
			<?php esc_html_e( 'Hovering over a row in the roles list will display action links that allow you to manage your role. You can perform the following actions:', 'user_roles' ); ?>
		</p>

		<ul>
			<li><?php _e( '<strong>Edit</strong> takes you to the editing screen for that role. You can also reach that screen by clicking on the role name.', 'user_roles' ); ?></li>
			<li><?php _e( '<strong>Delete</strong> removes your role from this list and permanently deletes it.', 'user_roles' ); ?></li>
			<li><?php _e( '<strong>Clone</strong> copies the role and takes you to the new role screen to further edit it.', 'user_roles' ); ?></li>
			<li><?php _e( '<strong>Users</strong> takes you to the users screen and lists the users that have that role.', 'user_roles' ); ?></li>
		</ul>
	<?php }

	/**
	 * Bulk actions help tab callback function.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function help_tab_bulk_actions() { ?>

		<p>
			<?php esc_html_e( 'You can permanently delete multiple roles at once. Select the roles you want to act on using the checkboxes, then select the action you want to take from the Bulk Actions menu and click Apply.', 'user_roles' ); ?>
		</p>
	<?php }
}
