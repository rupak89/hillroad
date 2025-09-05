<?php

namespace App\Services;
use App\Models\Group;

class GroupService
{
    /**
     * Get the list of groups with pagination.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getGroups($perPage = 10)
    {
        return Group::withCount('items')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Create a new group.
     *
     * @param array $data { name: string }
     * @return Group
     */
    public function createGroup(array $data): Group
    {
        return Group::create($data);
    }

    /**
     * Update an existing group.
     *
     * @param int $id
     * @param array $data
     * @return Group
     */
    public function updateGroup(int $id, array $data): Group
    {
        $group = Group::findOrFail($id);
        $group->update($data);
        return $group;
    }

    /**
     * Delete a group.
     *
     * @param int $id
     * @return bool|null
     */
    public function deleteGroup(string $id): ?bool
    {
        $group = Group::find($id);
        if ($group) {
            return $group->delete();
        }
        return null;
    }

    /**
     * Get a single group by ID.
     *
     * @param int $id
     * @return Group|null
     */
    public function getGroup(int $id): ?Group
    {
        return Group::find($id);
    }
}
