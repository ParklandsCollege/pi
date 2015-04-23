<?php
/**
 * Post Navigation.
 */

$in_same_cat = true;
$previous = get_previous_post_link( '<span class="prev">%link</span>', '<span class="arrow icon-left"></span> %title' );
$next = get_next_post_link( '<span class="next">%link</span>', '%title <span class="arrow icon-right"></span>' );

if ( ! empty( $previous ) || ! empty( $next ) ) : ?>

	<div class="post-nav clearfix">
		<?php echo $previous; ?>
		<?php echo $next; ?>
	</div>
	<!-- /.post-nav -->

<?php endif; // empty previous or next