<?php

namespace Ahmedessam\LaravelGitToolkit\Pipelines;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\ServiceProvider;

class GitCommandPipeline
{
    protected array $pipes = [
        ValidateGitRepository::class,
        ValidateAction::class,
    ];

    public function __construct(protected Pipeline $pipeline)
    {
        //
    }

    public function process(array $payload): array
    {
        return $this->pipeline
            ->send($payload)
            ->through($this->pipes)
            ->thenReturn();
    }

    public function addPipe(string $pipe): self
    {
        $this->pipes[] = $pipe;
        return $this;
    }

    public function setPipes(array $pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }
}
