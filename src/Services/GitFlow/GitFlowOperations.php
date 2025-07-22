<?php

namespace Ahmedessam\LaravelGitToolkit\Services\GitFlow;

use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class GitFlowOperations
{
    protected array $existedBranches = [];
    protected Command $command;
    protected mixed $components;

    public function __construct(
        protected GitRepositoryInterface $repository,
        protected ConfigInterface $config
    ) {}

    public function setCommand(Command $command, mixed $components = null): self
    {
        $this->command = $command;
        $this->components = $components;
        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function checkMainBranch(): string|null
    {
        $mainBranch = match (true) {
            $this->branchExists('master') => 'master',
            $this->branchExists('main')   => 'main',
            default                       => null,
        };

        if ($mainBranch === 'master') {
            $this->components->warn('Master branch found. It is recommended to use main as the main branch or rename it.');

            if ($this->components->confirm('Do you want to rename the master branch to main?')) {
                $this->executeCommand('git branch -m master main');
                $mainBranch = 'main';
            }
        }

        if ($mainBranch === 'main') {
            $this->components->info('Main branch found.');
        }

        if (!$mainBranch) {
            $mainBranch = $this->command->ask('No main branch found. Please enter the main branch name', 'main');
            $this->existedBranches[] = $mainBranch;
            $this->components->info("Main branch is $mainBranch.");
            return $mainBranch;
        }

        $this->existedBranches[] = $mainBranch;
        $this->components->info("Main branch is $mainBranch.");

        return null;
    }

    /**
     * @throws \Exception
     */
    protected function createBranch($branch): void
    {
        if ($this->branchExists($branch)) {
            $this->components->warn("Branch $branch already exists 🤷‍♂️...");
            $this->existedBranches[] = $branch;
            return;
        }

        $this->executeCommand(
            "checkout -b $branch",
            "Branch $branch created successfully 🚀...",
            "Branch $branch already exists or could not be created 🤷‍♂️..."
        );
    }

    /**
     * @throws \Exception
     */
    protected function createOptionalBranches(): array
    {
        $branches = [];

        foreach ($this->getFlowConfig('optional_branches') as $type) {
            if ($this->components->confirm("Do you want to create a $type branch?")) {
                $name = $this->command->ask(sprintf("Enter [%s] branch name", ucfirst($type)));
                $type = array_key_exists($type, $this->getFlowConfig('branch_prefixes')) ? $this->getFlowConfig('branch_prefixes')[$type] : $type;
                $this->createBranch("$type/$name");
                $branches[] = "$type/$name";
            }
        }

        return $branches;
    }

    /**
     * @throws \Exception
     */
    protected function pushBranches(array $branches): void
    {
        $unpublishedBranches = [];

        // Check which branches are not published to remote
        foreach ($branches as $branch) {
            if (!$this->remoteBranchExists($branch)) {
                $unpublishedBranches[] = $branch;
            }
        }

        if (empty($unpublishedBranches)) {
            $this->components->info('All branches are already published to remote.');
            return;
        }

        $this->components->info(sprintf(
            'Found %d unpublished branch(es): %s',
            count($unpublishedBranches),
            implode(', ', $unpublishedBranches)
        ));

        // Push unpublished branches
        foreach ($unpublishedBranches as $branch) {
            try {
                $this->executeCommand(
                    "push -u origin $branch",
                    "Branch $branch pushed to remote 🚀...",
                    "Failed to push branch $branch 🤷‍♂️..."
                );
            } catch (\Exception $e) {
                $this->components->error("Failed to push branch $branch: " . $e->getMessage());
                // Continue with next branch instead of stopping
                continue;
            }
        }
    }

    /**
     * Get Git Flow configuration value
     */
    protected function getFlowConfig(string $key, mixed $default = null): mixed
    {
        return $this->config->get("git_flow.{$key}", $default);
    }

    /**
     * Check if a branch exists locally
     */
    protected function branchExists(string $branch): bool
    {
        return $this->repository->branchExists($branch);
    }

    /**
     * Check if a branch exists on remote
     */
    protected function remoteBranchExists(string $branch): bool
    {
        try {
            $this->repository->executeGitCommand(['ls-remote', '--exit-code', 'origin', $branch]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Execute a git command with feedback
     */
    protected function executeCommand(string $command, string $successMessage = '', string $errorMessage = ''): void
    {
        try {
            $this->repository->executeGitCommand(explode(' ', $command));
            if ($successMessage) {
                $this->components->info($successMessage);
            }
        } catch (\Exception $e) {
            if ($errorMessage) {
                $this->components->error($errorMessage);
            }
            throw $e;
        }
    }
}
