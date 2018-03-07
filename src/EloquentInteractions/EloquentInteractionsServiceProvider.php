<?php

namespace ZachFlower\EloquentInteractions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use EloquentInteractions\Commands\InteractionMakeCommand;

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
          InteractionMakeCommand::class
      ]);
    }

    // validate the input object type against the defined object type
    Validator::extend('object', function ($attribute, $value, $parameters, $validator) {
      return $value instanceof $parameters[0];
    });

    // add translator namespace
    $this->app['translator']->addNamespace('EloquentInteractions', __DIR__.'/../lang');
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
