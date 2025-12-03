<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
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
        $compiledPath = config('view.compiled');

        if ($compiledPath) {
            $directory = is_dir($compiledPath) ? $compiledPath : dirname($compiledPath);

            File::ensureDirectoryExists($directory, 0755);

            // Ensure the web user can write compiled views to the configured path.
            @chmod($directory, 0775);
        }
    }
}
