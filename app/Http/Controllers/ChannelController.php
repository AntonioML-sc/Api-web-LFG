<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ChannelController extends Controller
{
    public function newChannel(Request $request) {
        try {
            Log::info('Creating channel');

            $validator = Validator::make($request->all(), [
                'game_id' => ['required', 'string', 'max:36', 'min:36'],
                'name' => ['required', 'string', 'max:255', 'min:4']
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => $validator->errors()
                    ],
                    400
                );
            }

            $gameId = $request->input("game_id");
            $name = $request->input("name");

            $channel = new Channel();

            $channel->game_id = $gameId;
            $channel->name = $name;

            $channel->save();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'New channel created: '. $name
                ],
                201
            );
        } catch (\Exception $exception) {

            Log::error("Error creating channel " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error creating channel'
                ],
                500
            );
        }
    }
}
