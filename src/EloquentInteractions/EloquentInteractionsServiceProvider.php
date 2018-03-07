<?php

namespace ZachFlower\EloquentInteractions;

use Illuminate\Support\ServiceProvider;
use ZachFlower\EloquentInteractions\Commands\MakeInteractionCommand;

class EloquentInteractionsServiceProvider extends ServiceProvider {

  /**
   * Bootstrap the application services.
   *
   * @return void
   */
  public function boot() {
    // register console commands
    if ($this->app->runningInConsole()) {
      $this->commands([
          MakeInteractionCommand::class
      ]);
    }

    // validate the input object type against the defined object type
    $this->app['validator']->extend('object', function ($attribute, $value, $parameters, $validator) {
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
