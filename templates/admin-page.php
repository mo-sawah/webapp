<?php
/**
 * Admin Page Template
 * 
 * @package WebAPP
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_theme = get_option('webapp_theme', 'modern');
$available_themes = WEBAPP_Theme_Manager::get_available_themes();
?>

<div class="wrap webapp-admin">
    <div class="webapp-admin-header">
        <div class="webapp-admin-title">
            <div class="webapp-logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L13.09 8.26L22 9L13.09 9.74L12 16L10.91 9.74L2 9L10.91 8.26L12 2Z" fill="#6366f1"/>
                </svg>
            </div>
            <div>
                <h1><?php _e('WebAPP Settings', 'webapp'); ?></h1>
                <p class="webapp-subtitle"><?php _e('Transform your WordPress site into a modern web app', 'webapp'); ?></p>
            </div>
        </div>
        <div class="webapp-admin-actions">
            <button type="button" class="button button-secondary" id="webapp-preview-btn">
                <?php _e('Preview', 'webapp'); ?>
            </button>
            <button type="button" class="button button-primary" id="webapp-save-btn">
                <?php _e('Save Changes', 'webapp'); ?>
            </button>
        </div>
    </div>

    <div class="webapp-admin-content">
        <div class="webapp-admin-main">
            <form method="post" action="options.php" id="webapp-settings-form">
                <?php settings_fields('webapp_settings'); ?>
                
                <!-- General Settings -->
                <div class="webapp-settings-section">
                    <div class="webapp-section-header">
                        <h2><?php _e('General Settings', 'webapp'); ?></h2>
                        <p><?php _e('Configure basic settings for your web app.', 'webapp'); ?></p>
                    </div>
                    
                    <div class="webapp-settings-grid">
                        <div class="webapp-setting-item">
                            <label class="webapp-toggle">
                                <input type="checkbox" name="webapp_enabled" value="1" <?php checked(1, get_option('webapp_enabled', 0)); ?> />
                                <span class="webapp-toggle-slider"></span>
                                <span class="webapp-toggle-label"><?php _e('Enable WebAPP', 'webapp'); ?></span>
                            </label>
                            <p class="webapp-setting-description"><?php _e('Enable the WebAPP transformation for your website', 'webapp'); ?></p>
                        </div>
                        
                        <div class="webapp-setting-item">
                            <label for="webapp_app_name"><?php _e('App Name', 'webapp'); ?></label>
                            <input type="text" id="webapp_app_name" name="webapp_app_name" value="<?php echo esc_attr(get_option('webapp_app_name', get_bloginfo('name'))); ?>" class="webapp-input" />
                            <p class="webapp-setting-description"><?php _e('The name of your web app', 'webapp'); ?></p>
                        </div>
                        
                        <div class="webapp-setting-item webapp-full-width">
                            <label for="webapp_app_description"><?php _e('App Description', 'webapp'); ?></label>
                            <textarea id="webapp_app_description" name="webapp_app_description" class="webapp-textarea" rows="3"><?php echo esc_textarea(get_option('webapp_app_description', get_bloginfo('description'))); ?></textarea>
                            <p class="webapp-setting-description"><?php _e('Brief description of your web app', 'webapp'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Theme Selection -->
                <div class="webapp-settings-section">
                    <div class="webapp-section-header">
                        <h2><?php _e('Theme Selection', 'webapp'); ?></h2>
                        <p><?php _e('Choose from 5 beautiful themes, each with light and dark modes.', 'webapp'); ?></p>
                    </div>
                    
                    <div class="webapp-themes-grid">
                        <?php foreach ($available_themes as $theme_key => $theme_data): ?>
                        <div class="webapp-theme-card <?php echo $current_theme === $theme_key ? 'active' : ''; ?>" data-theme="<?php echo esc_attr($theme_key); ?>">
                            <div class="webapp-theme-preview">
                                <div class="webapp-theme-mockup" style="background: linear-gradient(135deg, <?php echo implode(', ', $theme_data['colors']); ?>);">
                                    <div class="webapp-mockup-header"></div>
                                    <div class="webapp-mockup-content">
                                        <div class="webapp-mockup-card"></div>
                                        <div class="webapp-mockup-card"></div>
                                        <div class="webapp-mockup-card"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="webapp-theme-info">
                                <h3><?php echo esc_html($theme_data['name']); ?></h3>
                                <p><?php echo esc_html($theme_data['description']); ?></p>
                                <div class="webapp-theme-colors">
                                    <?php foreach ($theme_data['colors'] as $color): ?>
                                    <span class="webapp-color-dot" style="background-color: <?php echo esc_attr($color); ?>"></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <input type="radio" name="webapp_theme" value="<?php echo esc_attr($theme_key); ?>" <?php checked($current_theme, $theme_key); ?> />
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Appearance Settings -->
                <div class="webapp-settings-section">
                    <div class="webapp-section-header">
                        <h2><?php _e('Appearance Settings', 'webapp'); ?></h2>
                        <p><?php _e('Customize colors and appearance options.', 'webapp'); ?></p>
                    </div>
                    
                    <div class="webapp-settings-grid">
                        <div class="webapp-setting-item">
                            <label class="webapp-toggle">
                                <input type="checkbox" name="webapp_dark_mode" value="1" <?php checked(1, get_option('webapp_dark_mode', 0)); ?> />
                                <span class="webapp-toggle-slider"></span>
                                <span class="webapp-toggle-label"><?php _e('Dark Mode by Default', 'webapp'); ?></span>
                            </label>
                            <p class="webapp-setting-description"><?php _e('Enable dark mode as the default theme', 'webapp'); ?></p>
                        </div>
                        
                        <div class="webapp-setting-item">
                            <label for="webapp_primary_color"><?php _e('Primary Color', 'webapp'); ?></label>
                            <div class="webapp-color-input">
                                <input type="color" id="webapp_primary_color" name="webapp_primary_color" value="<?php echo esc_attr(get_option('webapp_primary_color', '#6366f1')); ?>" />
                                <input type="text" class="webapp-color-text" value="<?php echo esc_attr(get_option('webapp_primary_color', '#6366f1')); ?>" readonly />
                            </div>
                            <p class="webapp-setting-description"><?php _e('Primary color for your app theme', 'webapp'); ?></p>
                        </div>
                        
                        <div class="webapp-setting-item">
                            <label for="webapp_secondary_color"><?php _e('Secondary Color', 'webapp'); ?></label>
                            <div class="webapp-color-input">
                                <input type="color" id="webapp_secondary_color" name="webapp_secondary_color" value="<?php echo esc_attr(get_option('webapp_secondary_color', '#8b5cf6')); ?>" />
                                <input type="text" class="webapp-color-text" value="<?php echo esc_attr(get_option('webapp_secondary_color', '#8b5cf6')); ?>" readonly />
                            </div>
                            <p class="webapp-setting-description"><?php _e('Secondary color for your app theme', 'webapp'); ?></p>
                        </div>
                        
                        <div class="webapp-setting-item webapp-full-width">
                            <label for="webapp_custom_css"><?php _e('Custom CSS', 'webapp'); ?></label>
                            <textarea id="webapp_custom_css" name="webapp_custom_css" class="webapp-textarea webapp-code" rows="8" placeholder="/* Add your custom CSS here */"><?php echo esc_textarea(get_option('webapp_custom_css', '')); ?></textarea>
                            <p class="webapp-setting-description"><?php _e('Add custom CSS for additional styling', 'webapp'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- PWA Settings -->
                <div class="webapp-settings-section">
                    <div class="webapp-section-header">
                        <h2><?php _e('Progressive Web App', 'webapp'); ?></h2>
                        <p><?php _e('Enable PWA features for app-like experience.', 'webapp'); ?></p>
                    </div>
                    
                    <div class="webapp-settings-grid">
                        <div class="webapp-setting-item">
                            <label class="webapp-toggle">
                                <input type="checkbox" name="webapp_pwa_enabled" value="1" <?php checked(1, get_option('webapp_pwa_enabled', 1)); ?> />
                                <span class="webapp-toggle-slider"></span>
                                <span class="webapp-toggle-label"><?php _e('Enable PWA Features', 'webapp'); ?></span>
                            </label>
                            <p class="webapp-setting-description"><?php _e('Enable Progressive Web App features including offline support and installability', 'webapp'); ?></p>
                        </div>
                        
                        <div class="webapp-setting-item">
                            <label class="webapp-toggle">
                                <input type="checkbox" name="webapp_install_banner" value="1" <?php checked(1, get_option('webapp_install_banner', 1)); ?> />
                                <span class="webapp-toggle-slider"></span>
                                <span class="webapp-toggle-label"><?php _e('Install Banner', 'webapp'); ?></span>
                            </label>
                            <p class="webapp-setting-description"><?php _e('Show app installation banner to visitors', 'webapp'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="webapp-settings-section">
                    <div class="webapp-section-header">
                        <h2><?php _e('App Features', 'webapp'); ?></h2>
                        <p><?php _e('Enable or disable specific app features.', 'webapp'); ?></p>
                    </div>
                    
                    <div class="webapp-settings-grid">
                        <div class="webapp-setting-item">
                            <label class="webapp-toggle">
                                <input type="checkbox" name="webapp_header_enabled" value="1" <?php checked(1, get_option('webapp_header_enabled', 1)); ?> />
                                <span class="webapp-toggle-slider"></span>
                                <span class="webapp-toggle-label"><?php _e('App Header', 'webapp'); ?></span>
                            </label>
                            <p class="webapp-setting-description"><?php _e('Show app-style header with logo and actions', 'webapp'); ?></p>
                        </div>
                        
                        <div class="webapp-setting-item">
                            <label class="webapp-toggle">
                                <input type="checkbox" name="webapp_bottom_nav_enabled" value="1" <?php checked(1, get_option('webapp_bottom_nav_enabled', 1)); ?> />
                                <span class="webapp-toggle-slider"></span>
                                <span class="webapp-toggle-label"><?php _e('Bottom Navigation', 'webapp'); ?></span>
                            </label>
                            <p class="webapp-setting-description"><?php _e('Show bottom navigation bar on mobile devices', 'webapp'); ?></p>
                        </div>
                        
                        <div class="webapp-setting-item">
                            <label class="webapp-toggle">
                                <input type="checkbox" name="webapp_search_enabled" value="1" <?php checked(1, get_option('webapp_search_enabled', 1)); ?> />
                                <span class="webapp-toggle-slider"></span>
                                <span class="webapp-toggle-label"><?php _e('Search Bar', 'webapp'); ?></span>
                            </label>
                            <p class="webapp-setting-description"><?php _e('Show search bar in the header', 'webapp'); ?></p>
                        </div>
                        
                        <div class="webapp-setting-item">
                            <label class="webapp-toggle">
                                <input type="checkbox" name="webapp_categories_enabled" value="1" <?php checked(1, get_option('webapp_categories_enabled', 1)); ?> />
                                <span class="webapp-toggle-slider"></span>
                                <span class="webapp-toggle-label"><?php _e('Category Pills', 'webapp'); ?></span>
                            </label>
                            <p class="webapp-setting-description"><?php _e('Show category filter pills', 'webapp'); ?></p>
                        </div>
                        
                        <div class="webapp-setting-item">
                            <label class="webapp-toggle">
                                <input type="checkbox" name="webapp_notifications_enabled" value="1" <?php checked(1, get_option('webapp_notifications_enabled', 1)); ?> />
                                <span class="webapp-toggle-slider"></span>
                                <span class="webapp-toggle-label"><?php _e('Push Notifications', 'webapp'); ?></span>
                            </label>
                            <p class="webapp-setting-description"><?php _e('Enable push notifications for PWA', 'webapp'); ?></p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="webapp-admin-sidebar">
            <div class="webapp-sidebar-section">
                <h3><?php _e('Quick Actions', 'webapp'); ?></h3>
                <div class="webapp-quick-actions">
                    <button type="button" class="webapp-quick-btn" id="webapp-reset-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 12C3 12 3 8 12 8S21 12 21 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M8 8L12 4L16 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php _e('Reset to Defaults', 'webapp'); ?>
                    </button>
                    
                    <button type="button" class="webapp-quick-btn" id="webapp-export-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <?php _e('Export Settings', 'webapp'); ?>
                    </button>
                    
                    <button type="button" class="webapp-quick-btn" id="webapp-import-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M17 8L12 3L7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 3V15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <?php _e('Import Settings', 'webapp'); ?>
                    </button>
                </div>
            </div>
            
            <div class="webapp-sidebar-section">
                <h3><?php _e('Need Help?', 'webapp'); ?></h3>
                <div class="webapp-help-links">
                    <a href="https://sawahsolutions.com/docs/webapp" target="_blank" class="webapp-help-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php _e('Documentation', 'webapp'); ?>
                    </a>
                    
                    <a href="https://sawahsolutions.com/support" target="_blank" class="webapp-help-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <?php _e('Support', 'webapp'); ?>
                    </a>
                </div>
            </div>
            
            <div class="webapp-sidebar-section">
                <h3><?php _e('Plugin Info', 'webapp'); ?></h3>
                <div class="webapp-plugin-info">
                    <p><strong><?php _e('Version:', 'webapp'); ?></strong> <?php echo WEBAPP_VERSION; ?></p>
                    <p><strong><?php _e('Author:', 'webapp'); ?></strong> Mohamed Sawah</p>
                    <p><strong><?php _e('Website:', 'webapp'); ?></strong> <a href="https://sawahsolutions.com" target="_blank">sawahsolutions.com</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="webapp-preview-modal" class="webapp-modal" style="display: none;">
    <div class="webapp-modal-content">
        <div class="webapp-modal-header">
            <h3><?php _e('WebAPP Preview', 'webapp'); ?></h3>
            <button type="button" class="webapp-modal-close">&times;</button>
        </div>
        <div class="webapp-modal-body">
            <div class="webapp-preview-container">
                <iframe id="webapp-preview-frame" src="<?php echo home_url('?webapp_preview=1'); ?>" width="100%" height="600"></iframe>
            </div>
        </div>
    </div>
</div>