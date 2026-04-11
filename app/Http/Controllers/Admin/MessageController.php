<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $query = Message::with(['client', 'user'])->latest();

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('subject', 'like', "%{$s}%")
                  ->orWhere('body', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', "%{$s}%"));
            });
        }

        $messages = $query->paginate(25)->withQueryString();

        $stats = [
            'total' => Message::count(),
            'sent' => Message::where('status', 'sent')->count(),
            'opened' => Message::whereNotNull('opened_at')->count(),
            'failed' => Message::where('status', 'failed')->count(),
        ];

        return view('admin.marketing.messages', compact('messages', 'stats'));
    }
}
