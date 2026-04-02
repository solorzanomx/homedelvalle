<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Permissions ───────────────────────────────────

        $permissions = [
            // Leads
            ['slug' => 'leads.view',       'name' => 'Ver todos los leads',     'module' => 'leads'],
            ['slug' => 'leads.view.own',   'name' => 'Ver leads propios',       'module' => 'leads'],
            ['slug' => 'leads.create',     'name' => 'Crear leads',             'module' => 'leads'],
            ['slug' => 'leads.edit',       'name' => 'Editar leads',            'module' => 'leads'],
            ['slug' => 'leads.delete',     'name' => 'Eliminar leads',          'module' => 'leads'],
            ['slug' => 'leads.assign',     'name' => 'Asignar leads',           'module' => 'leads'],

            // Pipeline
            ['slug' => 'pipeline.view',    'name' => 'Ver pipeline',            'module' => 'pipeline'],
            ['slug' => 'pipeline.move',    'name' => 'Mover etapa en pipeline', 'module' => 'pipeline'],
            ['slug' => 'pipeline.edit',    'name' => 'Editar pipeline',         'module' => 'pipeline'],

            // Dashboard
            ['slug' => 'dashboard.view.basic',    'name' => 'Ver dashboard basico',    'module' => 'dashboard'],
            ['slug' => 'dashboard.view.advanced',  'name' => 'Ver dashboard avanzado',  'module' => 'dashboard'],

            // Users
            ['slug' => 'users.view',       'name' => 'Ver usuarios',            'module' => 'users'],
            ['slug' => 'users.create',     'name' => 'Crear usuarios',          'module' => 'users'],
            ['slug' => 'users.edit',       'name' => 'Editar usuarios',         'module' => 'users'],
            ['slug' => 'users.delete',     'name' => 'Eliminar usuarios',       'module' => 'users'],

            // System
            ['slug' => 'system.config',    'name' => 'Configuracion del sistema', 'module' => 'system'],
            ['slug' => 'system.automation','name' => 'Gestionar automatizaciones', 'module' => 'system'],

            // Finance
            ['slug' => 'finance.view',     'name' => 'Ver finanzas',            'module' => 'finance'],
            ['slug' => 'finance.manage',   'name' => 'Gestionar finanzas',      'module' => 'finance'],

            // CMS
            ['slug' => 'cms.manage',       'name' => 'Gestionar contenido',     'module' => 'cms'],

            // Marketing
            ['slug' => 'marketing.view',   'name' => 'Ver marketing',           'module' => 'marketing'],
            ['slug' => 'marketing.manage', 'name' => 'Gestionar marketing',     'module' => 'marketing'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // ─── Roles ─────────────────────────────────────────

        $roles = [
            ['slug' => 'super_admin',      'name' => 'Super Administrador', 'description' => 'Acceso total al sistema',                'is_system' => true],
            ['slug' => 'broker_senior',    'name' => 'Broker Senior',       'description' => 'Operativo completo, todos los leads',     'is_system' => true],
            ['slug' => 'broker_direccion', 'name' => 'Broker Direccion',    'description' => 'Vista completa, edicion limitada',        'is_system' => true],
            ['slug' => 'asesor',           'name' => 'Asesor',              'description' => 'Solo sus leads, pipeline propio',         'is_system' => true],
            ['slug' => 'user',             'name' => 'Usuario',             'description' => 'Acceso basico al dashboard',              'is_system' => true],
            ['slug' => 'client',           'name' => 'Cliente',             'description' => 'Acceso al portal de cliente',             'is_system' => true],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['slug' => $r['slug']], $r);
        }

        // ─── Permission assignments ────────────────────────

        $allPermissionIds = Permission::pluck('id');

        // Super Admin: ALL permissions
        $superAdmin = Role::where('slug', 'super_admin')->first();
        $superAdmin->permissions()->sync($allPermissionIds);

        // Broker Senior
        $brokerSenior = Role::where('slug', 'broker_senior')->first();
        $brokerSenior->permissions()->sync(
            Permission::whereIn('slug', [
                'leads.view', 'leads.view.own', 'leads.create', 'leads.edit', 'leads.delete', 'leads.assign',
                'pipeline.view', 'pipeline.move', 'pipeline.edit',
                'dashboard.view.basic', 'dashboard.view.advanced',
                'users.view',
                'finance.view',
                'marketing.view',
            ])->pluck('id')
        );

        // Broker Direccion
        $brokerDireccion = Role::where('slug', 'broker_direccion')->first();
        $brokerDireccion->permissions()->sync(
            Permission::whereIn('slug', [
                'leads.view', 'leads.view.own', 'leads.edit',
                'pipeline.view',
                'dashboard.view.basic', 'dashboard.view.advanced',
                'users.view',
                'finance.view',
                'marketing.view',
            ])->pluck('id')
        );

        // Asesor
        $asesor = Role::where('slug', 'asesor')->first();
        $asesor->permissions()->sync(
            Permission::whereIn('slug', [
                'leads.view.own', 'leads.create', 'leads.edit',
                'pipeline.view', 'pipeline.move',
                'dashboard.view.basic',
            ])->pluck('id')
        );

        // User
        $user = Role::where('slug', 'user')->first();
        $user->permissions()->sync(
            Permission::whereIn('slug', [
                'dashboard.view.basic',
            ])->pluck('id')
        );

        // Client: no CRM permissions (portal only)
        $client = Role::where('slug', 'client')->first();
        $client->permissions()->sync([]);

        // ─── Map existing users to new roles ───────────────

        $roleMap = [
            'admin'  => 'super_admin',
            'broker' => 'broker_senior',
            'editor' => 'broker_direccion',
            'viewer' => 'asesor',
            'user'   => 'user',
            'client' => 'client',
        ];

        foreach ($roleMap as $oldRole => $newRoleSlug) {
            $role = Role::where('slug', $newRoleSlug)->first();
            if (!$role) continue;

            $userIds = User::where('role', $oldRole)->pluck('id');
            foreach ($userIds as $userId) {
                DB::table('role_user')->insertOrIgnore([
                    'role_id' => $role->id,
                    'user_id' => $userId,
                ]);
            }
        }
    }
}
