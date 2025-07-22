<?php

namespace Ahmedessam\LaravelGitToolkit\Actions\Git;

use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Services\Branch\BranchService;
use Ahmedessam\LaravelGitToolkit\Events\BranchCreated;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

class BranchAction extends BaseGitAction
{
    public function __construct(
        protected \Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface $repository,
        protected \Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface $config,
        private BranchService $branchService
    ) {
        parent::__construct($repository, $config);
    }

    public function execute(array $options, ConsoleIOInterface $io): ActionResult
    {
        try {
            $branchName = $this->determineBranchName($options, $io);

            $this->repository->executeGitCommand(['checkout', '-b', $branchName]);

            event(new BranchCreated($branchName, $this->repository->getCurrentBranch()));

            $io->info("Created and switched to branch: {$branchName}");

            return $this->success("Successfully created branch {$branchName}", [
                'branch' => $branchName
            ]);
        } catch (\Exception $e) {
            $io->error("Branch creation failed: " . $e->getMessage());
            return $this->failure("Branch creation failed: " . $e->getMessage());
        }
    }

    private function determineBranchName(array $options, ConsoleIOInterface $io): string
    {
        if ($options['branch']) {
            return $this->branchService->sanitizeBranchName($options['branch']);
        }

        $type   = $io->choice('Branch type:', array_keys($this->config->getBranchTypes()));
        $name   = $io->ask('Branch name:');
        $use    = $io->ask('Feature area (optional):', 'general');
        $prefix = $io->ask('Prefix (optional):') ?? '';

        return $this->branchService->formatBranchName($name, $type, $use, $prefix);
    }

    public function getName(): string
    {
        return 'branch';
    }

    public function getDescription(): string
    {
        return 'Create a new branch and switch to it';
    }
}