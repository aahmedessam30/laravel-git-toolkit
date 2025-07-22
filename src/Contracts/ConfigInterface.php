<?php

namespace Ahmedessam\LaravelGitToolkit\Contracts;

interface ConfigInterface
{
    /**
     * Get configuration value
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Check if should push to default branch
     */
    public function shouldPushToDefaultBranch(): bool;

    /**
     * Get default branch name
     */
    public function getDefaultBranch(): string;

    /**
     * Check if should push with default message
     */
    public function shouldUseDefaultMessage(): bool;

    /**
     * Get default commit type
     */
    public function getDefaultCommitType(): string;

    /**
     * Get commit emoji for type
     */
    public function getCommitEmoji(string $type): string;

    /**
     * Get commit types array
     */
    public function getCommitTypes(): array;

    /**
     * Check if should push after commit
     */
    public function shouldPushAfterCommit(): bool;

    /**
     * Check if should return to previous branch
     */
    public function shouldReturnToPreviousBranch(): bool;

    /**
     * Check if should delete after merge
     */
    public function shouldDeleteAfterMerge(): bool;

    /**
     * Get default branches array
     */
    public function getDefaultBranches(): array;

    /**
     * Get default commit message
     */
    public function getDefaultCommitMessage(): string;

    /**
     * Get branch types configuration
     */
    public function getBranchTypes(): array;
}
