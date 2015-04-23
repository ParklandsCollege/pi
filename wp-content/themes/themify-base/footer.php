<?php
/**
 * Template for site footer
 * @package themify
 * @since 1.0.0
 */
?>

	<?php themify_base_layout_after(); //hook ?>
    </div>
	<!-- /body -->
		
	<div id="footerwrap">
    
    	<?php themify_base_footer_before(); // hook ?>
		<footer id="footer" class="pagewidth clearfix">
			<?php themify_base_footer_start(); // hook ?>

			<?php get_template_part( 'includes/footer-widgets'); ?>
	
			<p class="back-top"><a href="#header" class="icon-up" title="<?php _e( 'Back To Top', 'themify' ); ?>"></a></p>
		
			<?php if (function_exists('wp_nav_menu')) {
				wp_nav_menu(array('theme_location' => 'footer-nav' , 'fallback_cb' => '' , 'container'  => '' , 'menu_id' => 'footer-nav' , 'menu_class' => 'footer-nav')); 
			} ?>

			<div class="footer-text clearfix">
				<?php themify_base_the_footer_text(); ?>
				<?php themify_base_the_footer_text( 'two' ); ?>
			</div>
			<!-- /footer-text -->
			<?php themify_base_footer_end(); // hook ?>
		</footer>
		<!-- /#footer --> 
        <?php themify_base_footer_after(); // hook ?>
	</div>
	<!-- /#footerwrap -->
	
</div>
<!-- /#pagewrap -->

<?php
/**
 *  Stylesheets and Javascript files are enqueued in theme-functions.php
 */
?>

<?php themify_base_body_end(); // hook ?>
<!-- wp_footer -->
<?php wp_footer(); ?>

</body>
</html>