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

class Inquire extends Component
{
    public $product;
    public $inquiry = [];
    public $captcha;
    
    protected function rules() {
        return [
            'inquiry.contact_name' => ['required'],
            'inquiry.email' => ['required'],
            // 'captcha' => 'required|captcha'
        ];
    }

    protected $messages = [
        'inquiry.contact_name.required' =>'Your name is required.',
        'inquiry.email.required' => 'Your email is required.',
    ];

    public function validateInquiryForm() {
        $validator = $this->validate();
        return !$this->getErrorBag()->isNotEmpty();
    }

    public function sendInquiry($id) {

        $response = Http::post('https://www.google.com/recaptcha/api/siteverify?secret=' . config('recapcha.secret') . '&response=' . $this->captcha);
        $captcha = $response->json()['score'];

        if (!$captcha > .3) { 
            return session()->flash('fail', 'Google thinks you are a bot, please refresh and try again');
        } else {
            $this->resetValidation();

            $customer = $this->inquiry;
            
            if ($id != 0) {
                $company = ''; $notes = ''; $phone = '';
                
                $email = strip_tags($customer['email']);
                
                if (!empty($customer['phone']))
                    $phone = strip_tags($customer['phone']);
                if (!empty($customer['notes']))
                    $notes = strip_tags($customer['notes']);
                if (!empty($customer['company_name']))
                    $company = strip_tags($customer['company_name']);

                Inquiry::create([
                    'product_id' => $id,
                    'contact_name' => allFirstWordsToUpper(strip_tags($customer['contact_name'])),
                    'company_name' => allFirstWordsToUpper($company),
                    'email' => $email,
                    'phone' => $phone,
                    'notes' => $notes
                ]);
                
                if ($email)
                    $this->saveEmailForNewsLetter($email);

                $product = Product::find($id);

                $data = array(
                    'to' => 'info@swissmadecorp.com',
                    'replyTo' => $email,
                    'product' => $product->title,
                    'product_id' => $id,
                    'fullname'=>strip_tags($customer['contact_name']),
                    'image' => count($product->images)>0 ? $product->images->first()->location : 0,
                    'email' => $email,
                    'phone' => $phone,
                    'notes'=>$notes,
                    'subject'=>'Regarding your inquiry',
                    'template' => 'emails.test',
                );

                $gmailer = new GMailer($data);
                $gmailer->send();
                $this->reset("inquiry");

                $this->dispatch("inquiry-close-modal");
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
        return view('livewire.inquire');
    }
}
