<div class="p-6">

    <p class="pb-2 font-bold">Here you can generate an eBay Token.</p>
    <p class="pb-2">To generate an eBay token, click Generate. You will be redirected to eBay to provide consent. Once consent is granted, you will be redirected back to this page, where a Fetch Token button will appear. Clicking Fetch Token will create a new token, which will be securely saved in the database.</p>

    <p class="pb-2">Please note: you will need to repeat this process whenever your eBay token expires which is usually once a year. </p>
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
