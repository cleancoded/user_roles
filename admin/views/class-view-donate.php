<?php
/**
 * Handles the donate settings view.
 *
 * @package    user_roles
 * @subpackage Admin
 * @author     Cleancoded <admin@cleancoded.com>
 * @copyright  Copyright (c) 2009 - 2018, Cleancoded
 * @link       https://cleancoded.com/

 */

namespace user_roles\Admin;

/**
 * Sets up and handles the donate settings view.
 *
 * @since  2.2.0
 * @access public
 */
class View_Donate extends View {

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
	 * @since  2.2.0
	 * @access public
	 * @return void
	 */
	public function template() { ?>

		<div class="widefat">

			<div class="welcome-panel">

				<div class="welcome-panel-content">

					<h2>
						<?php esc_html_e( 'Donate Toward Future Development', 'user_roles' ); ?>
					</h2>

					<p class="about-description">
						<?php esc_html_e( 'The user_roles plugin needs funding to cover development costs toward version 3.0.', 'user_roles' ); ?>
					</p>

					<p class="user_roles-short-p">
						<?php esc_html_e( "user_roles itself will always remain free as long as I'm able to work on it. However, it is easily my largest and most complex plugin. A major update takes 100s of hours of development. If every user would donate just $1, it would fund fulltime development of this plugin for at least 3 years. Of course, it's not a reality that everyone is able donate. Pitching in any amount will help.", 'user_roles' ); ?>
					</p>

					<p>
						<a target="_blank" class="button button-primary button-hero" href="<?php echo esc_url( 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E9D2YGZFM8QT4&source=url' ); ?>"><?php esc_html_e( 'Donate Via PayPal', 'user_roles' ); ?></a>
					</p>
					<p>
						<a target="_blank" href="https://cleancoded.com/#donate"><?php esc_html_e( 'Learn More', 'user_roles' ); ?></a>
					</p>

				</div><!-- .plugin-card-top -->

			</div><!-- .plugin-card -->

		</div>

	<?php }
}
