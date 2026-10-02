<?php

namespace Ahmedessam\LaravelGitToolkit\Git;

use Ahmedessam\LaravelGitToolkit\Exceptions\GitCommandFailed;

final class GitResult
{
    /**
     * @param  list<string>  $command
     */
    public function __construct(
        public readonly array $command,
        public readonly string $output,
        public readonly string $errorOutput,
        public readonly int $exitCode,
    ) {}

    public function successful(): bool
    {
        return $this->exitCode === 0;
    }

    public function failed(): bool
    {
        return ! $this->successful();
    }

    public function throw(): static
    {
        if ($this->failed()) {
            throw new GitCommandFailed($this);
        }

        return $this;
    }

    public function trimmedOutput(): string
    {
        return trim($this->output);
    }

    /**
     * @return list<string>
     */
    public function lines(): array
    {
        if ($this->trimmedOutput() === '') {
            return [];
        }

        $lines = preg_split('/\R/', $this->output) ?: [];
        $lines = array_map(static fn (string $line): string => trim($line), $lines);
        $lines = array_filter($lines, static fn (string $line): bool => $line !== '');

        return array_values($lines);
    }
}
