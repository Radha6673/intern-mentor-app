<?php

namespace App\Providers;

use App\Mail\BrevoApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        if (app()->environment('production') || str_contains((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        config([
            'mail.mailers.brevo-api' => [
                'transport' => 'brevo-api',
            ],
        ]);

        Mail::extend('brevo-api', function (array $config = []) {
            return new BrevoApiTransport(
                apiKey: (string) env('BREVO_API_KEY', env('MAIL_PASSWORD'))
            );
        });
    }
}
