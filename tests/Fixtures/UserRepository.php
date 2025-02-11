<?php

namespace Splitstack\Stashable\Tests\Fixtures;

use Splitstack\Stashable\Traits\Stashable;
use Splitstack\Stashable\Attributes\WithCache;
use Illuminate\Support\Facades\DB;

class UserRepository
{
  use Stashable;

  #[WithCache(key: 'users.all', ttl: 300)]
  public function getAll()
  {
    return DB::table('users')->get();
  }

  #[WithCache(key: 'users.{id}', ttl: 300)]
  public function getById($id)
  {
    return DB::table('users')->where('id', $id)->first();
  }

  #[WithCache(key: 'users.search', ttl: 300, useQuery: true)]
  public function search(string $name)
  {
    return DB::table('users')->where('name', 'like', "%{$name}%")->get();
  }

  #[WithCache(key: 'users.role.{role}', ttl: 300, tags: ['roles'])]
  public function getByRole(string $role)
  {
    return DB::table('users')->where('role', $role)->get();
  }

  #[WithCache]
  public function getAdmins()
  {
    return DB::table('users')->where('role', 'admin')->get();
  }

  public function create(array $data)
  {
    return DB::table('users')->insert($data);
  }

  #[WithCache(useQuery: true)]
  public function flexSearch()
  {
    $query = request()->query();
    $name = $query['name'] ?? '';
    return DB::table('users')->where('name', 'like', "%{$name}%")->get();
  }
}
