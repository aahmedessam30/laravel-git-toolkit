<?php

namespace Ahmedessam\LaravelGitToolkit\Services\GitFlow;

class GitFlowToolkit extends GitFlowOperations
{
    protected array $branches = ['develop', 'staging', 'hotfix'];

    /**
     * @throws \Exception
     */
    public function run(): void
    {
        try {
            $this->validateAction()->performAction();
        } catch (\Exception $e) {
            $this->components->error($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    protected function validateAction(): static
    {
        if (!is_dir(base_path('.git'))) {
            throw new \Exception("This is not a git repository 🤷‍♂️, please initialize Git first.");
        }

        return $this;
    }

    protected function performAction(): void
    {
        $this->components->info('Initializing Git Flow branches...');

        if ($mainBranch = $this->checkMainBranch()) {
            $this->createBranch($mainBranch);
            array_unshift($this->branches, $mainBranch);
        }

        foreach ($this->branches as $branch) {
            $this->createBranch($branch);
        }

        $this->branches = array_merge($this->branches, $this->createOptionalBranches());

        if (empty(array_diff($this->branches, $this->existedBranches))) {
            $this->components->info('Git Flow branches initialized successfully.');
            return;
        }

        if ($this->components->confirm('Do you want to push the branches to the remote?', true)) {
            $this->pushBranches($this->branches);
        }

        $this->components->info('Git Flow branches initialized successfully.');
    }
}
