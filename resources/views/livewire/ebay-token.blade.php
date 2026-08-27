<div class="p-6">
    @if ($oauthCallbackProcessed)
        @if ($oauthCallbackSuccess)
            <div class="p-3 mb-3 text-green-800 bg-green-100 rounded">
                eBay OAuth was connected successfully. This window will close automatically or return to the OAuth status page.
            </div>
        @else
            <div class="p-3 mb-3 text-red-800 bg-red-100 rounded">
                eBay OAuth failed: {{ $oauthCallbackError }}
            </div>
        @endif
    @endif

    <p class="pb-2 font-bold">eBay OAuth connection</p>
    <p class="pb-2">Connect the eBay seller account to grant this application the <code>sell.inventory</code> permission. eBay will return both an access token and a refresh token, which are encrypted before being saved in the database.</p>

    @if ($setupError)
        <div class="p-3 mb-3 text-red-800 bg-red-100 rounded">
            {{ $setupError }} Run:<br>
            <code>php artisan migrate --path=database/migrations/2026_08_27_170000_add_oauth_columns_to_ebay_settings_table.php</code>
        </div>
    @endif

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
        <button wire:click="connectOAuth" type="button" @disabled($setupError) class="mt-1 mr-1 text-white bg-blue-700 hover:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            {{ $oauthConnected ? 'Reconnect eBay OAuth' : 'Connect eBay OAuth' }}
        </button>
        <div x-on:open-new-tab.window="window.open($event.detail.url, '_blank')"></div>
    </div>

    <script>
        @if ($oauthCallbackProcessed)
            // Do not leave eBay's short-lived authorization code in browser history.
            window.history.replaceState({}, document.title, window.location.pathname);

            if (window.opener) {
                window.opener.postMessage(
                    {
                        type: 'EBAY_OAUTH_COMPLETE',
                        success: @json($oauthCallbackSuccess),
                        error: @json($oauthCallbackError),
                    },
                    window.location.origin
                );

            }

            @if ($oauthCallbackSuccess)
                // Some browsers remove window.opener during the eBay redirect. Try
                // to close regardless, then fall back to the clean status page.
                window.setTimeout(function () {
                    window.close();

                    window.setTimeout(function () {
                        window.location.replace(@json(url('/admin/ebayToken')));
                    }, 300);
                }, 700);
            @endif
        @endif

        window.addEventListener('message', function (event) {
            if (event.origin === window.location.origin && event.data?.type === 'EBAY_OAUTH_COMPLETE') {
                if (event.data.success) {
                    window.location.reload();
                } else if (event.data.error) {
                    window.alert('eBay OAuth failed: ' + event.data.error);
                }
            }
        });
    </script>
</div>
