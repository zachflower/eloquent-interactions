<?php namespace EloquentInteractions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class EloquentInteractionsServiceProvider extends ServiceProvider {

  /**
   * Bootstrap the application services.
   *
   * @return void
   */
  public function boot() {
    if ($this->app->runningInConsole()) {
      $this->commands([
          MakeInteraction::class
      ]);
    }

    // validate the input object type against the defined object type
    Validator::extend('object', function ($attribute, $value, $parameters, $validator) {
      return $value instanceof $parameters[0];
    });
  }

  /**
   * Register the application services.
   *
   * @return void
   */
  public function register() {
    //
  }
}
