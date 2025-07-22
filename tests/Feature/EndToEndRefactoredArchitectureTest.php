<?php

namespace Tests\Feature;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Ahmedessam\LaravelGitToolkit\Services\Commit\CommitMessageBuilder;
use Ahmedessam\LaravelGitToolkit\Services\Branch\BranchService;
use Mockery;

class EndToEndRefactoredArchitectureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock the git repository to avoid actual git operations
        $mockRepository = Mockery::mock(GitRepositoryInterface::class);
        $mockRepository->shouldReceive('getCurrentBranch')->andReturn('main');
        $mockRepository->shouldReceive('executeGitCommand')->andReturn('Success');

        // Mock validation methods for PushAction
        $mockRepository->shouldReceive('hasUncommittedChanges')->andReturn(true);
        $mockRepository->shouldReceive('hasUnpushedCommits')->andReturn(false);

        $this->app->instance(GitRepositoryInterface::class, $mockRepository);
    }

    public function test_complete_git_workflow_through_command()
    {
        // Test git push
        $this->artisan('git push --message="Test commit"')
            ->assertExitCode(0);

        // Test git pull  
        $this->artisan('git pull --branch=main')
            ->assertExitCode(0);

        // Test git branch
        $this->artisan('git branch --branch=feature/test')
            ->assertExitCode(0);

        // Test git checkout
        $this->artisan('git checkout --branch=main')
            ->assertExitCode(0);

        // Test git fetch
        $this->artisan('git fetch')
            ->assertExitCode(0);

        // Test git merge
        $this->artisan('git merge --merge=feature/test')
            ->assertExitCode(0);
    }

    public function test_service_container_bindings()
    {
        // Test that all required services are properly bound
        $this->assertInstanceOf(
            \Ahmedessam\LaravelGitToolkit\Actions\GitActionRegistry::class,
            app(\Ahmedessam\LaravelGitToolkit\Actions\GitActionRegistry::class)
        );

        $this->assertInstanceOf(
            GitRepositoryInterface::class,
            app(GitRepositoryInterface::class)
        );

        $this->assertInstanceOf(
            ConfigInterface::class,
            app(ConfigInterface::class)
        );

        $this->assertInstanceOf(
            CommitMessageBuilder::class,
            app(CommitMessageBuilder::class)
        );

        $this->assertInstanceOf(
            BranchService::class,
            app(BranchService::class)
        );
    }

    public function test_action_interface_contract_compliance()
    {
        $registry = app(\Ahmedessam\LaravelGitToolkit\Actions\GitActionRegistry::class);

        $actions = ['push', 'pull', 'branch', 'merge', 'checkout', 'fetch'];

        foreach ($actions as $actionName) {
            $action = $registry->resolve($actionName);

            // Test that each action implements the interface
            $this->assertInstanceOf(
                \Ahmedessam\LaravelGitToolkit\Contracts\GitActionInterface::class,
                $action
            );

            // Test that required methods exist and return expected types
            $this->assertIsString($action->getName());
            $this->assertIsString($action->getDescription());
            $this->assertEquals($actionName, $action->getName());
            $this->assertNotEmpty($action->getDescription());
        }
    }

    public function test_console_io_abstraction_works()
    {
        // Test that ArtisanConsoleIO properly wraps Laravel Command
        $command = $this->createMock(\Illuminate\Console\Command::class);
        $consoleIO = new \Ahmedessam\LaravelGitToolkit\Services\Console\ArtisanConsoleIO($command);

        $this->assertInstanceOf(
            \Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface::class,
            $consoleIO
        );
    }

    public function test_architecture_reduces_coupling()
    {
        // Verify that GitCommand only depends on GitActionRegistry
        $gitCommand = new \Ahmedessam\LaravelGitToolkit\Console\Commands\GitCommand(
            app(\Ahmedessam\LaravelGitToolkit\Actions\GitActionRegistry::class)
        );

        $this->assertInstanceOf(
            \Ahmedessam\LaravelGitToolkit\Console\Commands\GitCommand::class,
            $gitCommand
        );

        // The refactored GitCommand should be much smaller
        $reflection = new \ReflectionClass($gitCommand);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED);

        // Filter to only methods defined in the GitCommand class itself, not inherited
        $gitCommandMethods = array_filter($methods, function ($method) {
            return $method->getDeclaringClass()->getName() === \Ahmedessam\LaravelGitToolkit\Console\Commands\GitCommand::class;
        });

        // Should have significantly fewer methods than the original monolithic version
        $methodNames = array_map(fn($method) => $method->getName(), $gitCommandMethods);
        $this->assertContains('handle', $methodNames);
        $this->assertLessThan(10, count($gitCommandMethods)); // Much fewer methods than original
    }
}
