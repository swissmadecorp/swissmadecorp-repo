<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use App\Notifications\NewMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Events\MessagesEvent;
use App\Events\MessageRead;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Notification; // *** NEW: Import the Notification facade ***

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(Authenticate::using('sanctum'));

// Add this line to your routes/api.php
Route::post('/broadcasting/auth', function (Request $request) {
    return Broadcast::auth($request);
})->middleware('auth:sanctum'); // Ensure it uses sanctum authentication

// *** NEW: API Endpoint to mark messages as read ***
Route::middleware('auth:sanctum')->post('/messages/mark-as-read1', function (Request $request) {
    Log::info("Marking messages as read for user: " . $request);
    $request->validate([
        'sender_id' => 'required|exists:users,id', // The user who sent messages being read
    ]);

    $recipientId = auth()->id(); // The authenticated user is the one reading messages
    $messageSenderId = $request->input('sender_id'); // The user whose messages are being read

    // Mark messages from messageSenderId to recipientId as seen
    $messageIdsToUpdate = ChatMessage::where('sender_id', $messageSenderId)
        ->where('receiver_id', $recipientId)
        ->where('seen', 0)
        ->pluck('id')
        ->toArray();

    $updatedCount = ChatMessage::whereIn('id', $messageIdsToUpdate)->update(['seen' => 1]);

    // *** NEW: Broadcast an event to the sender that their messages have been read ***
    if ($updatedCount > 0) {
        // ChatService broadcasts to send from A to B, so we need to broadcast
        // from B to A that the messages were read
        broadcast(new MessagesEvent(
                recipientId: $messageSenderId,
                senderId: $recipientId,
                message: $messageIdsToUpdate,
                createdAt: now()->toDateTimeString() // Use current time for read event
            ))->toOthers();
    }

    // You might also want to clear the recipient's badge count here, if this is the primary way they "read" notifications
    $user = User::find($recipientId);
    if ($user && $user->badge_count > 0) {
        $user->badge_count = 0;
        $user->save();
        Log::info("User {$recipientId} badge count cleared to 0.");
        // Optional: Send a silent push to clear badge on device immediately
        if ($user->device_token) {
            Notification::send($user, (new ChatMessage("Badge cleared", "", $user->id))->badge(0));
        }
    }

    return response()->json(['status' => 'messages marked as read', 'updated_count' => $updatedCount]);
});

Route::middleware('auth:sanctum')->post('/messages/mark-as-read', function (Request $request) {
    // ... validation and update logic ...
    $recipientId = auth()->id(); // The user who read the messages
    $messageSenderId = $request->input('sender_id'); // The user who sent the messages being read

    // Get the IDs of messages that were just marked as read
    $messageIdsToUpdate = ChatMessage::where('sender_id', $messageSenderId)
        ->where('receiver_id', $recipientId)
        ->where('seen', 0)
        ->pluck('id')
        ->toArray();

    $updatedCount = ChatMessage::whereIn('id', $messageIdsToUpdate)->update(['seen' => 1]);
    \Log::info("from Mark-as-read " . $updatedCount);
    if ($updatedCount > 0) {
        // *** DISPATCH THE NEW EVENT WITH THE CORRECT ARGUMENTS ***
        \Log::info("Broadcasting MessageRead event for recipient ID: {$recipientId}, sender ID: {$messageSenderId}, message IDs: " . implode(',', $messageIdsToUpdate));
        broadcast(new MessageRead(
            readerId: $recipientId,       // The ID of the user who read the messages
            recipientId: $messageSenderId, // The ID of the user who will receive the read receipt event
            messageIds: $messageIdsToUpdate
        ))->toOthers();
    }
    return response()->json(['status' => 'messages marked as read', 'updated_count' => $updatedCount]);
});

Route::post('/chatlogin', function (Request $request) {
    $request->validate([
        'username' => 'required',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('username', $request->username)->first();
    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json([
            'error' => 'The provided credentials are incorrect.'
        ], 401);
    }

    $token = $user->createToken($request->device_name, ['send:message'])->plainTextToken;
    $user->update(['api_token' => $token]);

    return response()->json([
        "token" => $token,
        "user" => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username
        ]
    ], 200);
});

Route::middleware('auth:sanctum')->get('users', function (Request $request) {
    $user = \Auth::user();
    if ($user->tokenCan('send:message')) {
        $users = User::select('id','name','username')->orderBy('id', 'asc')->get(); //->pluck('name');

        return $users;
    }
});

Route::middleware('auth:sanctum')->post('/upload-file', function (Request $request) {
    try {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,xlsx,docx|max:20480',
        ]);
    } catch (ValidationException $e) {
        // Return a JSON validation error instead of HTML
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'errors' => $e->errors(),
        ], 422); // Use 422 for validation errors
    }

    $file = $request->file('file');

    if ($file) {
        $ext = $file->getClientOriginalExtension();

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 10; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        $str = $randomString.'.'.$ext;
        $imagePath = $file->storeAs('images', $str ,'public');
        $imageLocation = base_path()."/storage/app/public/images/";
                File::move($imageLocation.$str, public_path("/images/chat/$str"));

        $str = "/images/chat/$str";

        return response()->json([
            'success' => true,
            'path' => $str,
        ],200);
    } else {
        return response()->json([
            'success' => false,
            'path' => null,
        ],500);
    }
});

