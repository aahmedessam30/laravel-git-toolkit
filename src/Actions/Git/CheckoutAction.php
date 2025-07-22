<?php

namespace Ahmedessam\LaravelGitToolkit\Actions\Git;

use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

class CheckoutAction extends BaseGitAction
{
    public function execute(array $options, ConsoleIOInterface $io): ActionResult
    {
        try {
            $branch = $options['branch'] ?? $io->ask('Branch name:');

            $this->repository->executeGitCommand(['checkout', $branch]);

            $io->info("Switched to branch: {$branch}");

            return $this->success("Successfully switched to {$branch}", [
                'branch' => $branch
            ]);
        } catch (\Exception $e) {
            $io->error("Checkout failed: " . $e->getMessage());
            return $this->failure("Checkout failed: " . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'checkout';
    }

    public function getDescription(): string
    {
        return 'Switch to a different branch';
    }
}
