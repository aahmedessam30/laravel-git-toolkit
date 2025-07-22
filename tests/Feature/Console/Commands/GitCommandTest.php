<?php

namespace Tests\Feature\Console\Commands;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Ahmedessam\LaravelGitToolkit\Actions\GitActionRegistry;
use Ahmedessam\LaravelGitToolkit\Actions\Git\PushAction;
use Ahmedessam\LaravelGitToolkit\Actions\Git\PullAction;
use Ahmedessam\LaravelGitToolkit\Actions\Git\BranchAction;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;
use Mockery;

class GitCommandTest extends TestCase
{
    public function test_git_command_without_action_shows_help()
    {
        $this->artisan('git')
            ->assertExitCode(0);
    }

    public function test_git_command_with_invalid_action_shows_error()
    {
        $this->artisan('git invalid-action')
            ->assertExitCode(1);
    }

    public function test_git_command_executes_valid_action_successfully()
    {
        // Mock the action registry to return a successful result
        $mockRegistry = Mockery::mock(GitActionRegistry::class);
        $mockAction = Mockery::mock(PushAction::class);

        $mockRegistry->shouldReceive('resolve')
            ->with('push')
            ->once()
            ->andReturn($mockAction);

        $mockAction->shouldReceive('execute')
            ->once()
            ->andReturn(ActionResult::success('Push completed successfully'));

        $this->app->instance(GitActionRegistry::class, $mockRegistry);

        $this->artisan('git push --message="Test commit"')
            ->assertExitCode(0);
    }

    public function test_git_command_handles_action_failure()
    {
        // Mock the action registry to return a failure result
        $mockRegistry = Mockery::mock(GitActionRegistry::class);
        $mockAction = Mockery::mock(PushAction::class);

        $mockRegistry->shouldReceive('resolve')
            ->with('push')
            ->once()
            ->andReturn($mockAction);

        $mockAction->shouldReceive('execute')
            ->once()
            ->andReturn(ActionResult::failure('Push failed: No changes to commit'));

        $this->app->instance(GitActionRegistry::class, $mockRegistry);

        $this->artisan('git push')
            ->assertExitCode(1);
    }

    public function test_git_command_handles_exception()
    {
        // Mock the action registry to throw an exception
        $mockRegistry = Mockery::mock(GitActionRegistry::class);
        $mockAction = Mockery::mock(PushAction::class);

        $mockRegistry->shouldReceive('resolve')
            ->with('push')
            ->once()
            ->andReturn($mockAction);

        $mockAction->shouldReceive('execute')
            ->once()
            ->andThrow(new \Exception('Unexpected error occurred'));

        $this->app->instance(GitActionRegistry::class, $mockRegistry);

        $this->artisan('git push')
            ->assertExitCode(1);
    }

    public function test_git_command_passes_options_to_action()
    {
        $mockRegistry = Mockery::mock(GitActionRegistry::class);
        $mockAction = Mockery::mock(BranchAction::class);

        $mockRegistry->shouldReceive('resolve')
            ->with('branch')
            ->once()
            ->andReturn($mockAction);

        // Verify that options are passed correctly
        $mockAction->shouldReceive('execute')
            ->withArgs(function ($options, $io) {
                return $options['branch'] === 'feature/new-branch' &&
                    $options['message'] === null &&
                    $options['type'] === null;
            })
            ->once()
            ->andReturn(ActionResult::success('Branch created successfully'));

        $this->app->instance(GitActionRegistry::class, $mockRegistry);

        $this->artisan('git branch --branch=feature/new-branch')
            ->assertExitCode(0);
    }
}
