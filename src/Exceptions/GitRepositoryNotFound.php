<?php

namespace Ahmedessam\LaravelGitToolkit\Exceptions;

class GitRepositoryNotFound extends LaravelGitToolkitException
{
    public function __construct(string $path = null)
    {
        $message = $path 
            ? "Git repository not found in path: {$path}" 
            : "This is not a git repository. Please initialize Git first.";
            
        parent::__construct($message);
    }
}
