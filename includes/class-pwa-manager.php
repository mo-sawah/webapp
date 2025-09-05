<?php
/**
 * PWA Manager Class
 * 
 * @package WebAPP
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WEBAPP_PWA_Manager {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        if (!get_option('webapp_pwa_enabled', 1)) {
            return;
        }
        
        add_action('wp_head', array($this, 'add_manifest_link'));
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'serve_manifest'));
        add_action('template_redirect', array($this, 'serve_service_worker'));
        add_action('wp_footer', array($this, 'register_service_worker'));
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule('^manifest\.json$', 'index.php?webapp_manifest=1', 'top');
        add_rewrite_rule('^sw\.js$', 'index.php?webapp_service_worker=1', 'top');
        add_rewrite_rule('^offline\.html$', 'index.php?webapp_offline=1', 'top');
    }
    
    public function add_manifest_link() {
        echo '<link rel="manifest" href="' . home_url('/manifest.json') . '">' . "\n";
        echo '<link rel="apple-touch-icon" href="' . $this->get_app_icon(192) . '">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="152x152" href="' . $this->get_app_icon(152) . '">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="180x180" href="' . $this->get_app_icon(180) . '">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="167x167" href="' . $this->get_app_icon(167) . '">' . "\n";
    }
    
    public function serve_manifest() {
        if (get_query_var('webapp_manifest')) {
            header('Content-Type: application/json');
            echo json_encode($this->generate_manifest());
            exit;
        }
    }
    
    public function serve_service_worker() {
        if (get_query_var('webapp_service_worker')) {
            header('Content-Type: application/javascript');
            echo $this->generate_service_worker();
            exit;
        }
    }
    
    public function register_service_worker() {
        if (!get_option('webapp_enabled', 0)) {
            return;
        }
        
        ?>
        <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registered successfully');
                    })
                    .catch(function(error) {
                        console.log('ServiceWorker registration failed');
                    });
            });
        }
        </script>
        <?php
    }
    
    private function generate_manifest() {
        $app_name = get_option('webapp_app_name', get_bloginfo('name'));
        $app_description = get_option('webapp_app_description', get_bloginfo('description'));
        $primary_color = get_option('webapp_primary_color', '#6366f1');
        
        return array(
            'name' => $app_name,
            'short_name' => $app_name,
            'description' => $app_description,
            'start_url' => '/?utm_source=webapp',
            'display' => 'standalone',
            'theme_color' => $primary_color,
            'background_color' => '#ffffff',
            'orientation' => 'portrait-primary',
            'scope' => '/',
            'icons' => array(
                array(
                    'src' => $this->get_app_icon(72),
                    'sizes' => '72x72',
                    'type' => 'image/png'
                ),
                array(
                    'src' => $this->get_app_icon(96),
                    'sizes' => '96x96',
                    'type' => 'image/png'
                ),
                array(
                    'src' => $this->get_app_icon(128),
                    'sizes' => '128x128',
                    'type' => 'image/png'
                ),
                array(
                    'src' => $this->get_app_icon(144),
                    'sizes' => '144x144',
                    'type' => 'image/png'
                ),
                array(
                    'src' => $this->get_app_icon(152),
                    'sizes' => '152x152',
                    'type' => 'image/png'
                ),
                array(
                    'src' => $this->get_app_icon(192),
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ),
                array(
                    'src' => $this->get_app_icon(384),
                    'sizes' => '384x384',
                    'type' => 'image/png'
                ),
                array(
                    'src' => $this->get_app_icon(512),
                    'sizes' => '512x512',
                    'type' => 'image/png'
                )
            ),
            'categories' => array('news', 'entertainment', 'magazines'),
            'lang' => get_locale(),
            'dir' => is_rtl() ? 'rtl' : 'ltr'
        );
    }
    
    private function generate_service_worker() {
        ob_start();
        ?>
const CACHE_NAME = 'webapp-v<?php echo WEBAPP_VERSION; ?>';
const urlsToCache = [
    '/',
    '/wp-content/plugins/webapp/assets/css/frontend.css',
    '/wp-content/plugins/webapp/assets/js/frontend.js',
    '/offline.html'
];

// Install event
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                return cache.addAll(urlsToCache);
            })
    );
});

// Fetch event
self.addEventListener('fetch', function(event) {
    event.respondWith(
        caches.match(event.request)
            .then(function(response) {
                // Cache hit - return response
                if (response) {
                    return response;
                }

                return fetch(event.request).then(
                    function(response) {
                        // Check if valid response
                        if(!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clone the response
                        var responseToCache = response.clone();

                        caches.open(CACHE_NAME)
                            .then(function(cache) {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    }
                ).catch(function() {
                    // Return offline page for navigate requests
                    if (event.request.mode === 'navigate') {
                        return caches.match('/offline.html');
                    }
                });
            })
    );
});

// Activate event
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Background sync
self.addEventListener('sync', function(event) {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

function doBackgroundSync() {
    // Handle background sync tasks
    return Promise.resolve();
}

// Push notifications
self.addEventListener('push', function(event) {
    const options = {
        body: event.data ? event.data.text() : 'New content available!',
        icon: '<?php echo $this->get_app_icon(192); ?>',
        badge: '<?php echo $this->get_app_icon(72); ?>',
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Read More',
                icon: '<?php echo WEBAPP_PLUGIN_URL; ?>assets/images/icons/read.png'
            },
            {
                action: 'close',
                title: 'Close',
                icon: '<?php echo WEBAPP_PLUGIN_URL; ?>assets/images/icons/close.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('<?php echo get_option("webapp_app_name", get_bloginfo("name")); ?>', options)
    );
});

// Notification click
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    if (event.action === 'explore') {
        event.waitUntil(clients.openWindow('/'));
    }
});
        <?php
        return ob_get_clean();
    }
    
    private function get_app_icon($size) {
        // Check if custom icon exists
        $custom_icon = get_option('webapp_app_icon', '');
        if (!empty($custom_icon)) {
            return $custom_icon;
        }
        
        // Check if site icon exists
        $site_icon_id = get_option('site_icon');
        if ($site_icon_id) {
            $icon_url = wp_get_attachment_image_url($site_icon_id, array($size, $size));
            if ($icon_url) {
                return $icon_url;
            }
        }
        
        // Fallback to default generated icon
        return $this->generate_default_icon($size);
    }
    
    private function generate_default_icon($size) {
        // Generate a simple colored icon with site initial
        $site_name = get_bloginfo('name');
        $initial = strtoupper(substr($site_name, 0, 1));
        $primary_color = get_option('webapp_primary_color', '#6366f1');
        
        // This would ideally generate an actual image, but for simplicity we'll use a placeholder
        return WEBAPP_PLUGIN_URL . 'assets/images/icons/default-' . $size . '.png';
    }
}