<?php

/*
  Plugin Name: WP Admin Menu Manager
  Plugin URI: http://codecanyon.net/item/wp-admin-menu-manager/9520160
  Description: Edit, rename, reorder and hide WordPress menu and submenu items was never that easy!
  Version: 2.1.5
 */

/**
 * Require our PluginCore
 */
require_once "core/core.php";

class wpAdminMenu extends PluginCore732 {

  // Mandatory
  private static $instance = null;
  public  $file            = __FILE__;

  // Setup: Text Domain
  public  $text_domain     = 'wp-admin-menu';
  // Setup: Plugin page slug
  public  $slug            = 'wpamm';
  // Setup: Embed ACF or not
  public  $addACF          = true;


  // Custom to this plugin:
  public  $post_type       = 'amm';
  private $options_slug    = 'amm_options_';
  private $postMenu;
  private $activeMenu      = false;
  private $menu            = array();

  // Sub Menu Support
  private $submenu         = array();
  private $newSubmenu;
  private $lists;

  /**
   * Creates or returns an instance of this class.
   */
  public static function get_instance() {
    // If an instance hasn't been created and set to $instance create an instance and set it to $instance.
    if (null == self::$instance) {self::$instance = new self;}
    return self::$instance;
  }

  // End of ACF

  /**
   * Enqueue and register JavaScript files here.
   */
  public function register_scripts() {
    wp_register_script('wpAdminMenuManager', plugins_url('assets/js/scripts.min.js', __FILE__), '7e79952bb67c1b20c34d52a5f4a38c8f', '', true);
    wp_enqueue_script('wpAdminMenuManager');
  }

  /**
   * Enqueue and register CSS files here.
   */
  public function register_styles() {
    wp_enqueue_style('wpAdminMenuManager', plugins_url('assets/css/main.min.css', __FILE__ ), '0d76c522cc7460ceb397acbb08832c96');
  }


  /**
   * Gets the current user role, used to figure out either to apply or not some menu setup.
   */
  public function getCurrentUserRole() {
    global $current_user;
    get_currentuserinfo();
    $user_roles = $current_user->roles;
    $user_role = array_shift($user_roles);
    return $user_role;
  }

  public function values($value) {
    return array_values($value);
  }

  /**
   * Equivalent of makeLists for menus, only for submenus
   */
  public function makeSubmenu($ID) {
    
    // Our saved submenu version
    $ourSubmenu = $this->getSubmenu($ID);
    // var_dump($ourSubmenu);
    
    // The default submenu
    $defaultSubmenu = $this->submenu;
    // var_dump($defaultSubmenu);
    
    // Now we flat the default (ajust the indexes) submenu so we can add them together
    $defaultSubmenu = array_map(array($this, 'values'), $defaultSubmenu);
    
    // Loop default menus
    foreach ($defaultSubmenu as $parent => &$submenus) {
      foreach ($submenus as $index => &$item) {
        // Add fields from our save version, with the modifications
        if (isset($ourSubmenu[$parent][$index])) $item = $ourSubmenu[$parent][$index] + $item;
      }
    }
    
    // var_dump($defaultSubmenu);
    // Return the result
    return $defaultSubmenu;
  }

