<?php

namespace App\Http\Controllers;

use App\Models\ValuationLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ValuationLeadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'owner_name'   => 'required|string|max:120',
            'owner_phone'  => 'required|string|max:20',
            'owner_email'  => 'nullable|email|max:150',
            'colonia_id'   => 'nullable|exists:market_colonias,id',
            'colonia_raw'  => 'nullable|string|max:150',
            'property_type'=> 'required|in:apartment,house,land,office',
            'm2_approx'    => 'nullable|numeric|min:10|max:5000',
            'message'      => 'nullable|string|max:1000',
        ]);

        ValuationLead::create(array_merge($data, [
            'source_page'  => $request->headers->get('referer'),
            'utm_source'   => $request->query('utm_source'),
            'utm_medium'   => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'status'       => 'new',
        ]));

        return redirect()
            ->route('mercado.opinion')
            ->with('lead_success', true);
    }
}
