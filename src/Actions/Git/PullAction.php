<?php

namespace Ahmedessam\LaravelGitToolkit\Actions\Git;

use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

class PullAction extends BaseGitAction
{
    public function execute(array $options, ConsoleIOInterface $io): ActionResult
    {
        try {
            $branch = $options['branch'] ?? $this->repository->getCurrentBranch();

            $io->info("Pulling from branch: {$branch}");

            $result = $this->repository->executeGitCommand(['pull', 'origin', $branch]);

            $io->info("Pull completed successfully");

            return $this->success("Successfully pulled from {$branch}", [
                'branch' => $branch,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            $io->error("Pull failed: " . $e->getMessage());
            return $this->failure("Pull failed: " . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'pull';
    }

    public function getDescription(): string
    {
        return 'Pull changes from remote repository';
    }
}
