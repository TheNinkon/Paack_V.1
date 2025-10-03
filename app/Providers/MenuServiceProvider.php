<?php

namespace App\Providers;

use App\Menus\MenuRegistry;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    View::composer([
      'layouts.sections.menu.*',
      'layouts.sections.navbar.*',
    ], function ($view): void {
      $view->with('menuData', MenuRegistry::resolve());
    });
  }
}
