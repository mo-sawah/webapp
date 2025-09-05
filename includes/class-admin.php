<?php
/**
 * Admin Class
 * 
 * @package WebAPP
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WEBAPP_Admin {
    
    public function __construct() {
        add_action('admin_init', array($this, 'init'));
        add_action('wp_ajax_webapp_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_webapp_reset_settings', array($this, 'reset_settings'));
        add_action('wp_ajax_webapp_preview_theme', array($this, 'preview_theme'));
    }
    
    public function init() {
        // Register settings
        register_setting('webapp_settings', 'webapp_enabled');
        register_setting('webapp_settings', 'webapp_theme');
        register_setting('webapp_settings', 'webapp_dark_mode');
        register_setting('webapp_settings', 'webapp_install_banner');
        register_setting('webapp_settings', 'webapp_pwa_enabled');
        register_setting('webapp_settings', 'webapp_app_name');
        register_setting('webapp_settings', 'webapp_app_description');
        register_setting('webapp_settings', 'webapp_primary_color');
        register_setting('webapp_settings', 'webapp_secondary_color');
        register_setting('webapp_settings', 'webapp_custom_css');
        register_setting('webapp_settings', 'webapp_header_enabled');
        register_setting('webapp_settings', 'webapp_bottom_nav_enabled');
        register_setting('webapp_settings', 'webapp_search_enabled');
        register_setting('webapp_settings', 'webapp_categories_enabled');
        register_setting('webapp_settings', 'webapp_notifications_enabled');
    }
    
    public function save_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'webapp_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Sanitize and save settings
        $settings = array(
            'webapp_enabled' => sanitize_text_field($_POST['enabled']),
            'webapp_theme' => sanitize_text_field($_POST['theme']),
            'webapp_dark_mode' => sanitize_text_field($_POST['dark_mode']),
            'webapp_install_banner' => sanitize_text_field($_POST['install_banner']),
            'webapp_pwa_enabled' => sanitize_text_field($_POST['pwa_enabled']),
            'webapp_app_name' => sanitize_text_field($_POST['app_name']),
            'webapp_app_description' => sanitize_textarea_field($_POST['app_description']),
            'webapp_primary_color' => sanitize_hex_color($_POST['primary_color']),
            'webapp_secondary_color' => sanitize_hex_color($_POST['secondary_color']),
            'webapp_custom_css' => wp_strip_all_tags($_POST['custom_css']),
            'webapp_header_enabled' => sanitize_text_field($_POST['header_enabled']),
            'webapp_bottom_nav_enabled' => sanitize_text_field($_POST['bottom_nav_enabled']),
            'webapp_search_enabled' => sanitize_text_field($_POST['search_enabled']),
            'webapp_categories_enabled' => sanitize_text_field($_POST['categories_enabled']),
            'webapp_notifications_enabled' => sanitize_text_field($_POST['notifications_enabled'])
        );
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        wp_send_json_success(array('message' => __('Settings saved successfully!', 'webapp')));
    }
    
    public function reset_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'webapp_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Reset to default values
        update_option('webapp_enabled', 0);
        update_option('webapp_theme', 'modern');
        update_option('webapp_dark_mode', 0);
        update_option('webapp_install_banner', 1);
        update_option('webapp_pwa_enabled', 1);
        update_option('webapp_app_name', get_bloginfo('name'));
        update_option('webapp_app_description', get_bloginfo('description'));
        update_option('webapp_primary_color', '#6366f1');
        update_option('webapp_secondary_color', '#8b5cf6');
        update_option('webapp_custom_css', '');
        update_option('webapp_header_enabled', 1);
        update_option('webapp_bottom_nav_enabled', 1);
        update_option('webapp_search_enabled', 1);
        update_option('webapp_categories_enabled', 1);
        update_option('webapp_notifications_enabled', 1);
        
        wp_send_json_success(array('message' => __('Settings reset to defaults!', 'webapp')));
    }
    
    public function preview_theme() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'webapp_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $theme = sanitize_text_field($_POST['theme']);
        $dark_mode = sanitize_text_field($_POST['dark_mode']);
        
        // Generate preview HTML
        ob_start();
        include WEBAPP_PLUGIN_PATH . 'templates/theme-preview.php';
        $preview_html = ob_get_clean();
        
        wp_send_json_success(array('html' => $preview_html));
    }
    
    /**
     * Get available themes
     */
    public static function get_available_themes() {
        return array(
            'modern' => array(
                'name' => __('Modern', 'webapp'),
                'description' => __('Clean and modern design with card-based layout', 'webapp'),
                'preview' => WEBAPP_PLUGIN_URL . 'assets/images/themes/modern-preview.jpg'
            ),
            'news' => array(
                'name' => __('News', 'webapp'),
                'description' => __('Professional news layout with rich media support', 'webapp'),
                'preview' => WEBAPP_PLUGIN_URL . 'assets/images/themes/news-preview.jpg'
            ),
            'magazine' => array(
                'name' => __('Magazine', 'webapp'),
                'description' => __('Magazine-style layout with featured content blocks', 'webapp'),
                'preview' => WEBAPP_PLUGIN_URL . 'assets/images/themes/magazine-preview.jpg'
            ),
            'minimal' => array(
                'name' => __('Minimal', 'webapp'),
                'description' => __('Clean and minimal design focusing on content', 'webapp'),
                'preview' => WEBAPP_PLUGIN_URL . 'assets/images/themes/minimal-preview.jpg'
            ),
            'dark' => array(
                'name' => __('Dark Pro', 'webapp'),
                'description' => __('Premium dark theme with elegant design', 'webapp'),
                'preview' => WEBAPP_PLUGIN_URL . 'assets/images/themes/dark-preview.jpg'
            )
        );
    }
    
    /**
     * Get theme colors
     */
    public static function get_theme_colors($theme) {
        $colors = array(
            'modern' => array(
                'primary' => '#6366f1',
                'secondary' => '#8b5cf6',
                'accent' => '#06b6d4'
            ),
            'news' => array(
                'primary' => '#dc2626',
                'secondary' => '#ea580c',
                'accent' => '#059669'
            ),
            'magazine' => array(
                'primary' => '#7c3aed',
                'secondary' => '#db2777',
                'accent' => '#0891b2'
            ),
            'minimal' => array(
                'primary' => '#374151',
                'secondary' => '#6b7280',
                'accent' => '#10b981'
            ),
            'dark' => array(
                'primary' => '#f59e0b',
                'secondary' => '#ef4444',
                'accent' => '#8b5cf6'
            )
        );
        
        return isset($colors[$theme]) ? $colors[$theme] : $colors['modern'];
    }
}