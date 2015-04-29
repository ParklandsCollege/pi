<?php

class acf_field_role_selector extends acf_field {


	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
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


	}


	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field_settings( $field ) {

		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/

		acf_render_field_setting( $field, array(
			'label'			=> __('Return Format','acf-role_selector'),
			'instructions'	=> __('Specify the returned value type','acf-role_selector'),
			'type'			=> 'radio',
			'name'			=> 'return_value',
			'layout'  =>  'horizontal',
			'choices' =>  array(
				'name'   => __( 'Role Name', 'acf' ),
				'object' => __( 'Role Object', 'acf' ),
			)
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Field Type','acf-role_selector'),
			'type'			=> 'select',
			'name'			=> 'field_type',
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



	}



	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field( $field ) {
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






}


// create field
new acf_field_role_selector();

?>
