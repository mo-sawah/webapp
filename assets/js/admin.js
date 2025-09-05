/**
 * WebAPP Admin JavaScript
 *
 * @package WebAPP
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  const WebAppAdmin = {
    init: function () {
      this.bindEvents();
      this.initColorPickers();
      this.initThemeCards();
      this.initToggles();
      this.initPreview();
      this.initQuickActions();
    },

    bindEvents: function () {
      // Save settings
      $("#webapp-save-btn").on("click", this.saveSettings.bind(this));

      // Preview
      $("#webapp-preview-btn").on("click", this.openPreview.bind(this));

      // Modal close
      $(".webapp-modal-close, .webapp-modal").on(
        "click",
        this.closeModal.bind(this)
      );
      $(".webapp-modal-content").on("click", function (e) {
        e.stopPropagation();
      });

      // Form changes
      $("#webapp-settings-form").on("change", this.markUnsaved.bind(this));

      // Keyboard shortcuts
      $(document).on("keydown", this.handleKeyboard.bind(this));
    },

    initColorPickers: function () {
      $('.webapp-color-input input[type="color"]').each(function () {
        const $colorInput = $(this);
        const $textInput = $colorInput.siblings(".webapp-color-text");

        $colorInput.on("change", function () {
          $textInput.val(this.value.toUpperCase());
          WebAppAdmin.updateThemePreview();
        });

        $textInput.on("change", function () {
          if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            $colorInput.val(this.value);
            WebAppAdmin.updateThemePreview();
          }
        });
      });
    },

    initThemeCards: function () {
      $(".webapp-theme-card").on("click", function () {
        const $card = $(this);
        const theme = $card.data("theme");

        // Update selection
        $(".webapp-theme-card").removeClass("active");
        $card.addClass("active");
        $card.find('input[type="radio"]').prop("checked", true);

        // Update colors based on theme
        WebAppAdmin.updateColorsForTheme(theme);

        // Update preview
        WebAppAdmin.updateThemePreview();

        // Add animation
        $card.addClass("webapp-fade-in");
        setTimeout(() => $card.removeClass("webapp-fade-in"), 300);
      });
    },

    initToggles: function () {
      $(".webapp-toggle").on("click", function (e) {
        if (e.target.tagName !== "INPUT") {
          const $checkbox = $(this).find('input[type="checkbox"]');
          $checkbox
            .prop("checked", !$checkbox.prop("checked"))
            .trigger("change");
        }
      });

      // Special handling for main enable toggle
      $('input[name="webapp_enabled"]').on("change", function () {
        const isEnabled = $(this).is(":checked");
        $(".webapp-settings-section:not(:first)").toggleClass(
          "webapp-disabled",
          !isEnabled
        );

        if (isEnabled) {
          WebAppAdmin.showNotification(
            "WebAPP enabled! Don't forget to save your changes.",
            "success"
          );
        } else {
          WebAppAdmin.showNotification("WebAPP disabled.", "warning");
        }
      });
    },

    initPreview: function () {
      // Auto-refresh preview on changes
      let previewTimeout;
      $("#webapp-settings-form").on("change", function () {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(() => {
          if ($("#webapp-preview-modal").is(":visible")) {
            WebAppAdmin.refreshPreview();
          }
        }, 1000);
      });
    },

    initQuickActions: function () {
      $("#webapp-reset-btn").on("click", this.resetSettings.bind(this));
      $("#webapp-export-btn").on("click", this.exportSettings.bind(this));
      $("#webapp-import-btn").on("click", this.importSettings.bind(this));
    },

    saveSettings: function (e) {
      e.preventDefault();

      const $btn = $("#webapp-save-btn");
      const originalText = $btn.text();

      $btn.addClass("webapp-loading").text("Saving...");

      const formData = new FormData();
      formData.append("action", "webapp_save_settings");
      formData.append("nonce", webapp_admin.nonce);

      // Collect form data
      $("#webapp-settings-form")
        .serializeArray()
        .forEach(function (item) {
          formData.append(item.name.replace("webapp_", ""), item.value);
        });

      // Handle checkboxes
      $('input[type="checkbox"]').each(function () {
        const name = $(this).attr("name");
        if (name && name.startsWith("webapp_")) {
          const key = name.replace("webapp_", "");
          if (!$(this).is(":checked")) {
            formData.set(key, "0");
          }
        }
      });

      $.ajax({
        url: webapp_admin.ajax_url,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          if (response.success) {
            WebAppAdmin.showNotification(response.data.message, "success");
            WebAppAdmin.markSaved();
          } else {
            WebAppAdmin.showNotification(
              "Error saving settings: " + response.data,
              "error"
            );
          }
        },
        error: function () {
          WebAppAdmin.showNotification(
            "Network error. Please try again.",
            "error"
          );
        },
        complete: function () {
          $btn.removeClass("webapp-loading").text(originalText);
        },
      });
    },

    resetSettings: function () {
      if (
        !confirm(
          "Are you sure you want to reset all settings to defaults? This cannot be undone."
        )
      ) {
        return;
      }

      const $btn = $("#webapp-reset-btn");
      const originalHtml = $btn.html();

      $btn.addClass("webapp-loading").html("<span>Resetting...</span>");

      $.ajax({
        url: webapp_admin.ajax_url,
        type: "POST",
        data: {
          action: "webapp_reset_settings",
          nonce: webapp_admin.nonce,
        },
        success: function (response) {
          if (response.success) {
            WebAppAdmin.showNotification(response.data.message, "success");
            setTimeout(() => location.reload(), 1000);
          } else {
            WebAppAdmin.showNotification("Error resetting settings.", "error");
          }
        },
        error: function () {
          WebAppAdmin.showNotification(
            "Network error. Please try again.",
            "error"
          );
        },
        complete: function () {
          $btn.removeClass("webapp-loading").html(originalHtml);
        },
      });
    },

    exportSettings: function () {
      const settings = {};

      $("#webapp-settings-form")
        .serializeArray()
        .forEach(function (item) {
          settings[item.name] = item.value;
        });

      // Handle checkboxes
      $('input[type="checkbox"]').each(function () {
        const name = $(this).attr("name");
        if (name && name.startsWith("webapp_")) {
          settings[name] = $(this).is(":checked") ? "1" : "0";
        }
      });

      const dataStr = JSON.stringify(settings, null, 2);
      const dataBlob = new Blob([dataStr], { type: "application/json" });

      const link = document.createElement("a");
      link.href = URL.createObjectURL(dataBlob);
      link.download =
        "webapp-settings-" + new Date().toISOString().split("T")[0] + ".json";
      link.click();

      WebAppAdmin.showNotification(
        "Settings exported successfully!",
        "success"
      );
    },

    importSettings: function () {
      const input = document.createElement("input");
      input.type = "file";
      input.accept = ".json";

      input.onchange = function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
          try {
            const settings = JSON.parse(e.target.result);
            WebAppAdmin.applyImportedSettings(settings);
            WebAppAdmin.showNotification(
              "Settings imported successfully!",
              "success"
            );
          } catch (error) {
            WebAppAdmin.showNotification("Invalid settings file.", "error");
          }
        };
        reader.readAsText(file);
      };

      input.click();
    },

    applyImportedSettings: function (settings) {
      Object.keys(settings).forEach(function (key) {
        const $input = $('[name="' + key + '"]');
        if ($input.length) {
          if ($input.attr("type") === "checkbox") {
            $input.prop("checked", settings[key] === "1");
          } else {
            $input.val(settings[key]);
          }
        }
      });

      // Update theme cards
      const selectedTheme = settings["webapp_theme"];
      if (selectedTheme) {
        $(".webapp-theme-card").removeClass("active");
        $('.webapp-theme-card[data-theme="' + selectedTheme + '"]').addClass(
          "active"
        );
      }

      // Update color picker displays
      $('.webapp-color-input input[type="color"]').each(function () {
        const $textInput = $(this).siblings(".webapp-color-text");
        $textInput.val(this.value.toUpperCase());
      });

      this.markUnsaved();
    },

    updateColorsForTheme: function (theme) {
      const themeColors = {
        modern: { primary: "#6366f1", secondary: "#8b5cf6" },
        news: { primary: "#dc2626", secondary: "#ea580c" },
        magazine: { primary: "#7c3aed", secondary: "#db2777" },
        minimal: { primary: "#374151", secondary: "#6b7280" },
        dark: { primary: "#f59e0b", secondary: "#ef4444" },
      };

      if (themeColors[theme]) {
        $("#webapp_primary_color").val(themeColors[theme].primary);
        $("#webapp_secondary_color").val(themeColors[theme].secondary);
        $(".webapp-color-text").each(function () {
          $(this).val(
            $(this).siblings('input[type="color"]').val().toUpperCase()
          );
        });
      }
    },

    updateThemePreview: function () {
      if ($("#webapp-preview-modal").is(":visible")) {
        this.refreshPreview();
      }
    },

    openPreview: function () {
      $("#webapp-preview-modal").fadeIn(300);
      this.refreshPreview();
    },

    closeModal: function (e) {
      if (e.target === e.currentTarget) {
        $(".webapp-modal").fadeOut(300);
      }
    },

    refreshPreview: function () {
      const $frame = $("#webapp-preview-frame");
      const currentSrc = $frame.attr("src");
      const separator = currentSrc.includes("?") ? "&" : "?";
      $frame.attr(
        "src",
        currentSrc + separator + "preview_refresh=" + Date.now()
      );
    },

    markUnsaved: function () {
      $("#webapp-save-btn").addClass("unsaved").text("Save Changes *");
    },

    markSaved: function () {
      $("#webapp-save-btn").removeClass("unsaved").text("Save Changes");
    },

    showNotification: function (message, type = "info") {
      const $notification = $(
        '<div class="webapp-notification ' + type + '">' + message + "</div>"
      );
      $("body").append($notification);

      setTimeout(() => $notification.addClass("show"), 100);

      setTimeout(() => {
        $notification.removeClass("show");
        setTimeout(() => $notification.remove(), 300);
      }, 4000);
    },

    handleKeyboard: function (e) {
      // Ctrl/Cmd + S to save
      if ((e.ctrlKey || e.metaKey) && e.key === "s") {
        e.preventDefault();
        this.saveSettings(e);
      }

      // Escape to close modal
      if (e.key === "Escape") {
        $(".webapp-modal").fadeOut(300);
      }
    },
  };

  // Initialize when document is ready
  $(document).ready(function () {
    WebAppAdmin.init();

    // Add smooth scroll behavior
    $("html").css("scroll-behavior", "smooth");

    // Add entrance animations
    $(".webapp-settings-section").each(function (index) {
      $(this)
        .css("animation-delay", index * 0.1 + "s")
        .addClass("webapp-slide-up");
    });

    // Auto-save draft functionality
    let autoSaveTimeout;
    $("#webapp-settings-form").on("input change", function () {
      clearTimeout(autoSaveTimeout);
      autoSaveTimeout = setTimeout(() => {
        localStorage.setItem(
          "webapp_draft_settings",
          JSON.stringify({
            timestamp: Date.now(),
            data: $("#webapp-settings-form").serialize(),
          })
        );
      }, 2000);
    });

    // Load draft on page load
    const draft = localStorage.getItem("webapp_draft_settings");
    if (draft) {
      try {
        const draftData = JSON.parse(draft);
        // Only restore if draft is less than 1 hour old
        if (Date.now() - draftData.timestamp < 3600000) {
          const params = new URLSearchParams(draftData.data);
          let hasChanges = false;

          for (const [key, value] of params) {
            const $input = $('[name="' + key + '"]');
            if ($input.length && $input.val() !== value) {
              hasChanges = true;
              break;
            }
          }

          if (hasChanges) {
            if (
              confirm(
                "You have unsaved changes from a previous session. Would you like to restore them?"
              )
            ) {
              for (const [key, value] of params) {
                const $input = $('[name="' + key + '"]');
                if ($input.length) {
                  if ($input.attr("type") === "checkbox") {
                    $input.prop("checked", value === "1");
                  } else {
                    $input.val(value);
                  }
                }
              }
              WebAppAdmin.markUnsaved();
            }
          }
        }
      } catch (e) {
        console.warn("Failed to parse draft settings");
      }
    }
  });
})(jQuery);
