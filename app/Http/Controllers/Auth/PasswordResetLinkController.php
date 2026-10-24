<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'We could not send the reset email right now. Please try again later.']);
        }

        $successMessage = config('mail.default') === 'log'
            ? 'Reset link generated. This app uses local log mail; check storage/logs/laravel.log for the link.'
            : 'If an account exists for that email, a password reset link will be sent shortly.';

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', $successMessage)
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
