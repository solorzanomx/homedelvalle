<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::latest();

        if ($request->filled('type') && $request->type === 'images') {
            $query->images();
        }

        if ($request->filled('folder')) {
            $query->inFolder($request->folder);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('filename', 'like', "%{$s}%")
                ->orWhere('title', 'like', "%{$s}%")
                ->orWhere('alt_text', 'like', "%{$s}%")
            );
        }

        $media = $query->paginate(30)->withQueryString();
        $folders = Media::whereNotNull('folder')->distinct()->pluck('folder')->sort()->values();

        return view('admin.media.index', compact('media', 'folders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array|max:20',
            'files.*' => 'required|file|max:10240',
            'folder' => 'nullable|string|max:100',
        ]);

        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store('media/' . (date('Y/m')), 'public');
            $mime = $file->getMimeType();

            $data = [
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $mime,
                'size' => $file->getSize(),
                'folder' => $request->folder,
            ];

            if (str_starts_with($mime, 'image/')) {
                $dimensions = @getimagesize($file->getRealPath());
                if ($dimensions) {
                    $data['width'] = $dimensions[0];
                    $data['height'] = $dimensions[1];
                }
            }

            $uploaded[] = Media::create($data);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'media' => collect($uploaded)->map(fn($m) => [
                    'id' => $m->id,
                    'url' => $m->url,
                    'filename' => $m->filename,
                    'mime_type' => $m->mime_type,
                    'human_size' => $m->human_size,
                    'is_image' => $m->is_image,
                ]),
            ]);
        }

        return back()->with('success', count($uploaded) . ' archivo(s) subido(s) correctamente.');
    }

    public function update(Request $request, Media $medium)
    {
        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'folder' => 'nullable|string|max:100',
        ]);

        $medium->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Archivo actualizado.');
    }

    public function destroy(Media $medium)
    {
        Storage::disk($medium->disk)->delete($medium->path);
        $medium->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Archivo eliminado.');
    }

    /**
     * JSON endpoint for the media browser modal (used by TinyMCE and other pickers).
     */
    public function browse(Request $request)
    {
        $query = Media::images()->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('filename', 'like', "%{$s}%")
                ->orWhere('title', 'like', "%{$s}%")
            );
        }

        $media = $query->paginate(40);

        return response()->json([
            'data' => $media->map(fn($m) => [
                'id' => $m->id,
                'url' => $m->url,
                'filename' => $m->filename,
                'alt_text' => $m->alt_text,
                'title' => $m->title,
                'human_size' => $m->human_size,
                'width' => $m->width,
                'height' => $m->height,
            ]),
            'next_page_url' => $media->nextPageUrl(),
        ]);
    }
}
