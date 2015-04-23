<?php
/**
 * The template part for displaying a message that posts cannot be found.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package annina
 */
?>

<section class="no-results not-found">
	<header class="page-header">
		<div class="content-annina-title annDouble">
			<h1 class="page-title"><?php _e( 'Nothing Found', 'annina' ); ?></h1>
		</div>
	</header><!-- .page-header -->

	<div class="page-content none404">
		<div class="content-annina annDouble">
		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

			<p><?php printf( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'annina' ), esc_url( admin_url( 'post-new.php' ) ) ); ?></p>

		<?php elseif ( is_search() ) : ?>

			<p><?php _e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'annina' ); ?></p>
			<?php get_search_form(); ?>

		<?php else : ?>

			<p><?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'annina' ); ?></p>
			<?php get_search_form(); ?>

		<?php endif; ?>
		</div>
	</div><!-- .page-content -->
</section><!-- .no-results -->
