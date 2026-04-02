<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmailAssetController extends Controller
{
    public function index()
    {
        $assets = EmailAsset::orderBy('created_at', 'desc')->get();
        return view('admin.email.assets', compact('assets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
            'name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $path = $file->store('email-assets', 'public');
        $name = $request->input('name') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $asset = EmailAsset::create([
            'name' => $name,
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'asset' => [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'url' => $asset->url,
                    'human_size' => $asset->human_size,
                ],
            ]);
        }

        return back()->with('success', 'Imagen subida correctamente.');
    }

    public function destroy(EmailAsset $asset)
    {
        Storage::disk('public')->delete($asset->path);
        $asset->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Imagen eliminada correctamente.');
    }

    /**
     * Returns JSON list of all assets for the gallery modal in the editor.
     */
    public function gallery()
    {
        $assets = EmailAsset::orderBy('created_at', 'desc')->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'name' => $a->name,
                'url' => $a->url,
                'human_size' => $a->human_size,
                'created' => $a->created_at->format('d/m/Y'),
            ];
        });

        return response()->json($assets);
    }
}
