<?php

namespace App\Http\Controllers;

use App\Models\GoogleSignatureRequest;
use Illuminate\View\View;

class ContratoPublicoController extends Controller
{
    public function show(string $token): View
    {
        $request = GoogleSignatureRequest::where('token', $token)->firstOrFail();

        return view('public.firma.show', compact('request'));
    }
}
