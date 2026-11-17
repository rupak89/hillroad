<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use App\Services\GroupService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct(private GroupService $groupService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $groups = $this->groupService->getGroups((int) ($validated['per_page'] ?? 10));

        return response()->json([
            'message' => 'Groups list',
            'groups' => $groups,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GroupRequest $request)
    {
        $group = $this->groupService->createGroup($request->validated());

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $group = $this->groupService->getGroup($id);

        if (! $group) {
            return response()->json([
                'message' => 'Group not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Group details',
            'group' => $group,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GroupRequest $request, string $id)
    {
        $group = $this->groupService->updateGroup($id, $request->validated());

        return response()->json([
            'message' => 'Group updated successfully',
            'group' => $group,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->groupService->deleteGroup($id);

        if ($deleted) {
            return response()->json([
                'message' => 'Group deleted successfully',
            ]);
        }

        return response()->json([
            'message' => 'Group not found or could not be deleted',
        ], 404);
    }

    /**
     * Get items for a specific group.
     */
    public function items(Request $request, string $id)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
        ]);

        $items = $this->groupService->getGroupItems(
            (int) $id,
            (int) ($validated['per_page'] ?? 10),
            $validated['search'] ?? null
        );

        return response()->json([
            'message' => 'Group items list',
            'items' => $items,
            'group' => $this->groupService->getGroup((int) $id),
        ]);
    }
}
