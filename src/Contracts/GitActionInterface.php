<?php

namespace Ahmedessam\LaravelGitToolkit\Contracts;

use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

interface GitActionInterface
{
    /**
     * Execute the git action with given options and console I/O interface
     */
    public function execute(array $options, ConsoleIOInterface $io): ActionResult;

    /**
     * Get the action name/identifier
     */
    public function getName(): string;

    /**
     * Get the action description
     */
    public function getDescription(): string;
}