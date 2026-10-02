<?php

namespace Ahmedessam\LaravelGitToolkit\Exceptions;

class TtyNotAvailable extends GitToolkitException
{
    public function __construct()
    {
        parent::__construct('TTY is not available.');
    }
}
