<?php

namespace Ahmedessam\LaravelGitToolkit\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class GitOperations
{
    protected Command $command;
    protected mixed $components;

    public function setCommand(Command $command, mixed $components = null): self
    {
        $this->command = $command;
        $this->components = $components;

        return $this;
    }

    protected function getCurrentBranch(): ?string
    {
        return exec('git branch --show-current');
    }

    protected function checkThereAreNoChangesToCommit(string $action): bool
    {
        return $action === 'push' && !Process::run('git status --porcelain')->output();
    }

    protected function localCommitsNotPushed(): bool
    {
        $result = Process::run('git log origin/' . $this->getCurrentBranch() . '..HEAD --oneline');
        return !empty(trim($result->output()));
    }

    protected function branchExists(string $branch): bool
    {
        return Process::run("git ls-remote --heads origin refs/heads/$branch")->output();
    }

    protected function formatGitCommand(...$args): string
    {
        return sprintf('git %s', implode(' ', $args));
    }

    /**
     * @throws \Exception
     */
    protected function executeCommand(string $command, string $success = null, string $error = null): void
    {
        $command = $this->formatGitCommand($command);

        $this->components->info(sprintf('Executing command: [%s]', $command));

        $result = Process::run($command);

        if ($result->failed()) {
            throw new \Exception(sprintf('Output: %s', $error ?: $result->errorOutput() ?: 'Failed to execute the command 🤷‍♂️...'));
        }

        $this->components->info(sprintf('Output: %s', $success ?: $result->output() ?: 'Command executed successfully 🚀...'));
    }
}
