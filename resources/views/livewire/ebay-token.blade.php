<div class="p-6">

    <p>Here you can generate an eBay Token.</p>
    <p>After clicking Generate, you will be redirected to eBay for consent. If the consent is given, you will be redirected back to this page with a new button called Fetch Token. Clicking on the Fetch Token button will
        create a token and is going to be saved to a detabase. You will need to run this when eBay token is expired. </p>
    <div class="flex justify-center p-6">
        <button wire:click="setReturnURL" type="button" class="mt-1 mr-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Generate</button>
        <div x-on:open-new-tab.window="window.open($event.detail.url, '_blank')"></div>
        @if (isset($sessionId))
        <div>
            <button wire:click="fetchToken" type="button" class="mt-1 mr-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Fetch Token</button>
        </div>
        @endif
    </div>

    <script>
    if (window.opener) {
        window.opener.postMessage(
            { type: 'EBAY_AUTH_COMPLETE' },
            window.location.origin
        );
        window.close();
    }
</script>

</div>
