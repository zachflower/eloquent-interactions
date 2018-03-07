<?php

namespace ZachFlower\EloquentInteractions\Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase {

  protected function getPackageProviders($app) {
    return [
      \ZachFlower\EloquentInteractions\EloquentInteractionsServiceProvider::class,
    ];
  }
}