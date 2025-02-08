<?php

namespace Splitstack\Stashable\Traits;

use Splitstack\Stashable\Services\RepoCache;


trait Stashable
{
  public static function cached($method, ...$args)
  {
    return RepoCache::cache(static::class, $method, $args);
  }

  public static function fresh($method, ...$args)
  {
    return RepoCache::fresh(static::class, $method, $args);
  }
}