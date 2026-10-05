<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\CustomOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer(
            ['partials.header', 'partials.footer'],
            function ($view): void {
                $view->with('navCategories', $this->navCategories());
            }
        );

        View::composer(
            'components.layouts.admin',
            function ($view): void {
                $view->with('pendingOrdersCount', CustomOrder::where('status', CustomOrder::STATUS_PENDING)->count());
            }
        );
    }

    private function navCategories()
    {
        return Cache::remember('nav.categories', now()->addMinutes(10), function () {
            return Category::select('name', 'slug')->orderBy('name')->get();
        });
    }
}