  /**
   * Actual Hook on users
   */
  public function changeMenu() {
    // Saves Menu before any changes
    $this->menu    = $GLOBALS['menu'];
    // Add Submenu support
    $this->submenu = $GLOBALS['submenu'];
    // var_dump($this->submenu);

    require_once $this->get_plugin_path()."/lib/wp-menu.php";

    // Get Actual user
    $user = wp_get_current_user();

    // Get Configs
    $args = array(
      'posts_per_page' => -1,
      'post_type'      => $this->post_type,
      'post_status'    => 'publish',
      
      // Optimizing query
      'cache_results' => false,
      'no_found_rows' => true,
      'fields'        => 'ids',
    );

    // Get the menus
    $menus = get_posts($args);

    // Loop menus
    if (!empty($menus)) :
      foreach ($menus as $menu) :
        $users = get_field('apply_to', $menu);

        // Added role support
        $roles    = get_field('roles', $menu);
        $userRole = $this->getCurrentUserRole();
    
        // If menu exists but is deactivated
        if (get_field('activated', $menu)) :

          // Check if user is of role
          if (($roles) && array_search($userRole, $roles) !== false) {
            $this->activeMenu = $menu;
          }

          else if ($users) {
            foreach ($users as $userToApply) {
              $found = array_search($user->user_email, $userToApply);
              if ($found) $this->activeMenu = $menu;
            }
          }
    
        // end if activated
        endif;
      endforeach;

    // End if
    endif;

    // if has menu modifier
    if ($this->activeMenu) :

      // Make new menu array available
      $getMenu = $this->makeLists($this->activeMenu, false);
      $newMenu = $this->lists['available'];
      //var_dump($this->activeMenu);

      // Sets as global menu
      unset($GLOBALS['menu']);
      $GLOBALS['menu'] = $newMenu;

      // Make new submenu
      $this->newSubmenu = $this->makeSubmenu($this->activeMenu);

      // Rename, relink and hide submenus
      $newSubmenu = $this->reStuffSubmenus($this->newSubmenu);

      // Resets our submenus as weel
      unset($GLOBALS['submenu']);
      $GLOBALS['submenu'] = $newSubmenu;

      //var_dump($GLOBALS['menu']);

      // Rename whats to rename
      $newMenu = $this->renameMenus($newMenu);

    endif;
  }

  /**
   * Rename Menu
   */
  public function renameMenus($menu) {
    if ($menu) :
      // Rename
      foreach ($menu as $menu) :
    
        // Rename Menu item
        if (isset($menu['rename']) && $menu['rename'] !== '') {
          
          // Rename
          rename_admin_menu_section($menu[0], $menu['rename']); 
          
          // Now that we renamed, we need to refresh this menu indentificator
          $menu[0] = $menu['rename'];
          
        }
    
        // Change Icon
        if (isset($menu['icon']) && $menu['icon'] !== '')
          change_icon($menu[0], $menu['icon']);
    
        //var_dump($menu);
    
      endforeach;
    endif;
    return $menu;
  }

  /**
   * Rename, relink and hide submenus
   */
  public function reStuffSubmenus($submenu) {

    if ($submenu) :
      // Rename
      foreach ($submenu as &$block) {
        foreach ($block as $id => &$item) {

          // Rename
          if (isset($item['rename']) && $item['rename'] !== '') {
            $item[0] = $item['rename'];
          }

          // Relink
          if (isset($item['link']) && $item['link'] !== '') {
            $item[2] = $item['link'];
          }

          // Hide
          if (isset($item['hide']) && $item['hide'] == 'on') {
            unset($block[$id]);
          }

        }
      }
    endif;
    //var_dump($submenu);
    return $submenu;
  }

  /**
   * Get Options Plugin
   */
  public function getMeta($postID, $option) {
    //delete_post_meta($postID, $this->options_slug.$option);
    return get_post_meta($postID, $this->options_slug.$option, true);
  }

  public function saveMeta($postID, $option, $value) {
    //delete_post_meta($postID, $this->options_slug.$option);
    return update_post_meta($postID, $this->options_slug.$option, $value);
  }

  /**
   * Specifics Gets Meta
   */
  public function getMenu($postID) {
    return $this->getMeta($postID, 'menu');
  }

  public function getDisabled($postID) {
    return $this->getMeta($postID, 'disabled');
  }

  public function getSubmenu($postID) {
    return $this->getMeta($postID, 'submenu');
  }

  // To end, order the arrays
  public function reOrder($a, $b) {
    return $a["order"] - $b["order"];
  }

  // Check if has submenu
  public function hasItemSubmenus($id) {
    return isset($this->newSubmenu[$id]);
  }

