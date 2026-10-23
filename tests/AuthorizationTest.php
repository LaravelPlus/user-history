<?php

declare(strict_types=1);

namespace LaravelPlus\UserHistory\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use LaravelPlus\UserHistory\Middleware\TrackUserActivity;
use LaravelPlus\UserHistory\Models\UserActivity;
use LaravelPlus\UserHistory\Providers\UserHistoryServiceProvider;
use Orchestra\Testbench\TestCase;

final class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [UserHistoryServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('user-history.api.middleware', ['api']);
    }

    protected function defineDatabaseMigrations(): void
    {
        \Illuminate\Support\Facades\Schema::create('users', function ($t): void {
            $t->id();
            $t->string('name')->nullable();
            $t->timestamps();
        });
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function defineRoutes($router): void
    {
        Route::get('/login', fn () => 'login')->name('login');
    }

    private function user(): User
    {
        return (new User)->forceFill(['id' => 1]);
    }

    private function seedActivity(): void
    {
        UserActivity::create(['user_id' => 1, 'action' => 'viewed', 'description' => 'x']);
    }

    public function test_cleanup_is_404_and_deletes_nothing_without_the_gate(): void
    {
        $this->seedActivity();

        $this->actingAs($this->user())->post('/admin/user-history/cleanup', ['days_to_keep' => 0])->assertNotFound();
        $this->actingAs($this->user())->getJson('/api/user-history/activities')->assertNotFound();

        $this->assertSame(1, UserActivity::count());
    }

    public function test_cleanup_rejects_zero_days_when_authorized(): void
    {
        Gate::define('viewUserHistory', fn () => true);
        $this->seedActivity();

        $this->actingAs($this->user())->postJson('/admin/user-history/cleanup', ['days_to_keep' => 0])
            ->assertStatus(422);

        $this->assertSame(1, UserActivity::count());
    }

    public function test_route_names_are_unique(): void
    {
        $names = collect(Route::getRoutes()->getRoutes())->map->getName()->filter();

        $this->assertSame($names->count(), $names->unique()->count());
    }

    public function test_tracking_middleware_is_a_singleton_so_deferred_terminate_works(): void
    {
        $this->assertSame(app(TrackUserActivity::class), app(TrackUserActivity::class));
    }
}
