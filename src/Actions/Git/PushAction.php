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
            $branch = $options['branch'] ?? $this->repository->getCurrentBranch();

            // Determine repository state
            $repositoryState = $this->analyzeRepositoryState();

            // Handle different repository states
            return match ($repositoryState) {
                'up_to_date'       => $this->handleUpToDateRepository($io),
                'unpushed_only'    => $this->handleUnpushedCommitsOnly($io, $branch),
                'uncommitted_only' => $this->handleUncommittedChangesOnly($options, $io, $branch),
                'mixed_changes'    => $this->handleMixedChanges($options, $io, $branch),
                default            => $this->failure("Unknown repository state: {$repositoryState}")
            };
        } catch (\Exception $e) {
            $io->error("Push failed: " . $e->getMessage());
            return $this->failure("Push failed: " . $e->getMessage());
        }
    }

    /**
     * Analyze the current state of the repository
     */
    private function analyzeRepositoryState(): string
    {
        $hasUncommitted = $this->repository->hasUncommittedChanges();
        $hasUnpushed = $this->repository->hasUnpushedCommits();

        return match (true) {
            !$hasUncommitted && !$hasUnpushed => 'up_to_date',
            !$hasUncommitted && $hasUnpushed  => 'unpushed_only',
            $hasUncommitted  && !$hasUnpushed => 'uncommitted_only',
            $hasUncommitted  && $hasUnpushed  => 'mixed_changes',
        };
    }

    /**
     * Handle repository that is up to date
     */
    private function handleUpToDateRepository(ConsoleIOInterface $io): ActionResult
    {
        $io->info("No changes to commit or push.");
        return $this->success("Repository is up to date - nothing to push");
    }

    /**
     * Handle repository with only unpushed commits
     */
    private function handleUnpushedCommitsOnly(ConsoleIOInterface $io, string $branch): ActionResult
    {
        $io->info("Found unpushed commits. Pushing existing commits to remote...");

        $result = $this->executeGitPush($branch);
        $io->info("Pushed existing commits to branch: {$branch}");

        return $this->success("Successfully pushed existing commits to {$branch}", [
            'branch' => $branch,
            'output' => $result
        ]);
    }

    /**
     * Handle repository with only uncommitted changes
     */
    private function handleUncommittedChangesOnly(array $options, ConsoleIOInterface $io, string $branch): ActionResult
    {
        $message = $this->buildCommitMessage($options, $io);

        $this->createCommit($message, $io);
        $this->dispatchCommitEvent($branch, $message);

        $result = $this->executeGitPush($branch);
        $io->info("Pushed to branch: {$branch}");

        return $this->success("Successfully committed and pushed to {$branch}", [
            'branch'  => $branch,
            'message' => $message,
            'output'  => $result
        ]);
    }

    /**
     * Handle repository with both uncommitted and unpushed changes
     */
    private function handleMixedChanges(array $options, ConsoleIOInterface $io, string $branch): ActionResult
    {
        $message = $this->buildCommitMessage($options, $io);

        $this->createCommit($message, $io);
        $this->dispatchCommitEvent($branch, $message);

        $result = $this->executeGitPush($branch);
        $io->info("Pushed to branch: {$branch}");

        return $this->success("Successfully committed and pushed to {$branch}", [
            'branch'  => $branch,
            'message' => $message,
            'output'  => $result
        ]);
    }

    /**
     * Create a commit with the given message
     */
    private function createCommit(string $message, ConsoleIOInterface $io): void
    {
        $this->repository->executeGitCommand(['add', '.']);
        $this->repository->executeGitCommand(['commit', '-m', $message]);
        $io->info("Changes committed successfully.");
    }

    /**
     * Execute git push command
     */
    private function executeGitPush(string $branch): string
    {
        $result = $this->repository->executeGitCommand(['push', 'origin', $branch]);

        // Handle both ProcessResult objects and string responses (for testing)
        return is_string($result) ? $result : $result->output();
    }

    /**
     * Dispatch commit pushed event
     */
    private function dispatchCommitEvent(string $branch, string $message): void
    {
        event(new CommitPushed($branch, $message));
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