  // item submenu
  public function getItemSubmenus($id) {
    if (isset($this->newSubmenu[$id])) {
      // Set Submenu
      $submenu  = $this->newSubmenu[$id];
      // Include view
      //var_dump($submenu);
      $newOrder = 0;
      foreach ($submenu as $order => $submenu) {

        // Add Aditional offsets
        if (!isset($submenu['rename'])) $submenu['rename'] = '';
        if (!isset($submenu['link'])) $submenu['link'] = '';
        if (!isset($submenu['hide'])) $submenu['hide'] = '';

        include $this->get_plugin_path()."/views/submenu-item.php";
        $newOrder++;
      }
    } else return false;
  }

  /**
   * Dashicons handler for especial icons, like woocommerce and so on
   * Also removing impossible icons
   */
  function handleIcon($item) {
    
    // Replace it for something we can effectivily display
    if (strpos($item[6], 'dashicons') === false || $item[6] == 'dashicons-admin-generic') {
      $item[6] = 'dashicons-dismiss';
    }
    
    // return item object
    return $item;
    
  }
  
  /**
   * Effectivelly make the menu list that iis used everywhere in the plugin
   */
  public function makeLists($menuID, $addIcon = true) {

    // Get our Menu Backup & settings
    $menu      = $this->menu;
    $available = $this->getMenu($menuID);
    $disabled  = $this->getDisabled($menuID);

    // Final menu
    $lists     = array('available' => array(), 'disabled' => array());

    // Make Disabled List
    if ($disabled) :
      // Loop
      foreach ($disabled as $item) :

        $menu[$item['order']]['separator'] = false;
        $menu[$item['order']]['rename']    = '';
        $menu[$item['order']]['icon']      = '';

        // Separator Exception
        if ($menu[$item['order']][0] === '') {
          $menu[$item['order']][0]           = __('Separator', $this->text_domain);
          $menu[$item['order']][6]           = 'dashicons-editor-insertmore';
          $menu[$item['order']]['separator'] = true;
        }

        // Check for impossible dashicons
        if ($addIcon) {
          $menu[$item['order']] = $this->handleIcon( $menu[$item['order']] );
        }

        $lists['disabled'][] = $item + $menu[$item['order']];

        // Unset this disabled from menu
        unset($menu[$item['order']]);

      endforeach;
      // End Loop

    endif;

      $menuIndexes = 0;

      // Loop
      if ($available) :
        foreach ($available as $item) :

          // Checks if menu original exists
          if (isset($menu[$item['order']])) :

          $menu[$item['order']]['separator'] = false;
          $menu[$item['order']]['rename']    = '';
          $menu[$item['order']]['icon']      = '';

            // Separator Exception
            if ($menu[$item['order']][0] === '') {
              $menu[$item['order']][0]           = __('Separator', $this->text_domain);
              $menu[$item['order']][6]           = 'dashicons-editor-insertmore';
              $menu[$item['order']]['separator'] = true;
            }

            // Check for impossible dashicons
            if ($addIcon) {
              $menu[$item['order']] = $this->handleIcon( $menu[$item['order']] );
            }

            $menuIndexes = $menuIndexes + 10;
            $lists['available'][$menuIndexes] = $item + $menu[$item['order']];

            // Put flag on the base $menu used as reference to flag them as looped
            // so we can check if new items were added to the menu with the installation of new plugins.
            $menu[$item['order']]['looped'] = true;

          endif;

        endforeach;
        // End Loop
      endif;

      // Now we have to loop the adiitonal items and plugins that the user
      // can and WILL add after
      foreach ($menu as $order => &$item) :
    
        // if it was not looped already
        if (!isset($item['looped']) && is_array($item)) {
          // var_dump($item);
          
          $item['rename']    = '';
          $item['icon']      = '';
          $item['order']     = $order;
          $item['id']        = $item[2];
          $item['separator'] = false;

          // Separator Exception
          if ($item[0] === '') {
            $item[0]           = __('Separator', $this->text_domain);
            $item[6]           = 'dashicons-editor-insertmore';
            $item['separator'] = true;
          }

          // Check for impossible dashicons
          if ($addIcon) {
            $item = $this->handleIcon( $item );
          }

          $item['looped'] = true;

          // Adds
          // var_dump($order);
          $order = ((int) $order) + rand(1,9);
          
          // Add, if not separator
          // Separator Exception
          if ($item[0] === __('Separator', $this->text_domain)) {}
          else {$lists['available'][$order] = $item;}
          
        }
      endforeach;

    // Add to the global scope
    $this->lists = $lists;
    // var_dump($lists['available']);
  }

