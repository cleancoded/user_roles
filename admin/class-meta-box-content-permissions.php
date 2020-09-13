<?php
/**
 * Content permissions meta box.
 *
 * @package    user_roles
 * @subpackage Admin
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

namespace user_roles\Admin;

/**
 * Class to handle the content permissios meta box and saving the meta.
 *
 * @since  2.0.0
 * @access public
 */
final class Meta_Box_Content_Permissions {

	/**
	 * Holds the instances of this class.
	 *
	 * @since  2.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Whether this is a new post.  Once the post is saved and we're
	 * no longer on the `post-new.php` screen, this is going to be
	 * `false`.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var    bool
	 */
	public $is_new_post = false;

	/**
	 * Sets up the appropriate actions.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @return void
	 */
	protected function __construct() {

		// If content permissions is disabled, bail.
		if ( ! user_roles_content_permissions_enabled() )
			return;

		add_action( 'load-post.php',     array( $this, 'load' ) );
		add_action( 'load-post-new.php', array( $this, 'load' ) );
	}

	/**
	 * Fires on the page load hook to add actions specifically for the post and
	 * new post screens.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function load() {

		// Make sure meta box is allowed for this post type.
		if ( ! $this->maybe_enable() )
			return;

		// Is this a new post?
		$this->is_new_post = 'load-post-new.php' === current_action();

		// Enqueue scripts/styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// Add custom meta boxes.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Save metadata on post save.
		add_action( 'save_post', array( $this, 'update' ), 10, 2 );
	}

	/**
	 * Enqueues scripts styles.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue() {

		wp_enqueue_script( 'user_roles-edit-post' );
		wp_enqueue_style( 'user_roles-admin' );
	}

	/**
	 * Adds the meta box.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  string  $post_type
	 * @return void
	 */
	public function add_meta_boxes( $post_type ) {

		// If the current user can't restrict content, bail.
		if ( ! current_user_can( 'restrict_content' ) )
			return;

		// Add the meta box.
		add_meta_box( 'user_roles-cp', esc_html__( 'Content Permissions', 'user_roles' ), array( $this, 'meta_box' ), $post_type, 'advanced', 'high' );
	}

	/**
	 * Checks if Content Permissions should appear for the given post type.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return bool
	 */
	public function maybe_enable() {

		// Get the post type object.
		$type = get_post_type_object( get_current_screen()->post_type );

		// Only enable for public post types and non-attachments by default.
		$enable = 'attachment' !== $type->name && $type->public;

		return apply_filters( "user_roles_enable_{$type->name}_content_permissions", $enable );
	}

