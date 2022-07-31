<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ChannelController extends Controller
{
    public function newChannel(Request $request) {
        try {
            Log::info('Creating channel');

            // validate data
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
                    Response::HTTP_BAD_REQUEST
                );
            }

            $gameId = $request->input("game_id");
            $name = $request->input("name");

            $userId = auth()->user()->id;

            // Creates the new channel
            $channel = new Channel();

            $channel->game_id = $gameId;
            $channel->name = $name;
            $channel->user_id = $userId;

            $channel->save();

            // the user joins to the created channel            

            $channel->users()->attach($userId);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'New channel created: '. $name
                ],
                Response::HTTP_CREATED
            );
        } catch (\Exception $exception) {

            Log::error("Error creating channel " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error creating channel'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function getChannelsByGameId($game_id)
    {

        try {

            Log::info("Getting all game's channels");

            $game = Game::find($game_id);

            // check if the game exists
            if (!$game) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The game is not registered'
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $channels = Channel::query()->where('game_id', $game_id)->get()->toArray();

            // check if there are channels
            if ($channels == []) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'There are no channels of the game specified yet'
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            // retrieve channels
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Channels retrieved successfully',
                    'data' => $channels
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $exception) {

            Log::error("Error getting channels: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error getting channels'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
