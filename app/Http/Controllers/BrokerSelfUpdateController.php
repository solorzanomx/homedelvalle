<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * A diferencia de BrokerVerificationController (que verifica leads nuevos
 * antes de decidir si entran a la red), este controlador deja que un broker
 * YA activo en Brokers Externos actualice su propia ficha — sin aprobación,
 * se guarda directo. Sirve para medir quién mantiene sus datos al día.
 */
class BrokerSelfUpdateController extends Controller
{
    public function show(string $token)
    {
        $broker = Broker::where('verification_token', $token)->firstOrFail();

        if ($broker->verification_completed_at) {
            return view('broker-verificacion.already-completed', ['lead' => $broker]);
        }

        $prefill = [
            'name'                => $broker->name,
            'has_company'         => $broker->company_name ? '1' : '0',
            'company_name'        => $broker->company_name,
            'interest_zones'      => implode(', ', $broker->interest_zones ?? []),
            'phone'               => $broker->phone,
            'license_number'      => $broker->license_number,
            'website'             => $broker->website,
            'operations_per_year' => $broker->operations_per_year,
            'birth_day'           => $broker->birth_date?->day,
            'birth_month'         => $broker->birth_date?->month,
        ];

        $submitRoute = route('broker-self-update.submit', $token);

        return view('broker-verificacion.show', compact('token', 'prefill', 'submitRoute'));
    }

    public function submit(Request $request, string $token)
    {
        $broker = Broker::where('verification_token', $token)->firstOrFail();

        if ($broker->verification_completed_at) {
            return view('broker-verificacion.already-completed', ['lead' => $broker]);
        }

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'has_company'         => 'required|in:0,1',
            'company_name'        => 'nullable|string|max:255',
            'interest_zones'      => 'nullable|string|max:500',
            'license_number'      => 'nullable|string|max:100',
            'phone'               => 'nullable|string|max:20',
            'website'             => 'nullable|url|max:255',
            'operations_per_year' => 'nullable|in:1-5,6-15,15+',
            'birth_day'           => 'nullable|integer|min:1|max:31',
            'birth_month'         => 'nullable|integer|min:1|max:12',
        ]);

        $birthDate = $broker->birth_date;
        if (!empty($validated['birth_day']) && !empty($validated['birth_month'])) {
            try {
                $birthDate = \Carbon\Carbon::createSafe(2000, (int) $validated['birth_month'], (int) $validated['birth_day']);
            } catch (\Throwable) {
                // Combinación imposible (ej. 30 de febrero) — se ignora y se conserva lo que ya tenía.
            }
        }

        $broker->update([
            'name'                       => $validated['name'],
            'company_name'               => $validated['has_company'] === '1' ? ($validated['company_name'] ?? null) : null,
            'interest_zones'             => !empty($validated['interest_zones'])
                ? array_values(array_filter(array_map('trim', explode(',', $validated['interest_zones']))))
                : [],
            'license_number'             => $validated['license_number'] ?? $broker->license_number,
            'phone'                      => $validated['phone'] ?? $broker->phone,
            'website'                    => $validated['website'] ?? null,
            'operations_per_year'        => $validated['operations_per_year'] ?? null,
            'birth_date'                 => $birthDate,
            'verification_completed_at'  => now(),
        ]);

        $adminId = User::where('role', 'admin')->value('id');
        if ($adminId) {
            Notification::create([
                'user_id' => $adminId,
                'type'    => 'system',
                'title'   => 'Broker actualizó sus datos',
                'body'    => "{$broker->name} confirmó/actualizó su ficha en Brokers Externos.",
                'data'    => ['url' => route('brokers.show', $broker), 'broker_id' => $broker->id],
            ]);
        }

        return view('broker-verificacion.thank-you');
    }
}
