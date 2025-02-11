<?php

namespace Splitstack\Stashable\Traits;

use Splitstack\Stashable\Services\RepoCache;


trait Stashable
{
  public static function cache($method, ...$args)
  {
    return RepoCache::cache(static::class, $method, $args);
  }

  public static function fresh($method, ...$args)
  {
    return RepoCache::fresh(static::class, $method, $args);
  }

  public static function get($method, ...$args)
  {
    return RepoCache::get(static::class, $method, $args);
  }

  public static function refresh($method, ...$args)
  {
    return RepoCache::refresh(static::class, $method, $args);
  }
}