<?php

namespace App\Livewire;

use App\Libs\eBayMain;
use App\Models\EbaySettings;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class EbayToken extends Component
{
    public $RuName = '';
    public $oauthConnected = false;
    public $accessTokenExpiresAt = null;
    public $refreshTokenExpiresAt = null;
    public $setupError = null;
    public $oauthCallbackProcessed = false;
    public $oauthCallbackSuccess = false;
    public $oauthCallbackError = null;

    public function connectOAuth(): void
    {
        if (! $this->oauthColumnsExist()) {
            $this->setupError = 'The eBay OAuth database migration has not been run yet.';

            return;
        }

        $state = bin2hex(random_bytes(32));
        session(['ebay_oauth_state' => $state]);

        $authorizationUrl = (new eBayMain)->getOAuthAuthorizationUrl($state);
        $this->dispatch('open-new-tab', url: $authorizationUrl);
    }

    public function mount(): void
    {
        $this->RuName = config('ebay.runame');

        if (! $this->oauthColumnsExist()) {
            $this->setupError = 'The eBay OAuth database migration has not been run yet.';

            return;
        }

        if (request()->filled('code') || request()->filled('error')) {
            $this->handleOAuthCallback();
        }

        $this->loadOAuthStatus();
    }

    private function handleOAuthCallback(): void
    {
        $this->oauthCallbackProcessed = true;
        $expectedState = (string) session()->pull('ebay_oauth_state', '');
        $returnedState = (string) request()->query('state', '');

        if (request()->filled('error')) {
            $this->oauthCallbackError = (string) request()->query(
                'error_description',
                request()->query('error')
            );

            return;
        }

        if ($expectedState === '' || $returnedState === '' || ! hash_equals($expectedState, $returnedState)) {
            $this->oauthCallbackError = 'The OAuth state check failed. Please start the connection again.';

            return;
        }

        try {
            (new eBayMain)->exchangeOAuthAuthorizationCode((string) request()->query('code'));
            $this->oauthCallbackSuccess = true;
        } catch (\Throwable $exception) {
            $this->oauthCallbackError = $exception->getMessage();
        }
    }

    private function loadOAuthStatus(): void
    {
        $ebaySettings = EbaySettings::first();

        $this->oauthConnected = (bool) ($ebaySettings?->oauth_refresh_token ?: config('ebay.oauth_refresh_token'));
        $this->accessTokenExpiresAt = $ebaySettings?->oauth_access_token_expires_at?->format('Y-m-d H:i:s T');
        $this->refreshTokenExpiresAt = $ebaySettings?->oauth_refresh_token_expires_at?->format('Y-m-d H:i:s T');
    }

    private function oauthColumnsExist(): bool
    {
        return Schema::hasColumns('ebay_settings', [
            'oauth_access_token',
            'oauth_refresh_token',
            'oauth_access_token_expires_at',
            'oauth_refresh_token_expires_at',
        ]);
    }

    public function render()
    {
        return view('livewire.ebay-token')
            ->layoutData(['pageName' => 'eBay Token Generator'])
            ->title('eBay OAuth Generator');
    }
}
