<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use App\Events\MessagesEvent;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Models\ChatMessage;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Services\ChatService;
use App\Events\MessageRead; // *** NEW: Import the MessageRead event ***
use GuzzleHttp\Client; // *** NEW: Import Guzzle HTTP Client ***

class Messages extends Component
{
    use WithFileUploads;

    public $messages=[];
    public $textMessage="";
    public $selectedUser;
    public $loggedInUsers;
    public $selectedUserId;
    public $messagesObj;
    public $loginId;
    public bool $showPopup = false;
    public $imageFile;
    public $isMaximized = false; // Add this new property

    // ... existing methods

    public function toggleMaximize()
    {
        $this->isMaximized = !$this->isMaximized;
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function submitText(ChatService $chatService) // *** NEW: Inject ChatService ***
    {
        // Validation for text message and image file
        $this->validate([
            'textMessage' => 'required_without:imageFile|string|max:255', // Message or image is required
            'imageFile' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt',
            'selectedUserId' => 'required|exists:users,id',
        ]);

        try {
            // *** Delegate message sending, broadcasting, and push notification to ChatService ***
            $createdMessage = $chatService->sendMessage(
                auth()->id(), // Authenticated user is the sender
                $this->selectedUserId,
                $this->textMessage,
                $this->imageFile // Pass the Livewire UploadedFile instance
            );

            // Update your Livewire component's state for immediate display
            // This assumes $messagesObj is an Eloquent collection or similar
            $this->messagesObj->push($createdMessage);

            // Dispatch event for JavaScript (e.g., for scrolling to bottom)
            $this->dispatch('get-message'); // This will still work as before

            $this->textMessage = ""; // Clear text input
            $this->imageFile = null; // Clear image input

            // Add a session flash for user feedback if needed
            session()->flash('message', 'Message sent successfully!');

        } catch (\Exception $e) {
            \Log::error("Livewire Chat Component: Failed to send message - " . $e->getMessage());
            // Add a session flash for error feedback
            session()->flash('error', 'Failed to send message.');
        }
    }

    // public function submitText1() {
    //     $str = "";

    //     if ($this->imageFile) {
    //         $ext = $this->imageFile->getClientOriginalExtension();
    //         $str = $this->generateRandomString(10).'.'.$ext;
    //         $imagePath = $this->imageFile->storeAs('images', $str ,'public');
    //         $imageLocation = base_path()."/storage/app/public/images/";
    //         File::move($imageLocation.$str, public_path("/images/chat/$str"));

    //         $str = "/images/chat/$str";
    //     }

    //     $messages = ChatMessage::create([
    //         'sender_id' => auth()->id(),
    //         "receiver_id" => $this->selectedUserId,
    //         "message" => $this->textMessage,
    //         'image_path' => $str ?? null,
    //     ]);

    //     $this->messagesObj->push($messages);

    //     if ($this->selectedUserId != 0) {
    //         broadcast(new MessagesEvent(
    //             recipientId: $this->selectedUserId,
    //             senderId: auth()->id(),
    //             message: $this->textMessage
    //         ))->toOthers();
    //     } else {

    //         foreach ($this->loggedInUsers as $user) {
    //             broadcast(new MessagesEvent(
    //                 recipientId: $user->id,
    //                 senderId: auth()->id(),
    //                 message: $this->textMessage
    //             ))->toOthers();
    //         }
    //     }

    //     $this->dispatch('get-message', []);

    //     $this->textMessage = "";
    //     $this->imageFile = null;

    // }

    public function updatedSelectedUserId($id) {

        if ($id == 0) {
            $this->selectedUser = "Group Text";
            $this->selectedUserId = 0;
        } else {
            $this->selectedUser = User::find($id);
            $this->selectedUserId = $this->selectedUser?->id;
        }

        $this->loadMessage();
        $this->dispatch('get-message');
    }

    public function MessageNotification($payload) {
        \Log::info("Livewire: Received new message event from Echo: " . json_encode($payload)); // <-- ADD THIS
        $this->selectedUserId = $payload['sender_id'];
        $this->loadMessage();
    }

    public function getListeners() {
        $loggedInUser = auth()->id();

        return [
            "echo-private:message.{$loggedInUser},.new-message" => "MessageNotification",
            "echo-private:message.{$loggedInUser},.messages-read" => "markMessagesAsRead",
        ];
    }

    public function updatedImageFile() {

        $mime = $this->imageFile->getMimeType();

        if (str_starts_with($mime, 'image/')) {
            $location = $this->imageFile->temporaryUrl();
        } else {
            $location = $this->imageFile->getClientOriginalName();
        }

        $this->dispatch('imageSelected', src: $location, ext: $this->imageFile->getClientOriginalExtension());
    }

    public function updatedTextMessage($value) {
        if (empty($this->selectedUser?->id))
            $selectedUserId = 0;
        else $selectedUserId = $this->selectedUser?->id;

        $this->dispatch('userTyping', userId: auth()->id(), userName:auth()->user()->name, selectedUserId: $selectedUserId);
    }

    // This is called by checkForNewMessages() and potentially from JS
    public function markMessagesAsRead() {
        // Mark messages in DB as read
        $updatedCount = ChatMessage::where('receiver_id', auth()->id())
            ->where('seen', false)->get();
            // ->update(['seen' => true]);

        $this->showPopup = false; // Hide new message popup

        // Trigger API call to broadcast read receipt and clear badge
        if ($updatedCount->isNotEmpty()) {
            $messageSenderId = $this->selectedUserId; // The sender whose messages I just read

            $client = new Client();
            $token = auth()->user()->createToken('read-receipt-api')->plainTextToken;

            try {
                $response = $client->post(config('app.url') . '/api/messages/mark-as-read', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'sender_id' => $messageSenderId, // The sender whose messages I just read
                    ],
                ]);
                \Log::info("Livewire: Mark as read API call successful: Status " . $response->getStatusCode() . ", Body: " . $response->getBody());
            } catch (\Exception $e) {
                \Log::error("Livewire: Mark as read API call failed: " . $e->getMessage());
            }
        }
    }

    // Method to handle incoming 'message.read' broadcast event
    public function messageReadEventReceived($data) {
        \Log::info("Livewire: Received message.read event from Echo: " . json_encode($data)); // <-- ADD THIS
        $readerId = $data['reader_id'];
        $senderId = $data['sender_id']; // This is the ID of the user who sent the message (me, if it's my message)

        // Only update if I am the sender of the messages that were read,
        // and the reader is the current conversation partner ($this->selectedUserId)
        if ($senderId === auth()->id() && $readerId === $this->selectedUserId) {
            foreach ($this->messagesObj as $message) {
                // If it's my sent message to this recipient, and it's unseen, mark it as seen
                if ($message->sender_id === auth()->id() && $message->receiver_id === $readerId && $message->seen === 0) {
                    $message->seen = 1; // Update locally
                    \Log::info("Livewire: Updated local message {$message->id} from sender {$senderId} as seen by {$readerId}."); // <-- ADD THIS
                }
            }
        }
    }

    // Method to be called by JS when messages are seen (directly)
    public function messageReadFromJS($messageSenderId)
    {

        \Log::info("Livewire: messageReadFromJS called for sender ID: {$messageSenderId}"); // <-- ADD THIS
        if ($messageSenderId == auth()->id()) {
            \Log::info("Livewire: messageReadFromJS - Ignoring call for own messages.");
            return;
        }

        $client = new Client();
        $token = auth()->user()->createToken('read-receipt-js-api')->plainTextToken;

        try {
            $response = $client->post(config('app.url') . '/api/messages/mark-as-read', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'sender_id' => $messageSenderId, // The sender whose messages I just read
                ],
            ]);
            \Log::info("Livewire: Mark as read from JS API call successful: Status " . $response->getStatusCode() . ", Body: " . $response->getBody());
        } catch (\Exception $e) {
            \Log::error("Livewire: Mark as read from JS API call failed: " . $e->getMessage());
        }
    }

    public function mount() {
        $this->imageFile = null;
        $this->loggedInUsers = User::all();
        $this->selectedUser ??= $this->loggedInUsers->first();
        $this->selectedUserId = $this->selectedUser?->id;
        $this->loginId = auth()->id();
        $this->loadMessage();
    }

    public function checkForNewMessages() {

        $this->showPopup = ChatMessage::where('receiver_id', auth()->id())
            ->where('seen', false)
            ->exists();

        if ($this->showPopup) {
            $this->dispatch('get-message');
            // $this->markMessagesAsRead();
        }

    }

    public function loadMessage() {

        $this->messagesObj = ChatMessage::query()
            ->where(function($q) {
                $q->where("sender_id", auth()->id())
                    ->where("receiver_id", $this->selectedUserId);
            })
            ->orWhere(function($q) {
                $q->where("sender_id", $this->selectedUserId)
                    ->where("receiver_id", auth()->id());
            })
            ->get();

        $this->checkForNewMessages();
    }

    public function render() {

        return view('livewire.messages');
    }
}
