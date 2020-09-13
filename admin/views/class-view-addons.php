<?php
/**
 * Handles the add-ons settings view.
 *
 * @package    user_roles
 * @subpackage Admin
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

namespace user_roles\Admin;

/**
 * Sets up and handles the add-ons settings view.
 *
 * @since  2.0.0
 * @access public
 */
class View_Addons extends View {

	/**
	 * Enqueues scripts/styles.
	 *
	 * @since  2.2.0
	 * @access public
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_style( 'user_roles-admin' );
	}

	/**
	 * Renders the settings page.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function template() {

		require_once( user_roles_plugin()->dir . 'admin/class-addon.php'      );
		require_once( user_roles_plugin()->dir . 'admin/functions-addons.php' );

		do_action( 'user_roles_register_addons' );

		$addons = user_roles_get_addons(); ?>

		<div class="widefat">

			<div class="welcome-panel">

				<div class="welcome-panel-content">

					<div>
						<div class="user_roles-svg-wrap">
							<a href="https://cleancoded.com/" class="user_roles-svg-link" target="_blank">
								<?php include user_roles_plugin()->dir . 'img/user_roles.svg'; ?>
							</a>
						</div>

						<div style="overflow: hidden;">
							<h2>
								<?php _e( 'Go Pro With user_roles Add-ons', 'user_roles' ); ?>
							</h2>
							<p class="about-description" style="margin:20px 0 10px;">
								<?php esc_html_e( 'Take your user_roleship site to the next level with add-ons. Pro users also enjoy live chat support and support forum access.', 'user_roles' ); ?>
							</p>
							<p>
								<a class="button button-primary button-hero" href="https://cleancoded.com/" target="_blank"><?php esc_html_e( 'Upgrade To Pro', 'user_roles' ); ?></a>
							</p>
						</div>
					</div>

				</div><!-- .welcome-panel-content -->

			</div><!-- .welcome-panel -->

		</div>

		<div class="widefat">

			<?php if ( $addons ) : ?>

				<?php foreach ( $addons as $addon ) : ?>

					<?php $this->addon_card( $addon ); ?>

				<?php endforeach; ?>

			<?php else : ?>

				<div class="error notice">
					<p>
						<strong><?php esc_html_e( 'There are currently no add-ons to show. Please try again later.', 'user_roles' ); ?></strong>
					</p>
				</div>

			<?php endif; ?>

		</div><!-- .widefat -->
	<?php }

	/**
	 * Renders an individual add-on plugin card.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function addon_card( $addon ) { ?>

		<div class="plugin-card plugin-card-<?php echo esc_attr( $addon->name ); ?>">

			<div class="plugin-card-top">

				<div class="name column-name">
					<h3>
						<?php if ( $addon->url ) : ?>
							<a href="<?php echo esc_url( $addon->url ); ?>" target="_blank">
						<?php endif; ?>

							<?php echo esc_html( $addon->title ); ?>

							<?php if ( file_exists( user_roles_plugin()->dir . "img/{$addon->name}.svg" ) ) : ?>

								<span class="plugin-icon user_roles-svg-link">
									<?php include user_roles_plugin()->dir . "img/{$addon->name}.svg"; ?>
								</span>

							<?php elseif ( $addon->icon_url ) : ?>

								<img class="plugin-icon" src="<?php echo esc_url( $addon->icon_url ); ?>" alt="" />

							<?php endif; ?>

						<?php if ( $addon->url ) : ?>
							</a>
						<?php endif; ?>
					</h3>
				</div>

				<div class="desc column-description" style="margin-right:0;">
					<?php echo wpautop( wp_kses_post( $addon->excerpt ) ); ?>
				</div>

			</div><!-- .plugin-card-top -->

		</div><!-- .plugin-card -->

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

		// Roles/Caps help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'overview',
				'title'    => esc_html__( 'Overview', 'user_roles' ),
				'callback' => array( $this, 'help_tab_overview' )
			)
		);

		// Roles/Caps help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'download',
				'title'    => esc_html__( 'Download', 'user_roles' ),
				'callback' => array( $this, 'help_tab_download' )
			)
		);

		// Roles/Caps help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'purchase',
				'title'    => esc_html__( 'Purchase', 'user_roles' ),
				'callback' => array( $this, 'help_tab_purchase' )
			)
		);

		// Set the help sidebar.
		$screen->set_help_sidebar( user_roles_get_help_sidebar_text() );
	}

	/**
	 * Displays the overview help tab.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function help_tab_overview() { ?>

		<p>
			<?php esc_html_e( 'The Add-Ons screen allows you to view available add-ons for the user_roles plugin. You can download some plugins directly. Others may be available to purchase.', 'user_roles' ); ?>
		</p>
	<?php }

	/**
	 * Displays the download help tab.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function help_tab_download() { ?>

		<p>
			<?php esc_html_e( 'Some plugins may be available for direct download. In such cases, you can click the download button to get a ZIP file of the plugin.', 'user_roles' ); ?>
		</p>
	<?php }

	/**
	 * Displays the purchase help tab.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function help_tab_purchase() { ?>

		<p>
			<?php esc_html_e( 'Some add-ons may require purchase before downloading them. Clicking the purchase button will take you off-site to view the add-on in more detail.', 'user_roles' ); ?>
		</p>
	<?php }
}
