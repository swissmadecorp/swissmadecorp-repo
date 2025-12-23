@extends ("layouts.admin-new-default")

@section('title', 'Invoices')

@section ('header')

@endsection


@section ('content')
<div class="bg-gray-200 w-full rounded-lg shadow dark:bg-gray-600">
    <h1 class="uppercase tracking-wide text-3xl text-gray-500 dark:text-white p-1.5 items-center">Invoices</h1>
</div>

@livewire('invoices')

@endsection

@section ('jquery')

<script>
    $(document).ready( function() {

        $(document).on("click", "#alert-border-1 button", function() {
            $(this).parent().slideUp("slow");;
        })

        $(document).on("click", ".menu-btn", function (e) {
            e.stopPropagation(); // Prevent closing when clicking the button

            let menu = $("#popup-menu");
            let button = $(this);
            let id = button.data("id"); // Get product ID from button
            let custId = button.attr("data-custid"); // Get product ID from button
            let status = button.data("status"); // Get product ID from button

            menu.find(".menu-item").attr("data-id", id);
            menu.find("li.print").attr("onclick", `window.open('/admin/orders/${id}/print', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);
            menu.find("li.printstatement").attr("onclick", `window.open('/admin/orders/${custId}/${status}/printstatement', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);
            menu.find("li.packingslip").attr("onclick", `window.open('/admin/orders/${id}/print/packingslip', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);
            menu.find("li.appraisal").attr("onclick", `window.open('/admin/orders/${id}/print/appraisal', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);
            menu.find("li.commercial").attr("onclick", `window.open('/admin/orders/${id}/print/commercial', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);
            
            menu.find("li.email").attr("wire:click.prevent", `sendEmail(${id})`);
            menu.find("li.textinvoice").attr("wire:click.prevent", `setCurrentInvoiceId(${id})`);
            menu.find("li.returnall").attr({
                "wire:click.prevent": `returnAllProducts(${id})`,
                "wire:confirm": `You're about to return all the products for the invoice/memo # ${id}. Are you sure you want to do that?`
            });
            
            menu.find("li.deleteinvoice").attr({
                "wire:click.prevent": `deleteInvoice(${id})`,
                "wire:confirm": `This action will completely delete invoice #${id}. Are you sure you want to do this?`
            });
            
            // menu.find("li.editinvoice").attr("wire:click.prevent", `makeInvoice(${productId})`);
            // menu.find("li.deleteitem").attr("wire:click", `deleteProduct(${productId})`);

            let buttonPosition = button.position(); // Relative position
            let buttonHeight = button.outerHeight();
            let menuHeight = menu.outerHeight();
            let container = button.closest("table").parent(); // Find the nearest scrolling container
            let containerScrollTop = container.scrollTop(); // Get the scroll position
            let containerHeight = container.height(); // Get the height of the scrollable area

            let spaceBelow = containerHeight - (buttonPosition.top + buttonHeight);
            let spaceAbove = buttonPosition.top;

            let topPosition;

            // Show above the button if there's not enough space below
            if (spaceBelow >= menuHeight || spaceBelow > spaceAbove) {
                topPosition = buttonPosition.top + buttonHeight + 5 + containerScrollTop;
            } else {
                topPosition = buttonPosition.top - menuHeight - 5 + containerScrollTop;
            }

            // If clicking the same button, toggle menu visibility
            if (menu.is(":visible") && menu.data("active-button") === button[0]) {
                menu.addClass("hidden").removeData("active-button");
            } else {
                menu.css({
                    top: topPosition + "px",
                    left: buttonPosition.left + "px",
                }).removeClass("hidden").data("active-button", button[0]);
            }
        });

        // Hide menu when clicking anywhere outside
        $(document).on("click", function () {
            $("#popup-menu").addClass("hidden").removeData("active-button");
        });

        // Prevent menu from closing when clicking inside it
        $("#popup-menu").on("click", function (e) {
            e.stopPropagation();
        });
    })

</script>

@endsection