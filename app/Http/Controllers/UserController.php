<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class UserController extends Controller
{
    const SUPER_ADMIN_ID_LOCAL = "a3c06730-7018-467d-8187-cef95f37224d";

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

    public function leaveChannel(Request $request)
    {
        try {

            Log::info("User leaving channel");

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

            $user->channels()->detach($channelId);

            Log::info('The user '. $user->email .' has left the channel '. $channel->name);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'The user '. $user->email .' has left the channel '. $channel->name
                ],
                200
            );

        } catch (\Exception $exception) {

            Log::error("Error in leaving channel: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error in leaving channel'
                ],
                500
            );
        }
    }

    //// ******** ADMIN AND SUPERADMIN MANAGEMENT ******** \\\\

    public function promoteUserToSuperAdmin($userId) {

        try {
            
            $user = User::find($userId);

            if (!$user) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User not found'
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $user->roles()->attach(self::SUPER_ADMIN_ID_LOCAL);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'User '. $user->name .' promoted to super_admin'
                ],
                Response::HTTP_CREATED
            );

        } catch (Exception $exception) {
            
            Log::error("Error promoting user to super_admin" . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error promoting user to super_admin'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function degradeUserFromSuperAdmin($userId) {

        try {
            
            $user = User::find($userId);

            if (!$user) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User not found'
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $user->roles()->detach(self::SUPER_ADMIN_ID_LOCAL);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'User '. $user->name .' is not super_admin anymore'
                ],
                Response::HTTP_OK
            );

        } catch (Exception $exception) {
            
            Log::error("Error degrading user from super_admin" . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error degrading user from super_admin'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
