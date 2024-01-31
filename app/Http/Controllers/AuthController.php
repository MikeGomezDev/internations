<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/authenticate",
     *     summary="Authenticates a user",
     *     tags={"AUTH"},
     *     @OA\RequestBody(
     *       required=true,
     *       description="Pass user credentials",
     *       @OA\JsonContent(
     *         required={"name","password"},
     *         @OA\Property(property="name", type="string", format="name", example="admin"),
     *         @OA\Property(property="password", type="string", format="password", example="admin_password"),
     *       ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully authenticated user",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid login details",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to access this resource",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Payload Validation error",
     *     ),
     * )
     */
    public function authenticate(Request $request)
    {
        //Create rules for user model
        $rules = [
            'name' => 'required|string',
            'password' => 'required|string',
        ];

        try{
            $this->validate($request, $rules);
        } catch (ValidationException $e) {
            return response()->json([
                'errors'=>$e->errors()
            ], 422);
        }

        //Attempt login
        if (!auth()->attempt($request->only('name', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        //Get user by email
        $user = User::where('name', $request->name)->firstOrFail();

        //Verify that user has admin role
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'You are not authorized to access this resource'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        //Return response
        return response(['token' => $token], 201);
    }

    public function unauthorized(Request $request)
    {
        return response()->json([
            'message' => 'You are not authorized to access this resource'
        ], 403);
    }
}
