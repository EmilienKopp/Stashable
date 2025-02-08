<?php

namespace Splitstack\Stashable;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Splitstack\Stashable\Console\Commands\MakeRepositoryCommand;

class StashableServiceProvider extends ServiceProvider
{
  public function boot()
  {
    if ($this->app->runningInConsole()) {
      $this->commands([
        MakeRepositoryCommand::class,
      ]);

      $this->publishes([
        __DIR__ . '/../config/stashable.php' => App::configPath('stashable.php'),
      ], 'stashable-config');
    }
  }

  public function register()
  {
    $this->mergeConfigFrom(
      __DIR__ . '/../config/stashable.php',
      'stashable'
    );
  }
}