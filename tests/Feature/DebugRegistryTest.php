<?php

namespace Tests\Feature;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Actions\GitActionRegistry;

class DebugRegistryTest extends TestCase
{
    public function test_debug_what_actions_are_registered()
    {
        $registry = app(GitActionRegistry::class);
        $actions = $registry->getSupportedActions();

        echo "\nRegistered actions:\n";
        foreach ($actions as $action) {
            echo "- $action\n";
        }

        $this->assertGreaterThan(0, count($actions));
    }
}
