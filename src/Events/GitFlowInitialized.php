<?php

namespace Ahmedessam\LaravelGitToolkit\Events;

class GitFlowInitialized
{
    public array $branches;
    public array $metadata;

    public function __construct(array $branches, array $metadata = [])
    {
        $this->branches = $branches;
        $this->metadata = $metadata;
    }
}
