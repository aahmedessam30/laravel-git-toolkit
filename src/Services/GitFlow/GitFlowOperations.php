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
            $this->components->warn('Main branch found. It is recommended to use main as the main branch.');
        }

        if (!$mainBranch) {
            $this->components->ask('No main branch found. Please enter the main branch name', 'main');
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
        foreach ($branches as $branch) {
            if ($this->branchExists($branch)) {
                $this->components->warn("Branch $branch already exists on remote 🤷‍♂️...");
                continue;
            }

            $this->executeCommand(
                "push -u origin $branch",
                "Branch $branch pushed to remote 🚀...",
                "Failed to push branch $branch 🤷‍♂️..."
            );
        }
    }
}
