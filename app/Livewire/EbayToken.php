<?php

namespace App\Livewire;

use Livewire\Component;
use App\Libs\eBaySession;
use App\Libs\eBayHelper;
use App\Libs\eBayMain;
use App\Models\EbaySettings;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Jantinnerezo\LivewireAlert\Enums\Position;

class EbayToken extends Component
{
    public $RuName = '';
    public $username = '';
    public $sessionId = '';

    protected $queryString = [
        'username',
        'sessionid',
        'token',
    ];

    public function setReturnURL() {
        $this->GetSessionID ();
    }

    public function fetchToken(Request $request) {
        $sessionId = session('ebay_session_id');


        session()->forget('ebay_session_id');



    }

    public function getSessionID() {
        $ebayMain = new eBayMain;

        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $xmlRequest .= "<GetSessionIDRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">";
        $xmlRequest .= "<RuName>" . $this->RuName . "</RuName>";
        $xmlRequest .= "</GetSessionIDRequest>";


        $ebayMain = new eBayMain;
        $response = $ebayMain->sendHeaders($xmlRequest,'GetSessionID');

        $sessionID = (string) $response->SessionID;
        session(['ebay_session_id' => $sessionID]);

        $this->sessionId = $sessionID;

        // $this->dispatch('open-new-tab', url: 'https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=Edward_Babekov-EdwardBa-dbe1-4-wsfvhauew&SessID='+$SessionID);
        // $sessionId = urldecode((string) $response->SessionID);

        $signinUrl = 'https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&RuName='. $this->RuName .'&SessID=' . $sessionID;
        $this->dispatch('open-new-tab', url:$signinUrl);
    }

    public function mount() {
        $this->username = request()->query('username');
        $this->sessionId = request()->query('sessionid');

        $this->RuName = config('ebay.runame');
    }

    public function render()
    {
        return view('livewire.ebay-token')
            ->layoutData(['pageName' => 'eBay Token Generator'])
            ->title("eBay Token Generator");
    }
}
