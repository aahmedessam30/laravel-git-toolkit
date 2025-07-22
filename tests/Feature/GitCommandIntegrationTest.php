<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Mockery;

class GitCommandIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock the git repository to avoid actual git operations during tests
        $mockRepository = Mockery::mock(GitRepositoryInterface::class);
        $mockRepository->shouldReceive('getCurrentBranch')->andReturn('main');
        $mockRepository->shouldReceive('executeGitCommand')->andReturn('Mocked git output');

        $this->app->instance(GitRepositoryInterface::class, $mockRepository);
    }

    public function test_git_command_shows_available_actions_when_no_action_provided()
    {
        $this->artisan('git')
            ->expectsOutputToContain('Available Git Actions:')
            ->expectsOutputToContain('Usage: php artisan git')
            ->assertExitCode(0);
    }

    public function test_git_push_command_works()
    {
        $this->artisan('git push --message="Test commit"')
            ->assertExitCode(0);
    }

    public function test_git_pull_command_works()
    {
        $this->artisan('git pull --branch=main')
            ->assertExitCode(0);
    }

    public function test_git_branch_command_works()
    {
        $this->artisan('git branch --branch=feature/test')
            ->assertExitCode(0);
    }

    public function test_git_checkout_command_works()
    {
        $this->artisan('git checkout --branch=main')
            ->assertExitCode(0);
    }

    public function test_git_fetch_command_works()
    {
        $this->artisan('git fetch')
            ->assertExitCode(0);
    }

    public function test_git_merge_command_works()
    {
        $this->artisan('git merge --merge=feature/test')
            ->assertExitCode(0);
    }

    public function test_invalid_action_shows_error()
    {
        $this->artisan('git invalid')
            ->expectsOutputToContain('Action [invalid] is not registered')
            ->expectsOutputToContain('Available Git Actions:')
            ->assertExitCode(1);
    }
}