  /**
   * Get Submenus
   */
  public function getSubMenus($parent) {
    $submenus = $GLOBALS['submenu'];
  }

  /**
   * Make available Menus List
   */
  public function makeAvailableList($menuID, $type = 'available') {

    // Get Menu
    $available = $this->lists['available'];
    //var_dump($available);

    // Make Loop
    $newOrder = 0;

    foreach ($available as $menu) {
      // Get Template
      $menuID = $menu['order'];
      include $this->get_plugin_path()."/views/menu-item.php";
      // Increase NewOrder
      $newOrder++;
    }
  }

  /**
   * Make available Menus List
   */
  public function makeDisabledList($menuID) {

    // Get Menu
    $available = $this->lists['available'];
    $disabled = $this->lists['disabled'];
    //var_dump($disabled);

    // Make Loop
    $newOrder = count($available) + 2;

    foreach ($disabled as $menu) {
      // Get Template
      $menuID = $menu['order'];
      include $this->get_plugin_path()."/views/menu-item.php";
      // Increase NewOrder
      $newOrder++;
    }
  }

  /**
   * Handles Save da Metabox
   */
  public function saveMetaData($postID, $post) {
   
    // Checks save status
    $is_autosave = wp_is_post_autosave($postID);
    $is_revision = wp_is_post_revision($postID);
    $is_valid_nonce = (isset($_POST['amm_nonce']) && wp_verify_nonce($_POST['amm_nonce'], $this->options_slug)) ? 'true' : 'false';
 
    // Exits script depending on save status
    if ($post->post_type !== $this->post_type || $is_autosave || $is_revision || !$is_valid_nonce ) {
      return;
    }

    // Submenus
    if (isset($_POST['submenu'])) {

      // Reorder submenu post
      $postSubmenus = array_map('array_values', $_POST['submenu']);

      // Save Active Menus
      $this->saveMeta($postID, 'submenu', $postSubmenus);
    }

    // Checks for input and sanitizes/saves if needed
    if (isset($_POST['menu'])) {
      //var_dump($_POST['menu']);

      // Set Post Globaly
      $this->postMenu = $_POST['menu'];

      // CUTTING OF DISABLED ONES
      $whereToCut = array_search("amm-separator", array_keys($this->postMenu));

      // Split
      $disabled = array_slice($this->postMenu, $whereToCut);
      // Makes final menu minus disabled
      unset($this->postMenu['amm-separator']);

      // Take amm-separator off
      array_shift($disabled);

      //var_dump($this->menu);
      //var_dump($disabled);

      // Save Active Menus
      $this->saveMeta($postID, 'menu', $this->postMenu);
      // Save Disabled Menus
      $this->saveMeta($postID, 'disabled', $disabled);
    }
   
  }

  /**
   * Loads Metaboxes
   */
  public function addAMMMetaboxes() {
    add_meta_box('amm_metabox', __('Manage Menus', $this->text_domain), array($this, 'renderAMMMetabox'), $this->post_type, 'normal', 'low');
  }

  /**
   * Create Notice only for edit pages
   */
  public function addAdminNotice() {
    if (isset($_GET['post_type']) && $_GET['post_type'] == $this->post_type && !$this->whitelabel)
      add_action('admin_notices', array($this, 'AdminNoticeMsg'));
  }

