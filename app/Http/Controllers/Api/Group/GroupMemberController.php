<?php

namespace App\Http\Controllers\Api\Group;

use App\Actions\Group\AddGroupMemberAction;
use App\Actions\Group\RemoveGroupMemberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Group\StoreGroupMemberRequest;
use App\Http\Resources\GroupMemberResource;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class GroupMemberController extends Controller
{
    public function index(Group $group): JsonResponse
    {
        $this->authorize('view', $group);

        $members = $group->members()->withPivot('created_at')->get();

        return response()->json(GroupMemberResource::collection($members));
    }

    public function store(StoreGroupMemberRequest $request, Group $group, AddGroupMemberAction $action): JsonResponse
    {
        $this->authorize('manageMembers', $group);

        $user = $action->execute($group, $request->validated('user_id'));

        return response()->json(new GroupMemberResource($user), 201);
    }

    public function destroy(Group $group, User $user, RemoveGroupMemberAction $action): JsonResponse
    {
        $this->authorize('manageMembers', $group);

        $action->execute($group, $user);

        return response()->json(['message' => 'Member removed successfully.']);
    }
}
