@extends ("layouts.admin-new-default")

@section('title', 'Products')

@section ('header')

@endsection


@section ('content')

@livewire('products') 
<livewire:invoice-item />

@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        $(document).on('mouseenter', 'span.hide', function () {
            $(this).css('opacity',1)
        }).on('mouseleave', 'span.hide', function () {
            $(this).css('opacity',0)
        })

        $(document).on("click", ".menu-btn", function (e) {
            e.stopPropagation(); // Prevent closing when clicking the button

            let menu = $("#popup-menu");
            let button = $(this);
            let productId = button.data("id"); // Get product ID from button

            menu.find(".menu-item").attr("data-id", productId);
            menu.find("li.print").attr("onclick", `window.open('/admin/products/${productId}/print', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);
            menu.find("li.ebay").attr("wire:click.prevent", `postToEbay(${productId})`);
            menu.find("li.return").attr("wire:click", `returnToVendor(${productId})`);
            
            menu.find("li.editinvoice").attr("wire:click.prevent", `makeInvoice(${productId})`);
            menu.find("li.deleteitem").attr("wire:click", `deleteProduct(${productId})`);

            let buttonOffset = button.offset();
            let buttonHeight = button.outerHeight();
            let menuHeight = menu.outerHeight();
            let windowHeight = $(window).height();
            let scrollTop = $(window).scrollTop(); // Get scroll position

            let spaceBelow = windowHeight + scrollTop - (buttonOffset.top + buttonHeight);
            let spaceAbove = buttonOffset.top - scrollTop;

            let topPosition;

            // Show below if there's space, otherwise show above
            if (spaceBelow >= menuHeight || spaceBelow > spaceAbove) {
                topPosition = buttonOffset.top + buttonHeight + 5;
            } else {
                topPosition = buttonOffset.top - menuHeight - 5;
            }
            
            // Check if the menu is already open for the same button
            if (menu.is(":visible") && menu.data("active-button") === button[0]) {
                menu.addClass("hidden").removeData("active-button"); // Hide menu
            } else {
                menu.css({
                    top: topPosition + "px",
                    left: buttonOffset.left + "px",
                }).removeClass("hidden").data("active-button", button[0]); // Show menu and store active button
            }
        });

        // Hide menu when clicking anywhere outside
        $(document).on("click", function () {
            $("#popup-menu").addClass("hidden").removeData("active-button");
        });

    })

</script>

@endsection