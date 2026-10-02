<?php

namespace Ahmedessam\LaravelGitToolkit\Exceptions;

class GitRepositoryNotFound extends GitToolkitException
{
    public function __construct(string $path)
    {
        parent::__construct("No git repository found at [{$path}].");
    }
}
