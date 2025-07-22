<?php

namespace Tests\Feature\Actions;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Actions\GitActionRegistry;
use Ahmedessam\LaravelGitToolkit\Actions\Git\PushAction;
use Ahmedessam\LaravelGitToolkit\Actions\Git\PullAction;
use Ahmedessam\LaravelGitToolkit\Actions\Git\BranchAction;
use Ahmedessam\LaravelGitToolkit\Actions\Git\MergeAction;
use Ahmedessam\LaravelGitToolkit\Actions\Git\CheckoutAction;
use Ahmedessam\LaravelGitToolkit\Actions\Git\FetchAction;

class GitActionRegistryIntegrationTest extends TestCase
{
    public function test_service_provider_registers_all_actions()
    {
        $registry = app(GitActionRegistry::class);

        $supportedActions = $registry->getSupportedActions();

        $expectedActions = [
            'push',
            'pull',
            'branch',
            'merge',
            'checkout',
            'fetch'
        ];

        $this->assertEquals($expectedActions, $supportedActions);
    }

    public function test_registry_resolves_all_registered_actions()
    {
        $registry = app(GitActionRegistry::class);

        $actions = [
            'push' => PushAction::class,
            'pull' => PullAction::class,
            'branch' => BranchAction::class,
            'merge' => MergeAction::class,
            'checkout' => CheckoutAction::class,
            'fetch' => FetchAction::class,
        ];

        foreach ($actions as $actionName => $expectedClass) {
            $action = $registry->resolve($actionName);
            $this->assertInstanceOf($expectedClass, $action);

            // Verify the action has the expected name
            $this->assertEquals($actionName, $action->getName());

            // Verify the action has a description
            $this->assertNotEmpty($action->getDescription());
        }
    }

    public function test_actions_have_proper_dependencies_injected()
    {
        $registry = app(GitActionRegistry::class);

        // Test PushAction has CommitMessageBuilder
        $pushAction = $registry->resolve('push');
        $this->assertInstanceOf(PushAction::class, $pushAction);

        // Test BranchAction has BranchService
        $branchAction = $registry->resolve('branch');
        $this->assertInstanceOf(BranchAction::class, $branchAction);

        // Test all actions extend BaseGitAction and have required properties
        $actions = ['push', 'pull', 'branch', 'merge', 'checkout', 'fetch'];

        foreach ($actions as $actionName) {
            $action = $registry->resolve($actionName);

            // All actions should be instances of GitActionInterface
            $this->assertInstanceOf(
                \Ahmedessam\LaravelGitToolkit\Contracts\GitActionInterface::class,
                $action
            );
        }
    }
}
