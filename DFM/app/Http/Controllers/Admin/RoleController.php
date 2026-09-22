<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'is_system' => $role->is_system,
                'users_count' => $role->users_count,
                'permissions' => $role->is_system ? Permissions::all() : $role->getPermissionKeys(),
            ]);

        return Inertia::render('admin/roles/index', [
            'roles' => $roles,
            'permissionCatalog' => Permissions::grouped(),
            'allPermissions' => Permissions::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'in:'.implode(',', Permissions::all())],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'is_system' => false,
        ]);

        $role->syncPermissions($validated['permissions']);

        return redirect()->back()->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return redirect()->back()->with('error', 'Super Admin role cannot be modified.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'in:'.implode(',', Permissions::all())],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        return redirect()->back()->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return redirect()->back()->with('error', 'Super Admin role cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete role assigned to active users.');
        }

        $role->permissions()->delete();
        $role->delete();

        return redirect()->back()->with('success', 'Role deleted successfully.');
    }
}
