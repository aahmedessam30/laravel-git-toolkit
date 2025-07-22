<?php

namespace Ahmedessam\LaravelGitToolkit\Actions\Git;

use Ahmedessam\LaravelGitToolkit\Contracts\GitActionInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

abstract class BaseGitAction implements GitActionInterface
{
    public function __construct(
        protected GitRepositoryInterface $repository,
        protected ConfigInterface $config
    ) {}

    /**
     * Execute pre-action validations
     */
    protected function validate(array $options): void
    {
        // Base validation - can be overridden
    }

    /**
     * Execute post-action operations
     */
    protected function afterExecution(array $options, ActionResult $result): void
    {
        // Base post-execution logic - can be overridden
    }

    /**
     * Create a success result with optional message and data
     */
    protected function success(string $message = '', mixed $data = null): ActionResult
    {
        return ActionResult::success($message, $data);
    }

    /**
     * Create a failure result with optional message and data
     */
    protected function failure(string $message = '', mixed $data = null): ActionResult
    {
        return ActionResult::failure($message, $data);
    }
}
