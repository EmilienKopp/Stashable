<?php

namespace Splitstack\Stashable\Tests;

use Illuminate\Support\Facades\Cache;
use Splitstack\Stashable\Tests\Fixtures\UserRepository;

class StashableTest extends TestCase
{
  private UserRepository $repository;

  protected function setUp(): void
  {
    parent::setUp();
    $this->repository = new UserRepository();
    Cache::flush();
  }

  public function test_it_caches_method_results()
  {
    // First call should hit database
    $result1 = UserRepository::cache('getAll');
    $this->assertCount(3, $result1);

    // Modify database
    \DB::table('users')->delete();

    // Second call should return cached result
    $result2 = UserRepository::cache('getAll');
    $this->assertCount(3, $result2);
    $this->assertEquals($result1, $result2);

    // Fresh call should hit database
    $result3 = UserRepository::fresh('getAll');
    $this->assertCount(0, $result3);
  }

  public function test_it_interpolates_cache_keys()
  {
    $user = UserRepository::cache('getById', 1);
    $this->assertEquals('John Doe', $user->name);

    // Modify user name
    \DB::table('users')->where('id', 1)->update(['name' => 'Changed Name']);

    // Should return cached result
    $cachedUser = UserRepository::cache('getById', 1);
    $this->assertEquals('John Doe', $cachedUser->name);

    // Fresh call should return updated data
    $freshUser = UserRepository::fresh('getById', 1);
    $this->assertEquals('Changed Name', $freshUser->name);
  }

  public function test_it_includes_query_params_in_cache_key()
  {
    $request = request();
    $request->merge(['sort' => 'name']);

    $users1 = UserRepository::cache('search', 'John');
    $this->assertCount(1, $users1);

    // Change query params
    $request->merge(['sort' => 'email']);

    // Should generate different cache key due to different query params
    \DB::table('users')->where('name', 'like', '%John%')->delete();
    $users2 = UserRepository::cache('search', 'John');
    $this->assertCount(0, $users2);
  }

  public function test_it_handles_cache_tags()
  {
    $admins1 = UserRepository::cache('getByRole', 'admin');
    $this->assertCount(1, $admins1);

    // Modify database
    \DB::table('users')->where('role', 'admin')->delete();


    // Should return cached result
    $admins2 = UserRepository::cache('getByRole', 'admin');
    $this->assertEquals(1, $admins2->count());

    // Clear cache for role tag
    Cache::tags(['roles'])->clear();

    // Should hit fetching closure and return updated db result (none since deletion)
    $admins3 = UserRepository::cache('getByRole', 'admin');
    $this->assertEquals(0, $admins3->count());
  }

  public function test_uncached_methods_always_hit_database()
  {
    $result = UserRepository::cache('create', ['name' => 'Test', 'email' => 'test@example.com', 'role' => 'user']);
    $this->assertTrue($result);

    // Method without cache attribute should always execute
    $this->assertDatabaseHas('users', ['name' => 'Test']);
  }

  public function test_it_can_get() 
  {
    $result = UserRepository::cache('getAll');
    $this->assertCount(3, $result);

    \DB::table('users')->delete();

    // Should still return stale cached data
    $result = UserRepository::get('getAll');
    $this->assertCount(3, $result);
  }

  public function test_it_can_refresh() 
  {
    $result = UserRepository::cache('getAll');
    $this->assertCount(3, $result);

    \DB::table('users')->delete();

    // Should still return stale cached data
    $result = UserRepository::refresh('getAll');
    $this->assertCount(0, $result);
  }
  
}
