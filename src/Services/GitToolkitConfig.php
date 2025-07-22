<?php

namespace Ahmedessam\LaravelGitToolkit\Services;

use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class GitToolkitConfig implements ConfigInterface
{
    public function __construct(
        private ConfigRepository $config
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config->get("git-toolkit.{$key}", $default);
    }

    public function shouldPushToDefaultBranch(): bool
    {
        return $this->get('push_to_default_branch', false);
    }

    public function getDefaultBranch(): string
    {
        return $this->get('default_branch', 'current');
    }

    public function shouldUseDefaultMessage(): bool
    {
        return $this->get('push_with_default_message', false);
    }

    public function getDefaultCommitType(): string
    {
        return $this->get('default_commit_type', 'feat');
    }

    public function getCommitEmoji(string $type): string
    {
        $emojis = $this->get('commit_emojis', []);
        return $emojis[$type] ?? '🔧';
    }

    public function getCommitTypes(): array
    {
        return $this->get('commit_types', []);
    }

    public function shouldPushAfterCommit(): bool
    {
        return $this->get('push_after_commit', true);
    }

    public function shouldReturnToPreviousBranch(): bool
    {
        return $this->get('return_to_previous_branch', true);
    }

    public function shouldDeleteAfterMerge(): bool
    {
        return $this->get('delete_after_merge', false);
    }

    public function getDefaultBranches(): array
    {
        return $this->get('default_branches', ['main', 'master', 'develop', 'staging', 'hotfix']);
    }

    public function getDefaultCommitMessage(): string
    {
        return $this->get('default_commit_message', 'Update [%s] branch with latest changes.');
    }

    public function getBranchTypes(): array
    {
        return $this->get('branch_types', [
            'feature' => 'Feature development',
            'bugfix' => 'Bug fixes',
            'hotfix' => 'Critical hotfixes',
            'release' => 'Release preparation',
            'chore' => 'Maintenance tasks'
        ]);
    }
}
