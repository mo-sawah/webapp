<?php
/**
 * Theme Manager Class
 * 
 * @package WebAPP
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WEBAPP_Theme_Manager {
    
    private $current_theme;
    
    public function __construct() {
        $this->current_theme = get_option('webapp_theme', 'modern');
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        if (!get_option('webapp_enabled', 0)) {
            return;
        }
        
        add_action('wp_head', array($this, 'add_theme_styles'), 20);
        add_filter('body_class', array($this, 'add_theme_body_class'));
    }
    
    public function add_theme_styles() {
        $theme_config = $this->get_theme_config($this->current_theme);
        
        echo '<style id="webapp-theme-styles">' . "\n";
        echo $this->generate_theme_css($theme_config) . "\n";
        echo '</style>' . "\n";
    }
    
    public function add_theme_body_class($classes) {
        $classes[] = 'webapp-theme-' . $this->current_theme;
        return $classes;
    }
    
    public function get_theme_config($theme) {
        $configs = array(
            'modern' => array(
                'name' => 'Modern',
                'description' => 'Clean and modern design with card-based layout',
                'colors' => array(
                    'primary' => '#6366f1',
                    'secondary' => '#8b5cf6',
                    'accent' => '#06b6d4',
                    'success' => '#10b981',
                    'warning' => '#f59e0b',
                    'error' => '#ef4444'
                ),
                'typography' => array(
                    'font_family' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                    'heading_weight' => '700',
                    'body_weight' => '400'
                ),
                'layout' => array(
                    'border_radius' => '16px',
                    'card_shadow' => '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                    'spacing' => '20px'
                )
            ),
            
            'news' => array(
                'name' => 'News',
                'description' => 'Professional news layout with rich media support',
                'colors' => array(
                    'primary' => '#dc2626',
                    'secondary' => '#ea580c',
                    'accent' => '#059669',
                    'success' => '#16a34a',
                    'warning' => '#d97706',
                    'error' => '#dc2626'
                ),
                'typography' => array(
                    'font_family' => 'Georgia, "Times New Roman", serif',
                    'heading_weight' => '600',
                    'body_weight' => '400'
                ),
                'layout' => array(
                    'border_radius' => '8px',
                    'card_shadow' => '0 2px 4px rgba(0, 0, 0, 0.1)',
                    'spacing' => '16px'
                )
            ),
            
            'magazine' => array(
                'name' => 'Magazine',
                'description' => 'Magazine-style layout with featured content blocks',
                'colors' => array(
                    'primary' => '#7c3aed',
                    'secondary' => '#db2777',
                    'accent' => '#0891b2',
                    'success' => '#059669',
                    'warning' => '#ea580c',
                    'error' => '#e11d48'
                ),
                'typography' => array(
                    'font_family' => '"Playfair Display", Georgia, serif',
                    'heading_weight' => '700',
                    'body_weight' => '400'
                ),
                'layout' => array(
                    'border_radius' => '12px',
                    'card_shadow' => '0 8px 25px rgba(0, 0, 0, 0.15)',
                    'spacing' => '24px'
                )
            ),
            
            'minimal' => array(
                'name' => 'Minimal',
                'description' => 'Clean and minimal design focusing on content',
                'colors' => array(
                    'primary' => '#374151',
                    'secondary' => '#6b7280',
                    'accent' => '#10b981',
                    'success' => '#059669',
                    'warning' => '#d97706',
                    'error' => '#dc2626'
                ),
                'typography' => array(
                    'font_family' => '"Inter", -apple-system, BlinkMacSystemFont, sans-serif',
                    'heading_weight' => '500',
                    'body_weight' => '400'
                ),
                'layout' => array(
                    'border_radius' => '4px',
                    'card_shadow' => '0 1px 3px rgba(0, 0, 0, 0.1)',
                    'spacing' => '16px'
                )
            ),
            
            'dark' => array(
                'name' => 'Dark Pro',
                'description' => 'Premium dark theme with elegant design',
                'colors' => array(
                    'primary' => '#f59e0b',
                    'secondary' => '#ef4444',
                    'accent' => '#8b5cf6',
                    'success' => '#10b981',
                    'warning' => '#f59e0b',
                    'error' => '#ef4444'
                ),
                'typography' => array(
                    'font_family' => '"SF Pro Display", -apple-system, BlinkMacSystemFont, sans-serif',
                    'heading_weight' => '600',
                    'body_weight' => '400'
                ),
                'layout' => array(
                    'border_radius' => '20px',
                    'card_shadow' => '0 10px 40px rgba(0, 0, 0, 0.3)',
                    'spacing' => '20px'
                )
            )
        );
        
        return isset($configs[$theme]) ? $configs[$theme] : $configs['modern'];
    }
    
    private function generate_theme_css($config) {
        $css = '';
        
        // Theme-specific CSS variables
        $css .= '.webapp-theme-' . $this->current_theme . ' {' . "\n";
        
        // Colors
        foreach ($config['colors'] as $name => $value) {
            $css .= '  --webapp-' . str_replace('_', '-', $name) . ': ' . $value . ';' . "\n";
        }
        
        // Typography
        $css .= '  --webapp-font-family: ' . $config['typography']['font_family'] . ';' . "\n";
        $css .= '  --webapp-heading-weight: ' . $config['typography']['heading_weight'] . ';' . "\n";
        $css .= '  --webapp-body-weight: ' . $config['typography']['body_weight'] . ';' . "\n";
        
        // Layout
        $css .= '  --webapp-border-radius: ' . $config['layout']['border_radius'] . ';' . "\n";
        $css .= '  --webapp-card-shadow: ' . $config['layout']['card_shadow'] . ';' . "\n";
        $css .= '  --webapp-spacing: ' . $config['layout']['spacing'] . ';' . "\n";
        
        $css .= '}' . "\n";
        
        // Theme-specific component styles
        $css .= $this->get_theme_component_styles($this->current_theme);
        
        return $css;
    }
    
    private function get_theme_component_styles($theme) {
        $styles = '';
        
        switch ($theme) {
            case 'modern':
                $styles .= $this->get_modern_theme_styles();
                break;
            case 'news':
                $styles .= $this->get_news_theme_styles();
                break;
            case 'magazine':
                $styles .= $this->get_magazine_theme_styles();
                break;
            case 'minimal':
                $styles .= $this->get_minimal_theme_styles();
                break;
            case 'dark':
                $styles .= $this->get_dark_theme_styles();
                break;
        }
        
        return $styles;
    }
    
    private function get_modern_theme_styles() {
        return '
        .webapp-theme-modern .webapp-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(99, 102, 241, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .webapp-theme-modern .webapp-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .webapp-theme-modern .webapp-button-primary {
            background: linear-gradient(135deg, var(--webapp-primary) 0%, var(--webapp-secondary) 100%);
            border-radius: 25px;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .webapp-theme-modern .webapp-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
        }
        ';
    }
    
    private function get_news_theme_styles() {
        return '
        .webapp-theme-news .webapp-card {
            border-left: 4px solid var(--webapp-primary);
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
        }
        
        .webapp-theme-news .webapp-headline {
            font-family: Georgia, "Times New Roman", serif;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .webapp-theme-news .webapp-category-badge {
            background: var(--webapp-primary);
            color: white;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .webapp-theme-news .webapp-meta {
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
        }
        ';
    }
    
    private function get_magazine_theme_styles() {
        return '
        .webapp-theme-magazine .webapp-featured-card {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.8) 0%, rgba(219, 39, 119, 0.8) 100%);
            color: white;
            overflow: hidden;
            position: relative;
        }
        
        .webapp-theme-magazine .webapp-featured-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Cpath d="m36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .webapp-theme-magazine .webapp-card {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.2);
        }
        
        .webapp-theme-magazine .webapp-title {
            font-family: "Playfair Display", Georgia, serif;
            font-weight: 700;
        }
        ';
    }
    
    private function get_minimal_theme_styles() {
        return '
        .webapp-theme-minimal .webapp-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: border-color 0.2s ease;
        }
        
        .webapp-theme-minimal .webapp-card:hover {
            border-color: var(--webapp-primary);
        }
        
        .webapp-theme-minimal .webapp-button {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .webapp-theme-minimal .webapp-header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .webapp-theme-minimal .webapp-title {
            font-weight: 500;
            color: #111827;
        }
        ';
    }
    
    private function get_dark_theme_styles() {
        return '
        .webapp-theme-dark {
            background: #0f0f0f;
            color: #ffffff;
        }
        
        .webapp-theme-dark .webapp-card {
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
            border: 1px solid rgba(245, 158, 11, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }
        
        .webapp-theme-dark .webapp-card:hover {
            border-color: rgba(245, 158, 11, 0.4);
            box-shadow: 0 20px 40px rgba(245, 158, 11, 0.1);
        }
        
        .webapp-theme-dark .webapp-header {
            background: rgba(15, 15, 15, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(245, 158, 11, 0.2);
        }
        
        .webapp-theme-dark .webapp-button-primary {
            background: linear-gradient(135deg, var(--webapp-primary) 0%, var(--webapp-secondary) 100%);
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
        }
        
        .webapp-theme-dark .webapp-text-secondary {
            color: #a1a1aa;
        }
        
        .webapp-theme-dark .webapp-border {
            border-color: rgba(245, 158, 11, 0.2);
        }
        ';
    }
    
    /**
     * Get all available themes
     */
    public static function get_available_themes() {
        return array(
            'modern' => array(
                'name' => __('Modern', 'webapp'),
                'description' => __('Clean and modern design with card-based layout', 'webapp'),
                'preview' => WEBAPP_PLUGIN_URL . 'assets/images/themes/modern-preview.jpg',
                'colors' => array('#6366f1', '#8b5cf6', '#06b6d4')
            ),
            'news' => array(
                'name' => __('News', 'webapp'),
                'description' => __('Professional news layout with rich media support', 'webapp'),
                'preview' => WEBAPP_PLUGIN_URL . 'assets/images/themes/news-preview.jpg',
                'colors' => array('#dc2626', '#ea580c', '#059669')
            ),
            'magazine' => array(
                'name' => __('Magazine', 'webapp'),
                'description' => __('Magazine-style layout with featured content blocks', 'webapp'),
                'preview' => WEBAPP_PLUGIN_URL . 'assets/images/themes/magazine-preview.jpg',
                'colors' => array('#7c3aed', '#db2777', '#0891b2')
            ),
            'minimal' => array(
                'name' => __('Minimal', 'webapp'),
                'description' => __('Clean and minimal design focusing on content', 'webapp'),
                'preview' => WEBAPP_PLUGIN_URL . 'assets/images/themes/minimal-preview.jpg',
                'colors' => array('#374151', '#6b7280', '#10b981')
            ),
            'dark' => array(
                'name' => __('Dark Pro', 'webapp'),
                'description' => __('Premium dark theme with elegant design', 'webapp'),
                'preview' => WEBAPP_PLUGIN_URL . 'assets/images/themes/dark-preview.jpg',
                'colors' => array('#f59e0b', '#ef4444', '#8b5cf6')
            )
        );
    }
}