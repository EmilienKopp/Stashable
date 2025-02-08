<?php

namespace Splitstack\Stashable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App; 

class MakeRepositoryCommand extends Command
{
  protected $signature = 'split:repo 
    {model : The name of the model}
    {--ttl=60 : The default to live for the cache}
  ';

  protected $description = 'Make a new custom repository class';

  public function handle()
  {
    $model = Str::studly($this->argument('model'));
    $ttl = $this->option('ttl') ?? Config::get('stashable.default_ttl');

    //Get model or throw
    $models = File::files(App::path('Models'));
    $modelFile = Arr::first($models, function ($file) use ($model) {
      return Str::contains($file->getFilename(), $model);
    });
    if (!$modelFile) {
      $this->error("Model '$model' not found");
      return;
    }

    $stub = $this->getStub();
    $code = str_replace('__NAME__', $model, $stub);
    $code = str_replace('"__DEFAULT_TTL__"', $ttl, $code);

    $repoPath = App::path('Repositories');
    if (!is_dir($repoPath)) {
      mkdir($repoPath, 0755, true);
    }

    $repoFile = $repoPath . '/' . $model . 'Repository.php';
    if (File::exists($repoFile)) {
      if (!$this->confirm("Repository already exists. Overwrite?")) {
        return;
      }
    }

    File::put($repoFile, $code);
    $this->info('Repository created successfully!');
  }

  private function getStub()
  {
    return file_get_contents(__DIR__ . '/../Stubs/__NAME__Repository.stub');
  }

}