<?php

namespace Ahmedessam\LaravelGitToolkit\Contracts;

interface GitRepositoryInterface
{
    /**
     * Get the current branch name
     */
    public function getCurrentBranch(): ?string;

    /**
     * Check if a branch exists
     */
    public function branchExists(string $branch): bool;

    /**
     * Execute a git command
     */
    public function executeGitCommand(array|string $command): mixed;

    /**
     * Check if there are uncommitted changes
     */
    public function hasUncommittedChanges(): bool;

    /**
     * Check if there are unpushed local commits
     */
    public function hasUnpushedCommits(): bool;

    /**
     * Check if current directory is a git repository
     */
    public function isGitRepository(): bool;
}
