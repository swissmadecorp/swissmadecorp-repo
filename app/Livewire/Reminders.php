<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reminder;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Js;
use Livewire\Attributes\Url;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;

class Reminders extends Component
{
    use WithPagination;

    public $page = 1;
    public $boxpapers;
    public $currentReminder;
    public $reminder = [];
    public $key;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    #[Url(keep: true)]
    public $search = "";

    protected function rules() {
        return [
            'reminder.pagename' => ['required'],
            'reminder.criteria' => ['required'],
            'reminder.assigned_to' => ['required'],
            'reminder.location' => ['required'],
            'reminder.action' => ['required'],
            //'items.*.price' => 'required'
        ];
    }

    protected $messages = [
        'reminder.pagename.required' =>'This field is required.',
        'reminder.location.required' =>'This field is required.',
        'reminder.criteria.required' => 'This field is required.',
        'reminder.assigned_to.required' => 'At least one row must be filled in.',
        'reminder.action.required' => 'At least one row must be filled in.',
    ];

    public function loadReminder($key) {
        $this->resetValidation();
        $this->key = $key;
        $reminder = Reminder::find($key);
        
        $this->resetValidation();
        if ($reminder) {
            $this->currentReminder = $reminder;
            $this->reminder['id'] = $reminder->id;
            $this->reminder['pagename'] = $reminder->pagename;
            $this->reminder['criteria'] = $reminder->criteria;
            $this->reminder['assigned_to'] = $reminder->assigned_to;
            $this->reminder['location'] = $reminder->location;
            $this->reminder['action'] = $reminder->action;
            $this->reminder['product_condition'] = unserialize($reminder->product_condition);
            $this->reminder['boxpapers'] = unserialize($reminder->boxpapers);
        }
    }

    public function newReminder() {
        $this->resetValidation();
        $this->reset('reminder','key','currentReminder');
    }

    public function saveReminder() {
        if (! Auth()->user()->hasRole('administrator')) { 
            abort(403);
        }

        try {
            $validatedData = $this->validate(
                $this->rules(),
                $this->messages
            ); 
            
            if ($this->key) {
                $reminder = $this->currentReminder;
                $reminder->update($this->reminder);
            } else {
                Reminder::create($this->reminder);
            }

            $this->dispatch('close',null);
        } catch (\Illuminate\Validation\ValidationException $e) {
            //throw $th;
            $this->dispatch('close', $e->errors());
        }

    }

    public function syncSelectedBoxPapers($key) {
        // Ensure the selectedBoxPapers array stays in sync
        if ($key) {
            $this->reminder['boxpapers'] = array_filter($key);
            $this->reminder['boxpapers'] = $this->reminder['boxpapers'];
        } else $this->reminder['boxpapers'] = null;

        // dd($this->reminder['boxpapers'] );
    }

    public function syncSelectedCondition($key) {
        // Ensure the selectedCondition array stays in sync
        if ($key) {
            $this->reminder['product_condition'] = array_filter($key);
            $this->reminder['product_condition'] = $this->reminder['product_condition'];
        } else $this->reminder['product_condition'] = null;
        
    }

    public function updatingSearch(){ 
        $this->resetPage();
    }

    public function deleteReminder($id) {
        if (! Auth()->user()->hasRole('administrator')) { 
            abort(403);
        }
        $reminder = Reminder::find($id);
        $reminder->delete();
        $this->search = '';

    }

    private function updateReminders() {
        $words = explode(' ', $this->search);
        $searchTerm = "";
        $searchWords = "";
        
        $columns = ['criteria','assigned_to'];
        
        if ($this->search) {
            $searchWords = "(";
            foreach($words as $word) {
                foreach ($columns as $key => $column) {
                    $searchWords .= $column.' LIKE "%'.$word .'%" OR ';
                }
                
                $searchWords = substr($searchWords,0,-4) . ") AND (";
                $searchTerm .= $searchWords;
                $searchWords = "";    
            }   
        }
    
        $searchTerm = substr($searchTerm,0,-6);
        $reminders = Reminder::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
                $query->whereRaw($searchTerm);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(perPage: 10);
        
        return $reminders;

    }

    public function render()
    {
        $reminders = $this->updateReminders();
        return view('livewire.reminders',['reminders' => $reminders]);
    }
}
