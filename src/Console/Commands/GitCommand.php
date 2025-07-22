<?php

namespace Ahmedessam\LaravelGitToolkit\Console\Commands;

use Illuminate\Console\Command;
use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Ahmedessam\LaravelGitToolkit\Services\Branch\BranchService;
use Ahmedessam\LaravelGitToolkit\Services\Commit\CommitMessageBuilder;
use Ahmedessam\LaravelGitToolkit\Pipelines\GitCommandPipeline;
use Ahmedessam\LaravelGitToolkit\Exceptions\UnsupportedAction;
use Ahmedessam\LaravelGitToolkit\Events\BranchCreated;
use Ahmedessam\LaravelGitToolkit\Events\CommitPushed;

class GitCommand extends Command
{
    protected $signature = 'git {action} 
    {--branch= : The branch name} 
    {--message= : The commit message} 
    {--type= : The commit type}
    {--merge= : The branch name to merge} 
    {--return= : The branch name to return to}
    {--commit= : The commit to reset to}';

    protected $description = 'Execute git commands from the console';

    protected array $supportedActions = [
        'pull',
        'push',
        'merge',
        'checkout',
        'branch',
        'push-branch',
        'delete-branch',
        'log',
        'diff',
        'fetch',
        'reset',
        'rebase'
    ];

    public function __construct(
        protected GitRepositoryInterface $gitRepository,
        protected ConfigInterface $config,
        protected BranchService $branchService,
        protected CommitMessageBuilder $commitBuilder,
        protected GitCommandPipeline $pipeline
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $action = $this->argument('action');
            $options = $this->options();

            // Validate through pipeline
            $this->pipeline->process($action, $options);

            // Execute the action
            $this->executeAction($action, $options);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->components->error($e->getMessage());
            return self::FAILURE;
        }
    }

    protected function executeAction(string $action, array $options): void
    {
        $method = sprintf('%sAction', str($action)->camel()->title());

        if (!method_exists($this, $method)) {
            throw new UnsupportedAction("Action [{$action}] is not supported.");
        }

        $this->$method($options);
    }

    protected function pullAction(array $options): void
    {
        $branch = $options['branch'] ?? $this->gitRepository->getCurrentBranch();
        $this->info("Pulling from branch: {$branch}");

        $result = $this->gitRepository->executeGitCommand(['pull', 'origin', $branch]);
        $this->info($result);
    }

    protected function pushAction(array $options): void
    {
        if (!$options['message'] && !$this->config->shouldUseDefaultMessage()) {
            $message = $this->commitBuilder->buildInteractiveCommitMessage();
        } else {
            $message = $options['message'] ?? $this->config->getDefaultCommitMessage();
        }

        $this->gitRepository->executeGitCommand(['add', '.']);
        $this->gitRepository->executeGitCommand(['commit', '-m', $message]);

        $branch = $options['branch'] ?? $this->gitRepository->getCurrentBranch();
        $result = $this->gitRepository->executeGitCommand(['push', 'origin', $branch]);

        event(new CommitPushed($branch, $message));
        $this->info("Pushed to branch: {$branch}");
    }

    protected function branchAction(array $options): void
    {
        if ($options['branch']) {
            $branchName = $this->branchService->sanitizeBranchName($options['branch']);
        } else {
            $type = $this->choice('Branch type:', array_keys($this->config->getBranchTypes()));
            $name = $this->ask('Branch name:');
            $use = $this->ask('Feature area (optional):', 'general');
            $prefix = $this->ask('Prefix (optional):') ?? '';
            $branchName = $this->branchService->formatBranchName($name, $type, $use, $prefix);
        }

        $this->gitRepository->executeGitCommand(['checkout', '-b', $branchName]);

        event(new BranchCreated($branchName, $this->gitRepository->getCurrentBranch()));
        $this->info("Created and switched to branch: {$branchName}");
    }
    protected function mergeAction(array $options): void
    {
        $sourceBranch = $options['merge'] ?? $this->ask('Source branch to merge:');
        $targetBranch = $options['branch'] ?? $this->gitRepository->getCurrentBranch();

        $this->gitRepository->executeGitCommand(['checkout', $targetBranch]);
        $this->gitRepository->executeGitCommand(['merge', $sourceBranch]);

        $this->info("Merged {$sourceBranch} into {$targetBranch}");
    }

    protected function checkoutAction(array $options): void
    {
        $branch = $options['branch'] ?? $this->ask('Branch name:');
        $this->gitRepository->executeGitCommand(['checkout', $branch]);
        $this->info("Switched to branch: {$branch}");
    }

    protected function deleteBranchAction(array $options): void
    {
        $branch = $options['branch'] ?? $this->ask('Branch to delete:');
        $this->gitRepository->executeGitCommand(['branch', '-d', $branch]);
        $this->info("Deleted branch: {$branch}");
    }

    protected function fetchAction(array $options): void
    {
        $result = $this->gitRepository->executeGitCommand(['fetch']);
        $this->info($result);
    }

    protected function resetAction(array $options): void
    {
        $commit = $options['commit'] ?? $this->ask('Commit hash to reset to:');
        $this->gitRepository->executeGitCommand(['reset', '--hard', $commit]);
        $this->info("Reset to commit: {$commit}");
    }
}