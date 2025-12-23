(function($) {
    // Shared state and configuration defaults for the plugin
    let defaults = {
        // Selector for the shared menu container
        menuSelector: "#popup-menu",

        // OPTIONAL: A secondary selector (like a modal/slider backdrop)
        // to bind the close event to, useful when document clicks are blocked.
        secondaryCloseSelector: null,

        // Animation settings
        animationDuration: 200, // Duration in ms for the fade animation

        // Function to map the clicked button's attributes to the required data fields.
        dataMapper: function($button) {
            // Returns all data-* attributes found on the button (e.g., {id: 1, custid: 'A', index: 5})
            return $button.data();
        },

        // Specific selector for items that need the data-id attribute set (e.g., 'li.menu-item')
        selectors: {
            menuItem: ".menu-item"
        },

        // The required callback function executed just before the menu is displayed.
        // Receives: (dataObject, menuElement)
        onMenuOpen: (data, menu) => {
            console.warn("Popup Menu: 'onMenuOpen' callback not defined. Menu item actions will not be set.");
        },
    };

    // Flag to ensure the document global listener is only attached once
    let isGlobalCaptureListenerAttached = false;

    /**
     * Finds and closes the currently visible and active menu.
     * @param {number} duration - The animation duration for the close.
     */
    function closeAnyMenu(duration) {
        // Find the currently active menu, regardless of its ID
        const $activeMenu = $('[data-active-button]:not(.hidden)');

        if ($activeMenu.length) {
             if (duration > 0) {
                 // Animated close
                 $activeMenu.stop(true, true).animate({ opacity: 0 }, duration, function() {
                     $activeMenu.addClass("hidden").removeData("active-button");
                 });
             } else {
                 // Instant close
                 $activeMenu.css('opacity', 0).addClass("hidden").removeData("active-button");
             }
        }
    }

    /**
     * Global click handler to close the menu if the click is outside the menu AND outside the button.
     * This function runs in the standard (bubbling) phase.
     * @param {Event} e - The click event.
     */
    function handleDocumentClick(e) {
        // Find the active menu to check if the click was inside it
        const $activeMenu = $('[data-active-button]:not(.hidden)');

        if ($activeMenu.length) {
             // Get the raw DOM element of the button that opened this menu (stored in data)
             const activeButtonElement = $activeMenu.data('active-button');

             // Check if the clicked target is inside the active menu OR inside the active button.
             const isClickInsideMenu = $(e.target).closest($activeMenu).length > 0;
             const isClickInsideButton = $(e.target).closest(activeButtonElement).length > 0;

             // If the click is NOT on the menu and NOT on the button, close it.
            if (!isClickInsideMenu && !isClickInsideButton) {
                // Instant close (no animation on global click)
                closeAnyMenu(0);
            }
        }
    }

    /**
     * Core logic for opening the menu, calculating position, and setting dynamic attributes.
     * @param {Event} e - The click event triggered by the menu button.
     */
    function handleButtonClick(e, settings) {
        e.stopPropagation(); // Prevent global document click handler from firing immediately

        const $button = $(e.currentTarget);
        // CRUCIAL: Get the correct menu element based on the settings for this instance
        const $menu = $(settings.menuSelector);

        // --- 1. DATA RETRIEVAL ---
        const dynamicData = settings.dataMapper($button, e);
        const genericId = dynamicData.id || dynamicData.invoiceId || dynamicData.productId || null;

        // Check if a DIFFERENT menu is currently open. If so, close it instantly.
        const $activeMenu = $('[data-active-button]:not(.hidden)');
        if ($activeMenu.length && $activeMenu[0] !== $menu[0]) {
             closeAnyMenu(0);
        }

        // If clicking the same button that's currently active, toggle close
        if ($menu.is(":visible") && $menu.data("active-button") === $button[0]) {
            // Animated Toggle OFF
            $menu.stop(true, true).animate({ opacity: 0 }, settings.animationDuration, function() {
                $menu.addClass("hidden").removeData("active-button");
            });
            return;
        }

        // Since we are opening a new menu or a menu attached to a new button,
        // ensure any previously open menu is closed (if the same menu is open but tied to a different button):
        if ($activeMenu.length && $activeMenu[0] === $menu[0] && $activeMenu.data("active-button") !== $button[0]) {
            closeAnyMenu(0);
        }

        // --- 2. SET GENERIC DATA & CALL EXTERNAL ACTION HANDLER ---
        if (genericId !== null) {
            $menu.find(settings.selectors.menuItem).attr("data-id", genericId);
        }
        settings.onMenuOpen(dynamicData, $menu);

        // --- 3. ROBUST VIEWPORT POSITIONING LOGIC (With Flip Logic) ---
        const buttonRect = $button[0].getBoundingClientRect();
        const buttonHeight = $button.outerHeight();

        // Temporarily ensure menu is visible for accurate height calculation
        $menu.css({ visibility: 'hidden', display: 'block', position: 'fixed' }).removeClass("hidden");
        const menuHeight = $menu.outerHeight();
        $menu.css({ visibility: '', display: '' }); // Reset properties

        const viewportHeight = $(window).height();

        const spaceBelow = viewportHeight - (buttonRect.top + buttonHeight);
        const spaceAbove = buttonRect.top;
        const offset = 5; // Spacing offset

        let topPosition;

        // Strategy: Show below if there's enough space, OR if space below is greater than space above.
        if (spaceBelow >= menuHeight || spaceBelow > spaceAbove) {
             // 1. Show BELOW the button (default position)
            topPosition = buttonRect.top + buttonHeight + offset;
        } else {
            // 2. Show ABOVE the button (flip position)
            topPosition = buttonRect.top - menuHeight - offset;
        }

        let leftPosition = buttonRect.left;

        // --- 4. DISPLAY MENU with ANIMATION (Toggle ON) ---
        $menu.css({
            position: "fixed",
            top: topPosition + "px",
            left: leftPosition + "px",
            opacity: 0, // Prepare for fade-in
        }).removeClass("hidden").data("active-button", $button[0]);

        $menu.stop(true, true).animate({
            opacity: 1
        }, settings.animationDuration);
    }

    /**
     * jQuery Plugin Definition
     * Initializes the popup menu functionality.
     */
    $.fn.popupMenu = function(options) {
        const settings = $.extend(true, {}, defaults, options);
        const buttonSelector = this.selector; // Get the selector used to call the plugin

        // CRUCIAL: Get the correct menu element for this instance
        const $menuInstance = $(settings.menuSelector);

        // 1. Button click handler (to open/toggle the menu)
        $(document).off("click.popupMenu", buttonSelector).on("click.popupMenu", buttonSelector, function(e) {
            handleButtonClick(e, settings);
        });

        $(document).on("click", function () {
            $menuInstance.addClass("hidden").removeData("active-button");
        });

        // 2. Hide menu when clicking anywhere outside (GLOBAL CLOSURE)

        // --- STANDARD BUBBLING PHASE BINDING ---
        // Bind to the document in the standard (bubbling) phase.
        if (!isGlobalCaptureListenerAttached) {
            // Use jQuery binding with a namespace to prevent conflicts
            $(document).on('click.popupMenuGlobal', handleDocumentClick);
            isGlobalCaptureListenerAttached = true;
        }

        // Additionally bind to secondary selector (if provided).
        if (settings.secondaryCloseSelector) {
            // Note: secondary selectors are bound multiple times if needed, unlike the document listener.
            $(settings.secondaryCloseSelector).off("click.popupMenuSecondary").on("click.popupMenuSecondary", handleDocumentClick);
        }

        // 3. Handle clicks inside the menu: stop propagation and close on item click.

        // 3a. Stop propagation on the menu container itself
        $menuInstance.off("click.popupMenuContainer").on("click.popupMenuContainer", function(e) {
            e.stopPropagation();
        });

        // 3b. Add a delegated handler to close the menu when any menu item is clicked
        $menuInstance.off("click.popupMenuItem", settings.selectors.menuItem)
             .on("click.popupMenuItem", settings.selectors.menuItem, function() {
            // Animated Toggle OFF - targets the specific menu that contains the clicked item
            const $clickedMenu = $(this).closest(settings.menuSelector);
            $clickedMenu.stop(true, true).animate({ opacity: 0 }, settings.animationDuration, function() {
                $clickedMenu.addClass("hidden").removeData("active-button");
            });
        });

        console.log(`PopupMenu plugin initialized for selector: ${buttonSelector} using menu: ${settings.menuSelector}`);
        return this; // Maintain chainability
    };

    // EXPOSE PUBLIC API: Attach directly to the jQuery object ($) for robustness.
    // Use $.popupMenuClose() to close any active menu from external code.
    $.popupMenuClose = function(duration = defaults.animationDuration) {
        closeAnyMenu(duration);
    };

})(jQuery);
