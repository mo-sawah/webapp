<?php
/**
 * Frontend Class
 * 
 * @package WebAPP
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WEBAPP_Frontend {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        if (!get_option('webapp_enabled', 0)) {
            return;
        }
        
        // Add frontend hooks
        add_action('wp_head', array($this, 'add_head_meta'), 5);
        add_action('wp_head', array($this, 'add_theme_colors'), 10);
        add_action('wp_head', array($this, 'add_custom_css'), 15);
        add_action('wp_footer', array($this, 'add_app_overlay'), 20);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_theme_assets'));
        
        // AJAX actions
        add_action('wp_ajax_webapp_toggle_like', array($this, 'toggle_like'));
        add_action('wp_ajax_nopriv_webapp_toggle_like', array($this, 'toggle_like'));
        add_action('wp_ajax_webapp_toggle_bookmark', array($this, 'toggle_bookmark'));
        add_action('wp_ajax_nopriv_webapp_toggle_bookmark', array($this, 'toggle_bookmark'));
        
        // Modify query for app layout
        add_action('pre_get_posts', array($this, 'modify_main_query'));
        
        // Add body classes
        add_filter('body_class', array($this, 'add_body_classes'));
    }
    
    public function add_head_meta() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . "\n";
        echo '<meta name="theme-color" content="' . get_option('webapp_primary_color', '#6366f1') . '">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
        echo '<meta name="apple-mobile-web-app-title" content="' . get_option('webapp_app_name', get_bloginfo('name')) . '">' . "\n";
    }
    
    public function add_theme_colors() {
        $theme = get_option('webapp_theme', 'modern');
        $colors = WEBAPP_Admin::get_theme_colors($theme);
        $primary_color = get_option('webapp_primary_color', $colors['primary']);
        $secondary_color = get_option('webapp_secondary_color', $colors['secondary']);
        $dark_mode = get_option('webapp_dark_mode', 0);
        
        echo '<style id="webapp-theme-colors">' . "\n";
        echo ':root {' . "\n";
        echo '  --webapp-primary: ' . $primary_color . ';' . "\n";
        echo '  --webapp-secondary: ' . $secondary_color . ';' . "\n";
        echo '  --webapp-accent: ' . $colors['accent'] . ';' . "\n";
        
        if ($dark_mode) {
            echo '  --webapp-bg: #1a1a1a;' . "\n";
            echo '  --webapp-bg-secondary: #2d2d2d;' . "\n";
            echo '  --webapp-text: #f5f6fa;' . "\n";
            echo '  --webapp-text-secondary: #c7c7c7;' . "\n";
            echo '  --webapp-border: #404040;' . "\n";
        } else {
            echo '  --webapp-bg: #ffffff;' . "\n";
            echo '  --webapp-bg-secondary: #f8f9fa;' . "\n";
            echo '  --webapp-text: #2f3542;' . "\n";
            echo '  --webapp-text-secondary: #57606f;' . "\n";
            echo '  --webapp-border: #dfe4ea;' . "\n";
        }
        
        echo '}' . "\n";
        echo '</style>' . "\n";
    }
    
    public function add_custom_css() {
        $custom_css = get_option('webapp_custom_css', '');
        if (!empty($custom_css)) {
            echo '<style id="webapp-custom-css">' . "\n";
            echo wp_strip_all_tags($custom_css) . "\n";
            echo '</style>' . "\n";
        }
    }
    
    public function enqueue_theme_assets() {
        $theme = get_option('webapp_theme', 'modern');
        
        // Enqueue theme-specific CSS if file exists
        $theme_css_path = WEBAPP_PLUGIN_PATH . 'assets/css/themes/' . $theme . '.css';
        if (file_exists($theme_css_path)) {
            wp_enqueue_style(
                'webapp-theme-' . $theme,
                WEBAPP_PLUGIN_URL . 'assets/css/themes/' . $theme . '.css',
                array(),
                WEBAPP_VERSION
            );
        }
        
        // Enqueue theme-specific JS if file exists
        $theme_js_path = WEBAPP_PLUGIN_PATH . 'assets/js/themes/' . $theme . '.js';
        if (file_exists($theme_js_path)) {
            wp_enqueue_script(
                'webapp-theme-' . $theme,
                WEBAPP_PLUGIN_URL . 'assets/js/themes/' . $theme . '.js',
                array('jquery'),
                WEBAPP_VERSION,
                true
            );
        }
    }
    
    public function add_app_overlay() {
        if (is_admin() || !get_option('webapp_enabled', 0)) {
            return;
        }
        
        $theme = get_option('webapp_theme', 'modern');
        $template_path = WEBAPP_PLUGIN_PATH . 'templates/app-overlay.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback basic overlay
            echo '<div class="webapp-overlay-placeholder" style="display:none;">App overlay template not found</div>';
        }
    }
    
    public function modify_main_query($query) {
        if (!is_admin() && $query->is_main_query() && is_home()) {
            // Modify posts per page for app layout
            $query->set('posts_per_page', 10);
        }
    }
    
    public function add_body_classes($classes) {
        if (get_option('webapp_enabled', 0)) {
            $classes[] = 'webapp-enabled';
            $classes[] = 'webapp-theme-' . get_option('webapp_theme', 'modern');
            
            if (get_option('webapp_dark_mode', 0)) {
                $classes[] = 'webapp-dark-mode';
            }
        }
        
        return $classes;
    }
    
    public function toggle_like() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'webapp_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_POST['post_id']);
        $user_id = get_current_user_id();
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        // Get current likes
        $likes = get_post_meta($post_id, '_webapp_likes', true);
        if (!is_array($likes)) {
            $likes = array();
        }
        
        $liked = false;
        if ($user_id > 0) {
            // Logged in user
            if (in_array($user_id, $likes)) {
                $likes = array_diff($likes, array($user_id));
            } else {
                $likes[] = $user_id;
                $liked = true;
            }
        } else {
            // Guest user - use IP address
            $ip = $_SERVER['REMOTE_ADDR'];
            if (in_array($ip, $likes)) {
                $likes = array_diff($likes, array($ip));
            } else {
                $likes[] = $ip;
                $liked = true;
            }
        }
        
        update_post_meta($post_id, '_webapp_likes', $likes);
        
        wp_send_json_success(array(
            'liked' => $liked,
            'count' => count($likes)
        ));
    }
    
    public function toggle_bookmark() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'webapp_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_POST['post_id']);
        $user_id = get_current_user_id();
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        if ($user_id === 0) {
            wp_send_json_error('Please login to bookmark posts');
        }
        
        // Get user bookmarks
        $bookmarks = get_user_meta($user_id, '_webapp_bookmarks', true);
        if (!is_array($bookmarks)) {
            $bookmarks = array();
        }
        
        $bookmarked = false;
        if (in_array($post_id, $bookmarks)) {
            $bookmarks = array_diff($bookmarks, array($post_id));
        } else {
            $bookmarks[] = $post_id;
            $bookmarked = true;
        }
        
        update_user_meta($user_id, '_webapp_bookmarks', $bookmarks);
        
        wp_send_json_success(array(
            'bookmarked' => $bookmarked,
            'message' => $bookmarked ? __('Post bookmarked!', 'webapp') : __('Bookmark removed!', 'webapp')
        ));
    }
    
    /**
     * Get post like count
     */
    public static function get_like_count($post_id) {
        $likes = get_post_meta($post_id, '_webapp_likes', true);
        return is_array($likes) ? count($likes) : 0;
    }
    
    /**
     * Check if user liked post
     */
    public static function is_liked($post_id, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        $likes = get_post_meta($post_id, '_webapp_likes', true);
        if (!is_array($likes)) {
            return false;
        }
        
        if ($user_id > 0) {
            return in_array($user_id, $likes);
        } else {
            return in_array($_SERVER['REMOTE_ADDR'], $likes);
        }
    }
    
    /**
     * Check if user bookmarked post
     */
    public static function is_bookmarked($post_id, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        if ($user_id === 0) {
            return false;
        }
        
        $bookmarks = get_user_meta($user_id, '_webapp_bookmarks', true);
        return is_array($bookmarks) && in_array($post_id, $bookmarks);
    }
    
    /**
     * Get featured posts
     */
    public static function get_featured_posts($limit = 5) {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => '_webapp_featured',
                    'value' => '1',
                    'compare' => '='
                )
            )
        );
        
        return get_posts($args);
    }
    
    /**
     * Mark post as featured
     */
    public static function set_featured_post($post_id, $featured = true) {
        if ($featured) {
            update_post_meta($post_id, '_webapp_featured', '1');
        } else {
            delete_post_meta($post_id, '_webapp_featured');
        }
    }
    
    /**
     * Get user's bookmarked posts
     */
    public static function get_user_bookmarks($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        if ($user_id === 0) {
            return array();
        }
        
        $bookmark_ids = get_user_meta($user_id, '_webapp_bookmarks', true);
        if (!is_array($bookmark_ids) || empty($bookmark_ids)) {
            return array();
        }
        
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'post__in' => $bookmark_ids,
            'orderby' => 'post__in',
            'posts_per_page' => -1
        );
        
        return get_posts($args);
    }
    
    /**
     * Get trending posts based on likes and views
     */
    public static function get_trending_posts($limit = 10, $days = 7) {
        $date_query = array(
            array(
                'after' => date('Y-m-d', strtotime('-' . $days . ' days'))
            )
        );
        
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'date_query' => $date_query,
            'meta_key' => '_webapp_likes',
            'orderby' => 'meta_value_num date',
            'order' => 'DESC'
        );
        
        return get_posts($args);
    }
    
    /**
     * Record post view
     */
    public static function record_post_view($post_id) {
        if (!$post_id) {
            return;
        }
        
        $views = get_post_meta($post_id, '_webapp_views', true);
        $views = $views ? intval($views) + 1 : 1;
        
        update_post_meta($post_id, '_webapp_views', $views);
        
        // Record in analytics table if exists
        global $wpdb;
        $table_name = $wpdb->prefix . 'webapp_analytics';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            $wpdb->insert(
                $table_name,
                array(
                    'post_id' => $post_id,
                    'event_type' => 'view',
                    'user_id' => get_current_user_id(),
                    'user_ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'event_data' => wp_json_encode(array(
                        'referrer' => wp_get_referer(),
                        'timestamp' => current_time('mysql')
                    ))
                ),
                array('%d', '%s', '%d', '%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Get post view count
     */
    public static function get_view_count($post_id) {
        $views = get_post_meta($post_id, '_webapp_views', true);
        return $views ? intval($views) : 0;
    }
    
    /**
     * Format numbers for display (e.g., 1.2K, 1.5M)
     */
    public static function format_number($number) {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        
        return number_format($number);
    }
    
    /**
     * Get related posts
     */
    public static function get_related_posts($post_id, $limit = 5) {
        $post = get_post($post_id);
        if (!$post) {
            return array();
        }
        
        // Get post categories
        $categories = wp_get_post_categories($post_id);
        if (empty($categories)) {
            return array();
        }
        
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'post__not_in' => array($post_id),
            'category__in' => $categories,
            'orderby' => 'rand'
        );
        
        return get_posts($args);
    }
}