<?php get_header(); ?>
<div id="primary" class="content-area gdlr-lms-primary-wrapper">
<div id="content" class="site-content" role="main">
<?php
	if( function_exists('gdlr_lms_get_header') && !empty($gdlr_lms_option['show-header']) && $gdlr_lms_option['show-header'] == 'enable' ){
		gdlr_lms_get_header();
	}
?>
	<div class="gdlr-lms-content">
		<div class="gdlr-lms-container gdlr-lms-container">
		<?php 
			while( have_posts() ){ the_post();
				$course_val = gdlr_lms_decode_preventslashes(get_post_meta(get_the_ID(), 'gdlr-lms-course-settings', true));
				$course_options = empty($course_val)? array(): json_decode($course_val, true);		
			
				echo '<div class="gdlr-lms-course-single">';
				echo '<div class="gdlr-lms-course-info-wrapper">';
				echo '<div class="gdlr-lms-course-info-author-image">';
				echo gdlr_lms_get_author_image($post->post_author, 'thumbnail');
				echo '</div>';			
				gdlr_lms_print_course_info($course_options, array('instructor', 'type', 'date', 'time', 'place', 'seat', 'rating'));
				gdlr_lms_print_course_price($course_options);
				gdlr_lms_print_course_button($course_options, array('buy', 'book'));				
				echo '</div>';
				
				echo '<div class="gdlr-lms-course-content">';
				gdlr_lms_print_course_thumbnail();
				echo '<div class="gdlr-lms-course-excerpt">';
				the_content();
				echo '</div>'; // course-excerpt

				echo '<div class="gdlr-lms-single-course-info">';
				$tag = get_the_term_list(get_the_ID(), 'course_tag', '', '<span class="sep">,</span> ' , '' );
				if( !empty($tag) ){
					echo '<div class="portfolio-info portfolio-tag"><i class="icon-tag" ></i>' . $tag . '</div>';
				}
				
				gdlr_lms_get_social_shares();
				echo '</div>';	// single-course-info
				echo '</div>'; // course-content
				echo '<div class="clear"></div>';
				echo '</div>'; // course-single		
			}
		?>
		</div><!-- gdlr-lms-container -->
	</div><!-- gdlr-lms-content -->
</div>
</div>
<?php 
if( !empty($gdlr_lms_option['show-sidebar']) && $gdlr_lms_option['show-sidebar'] == 'enable' ){ 
	get_sidebar( 'content' );
	get_sidebar();
}

get_footer(); ?>