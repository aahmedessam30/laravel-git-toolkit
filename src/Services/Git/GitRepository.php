<?php

namespace Ahmedessam\LaravelGitToolkit\Services\Git;

use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Ahmedessam\LaravelGitToolkit\Exceptions\GitRepositoryNotFound;
use Ahmedessam\LaravelGitToolkit\Exceptions\GitCommandFailed;
use Illuminate\Support\Facades\Process;

class GitRepository implements GitRepositoryInterface
{
    protected ?string $currentBranch = null;

    public function getCurrentBranch(): ?string
    {
        if ($this->currentBranch === null) {
            $this->currentBranch = exec('git branch --show-current') ?: null;
        }

        return $this->currentBranch;
    }

    public function branchExists(string $branch): bool
    {
        $result = Process::run("git ls-remote --heads origin refs/heads/{$branch}");
        return !empty(trim($result->output()));
    }

    public function executeGitCommand(array|string $command): mixed
    {
        if (!$this->isGitRepository()) {
            throw new GitRepositoryNotFound(getcwd());
        }

        if (is_array($command)) {
            $command = implode(' ', array_map('escapeshellarg', $command));
        }

        $fullCommand = "git {$command}";
        $result = Process::run($fullCommand);

        if ($result->failed()) {
            throw new GitCommandFailed($fullCommand, $result->errorOutput());
        }

        return $result;
    }

    public function hasUncommittedChanges(): bool
    {
        $result = Process::run('git status --porcelain');
        return !empty(trim($result->output()));
    }

    public function hasUnpushedCommits(): bool
    {
        $currentBranch = $this->getCurrentBranch();
        if (!$currentBranch) {
            return false;
        }

        $result = Process::run("git log origin/{$currentBranch}..HEAD --oneline");
        return !empty(trim($result->output()));
    }

    public function isGitRepository(): bool
    {
        return is_dir(base_path('.git'));
    }

    /**
     * Clear cached branch information
     */
    public function clearCache(): void
    {
        $this->currentBranch = null;
    }
}
