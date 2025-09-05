<?php
/**
 * Plugin Name: WebAPP
 * Plugin URI: https://sawahsolutions.com/plugins/webapp
 * Description: Transform your WordPress website into a modern Progressive Web App with multiple themes, dark/light modes, and app-like experience. Features 5 beautiful themes, PWA capabilities, offline support, and mobile-first design.
 * Version: 1.0.0
 * Author: Mohamed Sawah
 * Author URI: https://sawahsolutions.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: webapp
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * 
 * @package WebAPP
 * @author Mohamed Sawah
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// Define plugin constants
define('WEBAPP_VERSION', '1.0.0');
define('WEBAPP_PLUGIN_FILE', __FILE__);
define('WEBAPP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WEBAPP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WEBAPP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WEBAPP_PLUGIN_DIR', dirname(WEBAPP_PLUGIN_BASENAME));

// Minimum requirements check
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>WebAPP:</strong> This plugin requires PHP 7.4 or higher. You are running PHP ' . PHP_VERSION . '</p></div>';
    });
    return;
}

if (version_compare(get_bloginfo('version'), '5.0', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>WebAPP:</strong> This plugin requires WordPress 5.0 or higher. Please update WordPress.</p></div>';
    });
    return;
}

/**
 * Main WebAPP Plugin Class
 * 
 * @package WebAPP
 * @since 1.0.0
 */
final class WebAPP {
    
    /**
     * Plugin instance
     * 
     * @var WebAPP
     */
    private static $instance = null;
    
    /**
     * Plugin components
     * 
     * @var array
     */
    private $components = array();
    
    /**
     * Get plugin instance
     * 
     * @return WebAPP
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'), 0);
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Uninstall hook
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('webapp', false, WEBAPP_PLUGIN_DIR . '/languages/');
        
        // Load dependencies
        $this->load_dependencies();
        
        // Initialize hooks
        $this->init_hooks();
        
        // Initialize components
        $this->init_components();
        
        // Plugin is now fully loaded
        do_action('webapp_loaded');
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once WEBAPP_PLUGIN_PATH . 'includes/class-admin.php';
        require_once WEBAPP_PLUGIN_PATH . 'includes/class-frontend.php';
        require_once WEBAPP_PLUGIN_PATH . 'includes/class-pwa-manager.php';
        require_once WEBAPP_PLUGIN_PATH . 'includes/class-theme-manager.php';
        require_once WEBAPP_PLUGIN_PATH . 'includes/class-install-banner.php';
        require_once WEBAPP_PLUGIN_PATH . 'includes/class-settings.php';
        
        // Helper functions
        if (file_exists(WEBAPP_PLUGIN_PATH . 'includes/functions.php')) {
            require_once WEBAPP_PLUGIN_PATH . 'includes/functions.php';
        }
        
        // Third-party integrations
        if (file_exists(WEBAPP_PLUGIN_PATH . 'includes/integrations/')) {
            $integrations = glob(WEBAPP_PLUGIN_PATH . 'includes/integrations/*.php');
            foreach ($integrations as $integration) {
                require_once $integration;
            }
        }
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        // Initialize admin component
        if (is_admin()) {
            $this->components['admin'] = new WEBAPP_Admin();
        }
        
        // Initialize frontend component
        if (!is_admin() || wp_doing_ajax()) {
            $this->components['frontend'] = new WEBAPP_Frontend();
        }
        
        // Initialize PWA manager
        $this->components['pwa'] = new WEBAPP_PWA_Manager();
        
        // Initialize theme manager
        $this->components['theme'] = new WEBAPP_Theme_Manager();
        
        // Initialize install banner
        $this->components['banner'] = new WEBAPP_Install_Banner();
        
        // Initialize settings
        $this->components['settings'] = new WEBAPP_Settings();
        
        // Allow other plugins to add components
        $this->components = apply_filters('webapp_components', $this->components);
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Plugin action links
        add_filter('plugin_action_links_' . WEBAPP_PLUGIN_BASENAME, array($this, 'add_plugin_action_links'));
        add_filter('plugin_row_meta', array($this, 'add_plugin_row_meta'), 10, 2);
        
        // Rewrite rules for PWA
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // AJAX handlers
        add_action('wp_ajax_webapp_get_posts', array($this, 'ajax_get_posts'));
        add_action('wp_ajax_nopriv_webapp_get_posts', array($this, 'ajax_get_posts'));
        add_action('wp_ajax_webapp_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_webapp_reset_settings', array($this, 'ajax_reset_settings'));
        add_action('wp_ajax_webapp_export_settings', array($this, 'ajax_export_settings'));
        add_action('wp_ajax_webapp_import_settings', array($this, 'ajax_import_settings'));
        
        // REST API endpoints
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Cron hooks
        add_action('webapp_daily_cleanup', array($this, 'daily_cleanup'));
        
        // Plugin update checks
        add_action('admin_init', array($this, 'check_plugin_updates'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu page
        $main_page = add_menu_page(
            __('WebAPP', 'webapp'),
            __('WebAPP', 'webapp'),
            'manage_options',
            'webapp-settings',
            array($this, 'admin_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L13.09 8.26L22 9L13.09 9.74L12 16L10.91 9.74L2 9L10.91 8.26L12 2Z"/></svg>'),
            30
        );
        
        // Add submenu pages
        add_submenu_page(
            'webapp-settings',
            __('Dashboard', 'webapp'),
            __('Dashboard', 'webapp'),
            'manage_options',
            'webapp-settings'
        );
        
        add_submenu_page(
            'webapp-settings',
            __('Themes', 'webapp'),
            __('Themes', 'webapp'),
            'manage_options',
            'webapp-themes',
            array($this, 'themes_page')
        );
        
        add_submenu_page(
            'webapp-settings',
            __('PWA Settings', 'webapp'),
            __('PWA Settings', 'webapp'),
            'manage_options',
            'webapp-pwa',
            array($this, 'pwa_page')
        );
        
        add_submenu_page(
            'webapp-settings',
            __('Analytics', 'webapp'),
            __('Analytics', 'webapp'),
            'manage_options',
            'webapp-analytics',
            array($this, 'analytics_page')
        );
        
        add_submenu_page(
            'webapp-settings',
            __('Help & Support', 'webapp'),
            __('Help & Support', 'webapp'),
            'manage_options',
            'webapp-help',
            array($this, 'help_page')
        );
        
        // Add styles for the main admin page
        add_action('load-' . $main_page, array($this, 'admin_page_load'));
    }
    
    /**
     * Admin page templates
     */
    public function admin_page() {
        $this->load_template('admin-page');
    }
    
