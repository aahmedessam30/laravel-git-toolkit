<?php

namespace Ahmedessam\LaravelGitToolkit\Services\Branch;

use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Ahmedessam\LaravelGitToolkit\Exceptions\InvalidBranchName;

class BranchService
{
    protected GitRepositoryInterface $repository;
    protected ConfigInterface $config;

    public function __construct(GitRepositoryInterface $repository, ConfigInterface $config)
    {
        $this->repository = $repository;
        $this->config = $config;
    }

    public function sanitizeBranchName(string $name): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9\-_\/]/', '-', $name);
        $sanitized = trim($sanitized, '-/');
        
        if (empty($sanitized)) {
            throw new InvalidBranchName($name);
        }
        
        return $sanitized;
    }

    public function formatBranchName(string $name, string $type, string $use, string $prefix): string
    {
        $name = $this->sanitizeBranchName($name);
        
        // Remove existing prefixes
        $name = str_replace([$prefix . '/', $use . '/'], '', $name);
        $name = preg_replace('/\/+/', '/', $name);
        $name = trim($name, '/');

        if ($use === 'other') {
            return sprintf('%s/%s', $type, $name);
        }
        
        return sprintf('%s/%s/%s', $type, $use, $name);
    }

    public function isDefaultBranch(string $branch): bool
    {
        return in_array($branch, $this->config->getDefaultBranches());
    }

    public function getCurrentBranch(): ?string
    {
        return $this->repository->getCurrentBranch();
    }

    public function branchExists(string $branch): bool
    {
        return $this->repository->branchExists($branch);
    }

    public function createBranch(string $branchName): void
    {
        $sanitizedName = $this->sanitizeBranchName($branchName);
        $this->repository->executeGitCommand("checkout -b {$sanitizedName}");
    }

    public function checkoutBranch(string $branchName): void
    {
        $sanitizedName = $this->sanitizeBranchName($branchName);
        $this->repository->executeGitCommand("checkout {$sanitizedName}");
    }

    public function deleteBranch(string $branchName, bool $force = false): void
    {
        $flag = $force ? '-D' : '-d';
        $sanitizedName = $this->sanitizeBranchName($branchName);
        $this->repository->executeGitCommand("branch {$flag} {$sanitizedName}");
    }
}
