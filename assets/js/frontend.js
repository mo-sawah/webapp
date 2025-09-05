/**
 * WebAPP Frontend JavaScript
 *
 * @package WebAPP
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  const WebAppFrontend = {
    currentPage: 1,
    loading: false,
    hasMore: true,
    currentCategory: "",
    currentSearch: "",

    init: function () {
      this.createAppStructure();
      this.bindEvents();
      this.initTheme();
      this.initPWA();
      this.loadInitialContent();
    },

    createAppStructure: function () {
      // Hide original theme elements
      $("body").addClass("webapp-enabled");

      if (webapp_ajax.settings.dark_mode === "1") {
        $("body").addClass("webapp-dark-mode");
      }

      // Create app container
      const appHTML = this.generateAppHTML();
      $("body").prepend(appHTML);

      // Move original content
      const $originalContent = $("#main, #primary, .site-main, .main").first();
      if ($originalContent.length) {
        const posts = this.extractPostsFromContent($originalContent);
        this.renderPosts(posts);
        $originalContent.hide();
      }
    },

    generateAppHTML: function () {
      const settings = webapp_ajax.settings;
      let html = '<div class="webapp-container">';

      // Header
      if (settings.header_enabled === "1") {
        html += this.generateHeader();
      }

      // Search section
      if (settings.search_enabled === "1") {
        html += this.generateSearchSection();
      }

      // Content area
      html += '<div class="webapp-content">';
      html += '<div class="webapp-section">';
      html += '<div class="webapp-section-header">';
      html += '<h2 class="webapp-section-title">' + "Featured" + "</h2>";
      html += '<a href="#" class="webapp-see-all">' + "See all" + "</a>";
      html += "</div>";
      html += '<div class="webapp-featured-container"></div>';
      html += "</div>";

      html += '<div class="webapp-section">';
      html += '<div class="webapp-section-header">';
      html += '<h2 class="webapp-section-title">' + "Latest Posts" + "</h2>";
      html += '<a href="#" class="webapp-see-all">' + "See all" + "</a>";
      html += "</div>";

      // Category pills
      if (settings.categories_enabled === "1") {
        html += '<div class="webapp-category-pills"></div>';
      }

      html += '<div class="webapp-news-list"></div>';
      html +=
        '<div class="webapp-load-more-container" style="text-align: center; margin-top: 20px;">';
      html +=
        '<button class="webapp-load-more-btn" style="display: none;">Load More</button>';
      html += "</div>";
      html += "</div>";
      html += "</div>";

      // Bottom navigation
      if (settings.bottom_nav_enabled === "1") {
        html += this.generateBottomNav();
      }

      html += "</div>";

      return html;
    },

    generateHeader: function () {
      return `
                <div class="webapp-header">
                    <div class="webapp-header-left">
                        <a href="${window.location.origin}" class="webapp-logo">
                            <div class="webapp-logo-icon">W</div>
                            <div class="webapp-logo-text">${
                              webapp_ajax.strings.app_name || document.title
                            }</div>
                        </a>
                    </div>
                    <div class="webapp-header-actions">
                        <button class="webapp-header-btn webapp-notifications-btn">
                            🔔
                            <span class="webapp-notification-badge">3</span>
                        </button>
                        <button class="webapp-header-btn webapp-theme-toggle">
                            ${
                              webapp_ajax.settings.dark_mode === "1"
                                ? "☀️"
                                : "🌙"
                            }
                        </button>
                    </div>
                </div>
            `;
    },

    generateSearchSection: function () {
      return `
                <div class="webapp-search-section">
                    <div class="webapp-search-container">
                        <span class="webapp-search-icon">🔍</span>
                        <input type="text" class="webapp-search-bar" placeholder="${webapp_ajax.strings.search_placeholder}">
                        <button class="webapp-filter-btn">⚙️</button>
                    </div>
                </div>
            `;
    },

    generateBottomNav: function () {
      return `
                <div class="webapp-bottom-nav">
                    <div class="webapp-nav-items">
                        <a href="#" class="webapp-nav-item active" data-tab="home">
                            <div class="webapp-nav-icon">🏠</div>
                            <span>Home</span>
                        </a>
                        <a href="#" class="webapp-nav-item" data-tab="search">
                            <div class="webapp-nav-icon">🔍</div>
                            <span>Search</span>
                        </a>
                        <a href="#" class="webapp-nav-item" data-tab="bookmarks">
                            <div class="webapp-nav-icon">🔖</div>
                            <span>Saved</span>
                        </a>
                        <a href="#" class="webapp-nav-item" data-tab="menu">
                            <div class="webapp-nav-icon">📊</div>
                            <span>Menu</span>
                        </a>
                        <a href="#" class="webapp-nav-item" data-tab="profile">
                            <div class="webapp-nav-icon">👤</div>
                            <span>Profile</span>
                        </a>
                    </div>
                </div>
            `;
    },

    bindEvents: function () {
      // Theme toggle
      $(document).on(
        "click",
        ".webapp-theme-toggle",
        this.toggleTheme.bind(this)
      );

      // Search functionality
      let searchTimeout;
      $(document).on("input", ".webapp-search-bar", function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          WebAppFrontend.handleSearch($(this).val());
        }, 500);
      });

      // Category pills
      $(document).on(
        "click",
        ".webapp-category-pill",
        this.handleCategoryClick.bind(this)
      );

      // Load more
      $(document).on(
        "click",
        ".webapp-load-more-btn",
        this.loadMore.bind(this)
      );

      // Like functionality
      $(document).on("click", ".webapp-like-btn", this.handleLike.bind(this));

      // Bookmark functionality
      $(document).on(
        "click",
        ".webapp-bookmark-btn",
        this.handleBookmark.bind(this)
      );

      // Navigation
      $(document).on(
        "click",
        ".webapp-nav-item",
        this.handleNavigation.bind(this)
      );

      // Post clicks
      $(document).on(
        "click",
        ".webapp-news-card",
        this.handlePostClick.bind(this)
      );

      // Smooth scrolling for category pills
      this.initCategoryScroll();

      // Pull to refresh
      this.initPullToRefresh();
    },

    initTheme: function () {
      // Apply saved theme preferences
      const savedTheme = localStorage.getItem("webapp_theme_mode");
      if (savedTheme) {
        $("body").toggleClass("webapp-dark-mode", savedTheme === "dark");
        this.updateThemeToggleIcon();
      }
    },

    initPWA: function () {
      // Register service worker
      if ("serviceWorker" in navigator) {
        navigator.serviceWorker
          .register("/sw.js")
          .then(function (registration) {
            console.log("ServiceWorker registered");
          })
          .catch(function (error) {
            console.log("ServiceWorker registration failed");
          });
      }

      // Handle install prompt
      let deferredPrompt;
      window.addEventListener("beforeinstallprompt", (e) => {
        e.preventDefault();
        deferredPrompt = e;
        $(".webapp-install-btn").show();
      });

      $(document).on("click", ".webapp-install-btn", function () {
        if (deferredPrompt) {
          deferredPrompt.prompt();
          deferredPrompt.userChoice.then((choiceResult) => {
            deferredPrompt = null;
          });
        }
      });
    },

    loadInitialContent: function () {
      this.loadCategories();
      this.loadFeaturedPost();
      this.loadPosts();
    },

    loadCategories: function () {
      // Get categories from existing page or via AJAX
      const categories = this.extractCategories();
      const categoryHTML = categories
        .map(
          (cat) =>
            `<a href="#" class="webapp-category-pill ${
              cat.active ? "active" : ""
            }" data-category="${cat.slug}">
                    ${cat.name}
                </a>`
        )
        .join("");

      $(".webapp-category-pills").html(categoryHTML);
    },

    extractCategories: function () {
      // Default categories
      const defaultCategories = [
        { name: "All", slug: "", active: true },
        { name: "Technology", slug: "technology", active: false },
        { name: "Business", slug: "business", active: false },
        { name: "Lifestyle", slug: "lifestyle", active: false },
        { name: "Sports", slug: "sports", active: false },
        { name: "Health", slug: "health", active: false },
      ];

      // Try to extract from existing page
      const existingCategories = [];
      $(".cat-links a, .category a").each(function () {
        const name = $(this).text().trim();
        const href = $(this).attr("href");
        if (name && href) {
          const slug = href
            .split("/")
            .filter((s) => s)
            .pop();
          existingCategories.push({ name, slug, active: false });
        }
      });

      return existingCategories.length > 0
        ? [{ name: "All", slug: "", active: true }, ...existingCategories]
        : defaultCategories;
    },

    loadFeaturedPost: function () {
      // Try to find featured post from existing content
      const $featuredPost = $(
        ".featured-post, .sticky, .wp-block-latest-posts__featured-image"
      ).first();
      let featuredHTML = "";

      if ($featuredPost.length) {
        const title =
          $featuredPost.find("h1, h2, h3, .entry-title").first().text() ||
          "Featured Article";
        const link = $featuredPost.find("a").first().attr("href") || "#";

        featuredHTML = `
                    <div class="webapp-featured-card" data-href="${link}">
                        <div class="webapp-featured-image">
                            <div class="webapp-featured-overlay">
                                <h3 class="webapp-featured-title">${title}</h3>
                                <button class="webapp-featured-btn">Read Now</button>
                            </div>
                        </div>
                    </div>
                `;
      } else {
        // Default featured post
        featuredHTML = `
                    <div class="webapp-featured-card">
                        <div class="webapp-featured-image">
                            <div class="webapp-featured-overlay">
                                <h3 class="webapp-featured-title">Welcome to Your New Web App Experience</h3>
                                <button class="webapp-featured-btn">Get Started</button>
                            </div>
                        </div>
                    </div>
                `;
      }

      $(".webapp-featured-container").html(featuredHTML);
    },

    loadPosts: function (reset = false) {
      if (this.loading) return;

      this.loading = true;
      $(".webapp-load-more-btn")
        .addClass("webapp-loading")
        .text(webapp_ajax.strings.loading);

      if (reset) {
        this.currentPage = 1;
        this.hasMore = true;
        $(".webapp-news-list").empty();
      }

      // Try to extract posts from existing content first
      if (this.currentPage === 1) {
        const existingPosts = this.extractPostsFromContent();
        if (existingPosts.length > 0) {
          this.renderPosts(existingPosts);
          this.loading = false;
          $(".webapp-load-more-btn")
            .removeClass("webapp-loading")
            .text("Load More")
            .show();
          return;
        }
      }

      // Fallback to AJAX
      $.ajax({
        url: webapp_ajax.ajax_url,
        type: "POST",
        data: {
          action: "webapp_get_posts",
          nonce: webapp_ajax.nonce,
          page: this.currentPage,
          category: this.currentCategory,
          search: this.currentSearch,
        },
        success: (response) => {
          if (response.success) {
            this.renderPosts(response.data.posts);
            this.hasMore = response.data.has_more;
            this.currentPage++;

            if (!this.hasMore) {
              $(".webapp-load-more-btn").hide();
            }
          } else {
            this.showError(webapp_ajax.strings.error);
          }
        },
        error: () => {
          this.showError(webapp_ajax.strings.error);
        },
        complete: () => {
          this.loading = false;
          $(".webapp-load-more-btn")
            .removeClass("webapp-loading")
            .text("Load More");
        },
      });
    },

    extractPostsFromContent: function ($container) {
      const posts = [];
      const $articles = $container
        ? $container.find("article, .post, .entry")
        : $("article, .post, .entry");

      $articles.each(function () {
        const $post = $(this);
        const $title = $post
          .find("h1, h2, h3, .entry-title, .post-title")
          .first();
        const $excerpt = $post
          .find(".excerpt, .entry-summary, .post-excerpt")
          .first();
        const $image = $post.find("img").first();
        const $link = $post.find("a").first();
        const $author = $post.find(".author, .by-author").first();
        const $date = $post.find(".date, .published, .post-date").first();

        if ($title.length) {
          posts.push({
            id: $post.attr("id") || Math.random().toString(36).substr(2, 9),
            title: $title.text().trim(),
            excerpt:
              $excerpt.text().trim() ||
              $title.text().trim().substring(0, 100) + "...",
            permalink: $link.attr("href") || "#",
            thumbnail: $image.attr("src") || "",
            author: $author.text().trim() || "Author",
            date: $date.text().trim() || "Recently",
            categories: ["General"],
            likes: Math.floor(Math.random() * 100),
            comments: Math.floor(Math.random() * 20),
            is_liked: false,
            is_bookmarked: false,
          });
        }
      });

      return posts.slice(0, 10); // Limit to 10 posts
    },

    renderPosts: function (posts) {
      const postsHTML = posts
        .map((post) => this.generatePostHTML(post))
        .join("");

      if (this.currentPage === 1 || $(".webapp-news-list").is(":empty")) {
        $(".webapp-news-list").html(postsHTML);
      } else {
        $(".webapp-news-list").append(postsHTML);
      }

      // Add entrance animation
      $(".webapp-news-list .webapp-news-card:not(.animated)").each(function (
        index
      ) {
        const $card = $(this);
        setTimeout(() => {
          $card.addClass("webapp-fade-in animated");
        }, index * 100);
      });

      $(".webapp-load-more-btn").toggle(this.hasMore);
    },

    generatePostHTML: function (post) {
      const imageStyle = post.thumbnail
        ? `background-image: url('${post.thumbnail}')`
        : `background: linear-gradient(135deg, #${Math.floor(
            Math.random() * 16777215
          ).toString(16)}, #${Math.floor(Math.random() * 16777215).toString(
            16
          )})`;

      return `
                <div class="webapp-news-card" data-post-id="${
                  post.id
                }" data-href="${post.permalink}">
                    <div class="webapp-news-content">
                        <div class="webapp-news-image" style="${imageStyle}"></div>
                        <div class="webapp-news-info">
                            <h3 class="webapp-news-title">${post.title}</h3>
                            <div class="webapp-news-source">
                                <div class="webapp-source-icon">${post.author
                                  .charAt(0)
                                  .toUpperCase()}</div>
                                <span class="webapp-source-name">${
                                  post.author
                                }</span>
                                <span class="webapp-source-category">${
                                  post.categories[0] || "General"
                                }</span>
                            </div>
                            <div class="webapp-news-stats">
                                <div class="webapp-stat-item">
                                    <button class="webapp-like-btn ${
                                      post.is_liked ? "liked" : ""
                                    }" data-post-id="${post.id}">
                                        👍
                                    </button>
                                    <span class="webapp-like-count">${this.formatNumber(
                                      post.likes
                                    )}</span>
                                </div>
                                <div class="webapp-stat-item">
                                    <span>💬</span>
                                    <span>${this.formatNumber(
                                      post.comments
                                    )}</span>
                                </div>
                                <button class="webapp-bookmark-btn ${
                                  post.is_bookmarked ? "bookmarked" : ""
                                }" data-post-id="${post.id}">
                                    🔖
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    },

    formatNumber: function (num) {
      if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + "M";
      } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + "K";
      }
      return num.toString();
    },

    toggleTheme: function () {
      const $body = $("body");
      const isDark = $body.hasClass("webapp-dark-mode");

      $body.toggleClass("webapp-dark-mode", !isDark);
      localStorage.setItem("webapp_theme_mode", !isDark ? "dark" : "light");

      this.updateThemeToggleIcon();

      // Add transition effect
      $body.addClass("webapp-theme-transition");
      setTimeout(() => $body.removeClass("webapp-theme-transition"), 300);
    },

    updateThemeToggleIcon: function () {
      const isDark = $("body").hasClass("webapp-dark-mode");
      $(".webapp-theme-toggle").text(isDark ? "☀️" : "🌙");
    },

    handleSearch: function (query) {
      this.currentSearch = query;
      this.loadPosts(true);

      // Update active category
      $(".webapp-category-pill").removeClass("active");
      $('.webapp-category-pill[data-category=""]').addClass("active");
    },

    handleCategoryClick: function (e) {
      e.preventDefault();
      const $pill = $(e.currentTarget);
      const category = $pill.data("category");

      $(".webapp-category-pill").removeClass("active");
      $pill.addClass("active");

      this.currentCategory = category;
      this.currentSearch = "";
      $(".webapp-search-bar").val("");

      this.loadPosts(true);
    },

    loadMore: function () {
      this.loadPosts();
    },

    handleLike: function (e) {
      e.preventDefault();
      e.stopPropagation();

      const $btn = $(e.currentTarget);
      const postId = $btn.data("post-id");
      const $count = $btn.siblings(".webapp-like-count");
      const isLiked = $btn.hasClass("liked");

      // Optimistic UI update
      $btn.toggleClass("liked");
      let currentCount = parseInt($count.text().replace(/[^\d]/g, "")) || 0;
      currentCount += isLiked ? -1 : 1;
      $count.text(this.formatNumber(Math.max(0, currentCount)));

      // Add animation
      $btn.addClass("webapp-pulse");
      setTimeout(() => $btn.removeClass("webapp-pulse"), 300);

      // Send AJAX request
      $.ajax({
        url: webapp_ajax.ajax_url,
        type: "POST",
        data: {
          action: "webapp_toggle_like",
          nonce: webapp_ajax.nonce,
          post_id: postId,
        },
        success: (response) => {
          if (response.success) {
            $count.text(this.formatNumber(response.data.count));
            $btn.toggleClass("liked", response.data.liked);
          }
        },
        error: () => {
          // Revert on error
          $btn.toggleClass("liked");
          currentCount += isLiked ? 1 : -1;
          $count.text(this.formatNumber(Math.max(0, currentCount)));
        },
      });
    },

    handleBookmark: function (e) {
      e.preventDefault();
      e.stopPropagation();

      const $btn = $(e.currentTarget);
      const postId = $btn.data("post-id");
      const isBookmarked = $btn.hasClass("bookmarked");

      // Optimistic UI update
      $btn.toggleClass("bookmarked");

      // Add animation
      $btn.addClass("webapp-bounce");
      setTimeout(() => $btn.removeClass("webapp-bounce"), 300);

      // Send AJAX request
      $.ajax({
        url: webapp_ajax.ajax_url,
        type: "POST",
        data: {
          action: "webapp_toggle_bookmark",
          nonce: webapp_ajax.nonce,
          post_id: postId,
        },
        success: (response) => {
          if (response.success) {
            $btn.toggleClass("bookmarked", response.data.bookmarked);
            this.showToast(response.data.message);
          }
        },
        error: () => {
          // Revert on error
          $btn.toggleClass("bookmarked");
          this.showToast("Please log in to bookmark posts");
        },
      });
    },

    handleNavigation: function (e) {
      e.preventDefault();
      const $item = $(e.currentTarget);
      const tab = $item.data("tab");

      $(".webapp-nav-item").removeClass("active");
      $item.addClass("active");

      // Handle different tabs
      switch (tab) {
        case "home":
          this.showHomeContent();
          break;
        case "search":
          $(".webapp-search-bar").focus();
          break;
        case "bookmarks":
          this.showBookmarkedPosts();
          break;
        case "menu":
          this.showMenu();
          break;
        case "profile":
          this.showProfile();
          break;
      }
    },

    handlePostClick: function (e) {
      if ($(e.target).closest("button").length) {
        return; // Don't navigate if clicking a button
      }

      const href = $(e.currentTarget).data("href");
      if (href && href !== "#") {
        window.location.href = href;
      }
    },

    showHomeContent: function () {
      $(".webapp-section").show();
      this.currentCategory = "";
      this.currentSearch = "";
      $(".webapp-search-bar").val("");
      $(".webapp-category-pill").removeClass("active");
      $('.webapp-category-pill[data-category=""]').addClass("active");
      this.loadPosts(true);
    },

    showBookmarkedPosts: function () {
      // Implementation for bookmarked posts
      this.showToast("Bookmarked posts feature coming soon!");
    },

    showMenu: function () {
      // Implementation for menu
      this.showToast("Menu feature coming soon!");
    },

    showProfile: function () {
      // Implementation for profile
      this.showToast("Profile feature coming soon!");
    },

    initCategoryScroll: function () {
      const $container = $(".webapp-category-pills");
      let isDown = false;
      let startX;
      let scrollLeft;

      $container.on("mousedown", function (e) {
        isDown = true;
        startX = e.pageX - $container.offset().left;
        scrollLeft = $container.scrollLeft();
      });

      $container.on("mouseleave mouseup", function () {
        isDown = false;
      });

      $container.on("mousemove", function (e) {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - $container.offset().left;
        const walk = (x - startX) * 2;
        $container.scrollLeft(scrollLeft - walk);
      });
    },

    initPullToRefresh: function () {
      let startY = 0;
      let pullDistance = 0;
      const pullThreshold = 80;

      $(document).on("touchstart", function (e) {
        startY = e.originalEvent.touches[0].clientY;
      });

      $(document).on("touchmove", function (e) {
        if (window.scrollY === 0) {
          pullDistance = e.originalEvent.touches[0].clientY - startY;
          if (pullDistance > 0 && pullDistance < pullThreshold) {
            $("body").css("transform", `translateY(${pullDistance * 0.5}px)`);
          }
        }
      });

      $(document).on("touchend", function () {
        if (pullDistance > pullThreshold) {
          WebAppFrontend.refreshContent();
        }
        $("body").css("transform", "translateY(0)");
        pullDistance = 0;
      });
    },

    refreshContent: function () {
      this.showToast("Refreshing content...");
      this.loadPosts(true);
      this.loadFeaturedPost();
    },

    showToast: function (message, type = "info") {
      const $toast = $(
        `<div class="webapp-toast webapp-toast-${type}">${message}</div>`
      );
      $("body").append($toast);

      setTimeout(() => $toast.addClass("show"), 100);

      setTimeout(() => {
        $toast.removeClass("show");
        setTimeout(() => $toast.remove(), 300);
      }, 3000);
    },

    showError: function (message) {
      this.showToast(message, "error");
    },
  };

  // Initialize when document is ready
  $(document).ready(function () {
    // Only initialize if WebAPP is enabled
    if (typeof webapp_ajax !== "undefined") {
      WebAppFrontend.init();
    }
  });

  // Add CSS for animations and toast
  const additionalCSS = `
        <style>
        .webapp-theme-transition * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease !important;
        }
        
        .webapp-pulse {
            animation: webapp-pulse 0.3s ease;
        }
        
        @keyframes webapp-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .webapp-bounce {
            animation: webapp-bounce 0.3s ease;
        }
        
        @keyframes webapp-bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }
        
        .webapp-toast {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--webapp-bg-card);
            border: 1px solid var(--webapp-border);
            border-radius: 25px;
            padding: 12px 20px;
            color: var(--webapp-text);
            font-size: 14px;
            z-index: 10000;
            box-shadow: var(--webapp-shadow-lg);
            transition: transform 0.3s ease, opacity 0.3s ease;
            opacity: 0;
        }
        
        .webapp-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        
        .webapp-toast-error {
            background: #fee;
            border-color: #fcc;
            color: #c33;
        }
        
        .webapp-toast-success {
            background: #efe;
            border-color: #cfc;
            color: #363;
        }
        </style>
    `;

  $("head").append(additionalCSS);
})(jQuery);
