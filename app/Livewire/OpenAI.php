<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

class OpenAI extends Component
{
    public $prompt = '';
 
    public $question = '';
 
    public $answer = '';
 
    function submitPrompt()
    {
        // $this->question = $this->prompt;
 
        // $this->prompt = '';
 
        // $this->js('$wire.ask()');
    }
 
    function ask()
    {
        // $this->answer = OpenAI::ask($this->question, function ($partial) {
        //     $this->stream(to: 'answer', content: $partial); 
        // });
    }
 
    public function render()
    {
        $products = Product::where('p_status', 0)->where('p_qty',1)->get();

        return view('livewire.open-a-i',['products' => $products]);
    }
}
