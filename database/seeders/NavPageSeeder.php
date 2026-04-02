<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class NavPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Inicio',
                'slug' => 'inicio',
                'body' => '',
                'is_published' => true,
                'show_in_nav' => true,
                'nav_order' => 1,
                'nav_label' => 'Inicio',
                'nav_route' => 'home',
                'nav_style' => 'link',
            ],
            [
                'title' => 'Propiedades',
                'slug' => 'propiedades',
                'body' => '',
                'is_published' => true,
                'show_in_nav' => true,
                'nav_order' => 2,
                'nav_label' => 'Propiedades',
                'nav_route' => 'propiedades.index',
                'nav_style' => 'link',
            ],
            [
                'title' => 'Nosotros',
                'slug' => 'nosotros',
                'body' => '',
                'is_published' => true,
                'show_in_nav' => true,
                'nav_order' => 3,
                'nav_label' => 'Nosotros',
                'nav_route' => 'nosotros',
                'nav_style' => 'link',
            ],
            [
                'title' => 'Blog',
                'slug' => 'blog',
                'body' => '',
                'is_published' => true,
                'show_in_nav' => true,
                'nav_order' => 4,
                'nav_label' => 'Blog',
                'nav_route' => 'blog.index',
                'nav_style' => 'link',
            ],
            [
                'title' => 'Contacto',
                'slug' => 'contacto',
                'body' => '',
                'is_published' => true,
                'show_in_nav' => true,
                'nav_order' => 5,
                'nav_label' => 'Contacto',
                'nav_route' => 'contacto',
                'nav_style' => 'button',
            ],
            [
                'title' => 'Office',
                'slug' => 'office',
                'body' => '',
                'is_published' => true,
                'show_in_nav' => true,
                'nav_order' => 6,
                'nav_label' => 'Office',
                'nav_route' => 'admin.dashboard',
                'nav_style' => 'muted',
            ],
        ];

        foreach ($pages as $data) {
            Page::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        cache()->forget('nav_items');
    }
}
