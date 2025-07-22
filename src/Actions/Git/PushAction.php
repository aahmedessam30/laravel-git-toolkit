<?php

namespace Ahmedessam\LaravelGitToolkit\Actions\Git;

use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Services\Commit\CommitMessageBuilder;
use Ahmedessam\LaravelGitToolkit\Events\CommitPushed;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

class PushAction extends BaseGitAction
{
    public function __construct(
        protected \Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface $repository,
        protected \Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface $config,
        private CommitMessageBuilder $commitBuilder
    ) {
        parent::__construct($repository, $config);
    }

    public function execute(array $options, ConsoleIOInterface $io): ActionResult
    {
        try {
            // Build commit message
            $message = $this->buildCommitMessage($options, $io);

            // Add all changes
            $this->repository->executeGitCommand(['add', '.']);

            // Commit changes
            $this->repository->executeGitCommand(['commit', '-m', $message]);

            // Push to remote
            $branch = $options['branch'] ?? $this->repository->getCurrentBranch();
            $result = $this->repository->executeGitCommand(['push', 'origin', $branch]);

            // Dispatch event
            event(new CommitPushed($branch, $message));

            $io->info("Pushed to branch: {$branch}");

            return $this->success("Successfully pushed to {$branch}", [
                'branch' => $branch,
                'message' => $message,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            $io->error("Push failed: " . $e->getMessage());
            return $this->failure("Push failed: " . $e->getMessage());
        }
    }

    private function buildCommitMessage(array $options, ConsoleIOInterface $io): string
    {
        if (!$options['message'] && !$this->config->shouldUseDefaultMessage()) {
            return $this->commitBuilder->buildInteractiveCommitMessage(
                $this->repository->getCurrentBranch(),
                $io
            );
        } else {
            return $options['message'] ?? $this->config->getDefaultCommitMessage();
        }
    }

    public function getName(): string
    {
        return 'push';
    }

    public function getDescription(): string
    {
        return 'Add, commit and push changes to remote repository';
    }
}
