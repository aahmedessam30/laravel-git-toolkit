<?php

namespace Ahmedessam\LaravelGitToolkit\Actions\Git;

use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

class FetchAction extends BaseGitAction
{
    public function execute(array $options, ConsoleIOInterface $io): ActionResult
    {
        try {
            $result = $this->repository->executeGitCommand(['fetch']);

            $io->info("Fetch completed successfully");

            return $this->success("Successfully fetched from remote", [
                'output' => $result
            ]);
        } catch (\Exception $e) {
            $io->error("Fetch failed: " . $e->getMessage());
            return $this->failure("Fetch failed: " . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'fetch';
    }

    public function getDescription(): string
    {
        return 'Fetch changes from remote repository without merging';
    }
}
