<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class BrokerVerificationController extends Controller
{
    public function show(string $token)
    {
        $lead = FormSubmission::where('payload->broker_verification_token', $token)->firstOrFail();

        if (!empty($lead->payload['broker_verification_completed_at'])) {
            return view('broker-verificacion.already-completed', compact('lead'));
        }

        return view('broker-verificacion.show', compact('lead', 'token'));
    }

    public function submit(Request $request, string $token)
    {
        $lead = FormSubmission::where('payload->broker_verification_token', $token)->firstOrFail();

        if (!empty($lead->payload['broker_verification_completed_at'])) {
            return view('broker-verificacion.already-completed', compact('lead'));
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

        // Año fijo (2000, bisiesto) porque solo nos interesan día y mes — la
        // automatización de cumpleaños (SendBirthdayGreetings) compara con
        // whereMonth()/whereDay() y nunca lee el año.
        $birthDate = null;
        if (!empty($validated['birth_day']) && !empty($validated['birth_month'])) {
            try {
                $birthDate = \Carbon\Carbon::createSafe(2000, $validated['birth_month'], $validated['birth_day'])?->toDateString();
            } catch (\Throwable) {
                $birthDate = null;
            }
        }

        $payload = $lead->payload ?? [];
        $payload['broker_verification_completed_at'] = now()->toDateTimeString();
        $payload['broker_verification_data'] = [
            'name'                => $validated['name'],
            'company_name'        => $validated['has_company'] === '1' ? ($validated['company_name'] ?? null) : null,
            'interest_zones'      => $validated['interest_zones']
                ? array_values(array_filter(array_map('trim', explode(',', $validated['interest_zones']))))
                : [],
            'license_number'      => $validated['license_number'] ?? null,
            'phone'               => $validated['phone'] ?? $lead->phone,
            'website'             => $validated['website'] ?? null,
            'operations_per_year' => $validated['operations_per_year'] ?? null,
            'birth_date'          => $birthDate,
        ];

        $lead->update(['payload' => $payload]);

        $adminId = User::where('role', 'admin')->value('id');
        if ($adminId) {
            Notification::create([
                'user_id' => $adminId,
                'type'    => 'system',
                'title'   => 'Broker verificado — pendiente de aprobar',
                'body'    => "{$validated['name']} completó su verificación. Revisa sus datos y decide si entra a tu red de brokers.",
                'data'    => ['url' => route('admin.form-submissions.show', $lead), 'form_submission_id' => $lead->id],
            ]);
        }

        return view('broker-verificacion.thank-you');
    }
}
