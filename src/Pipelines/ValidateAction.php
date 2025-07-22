<?php

namespace Ahmedessam\LaravelGitToolkit\Pipelines;

use Ahmedessam\LaravelGitToolkit\Exceptions\UnsupportedAction;
use Closure;

class ValidateAction
{
    protected array $supportedActions = [
        'pull', 'push', 'merge', 'checkout', 'branch', 
        'push-branch', 'delete-branch', 'log', 'diff', 
        'fetch', 'reset', 'rebase'
    ];

    public function handle(array $payload, Closure $next)
    {
        $action = $payload['action'] ?? null;

        if (!$action || !in_array($action, $this->supportedActions)) {
            throw new UnsupportedAction($action, $this->supportedActions);
        }

        return $next($payload);
    }
}
