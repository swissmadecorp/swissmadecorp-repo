<?php

namespace App\Livewire;

use App\Libs\eBayMain;
use App\Models\EbaySettings;
use Livewire\Component;

class EbayToken extends Component
{
    public $RuName = '';
    public $oauthConnected = false;
    public $accessTokenExpiresAt = null;
    public $refreshTokenExpiresAt = null;

    public function connectOAuth(): void
    {
        $state = bin2hex(random_bytes(32));
        session(['ebay_oauth_state' => $state]);

        $authorizationUrl = (new eBayMain)->getOAuthAuthorizationUrl($state);
        $this->dispatch('open-new-tab', url: $authorizationUrl);
    }

    public function mount(): void
    {
        $this->RuName = config('ebay.runame');
        $ebaySettings = EbaySettings::first();

        $this->oauthConnected = (bool) ($ebaySettings?->oauth_refresh_token ?: config('ebay.oauth_refresh_token'));
        $this->accessTokenExpiresAt = $ebaySettings?->oauth_access_token_expires_at?->format('Y-m-d H:i:s T');
        $this->refreshTokenExpiresAt = $ebaySettings?->oauth_refresh_token_expires_at?->format('Y-m-d H:i:s T');
    }

    public function render()
    {
        return view('livewire.ebay-token')
            ->layoutData(['pageName' => 'eBay Token Generator'])
            ->title('eBay OAuth Generator');
    }
}
