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

        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $xmlRequest .= "<FetchTokenRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">";
        $xmlRequest .= "<SessionID>$sessionId</SessionID>";
        $xmlRequest .= "</FetchTokenRequest>";

        $ebayMain = new eBayMain;
        $response = $ebayMain->sendHeaders($xmlRequest,'FetchToken');

        session()->forget('ebay_session_id');

        if (isset($response->Ack)) {
            if ((string)$response->Ack == "Success" && (string)$response->eBayAuthToken) {
                $date = date_parse($response->HardExpirationTime);

                $ebaySettings = EbaySettings::where('id',0);
                $ebaySettings->update ([
                    'token' => $response->eBayAuthToken,
                    'experation_date' => $date['year'].'-'.$date['month'].'-'.$date['day']
                ]);

                LivewireAlert::title("Token has been successfully generated and saved to the database.")->success()->position(Position::TopEnd)->toast()->show();
            }
        } else {

            $response = simplexml_load_string($response);
            // Register eBay namespace
            $response->registerXPathNamespace('e', 'urn:ebay:apis:eBLBaseComponents');
            // Get ShortMessage
            $shortMessage = (string) $response->xpath('//e:Errors/e:ShortMessage')[0];
            // Optional: LongMessage
            $longMessage = (string) $response->xpath('//e:Errors/e:LongMessage')[0];

            LivewireAlert::title($longMessage)->error()->position(Position::TopEnd)->toast()->show();
        }

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
