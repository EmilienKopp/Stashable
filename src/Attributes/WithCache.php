<?php

namespace Splitstack\Stashable\Attributes;

use Attribute;

#[Attribute]
class WithCache
{
  public function __construct(
    public string $key,
    public int $ttl = 60,
    public bool $useQuery = false,
    public array $tags = []
  ) {
  }
}