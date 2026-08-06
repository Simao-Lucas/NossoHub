<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\WishlistItem;
use App\Policies\EventPolicy;
use App\Policies\WishlistItemPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(WishlistItem::class, WishlistItemPolicy::class);

        Carbon::setLocale(config('app.locale', 'pt_BR'));
    }
}
