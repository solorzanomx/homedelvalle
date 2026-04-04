<?php

use App\Models\User;
use App\Models\SiteSetting;

// Admin
if (!User::where('email', 'admin@crm.com')->exists()) {
    User::create([
        'name' => 'Admin',
        'last_name' => 'Sistema',
        'email' => 'admin@crm.com',
        'password' => 'Admin123!',
        'role' => 'admin',
        'can_read' => true,
        'can_edit' => true,
        'can_delete' => true,
    ]);
    echo "Admin creado\n";
} else {
    User::where('email', 'admin@crm.com')->update([
        'role' => 'admin', 'can_read' => true, 'can_edit' => true, 'can_delete' => true,
    ]);
    echo "Admin actualizado\n";
}

// Editor
if (!User::where('email', 'editor@crm.com')->exists()) {
    User::create([
        'name' => 'Carlos',
        'last_name' => 'Mendez',
        'email' => 'editor@crm.com',
        'password' => 'Editor123!',
        'role' => 'editor',
        'can_read' => true,
        'can_edit' => true,
        'can_delete' => false,
    ]);
    echo "Editor creado\n";
}

// Viewer
if (!User::where('email', 'viewer@crm.com')->exists()) {
    User::create([
        'name' => 'Maria',
        'last_name' => 'Lopez',
        'email' => 'viewer@crm.com',
        'password' => 'Viewer123!',
        'role' => 'viewer',
        'can_read' => true,
        'can_edit' => false,
        'can_delete' => false,
    ]);
    echo "Viewer creado\n";
}

// Site Settings
if (!SiteSetting::first()) {
    SiteSetting::create([
        'site_name' => 'Homedelvalle',
        'site_tagline' => 'Tu plataforma inmobiliaria',
        'primary_color' => '#667eea',
        'secondary_color' => '#764ba2',
        'home_welcome_text' => 'Bienvenido a Homedelvalle',
    ]);
    echo "Settings creados\n";
} else {
    echo "Settings ya existen\n";
}

// Listar usuarios
echo "\n=== USUARIOS ===\n";
$users = User::all(['id', 'name', 'last_name', 'email', 'role']);
foreach ($users as $u) {
    echo "{$u->id} | {$u->name} {$u->last_name} | {$u->email} | {$u->role}\n";
}
