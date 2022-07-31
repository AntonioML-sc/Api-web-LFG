<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    public function newGame(Request $request)
    {
        try {
            Log::info('Creating task');

            $validator = Validator::make($request->all(), [
                'title' => ['required', 'string', 'max:255', 'min:3', 'unique:games'],
                'genre' => ['required', 'string', 'max:255'],
                'age' => ['required', 'integer'],
                'dev_studio' => ['required', 'string', 'max:255'],
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

            $user_id = auth()->user()->id;

            $title = $request->input("title");
            $genre = $request->input("genre");
            $age = $request->input("age");
            $devStudio = $request->input("dev_studio");

            $game = new Game();

            $game->title = $title;
            $game->user_id = $user_id;
            $game->genre = $genre;
            $game->age = $age;
            $game->dev_studio = $devStudio;


            $game->save();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'New game created'
                ],
                Response::HTTP_CREATED
            );
        } catch (\Exception $exception) {

            Log::error("Error creating game " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error creating game'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function getGames()
    {
        try {

            Log::info('retrieving all games');

            $games = Game::query()
                ->leftJoin('channels', 'games.id', '=', 'channels.game_id')
                ->select('games.id as id', 'games.title as title', DB::raw("count(channels.id) as channels"))
                ->groupBy('games.id')
                ->orderBy('games.created_at', 'asc')
                ->get()
                ->toArray();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Games retrieved successfully',
                    'data' => $games
                ]
            );
        } catch (\Exception $exception) {

            Log::error("Error retrieving games " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error retrieving games'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function updateGame(Request $request, $gameId)
    {
        define('SUPER_ADMIN_ID_LOCAL', "a3c06730-7018-467d-8187-cef95f37224d");

        try {

            Log::info('Updating game');

            // validate data
            $validator = Validator::make($request->all(), [
                'title' => ['string', 'max:255', 'min:3', 'unique:games'],
                'genre' => ['string', 'max:255'],
                'age' => ['integer'],
                'dev_studio' => ['string', 'max:255']
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

            $game = Game::query()->find($gameId);

            // check if the game exists
            if (!$game) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Invalid game id'
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $userId = auth()->user()->id;
            $userIsSuperAdmin = User::find($userId)->roles->contains(SUPER_ADMIN_ID_LOCAL);

            // only the user that registered the game (who must be also admin) can update it
            // any superadmin can also update the game
            if (($game->user_id != $userId) && !$userIsSuperAdmin) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'This useris not allowed to update this game'
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // edit the game fields with the values provided in $request 
            $title = $request->input("title");
            $genre = $request->input("genre");
            $age = $request->input("age");
            $devStudio = $request->input("dev_studio");

            if (isset($title)) {
                $game->title = $title;
            }

            if (isset($genre)) {
                $game->genre = $genre;
            }

            if (isset($age)) {
                $game->age = $age;
            }

            if (isset($devStudio)) {
                $game->dev_studio = $devStudio;
            }

            $game->save();

            Log::info('Game updated. Info: ' . $game);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Game updated successfully',
                    'data' => $game
                ]
            );
        } catch (\Exception $exception) {

            Log::error("Error updating game " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error updating game'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function deleteGame($gameId)
    {        
        define('SUPER_ADMIN_ID_LOCAL2', "a3c06730-7018-467d-8187-cef95f37224d");

        try {

            Log::info('Trying to delete game');

            $game = Game::query()->find($gameId);

            // Check if the game exists
            if (!$game) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Invalid game id'
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $userId = auth()->user()->id;
            $userIsSuperAdmin = User::find($userId)->roles->contains(SUPER_ADMIN_ID_LOCAL2);

            // only the user that registered the game (who must be also an admin) or a superadmin can delete it
            if (($game->user_id != $userId) && !$userIsSuperAdmin) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'This user is not allowed to delete this game'
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            Log::info('The game ' . $game->id . ': ' . $game->title . ' is about to be deleted');

            $game->delete();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Game deleted successfully'
                ]
            );
        } catch (\Exception $exception) {

            Log::error("Error deleting game " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error deleting game'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
