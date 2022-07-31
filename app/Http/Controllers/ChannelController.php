<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ChannelUser;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ChannelController extends Controller
{
    public function newChannel(Request $request)
    {
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
                    'message' => 'New channel created: ' . $name
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

            $channels = Channel::query()
                ->join('channel_user', 'channels.id', '=', 'channel_user.channel_id')
                ->select('channels.id as id', 'channels.name as channel', DB::raw("count(channel_user.user_id) as members"))
                ->groupBy('channels.id')
                ->where('channels.game_id', $game_id)
                ->orderBy('channels.created_at', 'asc')
                ->get()
                ->toArray();

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

    public function updateChannel(Request $request, $channelId)
    {
        define('ADMIN_ID_LOCAL', "5695fbbd-4675-4b2a-b31d-603252c21c94");

        try {

            Log::info('Updating channel');

            // check if the channel exists

            $channel = Channel::find($channelId);

            if (!$channel) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Channel not found'
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $userId = auth()->user()->id;
            $userIsAdmin = User::find($userId)->roles->contains(ADMIN_ID_LOCAL);

            // check if the logged user is the author of the channel
            // an admin can also edit the channel
            if (($channel->user_id != $userId) && (!$userIsAdmin)) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The user is not allowed to edit this channel'
                    ],
                    Response::HTTP_FORBIDDEN
                );
            }

            // validate new name. User can only edit the name
            $validator = Validator::make($request->all(), [
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

            $newName = $request->input("name");

            $channel->name = $newName;

            $channel->save();

            Log::info('Channel ' . $channel->id . ' edited. New name: ' . $channel->name);

            return response()->json(
                [
                    'success' => true,
                    'message' => "Channel's name edited: " . $newName
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {

            Log::error("Error updating channel: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error updating channel'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function deleteChannel($channelId)
    {
        define('ADMIN_ID_LOCAL2', "5695fbbd-4675-4b2a-b31d-603252c21c94");

        try {

            Log::info('Deleting channel');

            // check if the channel exists

            $channel = Channel::find($channelId);

            if (!$channel) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Channel not found'
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $userId = auth()->user()->id;
            $userIsAdmin = User::find($userId)->roles->contains(ADMIN_ID_LOCAL2);

            // check if the logged user is the author of the channel
            // an admin can also delete the channel
            if (($channel->user_id != $userId) && (!$userIsAdmin)) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'This user is not allowed to delete this channel'
                    ],
                    Response::HTTP_FORBIDDEN
                );
            }

            // deleting channel

            Log::info('Channel ' . $channel->id . ': '  . $channel->name . ' is about to be deleted');

            $channel->delete();

            return response()->json(
                [
                    'success' => true,
                    'message' => "Channel deleted: "
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {

            Log::error("Error deleting channel: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error deleting channel'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
