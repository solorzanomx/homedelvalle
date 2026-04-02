<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::withCount('allItems')->get();

        return view('admin.menus.index', compact('menus'));
    }

    public function edit(Menu $menu)
    {
        $items = $menu->allItems()->with('page')->get();
        $pages = Page::published()->orderBy('title')->get(['id', 'title', 'slug']);

        $itemsJson = $items->map(function ($i) {
            return [
                'label' => $i->label,
                'type' => $i->type,
                'page_id' => $i->page_id,
                'url' => $i->url,
                'route_name' => $i->route_name,
                'target' => $i->target,
                'style' => $i->style,
                'is_active' => $i->is_active,
                'parent_id' => $i->parent_id,
                'children' => [],
            ];
        });

        return view('admin.menus.edit', compact('menu', 'items', 'pages', 'itemsJson'));
    }

    public function updateItems(Request $request, Menu $menu)
    {
        $request->validate([
            'items_json' => 'required|string',
        ]);

        $newItems = json_decode($request->items_json, true);

        if (!is_array($newItems)) {
            return back()->with('error', 'Datos invalidos.');
        }

        // Delete existing items and recreate
        $menu->allItems()->delete();

        $this->saveItems($menu, $newItems, null);

        Menu::clearCache();

        return back()->with('success', 'Menu actualizado correctamente.');
    }

    private function saveItems(Menu $menu, array $items, ?int $parentId): void
    {
        foreach ($items as $index => $item) {
            $menuItem = MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => $parentId,
                'label' => $item['label'] ?? 'Sin titulo',
                'type' => $item['type'] ?? 'url',
                'page_id' => ($item['type'] ?? '') === 'page' ? ($item['page_id'] ?? null) : null,
                'url' => $item['url'] ?? null,
                'route_name' => $item['route_name'] ?? null,
                'target' => $item['target'] ?? '_self',
                'style' => $item['style'] ?? 'link',
                'sort_order' => $index,
                'is_active' => $item['is_active'] ?? true,
            ]);

            if (!empty($item['children'])) {
                $this->saveItems($menu, $item['children'], $menuItem->id);
            }
        }
    }
}
