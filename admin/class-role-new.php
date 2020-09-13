<?php
/**
 * Handles the new role screen.
 *
 * @package    user_roles
 * @subpackage Admin
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

namespace user_roles\Admin;

/**
 * Class that displays the new role screen and handles the form submissions for that page.
 *
 * @since  2.0.0
 * @access public
 */
final class Role_New {

	/**
	 * Holds the instances of this class.
	 *
	 * @since  2.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Name of the page we've created.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var    string
	 */
	public $page = '';

	/**
	 * Role that's being created.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var    string
	 */
	public $role = '';

	/**
	 * Name of the role that's being created.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var    string
	 */
	public $role_name = '';

	/**
	 * Array of the role's capabilities.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var    array
	 */
	public $capabilities = array();

	/**
	 * Conditional to see if we're cloning a role.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var    bool
	 */
	public $is_clone = false;

	/**
	 * Role that is being cloned.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var    string
	 */
	public $clone_role = '';

	/**
	 * Sets up our initial actions.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// If the role manager is active.
		if ( user_roles_role_manager_enabled() )
			add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
	}

	/**
	 * Adds the roles page to the admin.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function add_admin_page() {

		$this->page = add_submenu_page( 'users.php', esc_html__( 'Add New Role', 'user_roles' ), esc_html__( 'Add New Role', 'user_roles' ), 'create_roles', 'role-new', array( $this, 'page' ) );

		// Let's roll if we have a page.
		if ( $this->page ) {

			add_action( "load-{$this->page}", array( $this, 'load'          ) );
			add_action( "load-{$this->page}", array( $this, 'add_help_tabs' ) );
		}
	}

	/**
	 * Checks posted data on load and performs actions if needed.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function load() {

		// Are we cloning a role?
		$this->is_clone = isset( $_GET['clone'] ) && user_roles_role_exists( $_GET['clone'] );

		if ( $this->is_clone ) {

			// Override the default new role caps.
			add_filter( 'user_roles_new_role_default_caps', array( $this, 'clone_default_caps' ), 15 );

			// Set the clone role.
			$this->clone_role = user_roles_sanitize_role( $_GET['clone'] );
		}

		// Check if the current user can create roles and the form has been submitted.
		if ( current_user_can( 'create_roles' ) && isset( $_POST['user_roles_new_role_nonce'] ) ) {

			// Verify the nonce.
			check_admin_referer( 'new_role', 'user_roles_new_role_nonce' );

			// Set up some variables.
			$this->capabilities = array();
			$new_caps           = array();
			$is_duplicate       = false;

			// Get all the capabilities.
			$_m_caps = user_roles_get_capabilities();

			// Add all caps from the cap groups.
			foreach ( user_roles_get_cap_groups() as $group )
				$_m_caps = array_merge( $_m_caps, $group->caps );

			// Make sure we have a unique array of caps.
			$_m_caps = array_unique( $_m_caps );

			// Check if any capabilities were selected.
			if ( isset( $_POST['grant-caps'] ) || isset( $_POST['deny-caps'] ) ) {

				$grant_caps = ! empty( $_POST['grant-caps'] ) ? user_roles_remove_hidden_caps( array_unique( $_POST['grant-caps'] ) ) : array();
				$deny_caps  = ! empty( $_POST['deny-caps'] )  ? user_roles_remove_hidden_caps( array_unique( $_POST['deny-caps']  ) ) : array();

				foreach ( $_m_caps as $cap ) {

					if ( in_array( $cap, $grant_caps ) )
						$new_caps[ $cap ] = true;

					else if ( in_array( $cap, $deny_caps ) )
						$new_caps[ $cap ] = false;
				}
			}

			$grant_new_caps = ! empty( $_POST['grant-new-caps'] ) ? user_roles_remove_hidden_caps( array_unique( $_POST['grant-new-caps'] ) ) : array();
			$deny_new_caps  = ! empty( $_POST['deny-new-caps'] )  ? user_roles_remove_hidden_caps( array_unique( $_POST['deny-new-caps']  ) ) : array();

			foreach ( $grant_new_caps as $grant_new_cap ) {

				$_cap = user_roles_sanitize_cap( $grant_new_cap );

				if ( ! in_array( $_cap, $_m_caps ) )
					$new_caps[ $_cap ] = true;
			}

			foreach ( $deny_new_caps as $deny_new_cap ) {

				$_cap = user_roles_sanitize_cap( $deny_new_cap );

				if ( ! in_array( $_cap, $_m_caps ) )
					$new_caps[ $_cap ] = false;
			}

			// Sanitize the new role name/label. We just want to strip any tags here.
			if ( ! empty( $_POST['role_name'] ) )
				$this->role_name = wp_strip_all_tags( wp_unslash( $_POST['role_name'] ) );

			// Sanitize the new role, removing any unwanted characters.
			if ( ! empty( $_POST['role'] ) )
				$this->role = user_roles_sanitize_role( $_POST['role'] );

			else if ( $this->role_name )
				$this->role = user_roles_sanitize_role( $this->role_name );

			// Is duplicate?
			if ( user_roles_role_exists( $this->role ) )
				$is_duplicate = true;

			// Add a new role with the data input.
			if ( $this->role && $this->role_name && ! $is_duplicate ) {

				add_role( $this->role, $this->role_name, $new_caps );

				// Action hook for when a role is added.
				do_action( 'user_roles_role_added', $this->role );

				// If the current user can edit roles, redirect to edit role screen.
				if ( current_user_can( 'edit_roles' ) ) {
					wp_redirect( esc_url_raw( add_query_arg( 'message', 'role_added', user_roles_get_edit_role_url( $this->role ) ) ) );
 					exit;
				}

				// Add role added message.
				add_settings_error( 'user_roles_role_new', 'role_added', sprintf( esc_html__( 'The %s role has been created.', 'user_roles' ), $this->role_name ), 'updated' );
			}

			// If there are new caps, let's assign them.
			if ( ! empty( $new_caps ) )
				$this->capabilities = $new_caps;

			// Add error if there's no role.
			if ( ! $this->role )
				add_settings_error( 'user_roles_role_new', 'no_role', esc_html__( 'You must enter a valid role.', 'user_roles' ) );

			// Add error if this is a duplicate role.
			if ( $is_duplicate )
				add_settings_error( 'user_roles_role_new', 'duplicate_role', sprintf( esc_html__( 'The %s role already exists.', 'user_roles' ), $this->role ) );

			// Add error if there's no role name.
			if ( ! $this->role_name )
				add_settings_error( 'user_roles_role_new', 'no_role_name', esc_html__( 'You must enter a valid role name.', 'user_roles' ) );
		}

		// If we don't have caps yet, get the new role default caps.
		if ( empty( $this->capabilities ) )
			$this->capabilities = user_roles_new_role_default_caps();

		// Load page hook.
		do_action( 'user_roles_load_role_new' );

		// Hook for adding in meta boxes.
		do_action( 'add_meta_boxes_' . get_current_screen()->id, '' );
		do_action( 'add_meta_boxes',   get_current_screen()->id, '' );

		// Add layout screen option.
		add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );

		// Load scripts/styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

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

		// Add help tabs.
		$screen->add_help_tab( user_roles_get_edit_role_help_overview_args()   );
		$screen->add_help_tab( user_roles_get_edit_role_help_role_name_args()  );
		$screen->add_help_tab( user_roles_get_edit_role_help_edit_caps_args()  );
		$screen->add_help_tab( user_roles_get_edit_role_help_custom_cap_args() );

		// Set the help sidebar.
		$screen->set_help_sidebar( user_roles_get_help_sidebar_text() );
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
	 * Outputs the page.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function page() { ?>

		<div class="wrap">

			<h1><?php ! $this->is_clone ? esc_html_e( 'Add New Role', 'user_roles' ) : esc_html_e( 'Clone Role', 'user_roles' ); ?></h1>

			<?php settings_errors( 'user_roles_role_new' ); ?>

			<div id="poststuff">

				<form name="form0" method="post" action="<?php echo esc_url( user_roles_get_new_role_url() ); ?>">

					<?php wp_nonce_field( 'new_role', 'user_roles_new_role_nonce' ); ?>

					<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? 1 : 2; ?>">

						<div id="post-body-content">

							<div id="titlediv" class="user_roles-title-div">

								<div id="titlewrap">
									<span class="screen-reader-text"><?php esc_html_e( 'Role Name', 'user_roles' ); ?></span>
									<input type="text" name="role_name" value="<?php echo ! $this->role && $this->clone_role ? esc_attr( sprintf( __( '%s Clone', 'user_roles' ), user_roles_get_role( $this->clone_role )->get( 'label' ) ) ) : esc_attr( $this->role_name ); ?>" placeholder="<?php esc_attr_e( 'Enter role name', 'user_roles' ); ?>" />
								</div><!-- #titlewrap -->

								<div class="inside">
									<div id="edit-slug-box">
										<strong><?php esc_html_e( 'Role:', 'user_roles' ); ?></strong> <span class="role-slug"><?php echo ! $this->role && $this->clone_role ? esc_attr( "{$this->clone_role}_clone" ) : esc_attr( $this->role ); ?></span> <!-- edit box -->
										<input type="text" name="role" value="<?php echo user_roles_sanitize_role( $this->role ); ?>" />
										<button type="button" class="role-edit-button button button-small closed"><?php esc_html_e( 'Edit', 'user_roles' ); ?></button>
									</div>
								</div><!-- .inside -->

							</div><!-- .user_roles-title-div -->

							<?php $cap_tabs = new Cap_Tabs( '', $this->capabilities ); ?>
							<?php $cap_tabs->display(); ?>

						</div><!-- #post-body-content -->

						<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
						<?php wp_nonce_field( 'meta-box-order',  'meta-box-order-nonce', false ); ?>

						<div id="postbox-container-1" class="postbox-container side">

							<?php do_meta_boxes( get_current_screen()->id, 'side', '' ); ?>

						</div><!-- .post-box-container -->

					</div><!-- #post-body -->
				</form>

			</div><!-- #poststuff -->

		</div><!-- .wrap -->

	<?php }

	/**
	 * Filters the new role default caps in the case that we're cloning a role.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  array  $capabilities
	 * @param  array
	 */
	public function clone_default_caps( $capabilities ) {

		if ( $this->is_clone ) {

			$role = get_role( $this->clone_role );

			if ( $role && isset( $role->capabilities ) && is_array( $role->capabilities ) )
				$capabilities = $role->capabilities;
		}

		return $capabilities;
	}

	/**
	 * Returns the instance.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		if ( ! self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
}

Role_New::get_instance();