    public function themes_page() {
        $this->load_template('themes-page');
    }
    
    public function pwa_page() {
        $this->load_template('pwa-page');
    }
    
    public function analytics_page() {
        $this->load_template('analytics-page');
    }
    
    public function help_page() {
        $this->load_template('help-page');
    }
    
    /**
     * Load template file
     */
    private function load_template($template) {
        $template_file = WEBAPP_PLUGIN_PATH . 'templates/' . $template . '.php';
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            echo '<div class="wrap"><h1>Template not found: ' . esc_html($template) . '</h1></div>';
        }
    }
    
    /**
     * Admin page load hook
     */
    public function admin_page_load() {
        // Add help tabs
        $screen = get_current_screen();
        
        $screen->add_help_tab(array(
            'id' => 'webapp-overview',
            'title' => __('Overview', 'webapp'),
            'content' => '<p>' . __('WebAPP transforms your WordPress site into a modern Progressive Web App with mobile-first design and app-like features.', 'webapp') . '</p>'
        ));
        
        $screen->add_help_tab(array(
            'id' => 'webapp-features',
            'title' => __('Features', 'webapp'),
            'content' => '<ul><li>' . __('5 beautiful themes with light/dark modes', 'webapp') . '</li><li>' . __('PWA capabilities with offline support', 'webapp') . '</li><li>' . __('Mobile-first responsive design', 'webapp') . '</li><li>' . __('App install prompts', 'webapp') . '</li></ul>'
        ));
        
        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:', 'webapp') . '</strong></p>' .
            '<p><a href="https://sawahsolutions.com/docs/webapp" target="_blank">' . __('Documentation', 'webapp') . '</a></p>' .
            '<p><a href="https://sawahsolutions.com/support" target="_blank">' . __('Support', 'webapp') . '</a></p>'
        );
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        if (!get_option('webapp_enabled', 0)) {
            return;
        }
        
        // Main frontend styles
        wp_enqueue_style(
            'webapp-frontend',
            WEBAPP_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WEBAPP_VERSION
        );
        
        // Theme-specific styles
        $theme = get_option('webapp_theme', 'modern');
        $theme_file = WEBAPP_PLUGIN_PATH . 'assets/css/themes/' . $theme . '.css';
        if (file_exists($theme_file)) {
            wp_enqueue_style(
                'webapp-theme-' . $theme,
                WEBAPP_PLUGIN_URL . 'assets/css/themes/' . $theme . '.css',
                array('webapp-frontend'),
                WEBAPP_VERSION
            );
        }
        
        // Frontend JavaScript
        wp_enqueue_script(
            'webapp-frontend',
            WEBAPP_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            WEBAPP_VERSION,
            true
        );
        
        // Localize script with data
        wp_localize_script('webapp-frontend', 'webapp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url('webapp/v1/'),
            'nonce' => wp_create_nonce('webapp_nonce'),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'theme' => $theme,
            'dark_mode' => get_option('webapp_dark_mode', 0),
            'settings' => array(
                'header_enabled' => get_option('webapp_header_enabled', 1),
                'bottom_nav_enabled' => get_option('webapp_bottom_nav_enabled', 1),
                'search_enabled' => get_option('webapp_search_enabled', 1),
                'categories_enabled' => get_option('webapp_categories_enabled', 1),
                'notifications_enabled' => get_option('webapp_notifications_enabled', 1),
                'pwa_enabled' => get_option('webapp_pwa_enabled', 1),
                'install_banner' => get_option('webapp_install_banner', 1)
            ),
            'colors' => array(
                'primary' => get_option('webapp_primary_color', '#6366f1'),
                'secondary' => get_option('webapp_secondary_color', '#8b5cf6')
            ),
            'strings' => array(
                'loading' => __('Loading...', 'webapp'),
                'error' => __('Something went wrong. Please try again.', 'webapp'),
                'no_results' => __('No results found.', 'webapp'),
                'load_more' => __('Load More', 'webapp'),
                'search_placeholder' => __('Search articles, topics, authors...', 'webapp'),
                'install_app' => __('Install App', 'webapp'),
                'app_name' => get_option('webapp_app_name', get_bloginfo('name'))
            )
        ));
        
        // Add custom CSS if any
        $custom_css = get_option('webapp_custom_css', '');
        if (!empty($custom_css)) {
            wp_add_inline_style('webapp-frontend', wp_strip_all_tags($custom_css));
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook_suffix) {
        // Only load on WebAPP admin pages
        if (strpos($hook_suffix, 'webapp') === false) {
            return;
        }
        
        // Admin styles
        wp_enqueue_style(
            'webapp-admin',
            WEBAPP_PLUGIN_URL . 'assets/css/admin.css',
            array('wp-color-picker'),
            WEBAPP_VERSION
        );
        
        // Admin JavaScript
        wp_enqueue_script(
            'webapp-admin',
            WEBAPP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker', 'jquery-ui-sortable'),
            WEBAPP_VERSION,
            true
        );
        
        // Additional dependencies
        wp_enqueue_media();
        wp_enqueue_script('wp-color-picker');
        
        // Localize admin script
        wp_localize_script('webapp-admin', 'webapp_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url('webapp/v1/'),
            'nonce' => wp_create_nonce('webapp_admin_nonce'),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'plugin_url' => WEBAPP_PLUGIN_URL,
            'themes' => WEBAPP_Theme_Manager::get_available_themes(),
            'strings' => array(
                'save_success' => __('Settings saved successfully!', 'webapp'),
                'save_error' => __('Error saving settings. Please try again.', 'webapp'),
                'reset_confirm' => __('Are you sure you want to reset all settings? This cannot be undone.', 'webapp'),
                'unsaved_changes' => __('You have unsaved changes. Are you sure you want to leave?', 'webapp'),
                'import_success' => __('Settings imported successfully!', 'webapp'),
                'export_success' => __('Settings exported successfully!', 'webapp'),
                'invalid_file' => __('Invalid settings file.', 'webapp'),
                'processing' => __('Processing...', 'webapp')
            )
        ));
    }
    
    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=webapp-settings') . '">' . __('Settings', 'webapp') . '</a>',
            '<a href="' . admin_url('admin.php?page=webapp-themes') . '">' . __('Themes', 'webapp') . '</a>'
        );
        
        return array_merge($plugin_links, $links);
    }
    
    /**
     * Add plugin row meta
     */
    public function add_plugin_row_meta($links, $file) {
        if (WEBAPP_PLUGIN_BASENAME === $file) {
            $meta_links = array(
                '<a href="https://sawahsolutions.com/docs/webapp" target="_blank">' . __('Documentation', 'webapp') . '</a>',
                '<a href="https://sawahsolutions.com/support" target="_blank">' . __('Support', 'webapp') . '</a>',
                '<a href="https://github.com/sawahsolutions/webapp" target="_blank">' . __('GitHub', 'webapp') . '</a>'
            );
            
            return array_merge($links, $meta_links);
        }
        
        return $links;
    }
    
    /**
     * Add rewrite rules for PWA
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^manifest\.json$', 'index.php?webapp_manifest=1', 'top');
        add_rewrite_rule('^sw\.js$', 'index.php?webapp_service_worker=1', 'top');
        add_rewrite_rule('^offline\.html$', 'index.php?webapp_offline=1', 'top');
        add_rewrite_rule('^webapp-preview/?$', 'index.php?webapp_preview=1', 'top');
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'webapp_manifest';
        $vars[] = 'webapp_service_worker';
        $vars[] = 'webapp_offline';
        $vars[] = 'webapp_preview';
        return $vars;
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('webapp/v1', '/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_posts'),
            'permission_callback' => '__return_true',
            'args' => array(
                'page' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint'
                ),
                'per_page' => array(
                    'default' => 10,
                    'sanitize_callback' => 'absint'
                ),
                'category' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'search' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        register_rest_route('webapp/v1', '/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_settings'),
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ));
    }
    
    /**
     * AJAX: Get posts
     */
    public function ajax_get_posts() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'webapp_nonce')) {
            wp_die(__('Security check failed', 'webapp'));
        }
        
        $page = absint($_POST['page'] ?? 1);
        $category = sanitize_text_field($_POST['category'] ?? '');
        $search = sanitize_text_field($_POST['search'] ?? '');
        $posts_per_page = 10;
        
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_webapp_featured',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => '_webapp_featured',
                    'value' => '1',
                    'compare' => '!='
                )
            )
        );
        
        if (!empty($category)) {
            $args['category_name'] = $category;
        }
        
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $posts = get_posts($args);
        $response = array();
        
        foreach ($posts as $post) {
            $categories = get_the_category($post->ID);
            $category_names = array();
            foreach ($categories as $cat) {
                $category_names[] = $cat->name;
            }
            
            $response[] = array(
                'id' => $post->ID,
                'title' => get_the_title($post),
                'excerpt' => wp_trim_words(get_the_excerpt($post), 20),
                'permalink' => get_permalink($post),
                'thumbnail' => get_the_post_thumbnail_url($post, 'medium'),
                'author' => get_the_author_meta('display_name', $post->post_author),
                'date' => human_time_diff(get_the_time('U', $post), current_time('timestamp')) . ' ' . __('ago', 'webapp'),
                'categories' => $category_names,
                'likes' => WEBAPP_Frontend::get_like_count($post->ID),
                'comments' => get_comments_number($post->ID),
                'is_liked' => WEBAPP_Frontend::is_liked($post->ID),
                'is_bookmarked' => WEBAPP_Frontend::is_bookmarked($post->ID)
            );
        }
        
        wp_send_json_success(array(
            'posts' => $response,
            'has_more' => count($posts) === $posts_per_page,
            'total_pages' => ceil(wp_count_posts()->publish / $posts_per_page)
        ));
    }
    
    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        if (isset($this->components['admin'])) {
            $this->components['admin']->save_settings();
        } else {
            wp_send_json_error(__('Admin component not loaded', 'webapp'));
        }
    }
    
    /**
     * AJAX: Reset settings
     */
    public function ajax_reset_settings() {
        if (isset($this->components['admin'])) {
            $this->components['admin']->reset_settings();
        } else {
            wp_send_json_error(__('Admin component not loaded', 'webapp'));
        }
    }
    
    /**
     * AJAX: Export settings
     */
    public function ajax_export_settings() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'webapp_admin_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed', 'webapp'));
        }
        
        $settings = $this->get_all_settings();
        
        wp_send_json_success(array(
            'data' => $settings,
            'filename' => 'webapp-settings-' . date('Y-m-d') . '.json'
        ));
    }
    
    /**
     * AJAX: Import settings
     */
    public function ajax_import_settings() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'webapp_admin_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed', 'webapp'));
        }
        
        $settings_data = json_decode(stripslashes($_POST['settings']), true);
        
        if (!is_array($settings_data)) {
            wp_send_json_error(__('Invalid settings data', 'webapp'));
        }
        
        foreach ($settings_data as $key => $value) {
            if (strpos($key, 'webapp_') === 0) {
                update_option($key, $value);
            }
        }
        
        wp_send_json_success(__('Settings imported successfully!', 'webapp'));
    }
    
    /**
     * REST: Get posts
     */
    public function rest_get_posts($request) {
        $page = $request->get_param('page');
        $per_page = min($request->get_param('per_page'), 50); // Max 50 per page
        $category = $request->get_param('category');
        $search = $request->get_param('search');
        
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page
        );
        
        if (!empty($category)) {
            $args['category_name'] = $category;
        }
        
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $posts = get_posts($args);
        $response = array();
        
        foreach ($posts as $post) {
            $response[] = array(
                'id' => $post->ID,
                'title' => get_the_title($post),
                'excerpt' => get_the_excerpt($post),
                'permalink' => get_permalink($post),
                'thumbnail' => get_the_post_thumbnail_url($post, 'medium'),
                'author' => get_the_author_meta('display_name', $post->post_author),
                'date' => get_the_date('c', $post),
                'categories' => wp_get_post_categories($post->ID, array('fields' => 'names'))
            );
        }
        
        return rest_ensure_response($response);
    }
    
    /**
     * REST: Get settings
     */
    public function rest_get_settings() {
        return rest_ensure_response($this->get_all_settings());
    }
    
    /**
     * Get all plugin settings
     */
    private function get_all_settings() {
        $settings = array();
        $option_names = array(
            'webapp_enabled',
            'webapp_theme',
            'webapp_dark_mode',
            'webapp_install_banner',
            'webapp_pwa_enabled',
            'webapp_app_name',
            'webapp_app_description',
            'webapp_primary_color',
            'webapp_secondary_color',
            'webapp_custom_css',
            'webapp_header_enabled',
            'webapp_bottom_nav_enabled',
            'webapp_search_enabled',
            'webapp_categories_enabled',
            'webapp_notifications_enabled'
        );
        
        foreach ($option_names as $option) {
            $settings[$option] = get_option($option, '');
        }
        
        return $settings;
    }
    
    /**
     * Daily cleanup cron job
     */
    public function daily_cleanup() {
        // Clean up expired transients
        delete_expired_transients();
        
        // Clean up old analytics data (if any)
        $this->cleanup_old_analytics_data();
        
        // Optimize database tables
        $this->optimize_database_tables();
    }
    
    /**
     * Check for plugin updates
     */
    public function check_plugin_updates() {
        $current_version = get_option('webapp_version', '0.0.0');
        
        if (version_compare($current_version, WEBAPP_VERSION, '<')) {
            $this->run_updates($current_version, WEBAPP_VERSION);
            update_option('webapp_version', WEBAPP_VERSION);
        }
    }
    
    /**
     * Run update routines
     */
    private function run_updates($from_version, $to_version) {
        // Update routines based on version
        if (version_compare($from_version, '1.0.0', '<')) {
            $this->update_to_1_0_0();
        }
        
        // Flush rewrite rules after updates
        flush_rewrite_rules();
    }
    
    /**
     * Update to version 1.0.0
     */
    private function update_to_1_0_0() {
        // Initial setup for new installations
        $this->setup_default_settings();
        
        // Create necessary database tables if needed
        $this->create_database_tables();
        
        // Schedule cron jobs
        if (!wp_next_scheduled('webapp_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'webapp_daily_cleanup');
        }
    }
    
    /**
     * Setup default settings
     */
    private function setup_default_settings() {
        $defaults = array(
            'webapp_enabled' => 0,
            'webapp_theme' => 'modern',
            'webapp_dark_mode' => 0,
            'webapp_install_banner' => 1,
            'webapp_pwa_enabled' => 1,
            'webapp_app_name' => get_bloginfo('name'),
            'webapp_app_description' => get_bloginfo('description') ?: __('A modern web app experience', 'webapp'),
            'webapp_primary_color' => '#6366f1',
            'webapp_secondary_color' => '#8b5cf6',
            'webapp_custom_css' => '',
            'webapp_header_enabled' => 1,
            'webapp_bottom_nav_enabled' => 1,
            'webapp_search_enabled' => 1,
            'webapp_categories_enabled' => 1,
            'webapp_notifications_enabled' => 1,
            'webapp_first_activation' => current_time('mysql'),
            'webapp_version' => WEBAPP_VERSION
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
    
    /**
     * Create database tables if needed
     */
    private function create_database_tables() {
        global $wpdb;
        
        // Analytics table (for future use)
        $table_name = $wpdb->prefix . 'webapp_analytics';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            event_type varchar(50) NOT NULL,
            user_id bigint(20) DEFAULT 0,
            user_ip varchar(45) NOT NULL,
            user_agent text,
            event_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Cleanup old analytics data
     */
    private function cleanup_old_analytics_data() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'webapp_analytics';
        
        // Delete analytics data older than 90 days
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE created_at < %s",
            date('Y-m-d H:i:s', strtotime('-90 days'))
        ));
    }
    
    /**
     * Optimize database tables
     */
    private function optimize_database_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'webapp_analytics'
        );
        
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
                $wpdb->query("OPTIMIZE TABLE $table");
            }
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Check requirements
        if (!$this->check_requirements()) {
            deactivate_plugins(WEBAPP_PLUGIN_BASENAME);
            wp_die(__('WebAPP activation failed. Please check the requirements.', 'webapp'));
        }
        
        // Setup default settings
        $this->setup_default_settings();
        
        // Create database tables
        $this->create_database_tables();
        
        // Create uploads directory for app assets
        $this->create_upload_directories();
        
        // Schedule cron jobs
        if (!wp_next_scheduled('webapp_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'webapp_daily_cleanup');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag for welcome screen
        set_transient('webapp_activation_redirect', true, 30);
        
        // Log activation
        error_log('WebAPP Plugin Activated - Version: ' . WEBAPP_VERSION);
        
        // Fire activation hook
        do_action('webapp_activated');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Unschedule cron jobs
        wp_clear_scheduled_hook('webapp_daily_cleanup');
        
        // Clean up temporary data
        delete_transient('webapp_activation_redirect');
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear any cached data
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Log deactivation
        error_log('WebAPP Plugin Deactivated');
        
        // Fire deactivation hook
        do_action('webapp_deactivated');
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove all plugin options
        $options = array(
            'webapp_enabled',
            'webapp_theme',
            'webapp_dark_mode',
            'webapp_install_banner',
            'webapp_pwa_enabled',
            'webapp_app_name',
            'webapp_app_description',
            'webapp_primary_color',
            'webapp_secondary_color',
            'webapp_custom_css',
            'webapp_header_enabled',
            'webapp_bottom_nav_enabled',
            'webapp_search_enabled',
            'webapp_categories_enabled',
            'webapp_notifications_enabled',
            'webapp_first_activation',
            'webapp_version'
        );
        
        foreach ($options as $option) {
            delete_option($option);
        }
        
        // Remove user meta
        delete_metadata('user', 0, '_webapp_bookmarks', '', true);
        
        // Remove post meta
        delete_metadata('post', 0, '_webapp_likes', '', true);
        delete_metadata('post', 0, '_webapp_featured', '', true);
        delete_metadata('post', 0, '_webapp_views', '', true);
        
        // Drop custom tables
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}webapp_analytics");
        
        // Clean up uploads directory
        $upload_dir = wp_upload_dir();
        $webapp_dir = $upload_dir['basedir'] . '/webapp';
        if (file_exists($webapp_dir)) {
            self::delete_directory($webapp_dir);
        }
        
        // Clear scheduled events
        wp_clear_scheduled_hook('webapp_daily_cleanup');
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log uninstall
        error_log('WebAPP Plugin Uninstalled');
        
        // Fire uninstall hook
        do_action('webapp_uninstalled');
    }
    
    /**
     * Check plugin requirements
     */
    private function check_requirements() {
        $requirements = array(
            'php_version' => '7.4',
            'wp_version' => '5.0',
            'extensions' => array('json', 'mbstring')
        );
        
        // Check PHP version
        if (version_compare(PHP_VERSION, $requirements['php_version'], '<')) {
            return false;
        }
        
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), $requirements['wp_version'], '<')) {
            return false;
        }
        
        // Check PHP extensions
        foreach ($requirements['extensions'] as $extension) {
            if (!extension_loaded($extension)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Create upload directories
     */
    private function create_upload_directories() {
        $upload_dir = wp_upload_dir();
        $webapp_dir = $upload_dir['basedir'] . '/webapp';
        
        $directories = array(
            $webapp_dir,
            $webapp_dir . '/icons',
            $webapp_dir . '/cache',
            $webapp_dir . '/screenshots'
        );
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
                
                // Add index.php to prevent directory browsing
                $index_content = "<?php\n// Silence is golden.\n";
                file_put_contents($dir . '/index.php', $index_content);
            }
        }
        
        // Create .htaccess for security
        $htaccess_content = "# WebAPP Security\n";
        $htaccess_content .= "Options -Indexes\n";
        $htaccess_content .= "<Files *.php>\n";
        $htaccess_content .= "deny from all\n";
        $htaccess_content .= "</Files>\n";
        
        file_put_contents($webapp_dir . '/.htaccess', $htaccess_content);
    }
    
    /**
     * Delete directory recursively
     */
    private static function delete_directory($dir) {
        if (!file_exists($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? self::delete_directory($path) : unlink($path);
        }
        
        rmdir($dir);
    }
    
    /**
     * Check if current theme is compatible
     */
    public function is_theme_compatible() {
        $theme = wp_get_theme();
        $compatible_themes = array(
            'smartmag',
            'newspaper',
            'newsmag',
            'sahifa',
            'jarida'
        );
        
        $theme_name = strtolower($theme->get('Name'));
        $theme_template = strtolower($theme->get('Template'));
        
        foreach ($compatible_themes as $compatible) {
            if (strpos($theme_name, $compatible) !== false || strpos($theme_template, $compatible) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get plugin information
     */
    public static function get_plugin_info() {
        return array(
            'name' => 'WebAPP',
            'version' => WEBAPP_VERSION,
            'author' => 'Mohamed Sawah',
            'author_uri' => 'https://sawahsolutions.com',
            'plugin_uri' => 'https://sawahsolutions.com/plugins/webapp',
            'description' => __('Transform your WordPress site into a modern Progressive Web App', 'webapp'),
            'text_domain' => 'webapp',
            'domain_path' => '/languages',
            'requires_wp' => '5.0',
            'tested_wp' => '6.4',
            'requires_php' => '7.4',
            'license' => 'GPL v2 or later',
            'license_uri' => 'https://www.gnu.org/licenses/gpl-2.0.html'
        );
    }
    
    /**
     * Get component by name
     */
    public function get_component($name) {
        return isset($this->components[$name]) ? $this->components[$name] : null;
    }
    
    /**
     * Check if plugin is enabled
     */
    public function is_enabled() {
        return (bool) get_option('webapp_enabled', 0);
    }
    
    /**
     * Get current theme
     */
    public function get_current_theme() {
        return get_option('webapp_theme', 'modern');
    }
    
    /**
     * Get app settings
     */
    public function get_app_settings() {
        return array(
            'name' => get_option('webapp_app_name', get_bloginfo('name')),
            'description' => get_option('webapp_app_description', get_bloginfo('description')),
            'primary_color' => get_option('webapp_primary_color', '#6366f1'),
            'secondary_color' => get_option('webapp_secondary_color', '#8b5cf6'),
            'theme' => $this->get_current_theme(),
            'dark_mode' => (bool) get_option('webapp_dark_mode', 0),
            'pwa_enabled' => (bool) get_option('webapp_pwa_enabled', 1)
        );
    }
    
    /**
     * Plugins loaded hook
     */
    public function plugins_loaded() {
        // Load integrations with other plugins
        $this->load_integrations();
        
        // Check for conflicting plugins
        $this->check_plugin_conflicts();
    }
    
    /**
     * Load integrations
     */
    private function load_integrations() {
        // WooCommerce integration
        if (class_exists('WooCommerce')) {
            require_once WEBAPP_PLUGIN_PATH . 'includes/integrations/woocommerce.php';
        }
        
        // Yoast SEO integration
        if (class_exists('WPSEO_Options')) {
            require_once WEBAPP_PLUGIN_PATH . 'includes/integrations/yoast-seo.php';
        }
        
        // Contact Form 7 integration
        if (class_exists('WPCF7')) {
            require_once WEBAPP_PLUGIN_PATH . 'includes/integrations/contact-form-7.php';
        }
    }
    
    /**
     * Check for plugin conflicts
     */
    private function check_plugin_conflicts() {
        $conflicting_plugins = array(
            'wp-pwa/wp-pwa.php' => 'WP PWA',
            'pwa/pwa.php' => 'PWA',
            'super-progressive-web-apps/super-progressive-web-apps.php' => 'Super Progressive Web Apps'
        );
        
        foreach ($conflicting_plugins as $plugin_file => $plugin_name) {
            if (is_plugin_active($plugin_file)) {
                add_action('admin_notices', function() use ($plugin_name) {
                    echo '<div class="notice notice-warning">';
                    echo '<p><strong>' . __('WebAPP Warning:', 'webapp') . '</strong> ';
                    echo sprintf(__('The plugin "%s" may conflict with WebAPP. Please deactivate it for best results.', 'webapp'), $plugin_name);
                    echo '</p></div>';
                });
            }
        }
    }
}

// Initialize the plugin
function webapp() {
    return WebAPP::get_instance();
}

// Hook into WordPress
add_action('plugins_loaded', 'webapp', 0);

// Activation redirect
add_action('admin_init', function() {
    if (get_transient('webapp_activation_redirect')) {
        delete_transient('webapp_activation_redirect');
        if (!isset($_GET['activate-multi']) && !is_network_admin()) {
            wp_safe_redirect(admin_url('admin.php?page=webapp-settings&welcome=1'));
            exit;
        }
    }
});

// Admin notices
add_action('admin_notices', function() {
    // Welcome notice
    if (isset($_GET['page']) && $_GET['page'] === 'webapp-settings' && isset($_GET['welcome'])) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php _e('Welcome to WebAPP!', 'webapp'); ?></strong>
                <?php _e('Thank you for installing WebAPP by Mohamed Sawah. Configure your settings below to transform your WordPress site into a modern web app.', 'webapp'); ?>
                <a href="https://sawahsolutions.com/docs/webapp" target="_blank" style="margin-left: 10px;"><?php _e('View Documentation', 'webapp'); ?></a>
            </p>
        </div>
        <?php
    }
    
    // Configuration notice
    if (get_option('webapp_enabled', 0) && !get_option('webapp_configured', 0)) {
        $webapp = webapp();
        if (!$webapp->is_theme_compatible()) {
            ?>
            <div class="notice notice-info">
                <p>
                    <strong><?php _e('WebAPP Notice:', 'webapp'); ?></strong>
                    <?php _e('WebAPP works best with magazine and news themes like SmartMag. Your current theme will work, but you may need to adjust some settings for optimal appearance.', 'webapp'); ?>
                    <a href="<?php echo admin_url('admin.php?page=webapp-themes'); ?>" style="margin-left: 10px;"><?php _e('Choose a Theme', 'webapp'); ?></a>
                </p>
            </div>
            <?php
        }
    }
    
    // PHP version warning
    if (version_compare(PHP_VERSION, '8.0', '<')) {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php _e('WebAPP Recommendation:', 'webapp'); ?></strong>
                <?php printf(__('You are running PHP %s. For better performance and security, consider upgrading to PHP 8.0 or higher.', 'webapp'), PHP_VERSION); ?>
            </p>
        </div>
        <?php
    }
});

// Add upgrade notice for multisite
if (is_multisite()) {
    add_action('network_admin_notices', function() {
        ?>
        <div class="notice notice-info">
            <p>
                <strong><?php _e('WebAPP Multisite:', 'webapp'); ?></strong>
                <?php _e('WebAPP is designed for individual WordPress sites. Network activation is not recommended. Please activate WebAPP on individual sites only.', 'webapp'); ?>
            </p>
        </div>
        <?php
    });
}

// Load plugin textdomain for translations
add_action('init', function() {
    load_plugin_textdomain('webapp', false, dirname(WEBAPP_PLUGIN_BASENAME) . '/languages/');
});

// Add custom image sizes for PWA icons
add_action('after_setup_theme', function() {
    add_image_size('webapp-icon-72', 72, 72, true);
    add_image_size('webapp-icon-96', 96, 96, true);
    add_image_size('webapp-icon-128', 128, 128, true);
    add_image_size('webapp-icon-144', 144, 144, true);
    add_image_size('webapp-icon-152', 152, 152, true);
    add_image_size('webapp-icon-192', 192, 192, true);
    add_image_size('webapp-icon-384', 384, 384, true);
    add_image_size('webapp-icon-512', 512, 512, true);
});

// Register custom post type for app content (future use)
add_action('init', function() {
    if (get_option('webapp_enabled', 0)) {
        register_post_type('webapp_content', array(
            'labels' => array(
                'name' => __('App Content', 'webapp'),
                'singular_name' => __('App Content', 'webapp'),
            ),
            'public' => false,
            'show_ui' => false,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'thumbnail'),
            'capability_type' => 'post'
        ));
    }
});

// Emergency disable function (for debugging)
if (defined('WEBAPP_EMERGENCY_DISABLE') && WEBAPP_EMERGENCY_DISABLE) {
    add_action('init', function() {
        update_option('webapp_enabled', 0);
    }, 0);
}

// Plugin compatibility checks
add_action('admin_init', function() {
    // Check for required functions
    $required_functions = array('wp_enqueue_script', 'wp_enqueue_style', 'add_action', 'add_filter');
    foreach ($required_functions as $func) {
        if (!function_exists($func)) {
            deactivate_plugins(WEBAPP_PLUGIN_BASENAME);
            wp_die(sprintf(__('WebAPP requires the %s function which is not available.', 'webapp'), $func));
        }
    }
});

// End of file - WebAPP Plugin by Mohamed Sawah
// For support: https://sawahsolutions.com/support
// Documentation: https://sawahsolutions.com/docs/webapp