<?php

namespace Ahmedessam\LaravelGitToolkit\Exceptions;

class GitCommandFailed extends LaravelGitToolkitException
{
    public function __construct(string $command, string $error = '')
    {
        $message = "Git command failed: {$command}";
        if ($error) {
            $message .= " - Error: {$error}";
        }
        
        parent::__construct($message);
    }
}
