<?php

namespace App\Traits;

use App\Models\Property;
use Spatie\Permission\Models\Role;

trait HasPropertyRoles
{
    /**
     * Assign a role to the user for a specific property
     * Example: $user->assignPropertyRole('manager', $property);
     */
    public function assignPropertyRole(string $role, Property $property): void
    {
        $roleName = "{$role}:property:{$property->id}";

        if (! Role::where('name', $roleName)->exists()) {
            Role::create(['name' => $roleName]);
        }

        $this->assignRole($roleName);
    }

    /**
     * Remove a role from the user for a specific property
     */
    public function removePropertyRole(string $role, Property $property): void
    {
        $roleName = "{$role}:property:{$property->id}";
        $this->removeRole($roleName);
    }

    /**
     * Check if user has a role for a specific property
     * Example: $user->hasPropertyRole('manager', $property);
     */
    public function hasPropertyRole(string $role, Property $property): bool
    {
        $roleName = "{$role}:property:{$property->id}";
        return $this->hasRole($roleName);
    }

    /**
     * Check if user has any role for a specific property
     */
    public function hasAnyPropertyRole(Property $property): bool
    {
        $pattern = ":property:{$property->id}";

        return $this->roles()
            ->where('name', 'like', "%{$pattern}")
            ->exists();
    }

    /**
     * Get all roles for a specific property
     */
    public function getPropertyRoles(Property $property): array
    {
        $pattern = ":property:{$property->id}";

        return $this->roles()
            ->where('name', 'like', "%{$pattern}")
            ->get()
            ->map(function ($role) {
                // Extract role name from "role:property:id" format
                return explode(':', $role->name)[0];
            })
            ->toArray();
    }

    /**
     * Sync property roles - removes all existing property roles and assigns new ones
     */
    public function syncPropertyRoles(array $roles, Property $property): void
    {
        // Remove all existing roles for this property
        $pattern = ":property:{$property->id}";
        $existingRoles = $this->roles()
            ->where('name', 'like', "%{$pattern}")
            ->get();

        foreach ($existingRoles as $role) {
            $this->removeRole($role);
        }

        // Assign new roles
        foreach ($roles as $role) {
            $this->assignPropertyRole($role, $property);
        }
    }
}
