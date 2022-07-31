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
    const ADMIN_ID_LOCAL = "5695fbbd-4675-4b2a-b31d-603252c21c94";
    const SUPER_ADMIN_ID_LOCAL = "a3c06730-7018-467d-8187-cef95f37224d";

    public function joinToChannel(Request $request)
    {
        try {

            Log::info("User joining to channel");

            // Validates channel_id
            $validator = Validator::make($request->all(), [
                'channel_id' => 'required|string|max:36|min:36'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), Response::HTTP_BAD_REQUEST);
            }

            $channelId = $request->input('channel_id');

            $channel = Channel::find($channelId);

            // check if channel exists
            if (!$channel) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The channel specified does not exist'
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $userId = auth()->user()->id;

            $user = User::find($userId);
            $userIsInChannel = $user->channels->contains($channelId);

            // check if user joined to the channel previously. If not, user joins to channel.
            if (!$userIsInChannel) {
                $user->channels()->attach($channelId);
            }

            Log::info('The user ' . $user->email . ' has joined to the channel ' . $channel->name);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'The user ' . $user->email . ' has joined to the channel ' . $channel->name
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {

            Log::error("Error in joining to channel: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error in joining to channel'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function leaveChannel(Request $request)
    {
        try {

            Log::info("User leaving channel");

            // validates channel_id
            $validator = Validator::make($request->all(), [
                'channel_id' => 'required|string|max:36|min:36'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), Response::HTTP_BAD_REQUEST);
            }

            $channelId = $request->input('channel_id');

            $channel = Channel::find($channelId);

            // verify that channel exists
            if (!$channel) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The channel specified does not exist'
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $userId = auth()->user()->id;

            $user = User::find($userId);

            // User leave the channel
            $user->channels()->detach($channelId);

            Log::info('The user ' . $user->email . ' has left the channel ' . $channel->name);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'The user ' . $user->email . ' has left the channel ' . $channel->name
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {

            Log::error("Error in leaving channel: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error in leaving channel'
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    //// ******** ADMIN AND SUPERADMIN MANAGEMENT ******** \\\\    

    public function setUserRole($newRole, $userId)
    {

        $superAdminIdLocal = "a3c06730-7018-467d-8187-cef95f37224d";
        $adminIdLocal = "5695fbbd-4675-4b2a-b31d-603252c21c94";

        // functions to verify user roles and set a user role. A superadmin must be an admin as well.
        function setUser($user, $adminId, $superId)
        {
            $user->roles()->detach($adminId);
            $user->roles()->detach($superId);
        }

        function setAdmin($user, $adminId, $superId)
        {
            $user->roles()->detach($superId);
            $isAdmin = $user->roles->contains($adminId);
            if (!$isAdmin) {
                $user->roles()->attach($adminId);
            }
        }

        function setSuperAdmin($user, $adminId, $superId)
        {
            $isAdmin = $user->roles->contains($adminId);
            $isSuperAdmin = $user->roles->contains($superId);
            if (!$isAdmin) {
                $user->roles()->attach($adminId);
            }
            if (!$isSuperAdmin) {
                $user->roles()->attach($superId);
            }
        }

        try {

            $user = User::find($userId);

            // check if user exists
            if (!$user) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User not found'
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            // assign the new role to user
            switch ($newRole) {
                case 'user':
                    setUser($user, $adminIdLocal, $superAdminIdLocal);
                    break;

                case 'admin':
                    setAdmin($user, $adminIdLocal, $superAdminIdLocal);
                    break;

                case 'superadmin':
                    setSuperAdmin($user, $adminIdLocal, $superAdminIdLocal);
                    break;

                default:
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'missing role'
                        ],
                        Response::HTTP_BAD_REQUEST
                    );
            }

            return response()->json(
                [
                    'success' => true,
                    'message' => $user->name . "'s role set to " . $newRole
                ],
                Response::HTTP_CREATED
            );
        } catch (Exception $exception) {

            Log::error("Error setting user's role" . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => "Error setting user's role"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function getUsers()
    {
        try {

            Log::info('Retrieving all users');

            $users = User::all();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Users retrieved successfully',
                    'data' => $users
                ]
            );
        } catch (Exception $exception) {

            Log::error("Error retrieveing users" . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => "Error retrieveing users"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function getUserById($userId) {
        try {

            Log::info('Retrieving all users');

            $user = User::find($userId);

            // check if user exists
            if (!$user) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User not found'
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            return response()->json(
                [
                    'success' => true,
                    'message' => 'User retrieved successfully',
                    'data' => $user
                ]
            );

        } catch (Exception $exception) {

            Log::error("Error retrieveing user" . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => "Error retrieveing user"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
