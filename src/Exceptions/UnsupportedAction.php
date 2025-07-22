<?php

namespace Ahmedessam\LaravelGitToolkit\Exceptions;

class UnsupportedAction extends LaravelGitToolkitException
{
    public function __construct(string $action, array $supportedActions = [])
    {
        $message = "Action '{$action}' is not supported.";
        
        if (!empty($supportedActions)) {
            $message .= " Supported actions: " . implode(', ', $supportedActions);
        }
        
        parent::__construct($message);
    }
}
