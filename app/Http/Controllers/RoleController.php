<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function createRole(Request $request)
    {

        try {
            Log::info('Creating role');

            $validator = Validator::make($request->all(), [
                'role_name' => ['required', 'string', 'max:255', 'min:3']
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

            $roleName = $request->input("role_name");

            $role = new Role();

            $role->role_name = $roleName;

            $role->save();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'New role created'
                ],
                201
            );
        } catch (\Exception $exception) {

            Log::error("Error creating role: " . $exception->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error creating role'
                ],
                500
            );
        }
    }
}
