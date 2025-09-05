<?php
/**
 * Install Banner Class
 * 
 * @package WebAPP
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WEBAPP_Install_Banner {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        if (!get_option('webapp_install_banner', 1) || !get_option('webapp_enabled', 0)) {
            return;
        }
        
        add_action('wp_footer', array($this, 'display_install_banner'));
        add_action('wp_ajax_webapp_dismiss_banner', array($this, 'dismiss_banner'));
        add_action('wp_ajax_nopriv_webapp_dismiss_banner', array($this, 'dismiss_banner'));
    }
    
    public function display_install_banner() {
        if (is_admin() || $this->is_banner_dismissed()) {
            return;
        }
        
        $app_name = get_option('webapp_app_name', get_bloginfo('name'));
        $primary_color = get_option('webapp_primary_color', '#6366f1');
        
        ?>
        <div id="webapp-install-banner" class="webapp-install-banner" style="display: none;">
            <div class="webapp-banner-content">
                <div class="webapp-banner-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L13.09 8.26L22 9L13.09 9.74L12 16L10.91 9.74L2 9L10.91 8.26L12 2Z" fill="currentColor"/>
                    </svg>
                </div>
                <div class="webapp-banner-text">
                    <div class="webapp-banner-title"><?php echo esc_html($app_name); ?></div>
                    <div class="webapp-banner-subtitle"><?php _e('Install our app for the best experience', 'webapp'); ?></div>
                </div>
            </div>
            <div class="webapp-banner-actions">
                <button id="webapp-install-btn" class="webapp-install-btn">
                    <?php _e('Install', 'webapp'); ?>
                </button>
                <button id="webapp-dismiss-btn" class="webapp-dismiss-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <style>
        .webapp-install-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, <?php echo $primary_color; ?> 0%, <?php echo $this->adjust_brightness($primary_color, -20); ?> 100%);
            color: white;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 999999;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transform: translateY(-100%);
            transition: transform 0.3s ease;
        }
        
        .webapp-install-banner.show {
            transform: translateY(0);
        }
        
        .webapp-banner-content {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }
        
        .webapp-banner-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }
        
        .webapp-banner-text {
            flex: 1;
        }
        
        .webapp-banner-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .webapp-banner-subtitle {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .webapp-banner-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .webapp-install-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            backdrop-filter: blur(10px);
        }
        
        .webapp-install-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }
        
        .webapp-dismiss-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
            opacity: 0.8;
        }
        
        .webapp-dismiss-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .webapp-install-banner {
                padding: 10px 12px;
            }
            
            .webapp-banner-icon {
                width: 36px;
                height: 36px;
            }
            
            .webapp-banner-title {
                font-size: 13px;
            }
            
            .webapp-banner-subtitle {
                font-size: 11px;
            }
        }
        </style>
        
        <script>
        (function() {
            const banner = document.getElementById('webapp-install-banner');
            const installBtn = document.getElementById('webapp-install-btn');
            const dismissBtn = document.getElementById('webapp-dismiss-btn');
            let deferredPrompt;
            
            // Show banner after page load
            window.addEventListener('load', function() {
                setTimeout(function() {
                    if (banner && !localStorage.getItem('webapp_banner_dismissed')) {
                        banner.style.display = 'flex';
                        setTimeout(function() {
                            banner.classList.add('show');
                        }, 100);
                    }
                }, 2000);
            });
            
            // Listen for beforeinstallprompt event
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredPrompt = e;
                
                // Update install button text
                if (installBtn) {
                    installBtn.textContent = '<?php _e("Add to Home Screen", "webapp"); ?>';
                }
            });
            
            // Install button click
            if (installBtn) {
                installBtn.addEventListener('click', function() {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        deferredPrompt.userChoice.then(function(choiceResult) {
                            if (choiceResult.outcome === 'accepted') {
                                console.log('PWA installed');
                                hideBanner();
                            }
                            deferredPrompt = null;
                        });
                    } else {
                        // Fallback for browsers that don't support PWA install
                        showInstallInstructions();
                    }
                });
            }
            
            // Dismiss button click
            if (dismissBtn) {
                dismissBtn.addEventListener('click', function() {
                    hideBanner();
                    
                    // Send AJAX request to dismiss banner
                    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'webapp_dismiss_banner',
                            nonce: '<?php echo wp_create_nonce("webapp_nonce"); ?>'
                        })
                    });
                });
            }
            
            function hideBanner() {
                if (banner) {
                    banner.classList.remove('show');
                    setTimeout(function() {
                        banner.style.display = 'none';
                    }, 300);
                }
                localStorage.setItem('webapp_banner_dismissed', 'true');
            }
            
            function showInstallInstructions() {
                const userAgent = navigator.userAgent;
                let instructions = '';
                
                if (userAgent.includes('iPhone') || userAgent.includes('iPad')) {
                    instructions = '<?php _e("To install: tap Share button, then 'Add to Home Screen'", "webapp"); ?>';
                } else if (userAgent.includes('Android')) {
                    instructions = '<?php _e("To install: tap menu (⋮), then 'Add to Home Screen'", "webapp"); ?>';
                } else {
                    instructions = '<?php _e("To install: look for install icon in your browser's address bar", "webapp"); ?>';
                }
                
                alert(instructions);
            }
        })();
        </script>
        <?php
    }
    
    public function dismiss_banner() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'webapp_nonce')) {
            wp_die('Security check failed');
        }
        
        // Set cookie to remember dismissal
        setcookie('webapp_banner_dismissed', '1', time() + (30 * DAY_IN_SECONDS), '/');
        
        wp_send_json_success();
    }
    
    private function is_banner_dismissed() {
        return isset($_COOKIE['webapp_banner_dismissed']) && $_COOKIE['webapp_banner_dismissed'] === '1';
    }
    
    private function adjust_brightness($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}