<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function postMessage(Request $request)
    {
        try {

            Log::info("Posting a message");

            $validator = Validator::make($request->all(), [
                'message_text' => 'required|string|max:65535',
                'channel_id' => 'required|string|max:36|min:36'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $channelId = $request->input('channel_id');

            $channel = Channel::find($channelId);

            if (!$channel) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The channel specified does not exist'
                    ],
                    404
                );
            }

            $userId = auth()->user()->id;

            $userInChannel = DB::table('channel_user')
                ->where('user_id', $userId)
                ->where('channel_id', $channelId)
                ->first();

            if (!$userInChannel) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The user does not belong to the channel specified'
                    ],
                    403
                );
            }

            $messageText = $request->input('message_text');

            $message = new Message();

            $message->user_id = $userId;
            $message->channel_id = $channelId;
            $message->message_text = $messageText;

            $message->save();

            Log::info('Message sent');

            return response()->json(
                [
                    'success' => true,
                    'message' => 'message sent'
                ],
                201
            );

        } catch (\Exception $exception) {

            Log::error("Error posting message: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error posting message'
                ],
                500
            );
        }
    }

    public function getMessagesByChannelId($channel_id)
    {
        try {

            Log::info('getting channel messages');

            $channel = Channel::find($channel_id);

            if (!$channel) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The channel does not exist'
                    ],
                    400
                );
            }
            
            $userId = auth()->user()->id;

            $userInChannel = DB::table('channel_user')
                ->where('user_id', $userId)
                ->where('channel_id', $channel_id)
                ->first();

            if (!$userInChannel) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The user does not belong to the channel specified'
                    ],
                    403
                );
            }

            $messages = Message::query()
                ->where('channel_id', $channel_id)
                ->select('users.name as user', 'messages.message_text')
                ->join('users', 'users.id', '=', 'messages.user_id')
                ->orderBy('messages.created_at', 'asc')
                ->get()                
                ->toArray();            

            if ($messages == []) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'There are no messages in this channel yet'
                    ],
                    404
                );
            }

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Messages retrieved successfully',
                    'data' => $messages
                ],
                200
            );
            
        } catch (\Exception $exception) {

            Log::error("Error getting channel messages: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error getting channel messages'
                ],
                500
            );
        }
    }
}
