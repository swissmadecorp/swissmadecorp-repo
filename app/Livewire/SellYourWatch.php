<?php

namespace App\Livewire;

use Imagick;
use App\Mail\GMailer;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\MessageBag;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SellYourWatch extends Component
{
    use WithFileUploads;

    public $contact = [];
    public $images;

    protected function rules() {
        return [
            'images' => 'required|array|min:5|max:10', // Ensure there are at least 5 and at most 10 images
            'images.*' => ['image','max:5120'],
            'contact.amount' => ['required'],
            'contact.name' => ['required'],
            'contact.brand' => ['required'],
            'contact.model' => ['required'],
            'contact.phone' => ['required'],
            'contact.email' => ['required','email'],
            'contact.selltrade.1' => ['required_without:contact.selltrade.2'],
            'contact.boxpapers.1' => ['required_without:contact.boxpapers.2'],
            'contact.cert.1' => ['required_without:contact.cert.2'],
            'contact.purchased.1' => ['required_without:contact.purchased.2'],
            'contact.proof.1' => ['required_without:contact.proof.2'],
            'contact.unworn.1' => ['required_without:contact.unworn.2'],
        ];
    }

    protected $messages = [
        'images.required' => 'Please upload at least 5 images.',
        'images.min' => 'You must upload at least 5 images.',
        'images.max' => 'You cannot upload more than 10 images.',
        'images.*.image' => 'Each file must be a valid image.',
        'images.*.max' => 'Each image must not exceed 5 MB.',

        'contact.name.required' => 'Your name is required.',
        'contact.phone.required' => 'Phone number is required.',
        'contact.model.required' => 'Model number is required.',
        'contact.brand.required' => 'Brand name is required.',
        'contact.amount.required' => 'Your desired selling amount is required.',
        'contact.email.required' => 'Email is required.',
        'contact.email.email' => 'Email must be a valid email address.',
        'contact.selltrade.1.required_without' => 'Please select either Trade In or Sell my watch.',
        'contact.boxpapers.1.required_without' => 'Please select either it comes with the Box or not.',
        'contact.cert.1.required_without' => 'Please select either it comes with the original Certificate or not.',
        'contact.purchased.1.required_without' => 'Please specify if the watch was purchased from Swiss Made Corp.',
        'contact.proof.1.required_without' => 'Please specify if you have a proof of purchase.',
        'contact.unworn.1.required_without' => 'Please specify if your watch is unworn or not.',
    ];

    public function save() {
       
        //code...
        $validatedData = $this->validate();
        
        $fields = ['purchased','boxpapers','selltrade','cert','proof','unworn'];
    
        $selectedOptions = array_map(function($field) {
            return $this->contact[$field][1] ?? $this->contact[$field][2] ?? 'None';
        }, $fields);
    
        // Combine fields and their selected options
        $selectedOptions = array_combine($fields, $selectedOptions);

        // dd($this->contact);
        if (empty($this->contact['age'])) $this->contact['age'] = "Less than 2 years";
        $image_names = Str::slug($this->contact['name']."-".$this->contact['brand']."-".$this->contact['model']);
        $u_image = '';
        foreach ($this->images as $index => $image) {
            $filename = $image_names." $index.jpg";
            
            $image->storeAs('images', $filename ,'public');
            $imageLocation = base_path()."/storage/app/public/images/";
            File::move($imageLocation.$filename, public_path("/uploads/$filename"));
        
            $this->adjustImage($filename);
            $u_image .= "<a href='https://www.swissmadecorp.com/uploads/$filename'><img src='https://www.swissmadecorp.com/uploads/$filename'></a><br>";
        } 

        $selections = '';
        foreach ($selectedOptions as $selection) {
            $ex = explode("-",$selection);
            $selections .= $ex[0].": ".$ex[1].'<br>'; 
        }

        $data = array(
            'image_names' => $image_names,
            'contacts' => $this->contact,
            'selections' => $selections,
            'to' => 'info@swissmadecorp.com',
            'subject'=>'Swissmade - I want to sell my watch',
            'template' => 'emails.sellwatch-new',
            'image' => $u_image
        );

        $gmail = new GMailer($data);
        $gmail->send();

        $this->resetValidation();
        $this->reset();
        
        return redirect()->to(url('sell-your-watches') . '?saved=1');
    }

    protected function adjustImage($filename) {
        $folderNameThumb = "thumbs";
        if (!file_exists(base_path()."/public/uploads/")) {
            mkdir(base_path()."/public/uploads/");
        }

        $imagelocation = base_path()."/public/uploads/$filename" ;
        $newimagelocation = base_path()."/public/uploads/".$filename ;
        
        list($width, $height, $type, $attr) = getimagesize($imagelocation);
        $img = new Imagick($imagelocation);
        $img->setImageBackgroundColor('#ffffff');
        $img->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $img = $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

        $img->setImageFormat ("jpg");
        // $img->thumbnailImage(450, 450,true,true);
        $img->writeImage($newimagelocation);
    }

    public function removeImage($index) {
        $image = $this->images[$index];
       
        // Delete temporary file
        File::delete([$image->getPath()."/".$image->getFilename()]);
       
        array_splice($this->images, $index, 1);
        if (count($this->images) == 0) {
            $this->reset('images');
        }
    
    }

    public function render()
    {
        return view('livewire.sell-your-watch');
    }
}
