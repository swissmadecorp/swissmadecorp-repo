<div class="p-6">
    <p class="pb-2 font-bold">eBay OAuth connection</p>
    <p class="pb-2">Connect the eBay seller account to grant this application the <code>sell.inventory</code> permission. eBay will return both an access token and a refresh token, which are encrypted before being saved in the database.</p>

    @if ($oauthConnected)
        <p class="pb-2 text-green-700">OAuth is connected. Access tokens will refresh automatically.</p>
        @if ($accessTokenExpiresAt)
            <p class="pb-1 text-sm">Current access token expires: {{ $accessTokenExpiresAt }}</p>
        @endif
        @if ($refreshTokenExpiresAt)
            <p class="pb-1 text-sm">Refresh token expires: {{ $refreshTokenExpiresAt }}</p>
        @endif
    @else
        <p class="pb-2 text-amber-700">OAuth has not been connected yet.</p>
    @endif

    <div class="flex justify-center p-6">
        <button wire:click="connectOAuth" type="button" class="mt-1 mr-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            {{ $oauthConnected ? 'Reconnect eBay OAuth' : 'Connect eBay OAuth' }}
        </button>
        <div x-on:open-new-tab.window="window.open($event.detail.url, '_blank')"></div>
    </div>

    <script>
        window.addEventListener('message', function (event) {
            if (event.origin === window.location.origin && event.data?.type === 'EBAY_OAUTH_COMPLETE') {
                window.location.reload();
            }
        });
    </script>
</div>
