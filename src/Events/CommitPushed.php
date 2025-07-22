<?php

namespace Ahmedessam\LaravelGitToolkit\Events;

class CommitPushed
{
    public string $branch;
    public string $message;
    public array $files;
    public array $metadata;

    public function __construct(string $branch, string $message, array $files = [], array $metadata = [])
    {
        $this->branch = $branch;
        $this->message = $message;
        $this->files = $files;
        $this->metadata = $metadata;
    }
}
