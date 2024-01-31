<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/groups",
     *     summary="Gets all the groups",
     *     tags={"GROUPS"},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     * )
     */
    public function index()
    {
        $groups = Group::all();
        return response()->json($groups->load('users:name'));
    }

    /**
     * @OA\Post(
     *     path="/api/group",
     *     summary="Creates a new group",
     *     tags={"GROUPS"},
     *     @OA\RequestBody(
     *       required=true,
     *       description="Pass group name",
     *       @OA\JsonContent(
     *         required={"name"},
     *         @OA\Property(property="name", type="string", format="name", example="Group 1"),
     *       ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully created group",
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
        $rules = [
            'name' => 'required|string|unique:groups',
        ];

        try{
            $this->validate($request, $rules);
        } catch (ValidationException $e) {
            return response()->json([
                'errors'=>$e->errors()
            ], 422);
        }

        $group = new Group([
            'name' => $request->name,
        ]);

        $group->save();

        return response()->json([
            'message' => 'Successfully created group!'
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/group/{group_id}/addUser/{user_id}",
     *     summary="Adds user to a given group",
     *     tags={"GROUPS"},
     *     @OA\Parameter(
     *         name="group_id",
     *         description="Id of the group",
     *         required=true,
     *         in="path",
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         description="Id of the user",
     *         required=true,
     *         in="path",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully added user to group!",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Group or user not found!",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="User already in group!",
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function addUser(Request $request, string $group_id, string $user_id)
    {
        try{
            $group = Group::findOrFail($group_id);
            $user = User::findOrFail($user_id);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Group or user not found!'
            ], 404);
        }

        if($group->users()->where('users.id', $user_id)->exists()) {
            return response()->json([
                'message' => 'User already in group!'
            ], 400);
        }

        $group->users()->attach($user);

        return response()->json([
            'message' => 'Successfully added user to group!'
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/group/{group_id}/removeUser/{user_id}",
     *     summary="Removes user to a given group",
     *     tags={"GROUPS"},
     *     @OA\Parameter(
     *         name="group_id",
     *         description="Id of the group",
     *         required=true,
     *         in="path",
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         description="Id of the user",
     *         required=true,
     *         in="path",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully removed user from group!",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Group or user not found!",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="User not in group!",
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function removeUser(Request $request, string $group_id, string $user_id)
    {
        try{
            $group = Group::findOrFail($group_id);
            $user = User::findOrFail($user_id);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Group or user not found!'
            ], 404);
        }

        if(!$group->users()->where('users.id', $user_id)->exists()) {
            return response()->json([
                'message' => 'User not in group!'
            ], 400);
        }

        $group->users()->detach($user);

        return response()->json([
            'message' => 'Successfully removed user from group!'
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/group/{id}",
     *     summary="Deletes a given group",
     *     tags={"GROUPS"},
     *     @OA\Parameter(
     *         name="id",
     *         description="Id of the group",
     *         required=true,
     *         in="path",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted group!",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Group has users!",
     *     ),
     *     @OA\Response(
     *        response=404,
     *        description="Group not found!",
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy(string $id)
    {
        try{
            $group = Group::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Group not found!'
            ], 404);
        }

        // Verify that group has no users
        if($group->users()->exists()) {
            return response()->json([
                'message' => 'Group has users!'
            ], 400);
        }

        $group->delete();

        return response()->json([
            'message' => 'Successfully deleted group!'
        ], 200);
    }
}
