<?php

namespace Ahmedessam\LaravelGitToolkit\Exceptions;

class InvalidBranchName extends LaravelGitToolkitException
{
    public function __construct(string $branchName)
    {
        parent::__construct("Invalid branch name: {$branchName}");
    }
}
