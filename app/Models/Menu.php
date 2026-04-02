<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'location'])]
class Menu extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public static function forLocation(string $location): ?self
    {
        $data = cache()->remember("menu_{$location}", 300, function () use ($location) {
            $menu = static::where('location', $location)->first();
            if (!$menu) return null;

            $menu->setRelation('items',
                $menu->items()
                    ->where('is_active', true)
                    ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'), 'page'])
                    ->get()
            );

            return $menu->toArray();
        });

        if (!$data) return null;

        $itemsData = $data['items'] ?? [];
        unset($data['items']);

        $menu = (new static)->forceFill($data);
        $menu->exists = true;

        if (!empty($itemsData)) {
            $items = collect($itemsData)->map(function ($item) {
                $children = $item['children'] ?? [];
                $pageData = $item['page'] ?? null;
                unset($item['children'], $item['page']);

                $mi = (new MenuItem)->forceFill($item);
                $mi->exists = true;
                if (!empty($children)) {
                    $mi->setRelation('children', collect($children)->map(function ($c) {
                        unset($c['children'], $c['page']);
                        $child = (new MenuItem)->forceFill($c);
                        $child->exists = true;
                        return $child;
                    }));
                } else {
                    $mi->setRelation('children', collect());
                }
                if ($pageData) {
                    $mi->setRelation('page', (new Page)->forceFill($pageData));
                }
                return $mi;
            });
            $menu->setRelation('items', $items);
        } else {
            $menu->setRelation('items', collect());
        }

        return $menu;
    }

    public static function clearCache(): void
    {
        cache()->forget('menu_header');
        cache()->forget('menu_footer');
    }
}
