<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (! trait_exists(\Laravel\Sanctum\HasApiTokens::class)) {
            require_once app_path('Support/sanctum-polyfill.php');
        }

        if (! class_exists(\Maatwebsite\Excel\Excel::class)) {
            require_once app_path('Support/excel-polyfill.php');
        }

        $this->app->singleton('excel', function () {
            return new \Maatwebsite\Excel\Excel();
        });

        if (! class_exists(\Barryvdh\DomPDF\PdfFactory::class)) {
            require_once app_path('Support/dompdf-polyfill.php');
        }

        $this->app->singleton('pdf', function () {
            return new \Barryvdh\DomPDF\PdfFactory();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
