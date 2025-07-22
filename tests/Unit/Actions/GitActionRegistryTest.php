<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Actions\GitActionRegistry;
use Ahmedessam\LaravelGitToolkit\Exceptions\UnsupportedAction;
use Ahmedessam\LaravelGitToolkit\Actions\Git\PushAction;
use Ahmedessam\LaravelGitToolkit\Actions\Git\PullAction;
use Mockery;

class GitActionRegistryTest extends TestCase
{
    private GitActionRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new GitActionRegistry();
    }

    public function test_register_and_resolve_action()
    {
        $this->registry->register('push', PushAction::class);

        $action = $this->registry->resolve('push');

        $this->assertInstanceOf(PushAction::class, $action);
    }

    public function test_resolve_unregistered_action_throws_exception()
    {
        $this->expectException(UnsupportedAction::class);
        $this->expectExceptionMessage('Action [unknown] is not registered');

        $this->registry->resolve('unknown');
    }

    public function test_get_supported_actions()
    {
        $this->registry->register('push', PushAction::class);
        $this->registry->register('pull', PullAction::class);

        $actions = $this->registry->getSupportedActions();

        $this->assertEquals(['push', 'pull'], $actions);
    }

    public function test_overwrite_existing_action()
    {
        $this->registry->register('push', PushAction::class);
        $this->registry->register('push', PullAction::class);

        $action = $this->registry->resolve('push');

        $this->assertInstanceOf(PullAction::class, $action);
    }
}
