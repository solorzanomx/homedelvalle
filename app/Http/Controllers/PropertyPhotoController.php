<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Http\Request;

class PropertyPhotoController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $request->validate([
            'photos' => 'required|array|max:20',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $currentCount = $property->photos()->count();
        $maxNew = 20 - $currentCount;

        if ($maxNew <= 0) {
            return back()->with('error', 'La propiedad ya tiene el maximo de 20 fotos.');
        }

        $files = array_slice($request->file('photos'), 0, $maxNew);
        $hasPrimary = $property->photos()->where('is_primary', true)->exists();
        $order = $property->photos()->max('sort_order') ?? 0;

        foreach ($files as $i => $file) {
            $path = $file->store('properties/photos', 'public');
            $order++;
            $property->photos()->create([
                'path' => $path,
                'is_primary' => !$hasPrimary && $i === 0,
                'sort_order' => $order,
            ]);
            if (!$hasPrimary && $i === 0) {
                $hasPrimary = true;
            }
        }

        return back()->with('success', count($files) . ' foto(s) subida(s).');
    }

    public function setPrimary(Property $property, PropertyPhoto $photo)
    {
        $property->photos()->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);

        // Also update the legacy photo field on the property
        $property->update(['photo' => $photo->path]);

        return back()->with('success', 'Imagen principal actualizada.');
    }

    public function destroy(Property $property, PropertyPhoto $photo)
    {
        $wasPrimary = $photo->is_primary;
        \Illuminate\Support\Facades\Storage::disk('public')->delete($photo->path);
        $photo->delete();

        if ($wasPrimary) {
            $next = $property->photos()->orderBy('sort_order')->first();
            if ($next) {
                $next->update(['is_primary' => true]);
                $property->update(['photo' => $next->path]);
            } else {
                $property->update(['photo' => null]);
            }
        }

        return back()->with('success', 'Foto eliminada.');
    }
}
