<?php

namespace Splitstack\Stashable\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Splitstack\Stashable\StashableServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestCase extends Orchestra
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    $this->setUpDatabase();
  }

  protected function getPackageProviders($app)
  {
    return [
      StashableServiceProvider::class,
    ];
  }

  protected function getEnvironmentSetUp($app)
  {
    // Database configuration
    $app['config']->set('database.default', 'pgsql');
    $app['config']->set('database.connections.pgsql', [
      'driver' => 'pgsql',
      'host' => env('DB_HOST', '127.0.0.1'),
      'port' => env('DB_PORT', '54329'),
      'database' => env('DB_DATABASE', 'testing'),
      'username' => env('DB_USERNAME', 'postgres'),
      'password' => env('DB_PASSWORD', 'password'),
      'charset' => 'utf8',
      'prefix' => '',
      'schema' => 'public',
      'sslmode' => 'prefer',
    ]);
  }

  protected function setUpDatabase()
  {
    $migration = include __DIR__.'/Fixtures/create_users_table.php';
    $migration->up();

    // Insert test data
    $this->seedTestData();
  }

  protected function seedTestData()
  {
    \DB::table('users')->insert([
      [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => 'admin',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'role' => 'user',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'name' => 'Bob Wilson',
        'email' => 'bob@example.com',
        'role' => 'user',
        'created_at' => now(),
        'updated_at' => now(),
      ],
    ]);
  }
}
