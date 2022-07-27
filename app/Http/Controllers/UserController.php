<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function joinToChannel(Request $request)
    {
        try {

            Log::info("User joining to channel");

            $validator = Validator::make($request->all(), [
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

            $user = User::find($userId);

            $user->channels()->attach($channelId);

            Log::info('The user '. $user->email .' has joined to the channel '. $channel->name);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'The user '. $user->email .' has joined to the channel '. $channel->name
                ],
                200
            );

        } catch (\Exception $exception) {

            Log::error("Error in joining to channel: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error in joining to channel'
                ],
                500
            );
        }
    }
}
