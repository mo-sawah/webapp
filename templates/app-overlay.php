<?php
/**
 * App Overlay Template
 * 
 * This template creates the app-like overlay structure
 * 
 * @package WebAPP
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin settings
$settings = array(
    'header_enabled' => get_option('webapp_header_enabled', 1),
    'bottom_nav_enabled' => get_option('webapp_bottom_nav_enabled', 1),
    'search_enabled' => get_option('webapp_search_enabled', 1),
    'categories_enabled' => get_option('webapp_categories_enabled', 1),
    'theme' => get_option('webapp_theme', 'modern'),
    'app_name' => get_option('webapp_app_name', get_bloginfo('name'))
);
?>

<!-- WebAPP Overlay Structure -->
<div class="webapp-overlay" id="webapp-overlay" style="display: none;">
    
    <?php if ($settings['header_enabled']): ?>
    <!-- App Header -->
    <div class="webapp-header">
        <div class="webapp-header-left">
            <a href="<?php echo home_url(); ?>" class="webapp-logo">
                <div class="webapp-logo-icon">
                    <?php echo substr($settings['app_name'], 0, 1); ?>
                </div>
                <div class="webapp-logo-text"><?php echo esc_html($settings['app_name']); ?></div>
            </a>
        </div>
        <div class="webapp-header-actions">
            <button class="webapp-header-btn webapp-notifications-btn" title="<?php _e('Notifications', 'webapp'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="webapp-notification-badge">3</span>
            </button>
            <button class="webapp-header-btn webapp-theme-toggle" title="<?php _e('Toggle Theme', 'webapp'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($settings['search_enabled']): ?>
    <!-- Search Section -->
    <div class="webapp-search-section">
        <div class="webapp-search-container">
            <svg class="webapp-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <input type="text" class="webapp-search-bar" placeholder="<?php _e('Search articles, topics, authors...', 'webapp'); ?>">
            <button class="webapp-filter-btn" title="<?php _e('Filter', 'webapp'); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content Area -->
    <div class="webapp-content">
        
        <!-- Featured Section -->
        <div class="webapp-section">
            <div class="webapp-section-header">
                <h2 class="webapp-section-title"><?php _e('Featured', 'webapp'); ?></h2>
                <a href="#" class="webapp-see-all"><?php _e('See all', 'webapp'); ?></a>
            </div>
            <div class="webapp-featured-container">
                <!-- Featured content will be loaded here -->
            </div>
        </div>
        
        <!-- News Section -->
        <div class="webapp-section">
            <div class="webapp-section-header">
                <h2 class="webapp-section-title"><?php _e('Latest Posts', 'webapp'); ?></h2>
                <a href="#" class="webapp-see-all"><?php _e('See all', 'webapp'); ?></a>
            </div>
            
            <?php if ($settings['categories_enabled']): ?>
            <!-- Category Pills -->
            <div class="webapp-category-pills">
                <!-- Categories will be loaded here -->
            </div>
            <?php endif; ?>
            
            <!-- Posts List -->
            <div class="webapp-news-list">
                <!-- Posts will be loaded here -->
            </div>
            
            <!-- Load More Button -->
            <div class="webapp-load-more-container" style="text-align: center; margin-top: 20px;">
                <button class="webapp-load-more-btn" style="display: none;">
                    <?php _e('Load More', 'webapp'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <?php if ($settings['bottom_nav_enabled']): ?>
    <!-- Bottom Navigation -->
    <div class="webapp-bottom-nav">
        <div class="webapp-nav-items">
            <a href="#" class="webapp-nav-item active" data-tab="home">
                <div class="webapp-nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span><?php _e('Home', 'webapp'); ?></span>
            </a>
            <a href="#" class="webapp-nav-item" data-tab="search">
                <div class="webapp-nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span><?php _e('Search', 'webapp'); ?></span>
            </a>
            <a href="#" class="webapp-nav-item" data-tab="bookmarks">
                <div class="webapp-nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span><?php _e('Saved', 'webapp'); ?></span>
            </a>
            <a href="#" class="webapp-nav-item" data-tab="menu">
                <div class="webapp-nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <line x1="3" y1="12" x2="21" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="3" y1="18" x2="21" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span><?php _e('Menu', 'webapp'); ?></span>
            </a>
            <a href="#" class="webapp-nav-item" data-tab="profile">
                <div class="webapp-nav-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span><?php _e('Profile', 'webapp'); ?></span>
            </a>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<!-- Loading Overlay -->
<div class="webapp-loading-overlay" id="webapp-loading" style="display: none;">
    <div class="webapp-loading-spinner">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21 12a9 9 0 11-6.219-8.56" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </div>
</div>

<style>
.webapp-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    backdrop-filter: blur(4px);
}

.webapp-loading-spinner svg {
    animation: webapp-spin 1s linear infinite;
    color: var(--webapp-primary, #6366f1);
}

@keyframes webapp-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.webapp-overlay {
    position: relative;
    z-index: 999;
}
</style>

<script>
// Initialize the overlay when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Show the overlay
    const overlay = document.getElementById('webapp-overlay');
    if (overlay) {
        overlay.style.display = 'block';
    }
    
    // Initialize WebAPP frontend if available
    if (typeof WebAppFrontend !== 'undefined') {
        WebAppFrontend.init();
    }
});
</script>