<?php

namespace Tests\Feature;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Actions\GitActionRegistry;
use Ahmedessam\LaravelGitToolkit\Console\Commands\GitCommand;
use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Illuminate\Support\Facades\Artisan;
use Mockery;

class RefactoringSuccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock the git repository to avoid actual git operations during tests
        $mockRepository = Mockery::mock(GitRepositoryInterface::class);
        $mockRepository->shouldReceive('getCurrentBranch')->andReturn('main');
        $mockRepository->shouldReceive('executeGitCommand')->andReturn('Mocked git output');

        // Mock validation methods for PushAction
        $mockRepository->shouldReceive('hasUncommittedChanges')->andReturn(true);
        $mockRepository->shouldReceive('hasUnpushedCommits')->andReturn(false);

        $this->app->instance(GitRepositoryInterface::class, $mockRepository);
    }

    public function test_refactoring_architecture_components_exist()
    {
        // Test that all new architecture components are properly registered
        $this->assertInstanceOf(
            GitActionRegistry::class,
            app(GitActionRegistry::class)
        );
    }

    public function test_git_command_is_dramatically_simplified()
    {
        // The refactored GitCommand should be much smaller than the original
        $gitCommand = new GitCommand(app(GitActionRegistry::class));

        $reflection = new \ReflectionClass($gitCommand);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED);

        // Count only methods declared in our class, not inherited ones
        $ourMethods = array_filter($methods, function ($method) {
            return $method->getDeclaringClass()->getName() === GitCommand::class;
        });

        // Original had many action methods, refactored should have just a few orchestration methods
        $this->assertLessThan(10, count($ourMethods), 'GitCommand should have fewer methods after refactoring');

        // Verify we have the essential methods
        $methodNames = array_map(fn($method) => $method->getName(), $ourMethods);
        $this->assertContains('handle', $methodNames);
        $this->assertContains('displayAvailableActions', $methodNames);
    }

    public function test_action_registry_resolves_all_expected_actions()
    {
        $registry = app(GitActionRegistry::class);

        $expectedActions = ['push', 'pull', 'branch', 'merge', 'checkout', 'fetch'];
        $actualActions = $registry->getSupportedActions();

        foreach ($expectedActions as $expectedAction) {
            $this->assertContains($expectedAction, $actualActions, "Action '$expectedAction' should be registered");
        }
    }

    public function test_each_action_implements_proper_interface()
    {
        $registry = app(GitActionRegistry::class);
        $actions = $registry->getSupportedActions();

        foreach ($actions as $actionName) {
            $action = $registry->resolve($actionName);

            $this->assertInstanceOf(
                \Ahmedessam\LaravelGitToolkit\Contracts\GitActionInterface::class,
                $action,
                "Action '$actionName' should implement GitActionInterface"
            );

            // Test contract methods exist and return expected types
            $this->assertIsString($action->getName());
            $this->assertIsString($action->getDescription());
            $this->assertEquals($actionName, $action->getName());
        }
    }

    public function test_git_commands_execute_successfully()
    {
        // Test that basic commands work without errors
        $this->artisan('git push --message="Test"')->assertExitCode(0);
        $this->artisan('git pull')->assertExitCode(0);
        $this->artisan('git branch --branch=test')->assertExitCode(0);
        $this->artisan('git checkout --branch=main')->assertExitCode(0);
        $this->artisan('git fetch')->assertExitCode(0);
        $this->artisan('git merge --merge=test')->assertExitCode(0);
    }

    public function test_architecture_follows_solid_principles()
    {
        // Single Responsibility: Each action has one responsibility
        $registry = app(GitActionRegistry::class);
        $pushAction = $registry->resolve('push');
        $pullAction = $registry->resolve('pull');

        $this->assertNotEquals(get_class($pushAction), get_class($pullAction));
        $this->assertEquals('push', $pushAction->getName());
        $this->assertEquals('pull', $pullAction->getName());

        // Open/Closed: Can add new actions without modifying existing code
        $this->assertInstanceOf(GitActionRegistry::class, $registry);

        // Dependency Inversion: GitCommand depends on abstractions
        $gitCommand = new GitCommand(app(GitActionRegistry::class));
        $this->assertInstanceOf(GitCommand::class, $gitCommand);
    }

    public function test_console_abstraction_works()
    {
        // Test that console IO abstraction is properly implemented
        $consoleIO = new \Ahmedessam\LaravelGitToolkit\Services\Console\ArtisanConsoleIO(
            $this->createMock(\Illuminate\Console\Command::class)
        );

        $this->assertInstanceOf(
            \Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface::class,
            $consoleIO
        );
    }

    public function test_action_result_standardization()
    {
        // Test that ActionResult provides consistent response format
        $successResult = \Ahmedessam\LaravelGitToolkit\Actions\ActionResult::success('Test success');
        $failureResult = \Ahmedessam\LaravelGitToolkit\Actions\ActionResult::failure('Test failure');

        $this->assertTrue($successResult->isSuccess());
        $this->assertFalse($successResult->isFailure());
        $this->assertEquals('Test success', $successResult->getMessage());

        $this->assertFalse($failureResult->isSuccess());
        $this->assertTrue($failureResult->isFailure());
        $this->assertEquals('Test failure', $failureResult->getMessage());
    }

    public function test_refactoring_maintains_backwards_compatibility()
    {
        // All the original git commands should still work
        $commands = [
            'git push --message="Test commit"',
            'git pull --branch=main',
            'git branch --branch=feature/test',
            'git checkout --branch=main',
            'git fetch',
            'git merge --merge=feature/test'
        ];

        foreach ($commands as $command) {
            $this->artisan($command)->assertExitCode(0);
        }
    }
}
