<?php

namespace App\Livewire;

use App\Mail\V4\Mailables\PropertyInquiryMail;
use App\Models\ContactSubmission;
use App\Services\SpamProtectionService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class PropertyInquiryForm extends Component
{
    public $propertyId = null;
    public $propertyTitle = 'Esta propiedad';
    public $propertyImage = null;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $message = '';
    public $accept_privacy = false;
    public $recaptcha_token = null;

    public $submitted = false;
    public $error = null;

    public function mount($propertyId = null, $propertyTitle = 'Esta propiedad', $propertyImage = null)
    {
        $this->propertyId = $propertyId;
        $this->propertyTitle = $propertyTitle;
        $this->propertyImage = $propertyImage;
    }

    public function submit(SpamProtectionService $spam)
    {
        $this->validate([
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:10|max:20',
            'accept_privacy' => 'required|accepted',
        ]);

        // Honeypot check
        if ($this->filled('website_url')) {
            $this->submitted = true;
            return;
        }

        // Spam protection
        $spamCheck = $spam->check(
            [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'message' => $this->message,
            ],
            $this->recaptcha_token,
            request()->ip(),
            'property_inquiry'
        );

        if (! $spamCheck['pass']) {
            $this->error = 'Por favor intenta de nuevo. Parece que hay un problema con tu solicitud.';
            return;
        }

        try {
            // Store submission
            ContactSubmission::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'message' => $this->message ?? "Interesado en: {$this->propertyTitle}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'utm_source' => request()->input('utm_source'),
                'utm_medium' => request()->input('utm_medium'),
                'utm_campaign' => request()->input('utm_campaign'),
            ]);

            // Send confirmation email to user
            Mail::to($this->email)->send(new PropertyInquiryMail(
                $this->name,
                $this->propertyTitle,
                $this->email,
                $this->phone
            ));

            $this->submitted = true;
            $this->reset(['name', 'email', 'phone', 'message', 'accept_privacy']);
        } catch (\Exception $e) {
            $this->error = 'Hubo un error al enviar tu solicitud. Por favor intenta nuevamente.';
            \Log::error('PropertyInquiryForm submission error', [
                'error' => $e->getMessage(),
                'email' => $this->email,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.property-inquiry-form');
    }
}
