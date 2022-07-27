<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function newGame(Request $request) {
        try {
            Log::info('Creating task');

            $validator = Validator::make($request->all(), [
                'title' => ['required', 'string', 'max:255', 'min:3'],
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
                    400
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
                201
            );
        } catch (\Exception $exception) {

            Log::error("Error creating game " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error creating game'
                ],
                500
            );
        }

    }
}
