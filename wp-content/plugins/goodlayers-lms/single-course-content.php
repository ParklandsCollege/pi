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
				global $gdlr_course_settings, $gdlr_course_options, $gdlr_time_left, $lms_page, $payment_row;

				// assign certificate at last page when there're no quiz
				if( ($lms_page == sizeof($gdlr_course_settings)) && $gdlr_course_options['quiz'] == 'none' &&
					(!empty($gdlr_course_options['enable-certificate']) && $gdlr_course_options['enable-certificate'] == 'enable') &&
					(empty($gdlr_course_settings['allow-non-member']) || $gdlr_course_settings['allow-non-member'] == 'disable') ){
					gdlr_lms_add_certificate(get_the_ID(), $gdlr_course_options['certificate-template']);
				}
				
				echo '<div class="gdlr-lms-course-single gdlr-lms-content-type">';
				echo '<div class="gdlr-lms-course-info-wrapper">';
				echo '<div class="gdlr-lms-course-info-title">' . __('Course Process', 'gdlr-lms') . '</div>';
				echo '<div class="gdlr-lms-course-info">';
				for( $i=1; $i<=sizeof($gdlr_course_settings); $i++ ){
					$part_class  = ($i == sizeof($gdlr_course_settings))? 'gdlr-last ': '';
					if($i < $lms_page){ 
						$part_class .= 'gdlr-pass '; 
					}else if($i == $lms_page){ 
						$part_class .= 'gdlr-current ';
					}else{ 
						$part_class .= 'gdlr-next '; 
					}
					
					echo '<div class="gdlr-lms-course-part ' . $part_class . '">';
					echo '<div class="gdlr-lms-course-part-icon">';
					echo '<div class="gdlr-lms-course-part-bullet"></div>';
					echo '<div class="gdlr-lms-course-part-line"></div>';
					echo '</div>'; // part-icon
					
					echo '<div class="gdlr-lms-course-part-content">';
					echo '<a href="' . add_query_arg(array('course_type'=>'content', 'course_page'=> $i)) . '" >';
					echo '<span class="part">' . __('Part', 'gdlr-lms') . ' ' . $i . '</span>';
					echo '<span class="title">' . $gdlr_course_settings[$i-1]['section-name'] . '</span>';
					echo '</a>';
					echo '</div>'; // part-content
					echo '</div>'; // course-part
				}
				echo '</div>'; // course-info

				if( (empty($payment_row) || ($payment_row->attendance_section >= sizeof($gdlr_course_settings))) &&
					(empty($gdlr_course_settings['allow-non-member']) || $gdlr_course_settings['allow-non-member'] == 'disable') ){
					gdlr_lms_print_course_button($gdlr_course_options, array('quiz'));
				}
				
				echo '<div class="gdlr-lms-course-pdf">';
				for( $i=1; $i<=$lms_page; $i++ ){
					if( !empty($gdlr_course_settings[$i-1]['pdf-download-link']) ){
						echo '<div class="gdlr-lms-part-pdf">';
						echo '<a class="gdlr-lms-pdf-download" target="_blank" href="' . $gdlr_course_settings[$i-1]['pdf-download-link'] . '">';
						echo '<i class="icon-file-text"></i>';
						echo '</a>';
						
						echo '<div class="gdlr-lms-part-pdf-info">';
						echo '<div class="gdlr-lms-part-title">' . __('Part', 'gdlr-lms') . ' ' . $i . '</div>';
						echo '<div class="gdlr-lms-part-caption">' . $gdlr_course_settings[$i-1]['section-name'] . '</div>';
						echo '</div>';
						echo '</div>';
					}
				}
				echo '</div>'; // course-pdf			
				echo '</div>'; // course-info-wrapper
				
				echo '<div class="gdlr-lms-course-content">';
				if( empty($gdlr_time_left) ){
					echo gdlr_lms_content_filter($gdlr_course_settings[$lms_page-1]['course-content']);
				}else{
					$day_left = intval($gdlr_time_left / 86400);
					$gdlr_time_left = $gdlr_time_left % 86400;
					$gdlr_day_left  = empty($day_left)? '': $day_left . ' ' . __('days', 'gdlr-lms') . ' '; 
					
					$hours_left = intval($gdlr_time_left / 3600);
					$gdlr_time_left = $gdlr_time_left % 3600;
					$gdlr_day_left .= empty($hours_left)? '': $hours_left . ' ' . __('hours', 'gdlr-lms') . ' '; 
					
					$minute_left = intval($gdlr_time_left / 60);
					$gdlr_time_left = $gdlr_time_left % 60;
					$gdlr_day_left .= empty($minute_left)? '': $minute_left . ' ' . __('minutes', 'gdlr-lms') . ' '; 				
					$gdlr_day_left .= empty($gdlr_time_left)? '': $gdlr_time_left . ' ' . __('seconds', 'gdlr-lms') . ' '; 	
					
					echo '<div class="gdlr-lms-course-content-time-left">';
					echo '<i class="icon-time" ></i>';
					echo sprintf(__('There\'re %s left before you can access to next part.', 'gdlr-lms'), $gdlr_day_left);
					echo '</div>';
				}
				
				echo '<div class="gdlr-lms-course-pagination">';
				if( $lms_page > 1 ){
					echo '<a href="' . add_query_arg(array('course_type'=>'content', 'course_page'=> $lms_page-1)) . '" class="gdlr-lms-button blue">';
					echo __('Previous Part', 'gdlr-lms');
					echo '</a>';
				}
				if( $lms_page < sizeof($gdlr_course_settings) && empty($gdlr_time_left) ){
					echo '<a href="' . add_query_arg(array('course_type'=>'content', 'course_page'=> $lms_page+1)) . '" class="gdlr-lms-button blue">';
					echo __('Next Part', 'gdlr-lms');
					echo '</a>';
				}
				echo '</div>'; // pagination
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