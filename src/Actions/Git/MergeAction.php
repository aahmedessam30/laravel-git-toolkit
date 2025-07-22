<?php

namespace Ahmedessam\LaravelGitToolkit\Actions\Git;

use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

class MergeAction extends BaseGitAction
{
    public function execute(array $options, ConsoleIOInterface $io): ActionResult
    {
        try {
            $sourceBranch = $options['merge'] ?? $io->ask('Source branch to merge:');
            $targetBranch = $options['branch'] ?? $this->repository->getCurrentBranch();

            $this->repository->executeGitCommand(['checkout', $targetBranch]);
            $this->repository->executeGitCommand(['merge', $sourceBranch]);

            $io->info("Merged {$sourceBranch} into {$targetBranch}");

            return $this->success("Successfully merged {$sourceBranch} into {$targetBranch}", [
                'source' => $sourceBranch,
                'target' => $targetBranch
            ]);
        } catch (\Exception $e) {
            $io->error("Merge failed: " . $e->getMessage());
            return $this->failure("Merge failed: " . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'merge';
    }

    public function getDescription(): string
    {
        return 'Merge one branch into another';
    }
}
