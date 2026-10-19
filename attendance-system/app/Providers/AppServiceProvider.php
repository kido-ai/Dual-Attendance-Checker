<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FaceRecognition\FaceRecognitionProvider;
use App\Services\FaceRecognition\HttpFaceRecognitionProvider;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FaceRecognitionProvider::class, function () {
            return new HttpFaceRecognitionProvider(
                config('services.face_recognition.endpoint'),
                config('services.face_recognition.key'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            $view->with('systemSettings', SystemSetting::allKeyed());
        });
    }
}
