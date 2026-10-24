<?php

namespace App\Http\Controllers;

use App\Models\AccountRecoveryPayment;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountSuspensionController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAccountBanned()) {
            return redirect()->route('newsfeed');
        }

        return view('account.suspended', [
            'user' => $user,
            'recoveryFee' => config('services.nex_recovery.fee_php'),
            'paypalConfigured' => filled(config('services.paypal.client_id')) && filled(config('services.paypal.client_secret')),
            'paymongoConfigured' => filled(config('services.paymongo.secret_key')) && filled(config('services.paymongo.webhook_secret')),
        ]);
    }

    public function checkout(Request $request, string $provider): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAccountBanned(), 404);
        abort_if($user->is_permanently_banned, 403, 'Permanent suspensions must be appealed to NEX support.');
        abort_unless(in_array($provider, ['paypal', 'gcash'], true), 404);

        $fee = config('services.nex_recovery.fee_php');
        if (!is_numeric($fee) || (float) $fee <= 0) {
            return back()->with('checkout_error', 'The NEX account recovery fee has not been configured yet.');
        }

        if ($provider === 'paypal' && (!filled(config('services.paypal.client_id')) || !filled(config('services.paypal.client_secret')))) {
            return back()->with('checkout_error', 'PayPal checkout is not configured yet.');
        }
        if ($provider === 'gcash' && (!filled(config('services.paymongo.secret_key')) || !filled(config('services.paymongo.webhook_secret')))) {
            return back()->with('checkout_error', 'GCash checkout is not configured yet.');
        }

        $amountMinor = (int) round((float) $fee * 100);
        $reference = 'NEX-' . Str::upper(Str::random(22));
        $payment = AccountRecoveryPayment::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'reference' => $reference,
            'amount_minor' => $amountMinor,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        try {
            return $provider === 'paypal'
                ? $this->startPaypalCheckout($payment)
                : $this->startGcashCheckout($payment);
        } catch (ConnectionException $exception) {
            report($exception);
            $payment->update(['status' => 'failed']);

            return back()->with('checkout_error', 'The payment service could not be reached. Please try again later.');
        }
    }

    public function paypalReturn(Request $request): RedirectResponse
    {
        $orderId = $request->query('token');
        abort_unless(is_string($orderId) && $orderId !== '', 404);

        $payment = AccountRecoveryPayment::where('provider', 'paypal')
            ->where('provider_payment_id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        if ($payment->status === 'paid') {
            return redirect()->route('newsfeed')->with('success', 'Your payment was already confirmed and your account is restored.');
        }
        abort_unless($payment->status === 'pending', 404);

        try {
            $response = $this->paypalApi('post', '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture', []);
        } catch (ConnectionException $exception) {
            report($exception);
            return redirect()->route('account.suspended')->with('checkout_error', 'PayPal could not confirm the payment. Please try again.');
        }

        if (!$response->successful()) {
            return redirect()->route('account.suspended')->with('checkout_error', 'PayPal did not confirm payment. Your account remains suspended.');
        }

        $data = $response->json();
        $unit = $data['purchase_units'][0] ?? [];
        $capture = $unit['payments']['captures'][0] ?? [];
        $paidMinor = (int) round(((float) ($capture['amount']['value'] ?? 0)) * 100);
        $valid = ($data['status'] ?? null) === 'COMPLETED'
            && ($unit['custom_id'] ?? null) === $payment->reference
            && ($capture['status'] ?? null) === 'COMPLETED'
            && strtoupper($capture['amount']['currency_code'] ?? '') === $payment->currency
            && $paidMinor === (int) $payment->amount_minor;

        if (!$valid) {
            return redirect()->route('account.suspended')->with('checkout_error', 'Payment could not be verified for this account. Contact NEX support.');
        }

        $this->restoreAfterPayment($payment, $capture['id'] ?? $orderId);

        return redirect()->route('newsfeed')->with('success', 'Payment confirmed. Your NEX account has been restored.');
    }

    public function paymongoWebhook(Request $request): \Illuminate\Http\Response
    {
        $rawBody = $request->getContent();
        $header = (string) $request->header('Paymongo-Signature', '');
        $parts = [];
        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? '';
        $mode = config('services.paymongo.secret_key');
        $signature = str_starts_with((string) $mode, 'sk_live_') ? ($parts['li'] ?? '') : ($parts['te'] ?? '');
        $secret = (string) config('services.paymongo.webhook_secret');
        abort_unless($timestamp !== '' && abs(time() - (int) $timestamp) <= 300 && $secret !== '' && $signature !== '', 401);

        $expected = hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);
        abort_unless(hash_equals($expected, $signature), 401);

        $event = json_decode($rawBody, true);
        $attributes = $event['data']['attributes'] ?? [];
        if (($attributes['type'] ?? null) !== 'checkout_session.payment.paid') {
            return response()->json(['received' => true]);
        }

        $session = $attributes['data'] ?? [];
        $sessionAttributes = $session['attributes'] ?? [];
        $reference = $sessionAttributes['reference_number'] ?? null;
        $payment = is_string($reference)
            ? AccountRecoveryPayment::where('provider', 'gcash')->where('reference', $reference)->first()
            : null;

        if (!$payment || $payment->status === 'paid') {
            return response()->json(['received' => true]);
        }

        $paidPayment = collect($sessionAttributes['payments'] ?? [])->first(fn ($item) => ($item['attributes']['status'] ?? null) === 'paid');
        $paidAttributes = $paidPayment['attributes'] ?? [];
        $valid = (int) ($paidAttributes['amount'] ?? 0) === (int) $payment->amount_minor
            && strtoupper($paidAttributes['currency'] ?? '') === $payment->currency
            && ($attributes['livemode'] ?? false) === str_starts_with((string) config('services.paymongo.secret_key'), 'sk_live_');

        if ($valid) {
            $this->restoreAfterPayment($payment, $paidPayment['id'] ?? $session['id'] ?? null);
        }

        return response()->json(['received' => true]);
    }

    private function startPaypalCheckout(AccountRecoveryPayment $payment): RedirectResponse
    {
        $response = $this->paypalApi('post', '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $payment->reference,
                'custom_id' => $payment->reference,
                'description' => 'NEX account recovery',
                'amount' => [
                    'currency_code' => 'PHP',
                    'value' => number_format($payment->amount_minor / 100, 2, '.', ''),
                ],
            ]],
            'application_context' => [
                'brand_name' => 'NEX',
                'user_action' => 'PAY_NOW',
                'return_url' => route('account.suspended.paypal.return'),
                'cancel_url' => route('account.suspended.paypal.cancel'),
            ],
        ]);

        if (!$response->successful()) {
            $payment->update(['status' => 'failed']);
            return back()->with('checkout_error', 'PayPal could not start checkout. Please try again later.');
        }

        $order = $response->json();
        $approveUrl = data_get(collect($order['links'] ?? [])->firstWhere('rel', 'approve'), 'href');
        if (!is_string($approveUrl) || !str_starts_with($approveUrl, 'https://')) {
            $payment->update(['status' => 'failed']);
            return back()->with('checkout_error', 'PayPal did not return a valid checkout link.');
        }
        $payment->update(['provider_payment_id' => $order['id'] ?? null]);

        return redirect()->away($approveUrl);
    }

    private function startGcashCheckout(AccountRecoveryPayment $payment): RedirectResponse
    {
        $response = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
            ->acceptJson()->asJson()->timeout(20)
            ->post('https://api.paymongo.com/v2/checkout_sessions', [
                'data' => ['attributes' => [
                    'line_items' => [[
                        'name' => 'NEX account recovery',
                        'amount' => $payment->amount_minor,
                        'currency' => 'PHP',
                        'quantity' => 1,
                        'description' => 'Account recovery fee for NEX account ' . $payment->user_id,
                    ]],
                    'payment_method_types' => ['gcash'],
                    'success_url' => route('account.suspended') . '?payment=pending',
                    'cancel_url' => route('account.suspended') . '?payment=cancelled',
                    'reference_number' => $payment->reference,
                    'description' => 'NEX account recovery',
                    'send_email_receipt' => true,
                ]],
            ]);

        if (!$response->successful()) {
            $payment->update(['status' => 'failed']);
            return back()->with('checkout_error', 'GCash checkout could not be started. Please try again later.');
        }

        $session = $response->json('data');
        $checkoutUrl = $session['attributes']['checkout_url'] ?? null;
        if (!is_string($checkoutUrl) || !str_starts_with($checkoutUrl, 'https://')) {
            $payment->update(['status' => 'failed']);
            return back()->with('checkout_error', 'GCash did not return a valid checkout link.');
        }
        $payment->update(['provider_payment_id' => $session['id'] ?? null]);

        return redirect()->away($checkoutUrl);
    }

    private function paypalApi(string $method, string $path, array $payload)
    {
        $baseUrl = config('services.paypal.sandbox')
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
        $tokenResponse = Http::asForm()->withBasicAuth(
            config('services.paypal.client_id'),
            config('services.paypal.client_secret'),
        )->timeout(20)->post($baseUrl . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        if (!$tokenResponse->successful() || !filled($tokenResponse->json('access_token'))) {
            throw new ConnectionException('PayPal authentication failed.');
        }

        return Http::withToken($tokenResponse->json('access_token'))
            ->acceptJson()->asJson()->timeout(20)
            ->send(strtoupper($method), $baseUrl . $path, ['json' => $payload]);
    }

    private function restoreAfterPayment(AccountRecoveryPayment $payment, ?string $providerPaymentId): void
    {
        DB::transaction(function () use ($payment, $providerPaymentId) {
            $lockedPayment = AccountRecoveryPayment::whereKey($payment->id)->lockForUpdate()->first();
            if (!$lockedPayment || $lockedPayment->status === 'paid') {
                return;
            }

            $user = User::whereKey($lockedPayment->user_id)->lockForUpdate()->first();
            if (!$user || !$user->isAccountBanned()) {
                $lockedPayment->update(['status' => 'paid', 'provider_payment_id' => $providerPaymentId, 'paid_at' => now()]);
                return;
            }

            $violation = $user->ban_violation;
            $lockedPayment->update(['status' => 'paid', 'provider_payment_id' => $providerPaymentId, 'paid_at' => now()]);
            $user->forceFill([
                'is_permanently_banned' => false,
                'banned_until' => null,
                'ban_violation' => null,
                'ban_reason' => null,
            ])->save();
            $user->moderationActions()->create([
                'action' => 'payment_recovery',
                'violation' => $violation,
                'reason' => 'Account restored after verified ' . strtoupper($lockedPayment->provider) . ' recovery payment (' . $lockedPayment->reference . ').',
            ]);
        });
    }
}
