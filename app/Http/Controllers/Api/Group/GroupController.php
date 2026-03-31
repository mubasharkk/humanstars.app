<?php

namespace App\Http\Controllers\Api\Group;

use App\Actions\Group\CreateGroupAction;
use App\Actions\Group\DeleteGroupAction;
use App\Actions\Group\UpdateGroupAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * List groups owned by the user and groups the user is a member of.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $owned = Group::where('user_id', $user->id)
            ->withCount('members')
            ->get();

        $member = Group::where('user_id', '!=', $user->id)
            ->whereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->withCount('members')
            ->get();

        return response()->json([
            'owned'  => GroupResource::collection($owned),
            'member' => GroupResource::collection($member),
        ]);
    }

    public function store(StoreGroupRequest $request, CreateGroupAction $action): JsonResponse
    {
        $this->authorize('create', Group::class);

        $group = $action->execute($request->user(), $request->validated());

        return response()->json(new GroupResource($group), 201);
    }

    public function show(Group $group): JsonResponse
    {
        $this->authorize('view', $group);

        return response()->json(new GroupResource($group->loadCount('members')));
    }

    public function update(UpdateGroupRequest $request, Group $group, UpdateGroupAction $action): JsonResponse
    {
        $this->authorize('update', $group);

        $group = $action->execute($group, $request->validated());

        return response()->json(new GroupResource($group->loadCount('members')));
    }

    public function destroy(Group $group, DeleteGroupAction $action): JsonResponse
    {
        $this->authorize('delete', $group);

        $action->execute($group);

        return response()->json(['message' => 'Group deleted successfully.']);
    }
}
