<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MigrationsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_ran_and_pending_migrations(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@paau.edu.ng')->first();

        $stub = database_path('migrations/9000_01_01_000000_test_never_run_migration.php');
        file_put_contents($stub, '<?php return new class extends Illuminate\Database\Migrations\Migration {};');

        try {
            Livewire::actingAs($admin)
                ->test('pages.admin.migrations')
                ->assertOk()
                ->assertSet('hasPending', true)
                ->assertSee('9000_01_01_000000_test_never_run_migration', false)
                ->assertSee('0001_01_01_000000_create_users_table', false);
        } finally {
            @unlink($stub);
        }

        $this->assertFileDoesNotExist($stub);
    }

    public function test_run_migrations_executes_pending_and_reports_success(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@paau.edu.ng')->first();

        $stub = database_path('migrations/9000_01_01_000000_test_never_run_migration.php');
        file_put_contents($stub, '<?php return new class extends Illuminate\Database\Migrations\Migration {};');

        try {
            Livewire::actingAs($admin)
                ->test('pages.admin.migrations')
                ->assertSet('hasPending', true)
                ->call('runMigrations')
                ->assertSet('ranSuccess', true)
                ->assertSet('hasPending', false)
                ->assertSet('output', fn (string $output) => str_contains($output, 'DONE'));
        } finally {
            @unlink($stub);
        }
    }

    public function test_reports_nothing_pending_when_all_migrations_have_run(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@paau.edu.ng')->first();

        Livewire::actingAs($admin)
            ->test('pages.admin.migrations')
            ->assertOk()
            ->assertSet('hasPending', false)
            ->assertSee('All migrations are up to date.');
    }
}
