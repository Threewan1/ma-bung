<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ganti tampilan email verifikasi bawaan jadi tema dark+gold, sama seperti email transaksional lain.
        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            return (new MailMessage)
                ->subject("Verifikasi Alamat Email - Ma'bung Barbershop")
                ->view('emails.verify-email', [
                    'user' => $notifiable,
                    'url' => $url,
                ]);
        });

        // Ganti tampilan email reset password bawaan jadi tema dark+gold, URL dibangun sama seperti resetUrl() bawaan Laravel.
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject("Reset Password - Ma'bung Barbershop")
                ->view('emails.reset-password', [
                    'user' => $notifiable,
                    'url' => $url,
                ]);
        });
    }
}
