<?php

namespace Ahmedessam\LaravelGitToolkit\Actions;

use Ahmedessam\LaravelGitToolkit\Contracts\GitActionInterface;
use Ahmedessam\LaravelGitToolkit\Exceptions\UnsupportedAction;

class GitActionRegistry
{
    private array $actions = [];

    /**
     * Register a git action
     */
    public function register(string $name, string $actionClass): void
    {
        $this->actions[$name] = $actionClass;
    }

    /**
     * Resolve an action by name
     */
    public function resolve(string $name): GitActionInterface
    {
        if (!isset($this->actions[$name])) {
            throw new UnsupportedAction("Action [{$name}] is not registered");
        }

        return app($this->actions[$name]);
    }

    /**
     * Get all supported action names
     */
    public function getSupportedActions(): array
    {
        return array_keys($this->actions);
    }

    /**
     * Get all registered actions with their class names
     */
    public function getAllActions(): array
    {
        return $this->actions;
    }

    /**
     * Check if an action is supported
     */
    public function supports(string $name): bool
    {
        return isset($this->actions[$name]);
    }
}
