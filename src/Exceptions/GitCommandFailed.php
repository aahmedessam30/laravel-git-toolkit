<?php

namespace Ahmedessam\LaravelGitToolkit\Exceptions;

use Ahmedessam\LaravelGitToolkit\Git\GitResult;

class GitCommandFailed extends GitToolkitException
{
    public function __construct(public readonly GitResult $result)
    {
        $message = trim($result->errorOutput);
        if ($message === '') {
            $message = trim($result->output);
        }

        parent::__construct('git '.implode(' ', $result->command).': '.$message);
    }
}