Route::middleware('auth:sanctum')->post('fcm-device-token', function (Request $request) {
    $request->validate([
        'fcm_device_token' => 'required|string',
    ]);
    \Log::info("Saving FCM device token for user: " . $request->user());
    $user = $request->user();
    $user->fcm_device_token = $request->input('fcm_device_token'); // Save to the new column
    $user->save();
    return response()->json(['message' => 'FCM device token saved.'], 200);
});

Route::middleware('auth:sanctum')->post('/device-token', function (Request $request) {

    $request->validate([
        'device_token' => 'required|string',
    ]);

    // Push Notification Key ID: P9YFD9VH87
    // Team Id: 3M34UFNUNK

    $user = $request->user(); // Get the authenticated user

    // Store the device token. You might have a 'device_tokens' table
    // or a 'device_token' column on your 'users' table, or a separate
    // 'user_devices' table for multiple devices per user.
    $user->device_token = $request->input('device_token'); // Simple example
    $user->save();

    // Or, for multiple tokens per user (more robust):
    // $user->deviceTokens()->updateOrCreate(
    //     ['device_id' => $request->input('device_id')], // A unique ID for this device installation
    //     ['token' => $request->input('device_token')]
    // );

    return response()->json(['message' => 'Device token updated successfully'], 200);
});

Route::middleware('auth:sanctum')->post('/send-message', function (Request $request) {
    // Validate the request data
    $request->validate([
        'receiver_id' => 'required|exists:users,id',
        'message' => 'required|string',
        'sender_id' => 'required|exists:users,id', // Ensure sender_id is also validated if coming from client
        'image_path' => 'nullable|string', // Validate image_path if it can be sent
    ]);

    // Create and save the chat message
    $message = ChatMessage::create([
        'sender_id' => $request->input("sender_id"), // Use input() for safer access
        "receiver_id" => $request->input("receiver_id"),
        "message" => $request->input("message"),
        'image_path' => $request->input("image_path") ?? null,
    ]);

    // Your existing broadcast event
    broadcast(new MessagesEvent(
        recipientId: $request->input("receiver_id"),
        senderId: $request->input("sender_id"),
        message: $request->input("message"),
        createdAt: $message->created_at, // Use the created_at timestamp from the message

    ))->toOthers();

    // *** NEW: Dispatch Push Notification ***
    $recipient = User::find($request->input('receiver_id')); // Find the recipient user

    // Only send notification if recipient exists and has a device token

    if ($recipient && $recipient->device_token) {
        Notification::send(
            $recipient,
            new NewMessage(
                $request->input('message'), // message content
                $request->user()->name,     // sender's name (authenticated user is the sender)
                $request->input('sender_id'),  // sender's ID (for navigation on iOS)
                $request->input('image_path') ?? null // Optional image path if provided
            )
        );
        \Log::info("API.php Push Notification dispatched for user {$recipient->id}."); // Log for debugging
    } else {
        \Log::warning("API.php Recipient user {$request->input('receiver_id')} not found or no device token to send push notification.");
    }

    // Return your response
    return response()->json(['message' => $message], 200);
});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $user = $request->user();

    if ($user) {
        // Delete the device token associated with this user
        $user->device_token = null; // Or delete the record from a separate 'device_tokens' table
        $user->save();

        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        Log::info("User ID: {$user->id} logged out. Device token cleared.");
        return response()->json(['message' => 'Logged out successfully, device token cleared.'], 200);
    }

    return response()->json(['message' => 'No active user session to log out.'], 401);
});

Route::middleware('auth:sanctum')->get('user/message/{from}/{to}', function (Request $request, $from='', $to='') {
    $user = \Auth::user();
    if ($user->tokenCan('send:message')) {
        $messages = ChatMessage::query()
            ->where(function($q) use ($to, $from) {
                $q->where("sender_id", $to)
                    ->where("receiver_id", $from);
            })
            ->orWhere(function($q) use ($to, $from) {
                $q->where("sender_id", $from)
                    ->where("receiver_id", $to);
            })
            ->orderBy('id','asc')
            ->get();

        return $messages;
    }
});


Route::middleware('auth:sanctum')->get('product/{id?}', 'App\Http\Controllers\Api\ProductApiController@show');


Route::get('bycategory/{name?}','App\Http\Controllers\Api\ProductApiController@byCategory');
Route::get('products','App\Http\Controllers\Api\ProductApiController@index');
Route::post('cart','App\Http\Controllers\Api\CartApiController@store');
Route::get('show','App\Http\Controllers\Api\CartApiController@show');
Route::post('sendOffer','App\Http\Controllers\Api\PriceOfferController@emailPriceOffer');

Route::post('login', 'App\Http\Controllers\Api\UserController@login');
Route::post('register', 'App\Http\Controllers\Api\UserController@register');

Route::group(['middleware' => 'auth:Api'], function(){
	Route::post('details', 'App\Http\Controllers\Api\UserController@details');
});