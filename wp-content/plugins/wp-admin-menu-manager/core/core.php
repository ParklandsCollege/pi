<?php

if (!class_exists('PluginCore732')) {

  class PluginCore732 {

    public $plugin_path;
    public $plugin_url;
    public $plugin_info;
    public $text_domain;
    public $addACF;

    // Carries where to add the header and footer
    public $pluginPages = array();
    public $aboutPage;

    // Carries 
    public $footerMenu;

    // Set if header is Expanded or not
    public $expandHeader = false;

    // Set if we should whitelabel this plugin
    public $whitelabel   = false;
    
    /**
     * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
     */
    public function __construct() {

      $this->plugin_path = plugin_dir_path($this->file);
      $this->plugin_url  = plugin_dir_url($this->file);

      load_plugin_textdomain($this->text_domain, false, $this->plugin_path . '/lang');

      add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
      add_action('admin_enqueue_scripts', array($this, 'register_styles'));

      // add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
      // add_action('wp_enqueue_scripts', array($this, 'register_styles'));

      register_activation_hook($this->file, array($this, 'activation'));
      register_deactivation_hook($this->file, array($this, 'deactivation'));
      add_action('activated_plugin', array($this, 'redirectAbout'));

      add_action('init', array($this, 'runCore'), -150);
      add_action('init', array($this, 'runPlugin'), -100);

      // Run plugin core setups
      // $this->runCore();

      // Run the plugin specifics
      // $this->runPlugin();
    }

    /**
     * Place code that runs at plugin activation here.
     */
    public function redirectAbout($plugin) {
      if ($plugin == plugin_basename($this->file) && !$this->whitelabel) {
        wp_redirect(admin_url("/admin.php?page={$this->slug}-about"), 301);
        exit;
      }
    }

    /**
     * Place code that runs at plugin activation here.
     */
    public function activation() {}

    /**
     * Place code that runs at plugin deactivation here.
     */
    public function deactivation() {}

    /**
     * Used to get the plugin url
     */
    public function get_plugin_url() {
      return $this->plugin_url;
    }

    /**
     * Used to get the plugin path
     */
    public function get_plugin_path() {
      return $this->plugin_path;
    }

    /**
     * Return full RUL relative to some dir in assets
     */
    public function getAsset($asset, $assetsDir = 'img') {
      return $this->get_plugin_url().'assets/'.$assetsDir.'/'.$asset;
    }

    /**
     * Used to get info directly retrieved from the plugin header
     */
    public function get_plugin_info($info) {
      $plugin_info = get_plugin_data($this->file);
      return $plugin_info[$info];
    }

    /**
     * Enqueue and register JavaScript files here.
     */
    public function register_scripts() {}

    /**
     * Enqueue and register CSS files here.
     */
    public function register_styles() {}

    /**
     * Adds commom about pages
     */
    public function addAboutPage() {
      // Adds admin page
      $aboutPage           = add_submenu_page($this->slug, __('About', $this->text_domain), __('About', $this->text_domain), 'manage_options', $this->slug.'-about', array($this, 'renderAboutPage'));
      $this->aboutPage     = $aboutPage;
      $this->pluginPages[] = $aboutPage;
    }

    /**
     * Adds commom about pages
     */
    public function renderAboutPage() {
      require_once $this->get_plugin_path().'/views/about.php';
    }

    /**
     * Create the plugins custom Footer
     */
    public function createFooterMenu() {
      // Menu carrier
      $footerMenu = array(
        // link => name
        $this->get_plugin_info('Name') . ' ' . $this->get_plugin_info('Version'),
        $this->get_plugin_info('PluginURI') => __('Get Support', $this->text_domain),
      );

      // Apply filters
      $this->footerMenu = apply_filters('add_footer_menu_732', $footerMenu);
    }

    /**
     * Load the plugins custom Header
     */
    public function addHeader() {
      require_once $this->get_plugin_path().'/views/header.php';
    }

    /**
     * Load the plugins custom Footer
     */
    public function addFooter() {
      require_once $this->get_plugin_path().'/views/footer.php';
    }

    /**
     * Add our custom classes to the admin body tag
     */
    public function addAdminBodyClasses($classes) {
      return "$classes plugin-page-732 plugin-{$this->slug}-732";
    }

    /**
     * Add classes and branding on custom post type pages
     */
    public function addBrandingPostTypePages() {
      // Check fot our post type
      $screen = get_current_screen();
      if ($screen->post_type === $this->post_type) {
        // Add Classes to the admin body
        add_filter('admin_body_class', array($this, 'addAdminBodyClasses'));
        add_action("load-edit.php", array($this, 'addBranding'));
      }
    }

    /**
     * Expand Header
     */
    public function expandHeader() {
      $this->expandHeader = true;
    }

    /**
     * Adds Header, Footer and contextual Help when needed
     */
    public function addBranding() {

      // Body Classes
      add_filter('admin_body_class', array($this, 'addAdminBodyClasses'));

      // Add Header
      add_action('admin_notices', array($this, 'addHeader'));

      // Add Footer
      add_action('in_admin_footer', array($this, 'addFooter'));

      // Mount our tab contents
      ob_start();
      include $this->get_plugin_path().'/views/tab-support.php';
      $tabSupport = ob_get_contents();
      ob_end_clean();

      ob_start();
      include $this->get_plugin_path().'/views/tab-rate.php';
      $tabRate = ob_get_contents();
      ob_end_clean();

      // Get our current screen to check for our slug
      $screen = get_current_screen();

      // Add my_help_tab if current screen is My Admin Page
      $screen->add_help_tab(array(
        'id'      => 'get-support',
        'title'   => __('Get Support', $this->text_domain),
        'content' => $tabSupport,
      ));

      $screen->add_help_tab(array(
        'id'      => 'rate-our-plugin',
        'title'   => __('Rate our Plugin', $this->text_domain),
        'content' => $tabRate,
      ));
    }

    /**
     * Decides when to load header, footer and help
     */
    public function getBranding() {

      // Adds our custom header and footer
      foreach ($this->pluginPages as $pageSlug) {
        add_action("load-{$pageSlug}", array($this, 'addBranding'));
      }

      // Expand header in case of the about page
      add_action("load-{$this->aboutPage}", array($this, 'expandHeader'));

      // Adds in case of custom post type
      if (property_exists($this, 'post_type')) {
        // adds aditional hooks when a custom post type exists
        add_action("current_screen", array($this, 'addBrandingPostTypePages'));
      }

    }

    /**
     * Dev Hooks
     */
    public function devHooks() {
      // Run filter that may set whitelable to true
      $this->whitelabel = apply_filters("{$this->slug}/settings/whitelabel", $this->whitelabel);
    }

    /**
     * Loads header and etc
     */
    public function branding() {

      if (!$this->whitelabel) {
        // Adds About Page
        add_action('admin_menu', array($this, 'addAboutPage'), 9999);

        // Adds Header, footer and Help
        add_action('admin_menu', array($this, 'getBranding'), 999999);
      }

    }

    /*
     *
     * ACF Include and Setup
     *
     */
    public function ACFSettingsPath($path) {
      // update path
      return $this->get_plugin_path() . 'includes/acf/';
    }

    public function ACFSettingsDir($dir) {
      return $this->get_plugin_url() . 'includes/acf/';
    }

    /**
     * Add Advanced Custom Fields if needed
     */
    public function addACF() {

      // Check if ACF exists
      if (!class_exists('acf') && $this->addACF){

        // 1. customize ACF path
        add_filter('acf/settings/path', array($this, 'ACFSettingsPath'));

        // 2. customize ACF dir
        add_filter('acf/settings/dir', array($this, 'ACFSettingsDir'));

        // 3. Hide ACF field group menu item
        add_filter('acf/settings/show_admin', '__return_false');

        // Include ACF
        include_once $this->get_plugin_path().'/includes/acf/acf.php';
      }
      
    }

    /**
     * Run Automatic things relative to the PluginCore
     */
    public function runCore() {

      // Include ACF v5, if needed
      add_action('init', array($this, 'addACF'), 0);

      // Run development hooks
      add_action('init', array($this, 'devHooks'));

      // Adds Branding
      add_action('init', array($this, 'branding'));

    }

    /**
     * RunPlugin, it's only here because it needs to be. You should overwrite this in your plugin
     */
    public function runPlugin() {}
  }

}