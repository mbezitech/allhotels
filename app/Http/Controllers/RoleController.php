<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::all();
        
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
            ]);

            if (isset($validated['permissions'])) {
                $role->permissions()->sync($validated['permissions']);
            }
        });

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load('permissions');
        
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $role->load('permissions');
        
        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::transaction(function () use ($role, $validated) {
            $role->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
            ]);

            if (isset($validated['permissions'])) {
                $role->permissions()->sync($validated['permissions']);
            } else {
                $role->permissions()->detach();
            }
        });

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Check if role is assigned to any users
        $hasUsers = DB::table('user_roles')
            ->where('role_id', $role->id)
            ->exists();

        if ($hasUsers) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete role that is assigned to users.');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
