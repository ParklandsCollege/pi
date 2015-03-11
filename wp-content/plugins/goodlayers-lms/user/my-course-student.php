<?php
	$course_val = gdlr_lms_decode_preventslashes(get_post_meta($_GET['course_id'], 'gdlr-lms-course-settings', true));
	$course_options = empty($course_val)? array(): json_decode($course_val, true);
?>
<h3 class="gdlr-lms-admin-head" ><?php echo get_the_title($_GET['course_id']); ?></h3>
<table class="gdlr-lms-table">
<tr>
	<th><?php _e('Student', 'gdlr-lms'); ?></th>
	<?php if($course_options['online-course'] == 'disable'){ ?>
	<th align="center" ><?php _e('Seat', 'gdlr-lms'); ?></th>
	<?php } ?>
	<th align="center" ><?php _e('Status', 'gdlr-lms'); ?></th>
	<th align="center" ><?php _e('Code', 'gdlr-lms'); ?></th>
	<?php if($course_options['online-course'] == 'enable'){ ?>
	<th align="center" ><?php _e('Score', 'gdlr-lms'); ?></th>
	<?php } ?>
</tr>
<?php 
	$temp_sql  = "SELECT id, student_id, payment_info, payment_status FROM " . $wpdb->prefix . "gdlrpayment ";
	$temp_sql .= "WHERE course_id = " . $_GET['course_id'] . " ";
	$temp_sql .= "ORDER BY payment_status, ID ASC";

	$results = $wpdb->get_results($temp_sql);
	foreach($results as $result){
		$user_info = get_user_meta($result->student_id);
		$payment_info = unserialize($result->payment_info);
		
		echo '<tr>';
		echo '<td>' . $user_info['first_name'][0] . ' ' . $user_info['last_name'][0] . ' ';
		echo '<a href="#" title="' . esc_attr(__('Delete Student', 'gdlr-lms')) . '" class="gdlr-lms-delete-student" ';
		echo 'data-title="' . esc_attr(__('Are you sure you want to remove this student from this course', 'gdlr-lms')) . '" ';
		echo 'data-yes="' . esc_attr(__('Confirm', 'gdlr-lms')) . '" data-no="' . esc_attr(__('Cancel', 'gdlr-lms')) . '" ';
		echo 'data-id="' . $result->id . '" data-ajax="' . admin_url('admin-ajax.php') . '" >';
		echo __('(Delete)', 'gdlr-lms') . '</a>';
		echo '</td>';
		
		if($course_options['online-course'] == 'disable'){
			echo '<td>' . $payment_info['amount'] . '</td>';
		}
		echo '<td>' . $result->payment_status . '</td>';
		echo '<td>' . $payment_info['code'] . '</td>';
		if($course_options['online-course'] == 'enable'){
			$temp_sql  = "SELECT quiz_score FROM " . $wpdb->prefix . "gdlrquiz ";
			$temp_sql .= "WHERE course_id = " . $_GET['course_id'] . " ";
			$temp_sql .= "AND student_id = " . $result->student_id;
			
			$quiz_row = $wpdb->get_row($temp_sql);
			$quiz_score = empty($quiz_row)? array(): unserialize($quiz_row->quiz_score);
			$quiz_score = empty($quiz_score)? array(): $quiz_score;
			$score_summary = gdlr_lms_score_summary($quiz_score);			
			
			echo '<td>' . $score_summary['score'] . '/' . $score_summary['from'] . '</td>';
		}
		echo '</tr>';
	}	
?>
</table>