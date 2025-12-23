<?php

namespace App\Livewire;

use App\Mail\GMailer;
use App\Models\Inquiry;
use App\Models\Product;
use Livewire\Component;
use App\Models\Newsletter;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Http;

class Offer extends Component
{
    public $product;
    public $customer = [];
    public $priceoffer;
    public $lowestpriceallowed = 0;
    public $captcha;
    
    protected function rules() {
        return [
            'customer.contact_name' => 'required',
            'customer.email' => 'required|email',
            'priceoffer' => 'required|numeric|min:3',
            // 'captcha' => 'required|captcha'
        ];
    }

    protected $messages = [
        'customer.contact_name.required' =>'Your name is required.',
        'customer.email.required' => 'Your email is required.',
        'customer.email.email' => 'The email field must be a valid email address.',
        'priceoffer.required' => 'Your price offer is required.',
        'priceoffer.numeric' => 'The price offer should be a numeric value.',
        'priceoffer.min' => 'Your price offer must be at least 3 or more digits.',
    ];

    public function validateForm() {
        $validator = $this->validate();
        return !$this->getErrorBag()->isNotEmpty();
    }

    public function sendOffer($id) {
        $response = Http::post('https://www.google.com/recaptcha/api/siteverify?secret=' . config('recapcha.secret') . '&response=' . $this->captcha);
        $captcha = $response->json()['score'];

        if (!$captcha > .3) { 
            return session()->flash('fail', 'Google thinks you are a bot, please refresh and try again');
        } else {
            $this->resetValidation();

            $customer = $this->customer;
            if ($id != 0) {
                $email = strip_tags($customer['email']);
                $amount = strip_tags($this->priceoffer);

                if ($email)
                    $this->saveEmailForNewsLetter($email);

                $product = Product::find($id);

                $data = array(
                    'to' => 'info@swissmadecorp.com',
                    'replyTo' => $email,
                    'item' => $product->title . " (" . $id . ")",
                    'fullname'=>allFirstWordsToUpper(strip_tags($customer['contact_name'])),
                    'image' => count($product->images)>0 ? $product->images->first()->location : 0,
                    'email' => $email,
                    'price' => number_format($amount,2),
                    'phone' => "",
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'subject'=>'Regarding a price offer',
                    'template' => 'emails.priceoffer',
                );

                $gmailer = new GMailer($data);
                $gmailer->send();

                $this->reset("customer");
                $this->dispatch("offer-close-modal");
            }
        }

    }

    private function saveEmailForNewsLetter($email) {
        $count = Newsletter::select('email')->where('email',$email)->count();
        if (!$count)
            Newsletter::create([
                'email' => $email,
                'subscribed' => 1
            ]);
    
    }

    public function render()
    {
        return view('livewire.offer');
    }
}
