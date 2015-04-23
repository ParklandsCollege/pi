<?php
/**
 * Template for common archive pages, author and search results
 *
 * @package themify
 * @since 1.0.0
 */
?>
<?php get_header(); ?>

	<!-- layout -->
	<div id="layout" class="pagewidth clearfix">

		<!-- content -->
		<?php themify_base_content_before(); //hook ?>
		<div id="content" class="clearfix">
			<?php themify_base_content_start(); //hook ?>

			<?php
			/////////////////////////////////////////////
			// Author Page
			/////////////////////////////////////////////
			if ( is_author() ) : ?>
				<?php
				$author_url = esc_url( get_the_author_meta( 'user_url' ) );
				/**
				 * Filter the size of the author avatar.
				 *
				 * @since 1.1.0
				 *
				 * @param int $size The avatar height and width size in pixels.
				 */
				$author_avatar_size = apply_filters( 'themify_base_author_avatar_size', 48 );
				$author_name = get_the_author_meta( 'user_firstname' ) . ' ' . get_the_author_meta( 'user_lastname' );
				if ( ' ' == $author_name ) {
					$author_name = get_the_author_meta( 'display_name' );
				}
				?>
				<div class="author-bio clearfix">
					<p class="author-avatar">
						<?php echo get_avatar( get_the_author_meta( 'user_email' ), $author_avatar_size ); ?>
					</p>

					<h2 class="author-name"><?php printf( __( 'About %s', 'themify' ), $author_name ); ?></h2>

					<?php if ( $author_url != '' ): ?>
						<p class="author-url">
							<a href="<?php echo $author_url; ?>"><?php echo $author_url; ?></a>
						</p>
					<?php endif; //author url ?>

					<div class="author-description">
						<?php the_author_meta( 'description' ); ?>
					</div>
					<!-- /.author-description -->
				</div>
				<!-- /.author bio -->

				<h2 class="author-posts-by"><?php printf( __( 'Posts by %s:', 'themify' ), $author_name ); ?></h2>
			<?php endif; ?>

			<?php
			/////////////////////////////////////////////
			// Search Title
			/////////////////////////////////////////////
			if ( is_search() ): ?>
				<h1 class="page-title"><?php _e( 'Search Results for:', 'themify' ); ?>
					<em><?php echo get_search_query(); ?></em>
				</h1>
			<?php endif; ?>

			<?php
			/////////////////////////////////////////////
			// Category Title
			/////////////////////////////////////////////
			if ( is_category() || is_tag() || is_tax() ): ?>
				<h1 class="page-title"><?php single_cat_title(); ?></h1>
				<?php echo themify_base_get_category_description(); ?>
			<?php endif; ?>

			<?php
			/////////////////////////////////////////////
			// Loop
			/////////////////////////////////////////////
			if ( have_posts() ) : ?>

				<!-- loops-wrapper -->
				<div id="loops-wrapper" class="loops-wrapper">

					<?php while ( have_posts() ) : the_post(); ?>

						<?php if ( is_search() ): ?>
							<?php get_template_part( 'includes/loop', 'search' ); ?>
						<?php else: ?>
							<?php get_template_part( 'includes/loop', 'index' ); ?>
						<?php endif; ?>

					<?php endwhile; ?>

				</div>
				<!-- /loops-wrapper -->

				<?php get_template_part( 'includes/pagination' ); ?>

			<?php else : ?>

				<?php
				/////////////////////////////////////////////
				// Error - No Page Found
				/////////////////////////////////////////////
				?>

				<p><?php _e( 'Sorry, nothing found.', 'themify' ); ?></p>

			<?php endif; ?>
			<?php themify_base_content_end(); //hook ?>
		</div>
		<?php themify_base_content_after(); //hook ?>
		<!-- /#content -->

		<?php
		/////////////////////////////////////////////
		// Sidebar
		/////////////////////////////////////////////
		get_sidebar(); ?>

	</div>
	<!-- /#layout -->

<?php get_footer(); ?>