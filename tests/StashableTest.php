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
    // First request with sort=name
    $this->get('/test-endpoint?sort=name');
    $users1 = UserRepository::cache('search', 'John');
    $this->assertCount(1, $users1);
    $key1 = "users.search" . json_encode(['sort' => 'name']);
    $this->assertTrue(Cache::has($key1));

    // Delete the matching user
    \DB::table('users')->where('name', 'like', '%John%')->delete();

    // Second request with different sort parameter
    $this->get('/test-endpoint?sort=email');
    $users2 = UserRepository::cache('search', 'John');
    // Should get fresh result since it's a different cache key
    $this->assertCount(0, $users2);
    $key2 = "users.search" . json_encode(['sort' => 'email']);
    $this->assertTrue(Cache::has($key2));
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

  public function test_generates_a_default_cache_key_with_method_name() 
  {
    UserRepository::cache('getAdmins');
    $this->assertTrue(Cache::has('user.getAdmins'));
  }

  public function test_query_parameters_affect_cache_keys()
  {
    //TODO
  }
}
