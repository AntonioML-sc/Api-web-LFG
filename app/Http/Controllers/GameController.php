<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
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

            $games = Game::all();

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

    public function updateGame(Request $request, $gameId) {
        try {

            Log::info('Updating game');

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

            if (!$game) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Invalid game id'
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $user_id = auth()->user()->id;

            if ($game->user_id != $user_id) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'This game register does not belong to you'
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $title = $request->input("title");
            $genre = $request->input("genre");
            $age = $request->input("age");
            $devStudio = $request->input("dev_studio");

            if(isset($title)) {
                $game->title = $title;
            }

            if(isset($genre)) {
                $game->genre = $genre;
            }

            if(isset($age)) {
                $game->age = $age;
            }

            if(isset($devStudio)) {
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
}
