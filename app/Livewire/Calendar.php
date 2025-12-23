<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Illuminate\Support\MessageBag;
use App\Models\Booking;
use App\Mail\GMailer; 
use App\Models\Product;
use Carbon\Carbon;

class Calendar extends Component
{
    public $bookDate;
    public $bookTime;
    public $calendar = [];
    public $productId;
    
    protected function rules() {
        return [
            'calendar.contact_name' => 'required',
            'calendar.calendar_email' => 'required|email',
        ];
    }

    protected $messages = [
        'calendar.contact_name.required' =>'Your name is required.',
        'calendar.calendar_email.required' => 'Your email is required.',
        'calendar.calendar_email.email' => 'The email field must be a valid email address.',
    ];

    public function Book() {

        try {
            // Trigger validation and catch any potential errors
            $validatedData = $this->validate(
                $this->rules(),
                $this->messages
            );

            if (!$this->bookDate) $this->bookDate = date("Y-m-d", strtotime(now()));
            
            $this->dispatch("calendar-close-modal");
            $bookStart = Carbon::parse($this->bookDate.' '.$this->bookTime, 'UTC');

            $product = Product::find($this->productId);
            Booking::create([
                'contact_name' => $this->calendar['contact_name'],
                'phone' => $this->calendar['phone'],
                'email' => $this->calendar['calendar_email'],
                'book_date' => $bookStart,
                'product_id' => $product->id
            ]);


            $data = array(
                'template' => 'emails.booking-1',
                'to' =>'info@swissmadecorp.com',
                'subject' => "Scheduled for " . date("m-d-Y", strtotime($this->bookDate)).', '.$this->bookTime . ' with Swiss Made Corp.',
                'contactname' => $this->calendar['contact_name'],
                'book_date' => date("l jS \of F Y", strtotime($this->bookDate)),
                'book_time' => $this->bookTime,
                'phone' => $this->calendar['phone'],
                'email' => $this->calendar['calendar_email'],
                'wristwatch' => $product->title,
                'product_id'=>$product->id
            );

            $gmail = new GMailer($data);
            $gmail->send();

            $data = array(
                'template' => 'emails.booking',
                'to' =>$this->calendar['calendar_email'],
                'subject' => "Scheduled for " . date("m-d-Y", strtotime($this->bookDate)).', '.$this->bookTime . ' with Swiss Made Corp.',
                'contactname' => $this->calendar['contact_name'],
                'book_date' => date("l jS \of F Y", strtotime($this->bookDate)),
                'book_time' => $this->bookTime,
                'wristwatch' => $product->title,
                'product_id'=>$product->id
            );

            $gmail = new GMailer($data);
            $gmail->send();

            $this->reset();

        } catch (\Illuminate\Validation\ValidationException $e) {

            // dd($e->errors());
            // session()->flash('errors', $e->validator->errors()->getMessages());
            $this->dispatch('show-validation-errors', $e->errors());
            
        }

    }

    public function render()
    {
        return view('livewire.calendar');
    }
}