	/**
	 * Outputs the meta box HTML.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  object  $post
	 * @global object  $wp_roles
	 * @return void
	 */
	public function meta_box( $post ) {
		global $wp_roles;

		// Get roles and sort.
		 $_wp_roles = $wp_roles->role_names;
		asort( $_wp_roles );

		// Get the roles saved for the post.
		$roles = get_post_meta( $post->ID, '_user_roles_access_role', false );

		if ( ! $roles && $this->is_new_post )
			$roles = apply_filters( 'user_roles_default_post_roles', array(), $post->ID );

		// Convert old post meta to the new system if no roles were found.
		if ( empty( $roles ) )
			$roles = user_roles_convert_old_post_meta( $post->ID );

		// Nonce field to validate on save.
		wp_nonce_field( 'user_roles_cp_meta_nonce', 'user_roles_cp_meta' );

		// Hook for firing at the top of the meta box.
		do_action( 'user_roles_cp_meta_box_before', $post ); ?>

		<div class="user_roles-tabs user_roles-cp-tabs">

			<ul class="user_roles-tab-nav">
				<li class="user_roles-tab-title">
					<a href="#user_roles-tab-cp-roles">
						<i class="dashicons dashicons-groups"></i>
						<span class="label"><?php esc_html_e( 'Roles', 'user_roles' ); ?></span>
					</a>
				</li>
				<li class="user_roles-tab-title">
					<a href="#user_roles-tab-cp-message">
						<i class="dashicons dashicons-edit"></i>
						<span class="label"><?php esc_html_e( 'Error Message', 'user_roles' ); ?></span>
					</a>
				</li>
			</ul>

			<div class="user_roles-tab-wrap">

				<div id="user_roles-tab-cp-roles" class="user_roles-tab-content">

					<span class="user_roles-tabs-label">
						<?php esc_html_e( 'Limit access to the content to users of the selected roles.', 'user_roles' ); ?>
					</span>

					<div class="user_roles-cp-role-list-wrap">

						<ul class="user_roles-cp-role-list">

						<?php foreach ( $_wp_roles as $role => $name ) : ?>
							<li>
								<label>
									<input type="checkbox" name="user_roles_access_role[]" <?php checked( is_array( $roles ) && in_array( $role, $roles ) ); ?> value="<?php echo esc_attr( $role ); ?>" />
									<?php echo esc_html( user_roles_translate_role( $role ) ); ?>
								</label>
							</li>
						<?php endforeach; ?>

						</ul>
					</div>

					<span class="user_roles-tabs-description">
						<?php printf( esc_html__( 'If no roles are selected, everyone can view the content. The author, any users who can edit the content, and users with the %s capability can view the content regardless of role.', 'user_roles' ), '<code>restrict_content</code>' ); ?>
					</span>

				</div>

				<div id="user_roles-tab-cp-message" class="user_roles-tab-content">

					<?php wp_editor(
						get_post_meta( $post->ID, '_user_roles_access_error', true ),
						'user_roles_access_error',
						array(
							'drag_drop_upload' => true,
							'editor_height'    => 200
						)
					); ?>

				</div>

			</div><!-- .user_roles-tab-wrap -->

		</div><!-- .user_roles-tabs --><?php

		// Hook that fires at the end of the meta box.
		do_action( 'user_roles_cp_meta_box_after', $post );
	}

	/**
	 * Saves the post meta.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  int     $post_id
	 * @param  object  $post
	 * @return void
	 */
	public function update( $post_id, $post = '' ) {

		$do_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );

		if ( $do_autosave || $is_autosave || $is_revision )
			return;

		// Fix for attachment save issue in WordPress 3.5.
		// @link http://core.trac.wordpress.org/ticket/21963
		if ( ! is_object( $post ) )
			$post = get_post();

		// Verify the nonce.
		if ( ! isset( $_POST['user_roles_cp_meta'] ) || ! wp_verify_nonce( $_POST['user_roles_cp_meta'], 'user_roles_cp_meta_nonce' ) )
			return;

		/* === Roles === */

		// Get the current roles.
		$current_roles = user_roles_get_post_roles( $post_id );

		// Get the new roles.
		$new_roles = isset( $_POST['user_roles_access_role'] ) ? $_POST['user_roles_access_role'] : '';

		// If we have an array of new roles, set the roles.
		if ( is_array( $new_roles ) )
			user_roles_set_post_roles( $post_id, array_map( 'user_roles_sanitize_role', $new_roles ) );

		// Else, if we have current roles but no new roles, delete them all.
		elseif ( !empty( $current_roles ) )
			user_roles_delete_post_roles( $post_id );

		/* === Error Message === */

		// Get the old access message.
		$old_message = user_roles_get_post_access_message( $post_id );

		// Get the new message.
		$new_message = isset( $_POST['user_roles_access_error'] ) ? wp_kses_post( wp_unslash( $_POST['user_roles_access_error'] ) ) : '';

		// If we have don't have a new message but do have an old one, delete it.
		if ( '' == $new_message && $old_message )
			user_roles_delete_post_access_message( $post_id );

		// If the new message doesn't match the old message, set it.
		else if ( $new_message !== $old_message )
			user_roles_set_post_access_message( $post_id, $new_message );
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

Meta_Box_Content_Permissions::get_instance();
