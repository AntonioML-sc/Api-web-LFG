<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    public function postMessage(Request $request)
    {
        try {

            Log::info("Posting a message");

            // validate the new message text
            $validator = Validator::make($request->all(), [
                'message_text' => 'required|string|max:65535',
                'channel_id' => 'required|string|max:36|min:36'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), Response::HTTP_BAD_REQUEST);
            }

            $channelId = $request->input('channel_id');

            $channel = Channel::find($channelId);

            // check if the channel exists
            if (!$channel) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Channel not found'
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $userId = auth()->user()->id;

            // Check if the user is registered in the channel

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
                    Response::HTTP_FORBIDDEN
                );
            }

            // If everything is ok, the message is created

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
                Response::HTTP_CREATED
            );
        } catch (\Exception $exception) {

            Log::error("Error posting message: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error posting message'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function getMessagesByChannelId($channel_id)
    {
        try {

            Log::info('getting channel messages');

            $channel = Channel::find($channel_id);

            // check if the channel exists
            if (!$channel) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The channel does not exist'
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $userId = auth()->user()->id;

            // check if the user is registered in the channel

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
                    Response::HTTP_FORBIDDEN
                );
            }

            // get all of the messages of the channel in chronological order
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
                    Response::HTTP_NOT_FOUND
                );
            }

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Messages retrieved successfully',
                    'data' => $messages
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {

            Log::error("Error getting channel messages: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error getting channel messages'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function editMessage(Request $request, $option, $msgId)
    {
        // It is permitted only to change the text of a message and it will be shown as 'edited' or 'deleted'
        // The channel can not be changed and the register of the message will not be destroyed when user make 'fake delete'
        try {

            Log::info("Editing a message");

            // validate the new message text
            $validator = Validator::make($request->all(), [
                'message_text' => 'string|max:65535'
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => $validator->errors()
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $message = Message::find($msgId);

            // check if the message exists
            if (!$message) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => "Message not found"
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $userId = auth()->user()->id;

            // check if the logged user is the author of the message
            if ($message->user_id != $userId) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The user is not allowed to edit this message'
                    ],
                    Response::HTTP_FORBIDDEN
                );
            }

            // set the new content of the message depending on the option            
            switch ($option) {
                case 'update':
                    $messageText = 'Message edited: ' . $request->input('message_text');
                    break;

                case 'delete':
                    $messageText = 'Message deleted by user';
                    break;

                default:
                    return response()->json(
                        [
                            'success' => false,
                            'message' => "Option missing or not available"
                        ],
                        Response::HTTP_BAD_REQUEST
                    );
            }

            $message->message_text = $messageText;
            $message->save();

            Log::info('Message id = '. $message->id . " edited by user (" . $option . ")");

            return response()->json(
                [
                    'success' => true,
                    'message' => "Message edited",
                    'data' => $message
                ]
            );

        } catch (\Exception $exception) {

            Log::error("Error editing message: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error editing message'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
