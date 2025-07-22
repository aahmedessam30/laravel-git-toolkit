<?php

namespace Ahmedessam\LaravelGitToolkit\Exceptions;

class NoChangesToCommit extends LaravelGitToolkitException
{
    public function __construct()
    {
        parent::__construct("There are no changes to commit.");
    }
}
