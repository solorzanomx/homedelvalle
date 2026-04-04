<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            if ($request->ajax()) {
                return response()->json(['error' => 'Maximo 20 fotos.'], 422);
            }
            return back()->with('error', 'La propiedad ya tiene el maximo de 20 fotos.');
        }

        $files = array_slice($request->file('photos'), 0, $maxNew);
        $hasPrimary = $property->photos()->where('is_primary', true)->exists();
        $order = $property->photos()->max('sort_order') ?? 0;
        $uploaded = [];

        foreach ($files as $i => $file) {
            $optimizer = new \App\Services\ImageOptimizer();
            $result = $optimizer->optimize($file, 'properties/photos');
            $path = $result['path'];
            $order++;
            $isPrimary = !$hasPrimary && $i === 0;
            $photo = $property->photos()->create([
                'path' => $path,
                'is_primary' => $isPrimary,
                'sort_order' => $order,
            ]);
            if ($isPrimary) {
                $hasPrimary = true;
                $property->update(['photo' => $path]);
            }
            $uploaded[] = [
                'id' => $photo->id,
                'url' => asset('storage/' . $path),
                'is_primary' => $photo->is_primary,
                'sort_order' => $photo->sort_order,
            ];
        }

        if ($request->ajax()) {
            $total = $property->photos()->count();
            return response()->json(['success' => true, 'photos' => $uploaded, 'total' => $total]);
        }

        return back()->with('success', count($files) . ' foto(s) subida(s).');
    }

    public function setPrimary(Request $request, Property $property, PropertyPhoto $photo)
    {
        $property->photos()->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);
        $property->update(['photo' => $photo->path]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Imagen principal actualizada.');
    }

    public function update(Request $request, Property $property, PropertyPhoto $photo)
    {
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
        ]);

        $photo->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Descripcion actualizada.');
    }

    public function reorder(Request $request, Property $property)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:property_photos,id',
        ]);

        foreach ($request->input('order') as $position => $photoId) {
            PropertyPhoto::where('id', $photoId)->where('property_id', $property->id)
                ->update(['sort_order' => $position + 1]);
        }

        // Update legacy photo field to match the primary or first photo
        $primary = $property->photos()->where('is_primary', true)->first()
            ?? $property->photos()->orderBy('sort_order')->first();
        if ($primary) {
            $property->update(['photo' => $primary->path]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Orden actualizado.');
    }

    public function destroy(Request $request, Property $property, PropertyPhoto $photo)
    {
        $wasPrimary = $photo->is_primary;
        Storage::disk('public')->delete($photo->path);
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

        if ($request->ajax()) {
            $total = $property->photos()->count();
            return response()->json(['success' => true, 'total' => $total]);
        }

        return back()->with('success', 'Foto eliminada.');
    }
}
