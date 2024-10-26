<?php

namespace Ahmedessam\LaravelGitToolkit\Services\GitFlow;

class GitFlowToolkit extends GitFlowOperations
{
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

        if (!$this->getFlowConfig('enabled')) {
            throw new \Exception("Git Flow is not enabled in the configuration file.");
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function performAction(): void
    {
        $branches = $this->getFlowConfig('branches');

        $this->components->info('Initializing Git Flow branches...');

        if ($mainBranch = $this->checkMainBranch()) {
            $this->createBranch($mainBranch);
            array_unshift($branches, $mainBranch);
        }

        foreach ($branches as $branch) {
            $this->createBranch($branch);
        }

        $branches = array_merge($branches, $this->createOptionalBranches());

        if (empty(array_diff($branches, $this->existedBranches))) {
            $this->components->info('Git Flow branches initialized successfully.');
            return;
        }

        if ($this->components->confirm('Do you want to push the branches to the remote?', true)) {
            $this->pushBranches($branches);
        }

        $this->components->info('Git Flow branches initialized successfully.');
    }
}
