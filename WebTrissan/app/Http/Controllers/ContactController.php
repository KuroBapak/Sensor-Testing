<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use App\Models\GlobalSetting;
use App\Models\PageSetting;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Store a new contact submission.
     *
     * When a WhatsApp number is set in the CMS, the visitor is then sent to WhatsApp with the message
     * prefilled, ready to send. Without one, they stay on the page with the CMS success message.
     *
     * The hidden `website` field is a honeypot: bots fill it, people do not. Such requests
     * get the normal success response but are not stored.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
            'service_id' => 'nullable|exists:services,id',
            'website' => 'nullable|string',
        ]);

        if (filled($validated['website'] ?? null)) {
            return back()->with('success', $this->successMessage());
        }

        ContactSubmission::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'],
            'service_id' => $validated['service_id'] ?? null,
            'status' => 'new',
        ]);

        $whatsappUrl = $this->whatsappUrl($validated);

        if ($whatsappUrl !== null) {
            return redirect()->away($whatsappUrl);
        }

        return back()->with('success', $this->successMessage());
    }

    /**
     * Build the wa.me link with the submission as prefilled text, or null when no WhatsApp number is configured.
     *
     * @param  array<string, mixed>  $validated
     */
    private function whatsappUrl(array $validated): ?string
    {
        $number = GlobalSetting::current()->whatsappNumber();

        if ($number === null) {
            return null;
        }

        $service = filled($validated['service_id'] ?? null) ? Service::find($validated['service_id']) : null;

        $lines = array_filter([
            'Halo, saya '.$validated['name'].'.',
            $service ? 'Layanan yang diminati: '.$service->service_title : null,
            'Email: '.$validated['email'],
            filled($validated['phone'] ?? null) ? 'Telepon: '.$validated['phone'] : null,
            '',
            $validated['message'],
        ], fn (?string $line): bool => $line !== null);

        return 'https://wa.me/'.$number.'?text='.rawurlencode(implode("\n", $lines));
    }

    /**
     * The confirmation text is managed in the CMS (Landing page settings). It may be blank,
     * in which case the form is only reset without a message.
     */
    private function successMessage(): string
    {
        return PageSetting::forPage('landing')?->extra('contact_success_message') ?? '';
    }
}