  /*
   * Loads Admin Message
   */
  public function AdminNoticeMsg() {
    require_once $this->get_plugin_path()."/views/admin-notice.php";
  }
  
  /*
   * Render Metabox
   */
  public function renderAMMMetabox($post) {
    // Make Lists
    $this->makeLists($post->ID);
    $this->newSubmenu = $this->makeSubmenu($post->ID);
    //var_dump($this->newSubmenu);
    require_once $this->get_plugin_path()."/views/amm-metabox.php";
  }

  /**
   * Register AMM custom post Type
   */
  public function addPostType() {

    $labels = array(
      'name'               => _x( 'Custom Admin Menus', 'post type general name', $this->text_domain),
      'singular_name'      => _x( 'Custom Admin Menu', 'post type singular name', $this->text_domain),
      'menu_name'          => _x( 'Menus', 'admin menu', $this->text_domain),
      'name_admin_bar'     => _x( 'Menu', 'add new on admin bar', $this->text_domain),
      'add_new'            => _x( 'Create new Menu Setup', 'book', $this->text_domain),
      'add_new_item'       => __( 'Create new Menu Setup', $this->text_domain),
      'new_item'           => __( 'New Menu Setup', $this->text_domain),
      'edit_item'          => __( 'Edit Menu Setup', $this->text_domain),
      'view_item'          => __( 'View Menu Setups', $this->text_domain),
      'all_items'          => __( 'Menu Setups', $this->text_domain),
      'search_items'       => __( 'Search Menu Setups', $this->text_domain),
      'parent_item_colon'  => __( 'Parent Menu Setups:', $this->text_domain),
      'not_found'          => __( 'No menu setups found.', $this->text_domain),
      'not_found_in_trash' => __( 'No menu setups found in Trash.', $this->text_domain)
    );

    $args = array(
      'labels'             => $labels,
      'public'             => false,
      'publicly_queryable' => false,
      'show_ui'            => true,
      'show_in_menu'       => 'wpamm',
      'query_var'          => true,
      'rewrite'            => array('slug' => 'amm'),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'supports'           => array('title', 'amm_metabox')
    );

    register_post_type($this->post_type, $args);

    // Adds Custom Post Type
    require_once $this->get_plugin_path()."/lib/acf-custom-fields.php";
  }

  /**
   * Add Admin Page and Submenus
   */
  public function addIndexPage() {
    // Adds admin page
    $this->pluginPages[] = add_menu_page(__('WP Admin Menu Manager', $this->text_domain), __('Manage Menus', $this->text_domain), 'remove_users', $this->slug, array($this, 'renderIndexView'), 'dashicons-menu', 10090.09);
  }

  /**
   * Native scripts used to make widgets JS work
   */
  public function enqueueDeafaultScripts() {
    wp_enqueue_script('admin-widgets');

    // If Mobile Version, adds Punch
    if (wp_is_mobile()) wp_enqueue_script('jquery-touch-punch');
  }
  
  /**
   * Adds custom role ACF field
   */
  public function addACFRoleField() {
    // Check if extension already exists
    if (!class_exists('acf_field_role_selector')) {
      include_once($this->get_plugin_path().'/includes/acf-role/acf-role_selector-v5.php');  
    }
  }
  
  /**
   * Created our custom columns
   */
  function wpammColumns($columns) {
	$columns = array(
		'cb'	 	=> '<input type="checkbox" />',
		'title' 	=> __('Title', $this->text_domain),
		'roles' 	=> __('Roles under Effect', $this->text_domain),
		'users'	    => __('Users under Effect', $this->text_domain),
        'activated'	=> __('Activated?', $this->text_domain),
		'date'		=> __('Date', $this->text_domain),
	);
	return $columns;
  }

