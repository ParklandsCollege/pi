<?php
/**
 * Main Options
 */
if (function_exists("register_field_group")) :
	register_field_group(array(
		'id' => 'acf_custom-menu-setup',
		'title' => 'Custom Menu Setup',
		'fields' => array (
			array (
				'key' => 'field_545bc84396b19',
				'label' => __('Users to apply', $this->text_domain),
				'name' => 'apply_to',
				'type' => 'user',
				'required' => 0,
				'instructions' => __('Select the Users to which this menu will be visible.', $this->text_domain),
				'role' => '',
				'field_type' => 'multi_select',
				'allow_null' => 0,
				'multiple'   => 1,
			),

			array (
				'key' => 'field_547845c8605b9',
				'label' => __('Roles to apply', $this->text_domain),
				'name' => 'roles',
				'type' => 'role_selector',
				'instructions' => __('Select the Roles to which this menu will be visible.', $this->text_domain),
				'return_value' => 'name',
				'field_type' => 'multi_select',
			),
		),

		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => $this->post_type,
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),

		'options' => array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
endif;

/**
 * Activate Menu options
 */
if(function_exists('register_field_group')):

  register_field_group(array (
      'key' => 'group_54c23548a04e7',
      'title' => __('Activated', $this->text_domain),
      'fields' => array (
          array (
              'key' => 'field_54c235579bfe9',
              'label' => 'Activate Menu?',
              'name' => 'activated',
              'prefix' => '',
              'type' => 'true_false',
              'instructions' => __('To prevent this menu from taking any effect, just turn off this switch.', $this->text_domain),
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array (
                  'width' => '',
                  'class' => '',
                  'id' => '',
              ),
              'message' => __('Activate this menu setup.', $this->text_domain),
              'default_value' => 1,
          ),
      ),
      'location' => array (
          array (
              array (
                  'param' => 'post_type',
                  'operator' => '==',
                  'value' => $this->post_type,
              ),
          ),
      ),
      'menu_order' => 0,
      'position' => 'side',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
  ));

endif;