<?php
/**
 * Publish to Apple News partials: Options page template
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
 *
 * @global array $sections
 *
 * @package Apple_News
 */
?>
<div class="wrap apple-news-settings">
	<h1><?php esc_html_e( 'Manage Settings', 'apple-news' ); ?></h1>
	<?php if ( Apple_News::is_initialized() ) : ?>
		<div class="notice notice-success">
			<p><?php esc_html_e( 'The Apple News channel config has been successfully added.', 'apple-news' ); ?></p>
		</div>
	<?php endif; ?>
	<form method="post" action="" id="apple-news-settings-form">
		<?php wp_nonce_field( 'apple_news_options' ); ?>
		<input type="hidden" name="action" value="apple_news_options" />
		<?php foreach ( $sections as $apple_section ) : ?>
			<?php $apple_section->before_section(); ?>
			<?php
			if ( $apple_section->is_hidden() ) {
				include plugin_dir_path( __FILE__ ) . 'page-options-section-hidden.php';
			} else {
				include plugin_dir_path( __FILE__ ) . 'page-options-section.php';
			}
				$apple_section->after_section();
			?>
		<?php endforeach; ?>

		<h3><?php esc_html_e( 'Posts blocked for outbound syndication to Apple News', 'apple-news' ); ?></h3>
		<?php
			$excluded_posts_ids = get_option( Admin_Apple_News::EXCLUDED_POSTS_OPTION, [] );
			if ( ! empty( $excluded_posts_ids ) ) {
				// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
				$excluded_posts = get_posts( [
					'include'          => $excluded_posts_ids,
					'suppress_filters' => false,
				] );
			}

			if ( count( $excluded_posts_ids ) && count( $excluded_posts ) ) {

				echo '<ul class="apple-news-settings-excluded-posts">';

				foreach ( $excluded_posts as $i => $excluded_post ) {
					echo '<li>' .
						' <label for="excluded-post-' . esc_attr( $i ) . '">' . esc_html( get_the_title( $excluded_post ) ) . '</label>' .
						' &nbsp;<a href="' . esc_url( get_permalink(  $excluded_post->ID ) ) .'">' . esc_html__( 'View Post' ) . '</a>' .
						' &nbsp;<a href="' . esc_url( get_edit_post_link( $excluded_post->ID ) ) . '#fm-post_settings-0-distribution-0-tab">' . esc_html__( 'Edit Distribution Settings' ) . '</a>' .
						'</li>';
				}

				echo '</ul>';

			} else {
				echo '<p class="description">' . esc_html__( 'There are no posts manually excluded from the Apple News partner feed.', 'apple-news' ) . '</p>';
			}
		?>

		<?php if ( is_plugin_active( 'brightcove-video-connect/brightcove-video-connect.php' ) ) : ?>
			<h3><?php esc_html_e( 'Brightcove Support', 'apple-news' ); ?></h3>
			<p>
				<?php
					esc_html_e(
						'Brightcove support was added in version 2.1.0 for users of the Brightcove Video Connect plugin. However, you will need to contact Apple Support to connect your Brightcove account to your Apple News channel for this feature to work properly.',
						'apple-news'
					);
				?>
			</p>
		<?php endif; ?>

		<?php submit_button(); ?>
	</form>
</div>