  /**
   * Display the custom values of our custom columns
   */
  public function wpammColumnsValues($column) {
	global $post;
  
    /**
     * Display Roles under effect
     */
	if ($column == 'roles') {
      
      // Display roles
      $rolesList = "";
      
      // Make list of roles under effect
      $roles = get_field('roles');
      
      // Null or false
      if (!$roles) _e('No roles under effect.');
      
      // add role
      else {
        foreach($roles as $role) {
          $role = ucfirst($role);
          $rolesList .= (empty($rolesList)) ? $role : ", $role";
        }
      }
      
      // Display list
      echo $rolesList;
	}
    
    else if ($column == 'users') {
      
      // Display users
      $userList = "";
      
      // Make list of users under effect
      $users = get_field('apply_to');
      //var_dump($users);
      
      // Null or false
      if (!$users) _e('No <strong>specific</strong> users under effect.');
      
      // add role
      else {
        foreach($users as $user) {
          $user = $user['display_name'];
          $userList .= (empty($userList)) ? $user : ", $user";
        }
      }
      
      // Display list
      echo $userList;
      
	}
    
    else if ($column == 'activated') {
      // Make list of roles under effect
      $activated = get_field('activated');
      if ($activated) _e('Yes', $this->text_domain);
      else            _e('No', $this->text_domain);
	}
    
  }
  
  /**
   * Adds activated column to the sortable ones
   */
  function wpammAddSortableColumns($columns) {
	$columns['activated'] = 'activated';
	return $columns;
  }
  
  /**
   * Adds out custom export link
   */
  function addExportLink($actions, $post) {
    
    // Add Export button if is amm
    if ($post->post_type == $this->post_type) {
      
      // Vars
      $label = __('Export', $this->text_domain);
      $link  = admin_url("edit.php?post_type={$this->post_type}&{$this->slug}_export={$post->ID}");
      
      // Adds our link
      $actions['export'] = "<a href='{$link}'>{$label}</a>"; 
      
    }
    
    // Return
	return $actions;
  }
  
  /**
   * Export the only menu passind its ID.
   */
  public function addExporterAction() {
    
    // Check if request exists
    if (isset($_GET["{$this->slug}_export"])) {
      include_once($this->get_plugin_path().'/lib/export.php');
      
      // Run function and pass the ID
      wpamm_export_wp($_GET["{$this->slug}_export"]);
      exit;
    }
    
  }

  /**
   * Place code for your plugin's functionality here.
   */
  public function runPlugin() {

    // Adds Exporter Action
    add_action('load-edit.php', array($this, 'addExporterAction'));
    
    // Custom Columns pra fields
    add_action("manage_{$this->post_type}_posts_custom_column", array($this, 'wpammColumnsValues'));
    add_filter("manage_edit-{$this->post_type}_columns", array($this, 'wpammColumns'));
    add_filter("manage_edit-{$this->post_type}_sortable_columns", array($this, 'wpammAddSortableColumns'));
    add_filter("post_row_actions", array($this, 'addExportLink'), 10, 2);
    
    // Hooks ACF Role Field
    add_action('init', array($this, 'addACFRoleField'), 0);

    // Effectvilly changes the final menu
    add_action('admin_menu', array($this, 'changeMenu'), 1000000);

    // Adds Index admin page
    add_action('admin_menu', array($this, 'addIndexPage'));

     // Adds Custom Post Type
    add_action('init', array($this, 'addPostType'));

    // Adds Admin Notice
    add_action('load-edit.php', array($this, 'addAdminNotice'));

    // Add Metboxes
    add_action('add_meta_boxes', array($this, 'addAMMMetaboxes'));

    // Loads Drag and Drop Scripts
    add_action('admin_enqueue_scripts', array($this, 'enqueueDeafaultScripts'));

    // handles data
    add_action('save_post', array($this, 'saveMetaData'), 10, 2);
  }

}

$wpAdminMenu = wpAdminMenu::get_instance();
