<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Gets all the users",
     *     tags={"USERS"},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     * )
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users->load('roles:name', 'groups:name'));
    }

    /**
     * @OA\Post(
     *     path="/api/user",
     *     summary="Creates a new user",
     *     tags={"USERS"},
     *     @OA\RequestBody(
     *        required=true,
     *        description="Pass user credentials",
     *        @OA\JsonContent(
     *           required={"name","password"},
     *           @OA\Property(property="name", type="string", format="name", example="John Doe"),
     *           @OA\Property(property="password", type="string", format="password", example="password"),
     *        ),
     *    ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully created user",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Payload Validation error",
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {   
        //Create rules for user model
        $rules = [
            'name' => 'required|string|unique:users',
            'password' => 'required|string|min:8',
        ];

        try{
            $this->validate($request, $rules);
        } catch (ValidationException $e) {
            return response()->json([
                'errors'=>$e->errors()
            ], 422);
        }

        //Create new user
        $user = new User([
            'name' => $request->name,
            'password' => bcrypt($request->password)
        ]);

        //Save user
        $user->save();

        //Attach new user the user role
        $user->roles()->attach(2);

        //Return response
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);

    }

    /**
     * @OA\Delete(
     *     path="/api/user/{id}",
     *     summary="Deletes a given user",
     *     tags={"USERS"},
     *     @OA\Parameter(
     *         name="id",
     *         description="Id of the user",
     *         required=true,
     *         in="path",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted user!",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="You are not authorized to delete this user!",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found!",
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy(string $id)
    {
        try{
            $user = User::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User not found!'
            ], 404);
        }

        if($user->isAdmin()) {
            return response()->json([
                'message' => 'You are not authorized to delete this user!'
            ], 401);
        }

        $user->delete();

        return response()->json([
            'message' => 'Successfully deleted user!'
        ], 200);
    }
}
