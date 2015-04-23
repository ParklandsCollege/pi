<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package annina
 */
?>

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="site-info content-annina-title annDouble smallPart">
			<div class="text-copy">
			<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'annina' ) ); ?>"><?php printf( __( 'Proudly powered by %s', 'annina' ), 'WordPress' ); ?></a>
			<span class="sep"> | </span>
			<?php printf( __( '%1$s by %2$s.', 'annina' ), 'Annina Free', '<a target="_blank" href="http://crestaproject.com" rel="designer" title="CrestaProject">CrestaProject</a>' ); ?>
			</div>
			<div id="toTop"><i class="fa fa-angle-up fa-lg"></i></div>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->
</div><!-- #content -->
<?php wp_footer(); ?>

</body>
</html>
