<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>Account suspended · NEX</title>
    @vite(['resources/css/nex-suspended.css'])
</head>
<body class="nex-suspended-body">
    <main class="nex-suspended-shell">
        <a class="nex-suspended-brand" href="{{ url('/') }}"><span>N</span>NEX</a>

        <section class="nex-suspended-card" aria-labelledby="suspended-title">
            <div class="nex-suspended-icon" aria-hidden="true">!</div>
            <span class="nex-suspended-eyebrow">ACCOUNT STATUS</span>
            <h1 id="suspended-title">Your account is {{ $user->is_permanently_banned ? 'permanently suspended' : 'temporarily suspended' }}</h1>
            <p class="nex-suspended-intro">Hi {{ $user->name }}, your account access is currently restricted following a NEX community review.</p>

            <div class="nex-suspended-details">
                <div class="nex-suspended-detail-row">
                    <span>Violation</span>
                    <strong>{{ str($user->ban_violation ?: 'community guidelines')->replace('_', ' ')->title() }}</strong>
                </div>
                @if($user->is_permanently_banned)
                    <div class="nex-suspended-detail-row">
                        <span>Duration</span><strong>Permanent</strong>
                    </div>
                @else
                    <div class="nex-suspended-detail-row">
                        <span>Time remaining</span><strong>{{ $user->banned_until->diffForHumans(now(), true) }}</strong>
                    </div>
                    <div class="nex-suspended-detail-row">
                        <span>Access returns</span><strong>{{ $user->banned_until->timezone(config('app.timezone'))->format('M j, Y · g:i A') }}</strong>
                    </div>
                @endif
                <div class="nex-suspended-message">
                    <span>MESSAGE FROM NEX</span>
                    <p>{{ $user->ban_reason ?: 'No additional message was provided.' }}</p>
                </div>
            </div>

            @if(session('checkout_error'))
                <div class="nex-suspended-alert" role="alert">{{ session('checkout_error') }}</div>
            @endif

            @if(!$user->is_permanently_banned)
                <div class="nex-suspended-payments">
                    <form method="POST" action="{{ route('account.suspended.checkout', 'paypal') }}">
                        @csrf
                        <button class="nex-suspended-pay" type="submit" @disabled(!$paypalConfigured || !$recoveryFee)><span>Continue with PayPal</span><span aria-hidden="true">→</span></button>
                    </form>
                    <form method="POST" action="{{ route('account.suspended.checkout', 'gcash') }}">
                        @csrf
                        <button class="nex-suspended-pay nex-suspended-pay-secondary" type="submit" @disabled(!$paymongoConfigured || !$recoveryFee)><span>Pay with GCash</span><span aria-hidden="true">→</span></button>
                    </form>
                </div>
                @if($recoveryFee)
                    <p class="nex-suspended-fee">Recovery fee: ₱{{ number_format((float) $recoveryFee, 2) }} PHP</p>
                @else
                    <p class="nex-suspended-fee">Recovery fee will appear here once configured.</p>
                @endif
                <p class="nex-suspended-hint">Your account access will be restored only after NEX verifies payment. @if(request('payment') === 'pending') The GCash payment is being confirmed; refresh this page in a moment. @endif</p>
                @if(!$paypalConfigured || !$paymongoConfigured || !$recoveryFee)
                    <p class="nex-suspended-hint">Each payment option becomes available after a recovery fee and credentials for that provider are configured in the server environment.</p>
                @endif
            @else
                <p class="nex-suspended-hint">This permanent suspension cannot be recovered with a payment. Contact NEX support to appeal this decision.</p>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="nex-suspended-logout">
                @csrf
                <button type="submit">Sign out</button>
            </form>
        </section>
        <p class="nex-suspended-footer">Need help? Contact NEX support about this decision.</p>
    </main>
</body>
</html>
