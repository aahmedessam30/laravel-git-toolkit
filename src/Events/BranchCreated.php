<?php

namespace Ahmedessam\LaravelGitToolkit\Events;

class BranchCreated
{
    public string $branchName;
    public string $sourceBranch;
    public array $metadata;

    public function __construct(string $branchName, string $sourceBranch = null, array $metadata = [])
    {
        $this->branchName = $branchName;
        $this->sourceBranch = $sourceBranch ?? 'current';
        $this->metadata = $metadata;
    }
}
