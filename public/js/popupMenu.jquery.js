(function($) {
    // Shared state and configuration defaults for the plugin
    let defaults = {
        // Selector for the shared menu container
        menuSelector: "#popup-menu",

        // OPTIONAL: The selector for the buttons.
        // REQUIRED for Livewire/Dynamic content to work without refreshes in jQuery 3.0+
        buttonSelector: null,

        // OPTIONAL: A secondary selector (like a modal/slider backdrop)
        secondaryCloseSelector: null,

        // Animation settings
        animationDuration: 200,

        dataMapper: function($button) {
            // $.data() returns data from the data-* attributes.
            return $button.data();
        },

        selectors: {
            menuItem: ".menu-item"
        },

        onMenuOpen: (data, menu) => {
            console.warn("Popup Menu: 'onMenuOpen' callback not defined.");
        },
    };

    /**
     * Finds and closes the currently visible and active menu.
     */
    function closeAnyMenu(duration) {
        // Find any menu that is NOT hidden and has the active-button attribute
        const $activeMenu = $('[data-active-button]:not(.hidden)');

        if ($activeMenu.length) {
             if (duration > 0) {
                 // Animated close
                 $activeMenu.stop(true, true).animate({ opacity: 0 }, duration, function() {
                     $activeMenu.addClass("hidden")
                                .removeData("active-button") // Clear jQuery data
                                .removeAttr("data-active-button"); // Clear HTML attribute
                 });
             } else {
                 // Instant close
                 $activeMenu.stop(true, true).css('opacity', 0)
                            .addClass("hidden")
                            .removeData("active-button")
                            .removeAttr("data-active-button");
             }
        }
    }

    /**
     * Global click handler to close menu when clicking outside.
     */
    function handleDocumentClick(e) {
        // Use the attribute selector which now works because we set the attr explicitly
        const $activeMenu = $('[data-active-button]:not(.hidden)');

        if ($activeMenu.length) {
             const activeButtonElement = $activeMenu.data('active-button');

             // Check if the click target is inside the active menu or the active button
             const isClickInsideMenu = $(e.target).closest($activeMenu).length > 0;

             // Check if the target is the button.
             const isClickInsideButton = activeButtonElement && (
                 $(e.target).closest(activeButtonElement).length > 0 ||
                 !document.contains(activeButtonElement)
             );

            if (!isClickInsideMenu && !isClickInsideButton) {
                closeAnyMenu(0);
            }
        }
    }

    /**
     * Core logic for opening/toggling the menu.
     */
    function handleButtonClick(e, settings) {
        // Stop bubbling so handleDocumentClick doesn't immediately close the menu we are opening
        e.stopPropagation();

        const $button = $(e.currentTarget);
        const $menu = $(settings.menuSelector);

        // Determine if THIS menu is already open
        const activeBtn = $menu.data("active-button");
        const isVisible = $menu.is(":visible") && $menu.css('opacity') > 0;

        // --- IMPROVED TOGGLE LOGIC ---
        if (isVisible && activeBtn) {
            // Case 1: Exact DOM match (Static toggle) -> Close and Stop.
            if (activeBtn === $button[0]) {
                closeAnyMenu(settings.animationDuration);
                return;
            }

            // Case 2: Button replaced by Livewire (DOM Node different, but logically same)
            // If the active button is no longer in the document, it was likely replaced.
            if (!document.contains(activeBtn)) {
                 const oldId = $(activeBtn).data('id');
                 const newId = $button.data('id');

                 // If IDs match, treat as a toggle OFF (Close and Stop).
                 if (oldId !== undefined && newId !== undefined && oldId == newId) {
                     closeAnyMenu(0);
                     return;
                 }

                 // Otherwise, it's a "orphan" menu from a previous render, but the user clicked
                 // a different item. Close old, allow new open to proceed.
                 closeAnyMenu(0);
            }
            // Case 3: Different button entirely -> Close old, allow new open to proceed.
            else {
                closeAnyMenu(0);
            }
        } else {
            // Ensure any lingering menus from other instances are closed
            closeAnyMenu(0);
        }

        // Fetch data using the mapper
        const dynamicData = settings.dataMapper($button, e);
        const genericId = dynamicData.id || dynamicData.invoiceId || dynamicData.productId || null;

        // Apply generic data-id for menu items
        if (genericId !== null) {
            $menu.find(settings.selectors.menuItem).attr("data-id", genericId);
        }

        // Trigger user callback to set custom attributes/content
        settings.onMenuOpen(dynamicData, $menu);

        // Calculate Position
        const buttonRect = $button[0].getBoundingClientRect();
        const buttonHeight = $button.outerHeight();

        // Temporarily render to calculate accurate dimensions
        $menu.css({ visibility: 'hidden', display: 'block', position: 'fixed' }).removeClass("hidden");
        const menuHeight = $menu.outerHeight();
        const menuWidth = $menu.outerWidth();
        $menu.css({ visibility: '', display: '' });

        const viewportHeight = $(window).height();
        const viewportWidth = $(window).width();

        const spaceBelow = viewportHeight - (buttonRect.top + buttonHeight);
        const spaceAbove = buttonRect.top;
        const offset = 5;

        // Flip logic: vertical
        let topPosition = (spaceBelow >= menuHeight || spaceBelow > spaceAbove)
            ? buttonRect.top + buttonHeight + offset
            : buttonRect.top - menuHeight - offset;

        // Horizontal adjustment to prevent screen overflow
        let leftPosition = buttonRect.left;
        if (leftPosition + menuWidth > viewportWidth) {
            leftPosition = viewportWidth - menuWidth - offset;
        }

        // Display Menu
        $menu.css({
            position: "fixed",
            top: topPosition + "px",
            left: leftPosition + "px",
            opacity: 0,
            zIndex: 9999
        }).removeClass("hidden")
          .data("active-button", $button[0])      // Store object ref in jQuery data
          .attr("data-active-button", "true");    // CRITICAL: Store attr in DOM so selector finds it

        $menu.stop(true, true).animate({ opacity: 1 }, settings.animationDuration);
    }

    $.fn.popupMenu = function(options) {
        const settings = $.extend(true, {}, defaults, options);

        // Modern jQuery versions remove the .selector property.
        // If buttonSelector isn't passed, we try to use the legacy property.
        const buttonSelector = settings.buttonSelector || this.selector;

        if (buttonSelector) {
            // DELEGATION MODE: Attached to document, works for future elements (Livewire compatible)
            $(document).off("click.popupMenu", buttonSelector)
                       .on("click.popupMenu", buttonSelector, function(e) {
                handleButtonClick(e, settings);
            });
        } else {
            // DIRECT BINDING MODE: Attached only to existing elements.
            // If Livewire refreshes, these listeners are lost.
            this.off("click.popupMenu").on("click.popupMenu", function(e) {
                handleButtonClick(e, settings);
            });
        }


        $(document).on("click", function () {
            $menuInstance.addClass("hidden").removeData("active-button");
        });

        // Global closure logic (bind once per namespace)
        $(document).off('click.popupMenuGlobal').on('click.popupMenuGlobal', handleDocumentClick);

        // Bind secondary close targets
        if (settings.secondaryCloseSelector) {
            $(document).off("click.popupMenuSecondary", settings.secondaryCloseSelector)
                       .on("click.popupMenuSecondary", settings.secondaryCloseSelector, handleDocumentClick);
        }

        // Prevent menu closure when clicking inside the menu content
        const $menuInstance = $(settings.menuSelector);
        $menuInstance.off("click.popupMenuContainer").on("click.popupMenuContainer", (e) => e.stopPropagation());

        // Close menu when a menu item is clicked
        $menuInstance.off("click.popupMenuItem", settings.selectors.menuItem)
             .on("click.popupMenuItem", settings.selectors.menuItem, function() {
            closeAnyMenu(settings.animationDuration);
        });

        return this;
    };

    // Public method to close menus manually
    $.popupMenuClose = function(duration = defaults.animationDuration) {
        closeAnyMenu(duration);
    };

})(jQuery);