<?php
/**
 * Settings Class
 * 
 * @package WebAPP
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WEBAPP_Settings {
    
    private $settings_group = 'webapp_settings';
    
    public function __construct() {
        add_action('admin_init', array($this, 'init_settings'));
    }
    
    public function init_settings() {
        // Register settings sections
        add_settings_section(
            'webapp_general_section',
            __('General Settings', 'webapp'),
            array($this, 'general_section_callback'),
            $this->settings_group
        );
        
        add_settings_section(
            'webapp_appearance_section',
            __('Appearance Settings', 'webapp'),
            array($this, 'appearance_section_callback'),
            $this->settings_group
        );
        
        add_settings_section(
            'webapp_pwa_section',
            __('PWA Settings', 'webapp'),
            array($this, 'pwa_section_callback'),
            $this->settings_group
        );
        
        add_settings_section(
            'webapp_features_section',
            __('Features', 'webapp'),
            array($this, 'features_section_callback'),
            $this->settings_group
        );
        
        // Register individual settings
        $this->register_general_settings();
        $this->register_appearance_settings();
        $this->register_pwa_settings();
        $this->register_feature_settings();
    }
    
    private function register_general_settings() {
        // Enable WebAPP
        add_settings_field(
            'webapp_enabled',
            __('Enable WebAPP', 'webapp'),
            array($this, 'checkbox_field_callback'),
            $this->settings_group,
            'webapp_general_section',
            array(
                'label_for' => 'webapp_enabled',
                'description' => __('Enable the WebAPP transformation for your website', 'webapp')
            )
        );
        
        // App Name
        add_settings_field(
            'webapp_app_name',
            __('App Name', 'webapp'),
            array($this, 'text_field_callback'),
            $this->settings_group,
            'webapp_general_section',
            array(
                'label_for' => 'webapp_app_name',
                'description' => __('The name of your web app', 'webapp'),
                'placeholder' => get_bloginfo('name')
            )
        );
        
        // App Description
        add_settings_field(
            'webapp_app_description',
            __('App Description', 'webapp'),
            array($this, 'textarea_field_callback'),
            $this->settings_group,
            'webapp_general_section',
            array(
                'label_for' => 'webapp_app_description',
                'description' => __('Brief description of your web app', 'webapp'),
                'placeholder' => get_bloginfo('description')
            )
        );
    }
    
    private function register_appearance_settings() {
        // Theme Selection
        add_settings_field(
            'webapp_theme',
            __('Theme', 'webapp'),
            array($this, 'select_field_callback'),
            $this->settings_group,
            'webapp_appearance_section',
            array(
                'label_for' => 'webapp_theme',
                'description' => __('Choose the theme for your web app', 'webapp'),
                'options' => $this->get_theme_options()
            )
        );
        
        // Dark Mode
        add_settings_field(
            'webapp_dark_mode',
            __('Dark Mode', 'webapp'),
            array($this, 'checkbox_field_callback'),
            $this->settings_group,
            'webapp_appearance_section',
            array(
                'label_for' => 'webapp_dark_mode',
                'description' => __('Enable dark mode by default', 'webapp')
            )
        );
        
        // Primary Color
        add_settings_field(
            'webapp_primary_color',
            __('Primary Color', 'webapp'),
            array($this, 'color_field_callback'),
            $this->settings_group,
            'webapp_appearance_section',
            array(
                'label_for' => 'webapp_primary_color',
                'description' => __('Primary color for your app theme', 'webapp')
            )
        );
        
        // Secondary Color
        add_settings_field(
            'webapp_secondary_color',
            __('Secondary Color', 'webapp'),
            array($this, 'color_field_callback'),
            $this->settings_group,
            'webapp_appearance_section',
            array(
                'label_for' => 'webapp_secondary_color',
                'description' => __('Secondary color for your app theme', 'webapp')
            )
        );
        
        // Custom CSS
        add_settings_field(
            'webapp_custom_css',
            __('Custom CSS', 'webapp'),
            array($this, 'textarea_field_callback'),
            $this->settings_group,
            'webapp_appearance_section',
            array(
                'label_for' => 'webapp_custom_css',
                'description' => __('Add custom CSS for additional styling', 'webapp'),
                'class' => 'large-text code',
                'rows' => 10
            )
        );
    }
    
    private function register_pwa_settings() {
        // PWA Enabled
        add_settings_field(
            'webapp_pwa_enabled',
            __('Enable PWA', 'webapp'),
            array($this, 'checkbox_field_callback'),
            $this->settings_group,
            'webapp_pwa_section',
            array(
                'label_for' => 'webapp_pwa_enabled',
                'description' => __('Enable Progressive Web App features', 'webapp')
            )
        );
        
        // Install Banner
        add_settings_field(
            'webapp_install_banner',
            __('Install Banner', 'webapp'),
            array($this, 'checkbox_field_callback'),
            $this->settings_group,
            'webapp_pwa_section',
            array(
                'label_for' => 'webapp_install_banner',
                'description' => __('Show app installation banner to visitors', 'webapp')
            )
        );
    }
    
    private function register_feature_settings() {
        // Header
        add_settings_field(
            'webapp_header_enabled',
            __('App Header', 'webapp'),
            array($this, 'checkbox_field_callback'),
            $this->settings_group,
            'webapp_features_section',
            array(
                'label_for' => 'webapp_header_enabled',
                'description' => __('Show app-style header with logo and actions', 'webapp')
            )
        );
        
        // Bottom Navigation
        add_settings_field(
            'webapp_bottom_nav_enabled',
            __('Bottom Navigation', 'webapp'),
            array($this, 'checkbox_field_callback'),
            $this->settings_group,
            'webapp_features_section',
            array(
                'label_for' => 'webapp_bottom_nav_enabled',
                'description' => __('Show bottom navigation bar (mobile)', 'webapp')
            )
        );
        
        // Search
        add_settings_field(
            'webapp_search_enabled',
            __('Search Bar', 'webapp'),
            array($this, 'checkbox_field_callback'),
            $this->settings_group,
            'webapp_features_section',
            array(
                'label_for' => 'webapp_search_enabled',
                'description' => __('Show search bar in the header', 'webapp')
            )
        );
        
        // Categories
        add_settings_field(
            'webapp_categories_enabled',
            __('Category Pills', 'webapp'),
            array($this, 'checkbox_field_callback'),
            $this->settings_group,
            'webapp_features_section',
            array(
                'label_for' => 'webapp_categories_enabled',
                'description' => __('Show category filter pills', 'webapp')
            )
        );
        
        // Notifications
        add_settings_field(
            'webapp_notifications_enabled',
            __('Notifications', 'webapp'),
            array($this, 'checkbox_field_callback'),
            $this->settings_group,
            'webapp_features_section',
            array(
                'label_for' => 'webapp_notifications_enabled',
                'description' => __('Enable push notifications (PWA)', 'webapp')
            )
        );
    }
    
    // Callback functions for section descriptions
    public function general_section_callback() {
        echo '<p>' . __('Configure basic settings for your web app.', 'webapp') . '</p>';
    }
    
    public function appearance_section_callback() {
        echo '<p>' . __('Customize the look and feel of your web app.', 'webapp') . '</p>';
    }
    
    public function pwa_section_callback() {
        echo '<p>' . __('Configure Progressive Web App features.', 'webapp') . '</p>';
    }
    
    public function features_section_callback() {
        echo '<p>' . __('Enable or disable specific app features.', 'webapp') . '</p>';
    }
    
    // Field callback functions
    public function text_field_callback($args) {
        $option = get_option($args['label_for'], '');
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        
        printf(
            '<input type="text" id="%s" name="%s" value="%s" placeholder="%s" class="regular-text" />',
            esc_attr($args['label_for']),
            esc_attr($args['label_for']),
            esc_attr($option),
            esc_attr($placeholder)
        );
        
        if (isset($args['description'])) {
            printf('<p class="description">%s</p>', $args['description']);
        }
    }
    
    public function textarea_field_callback($args) {
        $option = get_option($args['label_for'], '');
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        $class = isset($args['class']) ? $args['class'] : 'regular-text';
        $rows = isset($args['rows']) ? $args['rows'] : 4;
        
        printf(
            '<textarea id="%s" name="%s" placeholder="%s" class="%s" rows="%d">%s</textarea>',
            esc_attr($args['label_for']),
            esc_attr($args['label_for']),
            esc_attr($placeholder),
            esc_attr($class),
            $rows,
            esc_textarea($option)
        );
        
        if (isset($args['description'])) {
            printf('<p class="description">%s</p>', $args['description']);
        }
    }
    
    public function checkbox_field_callback($args) {
        $option = get_option($args['label_for'], 0);
        
        printf(
            '<input type="checkbox" id="%s" name="%s" value="1" %s />',
            esc_attr($args['label_for']),
            esc_attr($args['label_for']),
            checked(1, $option, false)
        );
        
        if (isset($args['description'])) {
            printf('<p class="description">%s</p>', $args['description']);
        }
    }
    
    public function select_field_callback($args) {
        $option = get_option($args['label_for'], '');
        $options = isset($args['options']) ? $args['options'] : array();
        
        printf('<select id="%s" name="%s">', esc_attr($args['label_for']), esc_attr($args['label_for']));
        
        foreach ($options as $value => $label) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($value),
                selected($option, $value, false),
                esc_html($label)
            );
        }
        
        echo '</select>';
        
        if (isset($args['description'])) {
            printf('<p class="description">%s</p>', $args['description']);
        }
    }
    
    public function color_field_callback($args) {
        $option = get_option($args['label_for'], '#6366f1');
        
        printf(
            '<input type="color" id="%s" name="%s" value="%s" class="webapp-color-picker" />',
            esc_attr($args['label_for']),
            esc_attr($args['label_for']),
            esc_attr($option)
        );
        
        if (isset($args['description'])) {
            printf('<p class="description">%s</p>', $args['description']);
        }
    }
    
    private function get_theme_options() {
        $themes = WEBAPP_Theme_Manager::get_available_themes();
        $options = array();
        
        foreach ($themes as $key => $theme) {
            $options[$key] = $theme['name'];
        }
        
        return $options;
    }
    
    /**
     * Get setting value with default
     */
    public static function get_setting($key, $default = '') {
        return get_option('webapp_' . $key, $default);
    }
    
    /**
     * Update setting value
     */
    public static function update_setting($key, $value) {
        return update_option('webapp_' . $key, $value);
    }
}