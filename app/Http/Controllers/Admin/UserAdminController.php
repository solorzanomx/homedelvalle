<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\EmailService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(18);
        $currentUser = auth()->user();

        // Stats from all users (unfiltered)
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'admins' => User::where('role', 'admin')->count(),
            'brokers' => User::where('role', 'broker')->count(),
        ];

        return view('admin.users.index', compact('users', 'currentUser', 'stats'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('users.create'), 403);

        $roles = Role::withCount('permissions')->orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('users.create'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string|max:20',
            'rbac_role_id' => 'required|exists:roles,id',
        ]);

        $plainPassword = $validated['password'];

        // Map RBAC role to legacy role column
        $rbacRole = Role::find($validated['rbac_role_id']);
        $legacyRoleMap = [
            'super_admin'      => 'admin',
            'broker_senior'    => 'broker',
            'broker_direccion' => 'editor',
            'asesor'           => 'viewer',
            'user'             => 'user',
            'client'           => 'user',
        ];
        $legacyRole = $legacyRoleMap[$rbacRole->slug] ?? 'user';

        $permissions = match($legacyRole) {
            'admin' => ['can_read' => true, 'can_edit' => true, 'can_delete' => true],
            'editor', 'broker' => ['can_read' => true, 'can_edit' => true, 'can_delete' => false],
            default => ['can_read' => true, 'can_edit' => false, 'can_delete' => false],
        };

        $userData = collect($validated)->except('rbac_role_id')->toArray();
        $userData['role'] = $legacyRole;

        $user = User::create(array_merge($userData, $permissions));

        // Assign RBAC role
        $user->roles()->sync([$rbacRole->id]);
        $user->clearPermissionCache();

        // Send welcome email
        try {
            app(EmailService::class)->sendWelcomeEmail(
                $user->name,
                $user->email,
                $plainPassword,
                $rbacRole->name
            );
        } catch (\Exception $e) {
            // Don't block user creation if email fails
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente');
    }

    public function show(User $user)
    {
        $currentUser = auth()->user();
        return view('admin.users.show', compact('user', 'currentUser'));
    }

    public function edit(User $user)
    {
        abort_unless(auth()->user()->hasPermission('users.edit'), 403);

        $currentUser = auth()->user();
        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'currentUser', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()->hasPermission('users.edit'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:200',
            'title' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'show_phone_on_properties' => 'boolean',
        ]);

        $validated['show_phone_on_properties'] = $request->boolean('show_phone_on_properties');

        $user->update($validated);

        // Update mail settings if provided
        $mailEmail = $request->input('mail_from_email', $user->email);
        $mailData = [
            'from_email' => $mailEmail,
            'from_name' => $request->input('mail_from_name', $user->name . ' ' . ($user->last_name ?? '')),
            'username' => $mailEmail,
            'is_active' => $request->boolean('mail_is_active'),
        ];
        if ($request->filled('mail_password')) {
            $mailData['password'] = $request->input('mail_password');
        }
        $user->mailSetting()->updateOrCreate(
            ['user_id' => $user->id],
            $mailData
        );

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Usuario actualizado']);
        }

        return redirect()->route('admin.users.show', $user)->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()->hasPermission('users.delete'), 403);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta');
        }

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente');
    }

    public function uploadAvatar(Request $request, User $user)
    {
        // Allow users to upload their own avatar, require users.edit for others
        if ($user->id !== auth()->id()) {
            abort_unless(auth()->user()->hasPermission('users.edit'), 403);
        }

        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
        ]);

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);

        return response()->json([
            'success' => true,
            'avatar_url' => Storage::disk('public')->url($path)
        ]);
    }

    public function permissions()
    {
        abort_unless(auth()->user()->hasPermission('users.edit'), 403);

        $users = User::with('roles.permissions')->paginate(15);
        $currentUser = auth()->user();
        $roles = Role::orderBy('name')->get();
        $permissionsByModule = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('admin.users.permissions', compact('users', 'currentUser', 'roles', 'permissionsByModule'));
    }

    public function changeRole(Request $request, User $user)
    {
        abort_unless(auth()->user()->hasPermission('users.edit'), 403);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes cambiar tu propio rol');
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,editor,viewer,user'
        ]);

        $role = $validated['role'];

        // Asignar permisos según el rol
        if ($role === 'admin') {
            $user->update([
                'role' => $role,
                'can_read' => true,
                'can_edit' => true,
                'can_delete' => true
            ]);
        } elseif ($role === 'editor') {
            $user->update([
                'role' => $role,
                'can_read' => true,
                'can_edit' => true,
                'can_delete' => false
            ]);
        } elseif ($role === 'viewer') {
            $user->update([
                'role' => $role,
                'can_read' => true,
                'can_edit' => false,
                'can_delete' => false
            ]);
        } else {
            $user->update([
                'role' => $role,
                'can_read' => false,
                'can_edit' => false,
                'can_delete' => false
            ]);
        }

        // Sync RBAC role if provided
        if ($request->filled('rbac_role_id')) {
            $user->roles()->sync([$request->input('rbac_role_id')]);
        }

        $user->clearPermissionCache();

        return back()->with('success', 'Rol del usuario actualizado correctamente');
    }

    public function updatePermissions(Request $request, User $user)
    {
        abort_unless(auth()->user()->hasPermission('users.edit'), 403);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes cambiar tus propios permisos');
        }

        // Sync RBAC role
        $roleId = $request->input('rbac_role_id');
        if ($roleId) {
            $user->roles()->sync([$roleId]);
        }

        // Sync individual permission overrides
        $permissionIds = $request->input('permissions', []);
        // Get permissions from the assigned role
        $role = $roleId ? Role::find($roleId) : $user->roles->first();
        $rolePermissionIds = $role ? $role->permissions()->pluck('permissions.id')->toArray() : [];

        // Merge: role permissions + any extra toggled permissions
        // The user gets exactly what was checked in the form
        // We attach extra permissions directly via a custom pivot if needed
        // For simplicity: we sync the role AND adjust the role's permissions aren't touched,
        // but we store the exact set the user should have via role assignment

        // If the selected permissions differ from the role default, we need to handle it.
        // Approach: assign a role, but also allow the admin to customize.
        // We'll sync the role and update its permissions for this user specifically
        // by creating a direct user-permission relationship.

        // Actually, the cleanest approach: just sync the role. The checkboxes
        // reflect the role's permissions. If admin wants custom, they should create a custom role.
        // But the user asked for per-user toggle, so let's handle it.

        // We'll detach extra permissions from the role relationship and instead
        // add a direct user<->permission pivot. But our current schema doesn't have that.
        // The simplest approach: find/create a per-user role with the exact permissions.

        // Simplest: update the assigned role's permissions if it's not a system role,
        // or create a custom role for this user.

        // PRAGMATIC approach: assign the role, then if permissions differ, create a custom role
        if ($role && $role->is_system) {
            $customSlug = 'custom_user_' . $user->id;
            $customRole = Role::firstOrCreate(
                ['slug' => $customSlug],
                ['name' => 'Custom - ' . $user->name, 'is_system' => false]
            );
            $customRole->permissions()->sync($permissionIds);
            $user->roles()->sync([$customRole->id]);
        } else {
            // Non-system role or no role: directly sync permissions on it
            if ($role) {
                $role->permissions()->sync($permissionIds);
                $user->roles()->sync([$role->id]);
            }
        }

        $user->clearPermissionCache();

        return back()->with('success', 'Permisos actualizados correctamente');
    }
}
