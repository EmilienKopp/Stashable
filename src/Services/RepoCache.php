<?php

namespace Splitstack\Stashable\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Splitstack\Stashable\Utils\Attr;
use Splitstack\Stashable\Attributes\WithCache;
use ReflectionClass;

class RepoCache
{
  public static function cache($class, $method, $args, $tags = [])
  {
    $reflection = new ReflectionClass($class);
    $method = $reflection->getMethod($method);
    $attributes = $method->getAttributes(WithCache::class);
    if (empty($attributes)) {
      return $method->invokeArgs(new $class, $args);
    }

    $ttl = Attr::get($attributes, 'ttl');
    $key = self::generateKey($args, $attributes, $method);

    if (count($tags) > 0) {
      return Cache::tags($tags)->remember($key, $ttl, function () use ($class, $method, $args) {
        return $method->invokeArgs(new $class, $args);
      });
    } else {
      return Cache::remember($key, $ttl, function () use ($class, $method, $args) {
        return $method->invokeArgs(new $class, $args);
      });
    }
  }

  public static function refresh($class, $method, $args, $tags = [])
  {
    $reflection = new ReflectionClass($class);
    $method = $reflection->getMethod($method);
    $attributes = $method->getAttributes(WithCache::class);
    self::bust($attributes, $args, $tags);
    return $method->invokeArgs(new $class, $args);
  }

  public static function fresh($class, $method, $args, $tags = [])
  {
    $reflection = new ReflectionClass($class);
    $method = $reflection->getMethod($method);
    return $method->invokeArgs(new $class, $args);
  }

  public static function get($class, $method, $args, $tags = [])
  {
    $reflection = new ReflectionClass($class);
    $method = $reflection->getMethod($method);
    $attributes = $method->getAttributes(WithCache::class);
    if (empty($attributes)) {
      return $method->invokeArgs(new $class, $args);
    }

    $key = self::generateKey($args, $attributes);
    if (count($tags) > 0) {
      return Cache::tags($tags)->get($key);
    } else {
      return Cache::get($key);
    }
  }

  public static function bust($attributes, $args, $tags = [])
  {
    $key = self::generateKey($args, $attributes);
    if (!empty($attributes)) {
      if (count($tags) > 0) {
        Cache::tags($tags)->forget($key);
      } else {
        Cache::forget($key);
      }
    }
  }

  public static function generateKey($args, $attributes, $method = null)
  {
    $baseKey = Attr::get($attributes, 'key');
    if(empty($baseKey) && $method) {
      $repoName = Str::snake(Str::replaceLast('Repository', '', (new ReflectionClass($method->class))->getShortName()));
      $methodName = $method->getName();
      $baseKey = "{$repoName}.{$methodName}";
    }
    $key = self::interpolateKey($baseKey, $args);

    if (Attr::get($attributes, 'useQuery')) {
      $key .= json_encode(Request::query());
    }

    return $key;
  }


  private static function interpolateKey($subject, $args)
  {
    $matches = [];

    if (Arr::isAssoc($args)) {
      foreach ($args as $key => $value) {
        $subject = Str::replaceFirst("{{$key}}", $value, $subject);
      }
    } else {
      preg_match_all('/\{(\w+)\}/', $subject, $matches);
      for ($i = 0; $i < count($matches[0]); $i++) {
        $subject = Str::replaceFirst($matches[0][$i], $args[$i], $subject);
      }
    }
    return $subject;
  }
}