<?php

class acf_field_role_selector extends acf_field {

	var $settings,
		$defaults;


	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/

	function __construct() {

		$this->name     = 'role_selector';
		$this->label    = __( 'User Role Selector', 'acf' );
		$this->category = __( 'Relational', 'acf' );
		$this->defaults = array(
			'return_value' => 'name',
			'field_type'   => 'checkbox',
		);

		parent::__construct();

		$this->settings = array(
			'path'    => apply_filters( 'acf/helpers/get_path', __FILE__ ),
			'dir'     => apply_filters( 'acf/helpers/get_dir', __FILE__ ),
			'version' => '1.0.0'
		);

	}


	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/

	function create_options( $field ) {

		$field = array_merge( $this->defaults, $field );
		$key = $field['name'];

		?>
			<tr class="field_option field_option_<?php echo $this->name; ?>">
				<td class="label">
					<label><?php _e( 'Return Format', 'acf' ); ?></label>
					<p class="description"><?php _e( 'Specify the returned value on front end', 'acf' ); ?></p>
				</td>
				<td>
					<?php

					do_action( 'acf/create_field', array(
						'type'    =>  'radio',
						'name'    =>  'fields[' . $key . '][return_value]',
						'value'   =>  $field['return_value'],
						'layout'  =>  'horizontal',
						'choices' =>  array(
							'name'   => __( 'Role Name', 'acf' ),
							'object' => __( 'Role Object', 'acf' ),
						)
					));

					?>
				</td>
			</tr>

			<tr class="field_option field_option_<?php echo $this->name; ?>">
				<td class="label">
					<label><?php _e( 'Field Type', 'acf' ); ?></label>
				</td>
				<td>
					<?php

					do_action('acf/create_field', array(
						'type'    =>  'select',
						'name'    =>  'fields[' . $key . '][field_type]',
						'value'   =>  $field['field_type'],
						'choices' => array(
							__( 'Multiple Values', 'acf' ) => array(
								'checkbox' => __( 'Checkbox', 'acf' ),
								'multi_select' => __( 'Multi Select', 'acf' )
							),
							__( 'Single Value', 'acf' ) => array(
								'radio' => __( 'Radio Buttons', 'acf' ),
								'select' => __( 'Select', 'acf' )
							)
						)
					));

					?>
				</td>
			</tr>
		<?php

	}


	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function create_field( $field ) {
	    global $wp_roles;

	    if( $field['field_type'] == 'select' || $field['field_type'] == 'multi_select' ) :
	    	$multiple = ( $field['field_type'] == 'multi_select' ) ? 'multiple="multiple"' : '';
		?>

			<select name='<?php echo $field['name'] ?>[]' <?php echo $multiple ?>>
				<?php
					foreach( $wp_roles->roles as $role => $data ) :
					$selected = ( !empty( $field['value'] ) && in_array( $role, $field['value'] ) ) ? 'selected="selected"' : '';
				?>
					<option <?php echo $selected ?> value='<?php echo $role ?>'><?php echo $data['name'] ?></option>
				<?php endforeach; ?>

			</select>
		<?php
		else :
			echo '<ul class="acf-'.$field['field_type'].'-list '.$field['field_type'].' vertical">';
			foreach( $wp_roles->roles as $role => $data ) :
				$checked = ( !empty( $field['value'] ) && in_array( $role, $field['value'] ) ) ? 'checked="checked"' : '';
		?>
		<li><label><input <?php echo $checked ?> type="<?php echo $field['field_type'] ?>" name="<?php echo $field['name'] ?>[]" value="<?php echo $role ?>"><?php echo $data['name'] ?></label></li>
		<?php
			endforeach;
			echo '</ul>';
		endif;
	}


	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed to the create_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/

	function format_value($value, $post_id, $field)
	{
		if( $field['return_value'] == 'object' )
		{
			foreach($value as $key => $name) {
				$value[$key] = get_role($name);
			}
		}
		return $value;
	}


	/*
	*  format_value_for_api()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/

	function format_value_for_api($value, $post_id, $field)
	{

		// format
		if( $field['return_value'] == 'object' )
		{
			foreach( $value as $key => $name ) {
				$value[$key] = get_role( $name );
			}
		}

		return $value;
	}


}


// create field
new acf_field_role_selector();

?>
