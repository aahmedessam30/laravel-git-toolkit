<?php

namespace Ahmedessam\LaravelGitToolkit\Services;


class GitCommands extends GitOperations
{
    protected function askForBranch(): string
    {
        return $this->components->ask(sprintf('Enter the branch name, Leave empty to use the current branch [%s]', $this->getCurrentBranch())) ?: $this->getCurrentBranch();
    }

    private function getCommitChoices(): array
    {
        return [
            'feat'     => '🚀 Feature: A new feature',
            'fix'      => '🐛 Fix: A bug fix',
            'docs'     => '📝 Docs: Documentation only changes',
            'style'    => '💄 Style: Changes that do not affect the meaning of the code',
            'refactor' => '♻️ Refactor: A code change that neither fixes a bug nor adds a feature',
            'test'     => '🚨 Test: Adding missing tests or correcting existing tests',
            'chore'    => '🔧 Chore: Changes to the build process or auxiliary tools and libraries such as documentation generation',
        ];
    }

    private function getCommitEmoji(string $commit_type): string
    {
        $emojis = [
            'feat'     => '🚀',
            'fix'      => '🐛',
            'docs'     => '📝',
            'style'    => '💄',
            'refactor' => '♻️',
            'test'     => '🚨',
            'chore'    => '🔧',
        ];

        return $emojis[$commit_type];
    }

    private function getCommitMessage(): string
    {
        $default_message = sprintf("Update [%s] branch with latest changes.", $this->getCurrentBranch());
        $commit          = $this->components->ask(sprintf('Enter the commit message, Leave empty to use the default message [%s]', $default_message)) ?: $default_message;
        $commit_type     = $this->command->choice('Enter the commit type', $this->getCommitChoices(), 'feat');

        return sprintf('%s %s: %s', $this->getCommitEmoji($commit_type), $commit_type, $commit);
    }

    /**
     * @throws \Exception
     */
    protected function pushLocalCommits(): void
    {
        $this->components->info('Pushing local commits 🚀...');

        $this->executeCommand(sprintf('push orgin %s', $this->getCurrentBranch()));

        $this->components->info('Local commits pushed successfully 🚀...');
    }

    /**
     * @throws \Exception
     */
    protected function pushAction(): void
    {
        if ($this->checkThereAreNoChangesToCommit('push')) {
            throw new \Exception('There are no changes to commit 🤷‍♂️...');
        }

        if ($this->localCommitsNotPushed()) {
            $this->pushLocalCommits();
            return;
        }

        $branch = $this->options['branch'] ?? $this->askForBranch();

        if ($branch !== $this->getCurrentBranch()) {
            $commands = [
                sprintf('checkout %s', $branch),
                sprintf('pull origin %s', $branch),
                'add .',
                sprintf('commit -m "%s"', $this->getCommitMessage()),
                sprintf('push origin %s', $branch),
                sprintf('checkout %s', $this->getCurrentBranch()),
            ];
        } else {
            $commands = [
                'add .',
                sprintf('commit -m "%s"', $this->getCommitMessage()),
                sprintf('push origin %s', $branch),
            ];
        }

        $this->components->info('Pushing changes 🚀...');

        foreach ($commands as $command) {
            $this->executeCommand($command);
        }

        $this->components->info('Pushed successfully 🚀...');
    }

    /**
     * @throws \Exception
     */
    protected function mergeAction(): void
    {
        $branch = $this->options['branch'] ?? $this->components->ask('Enter the branch name to merge into (target branch)');

        if (!$branch) {
            $this->components->error('You must enter the target branch 🤷‍♂️...');
            $this->mergeAction();
        }

        $merge = $this->options['merge'] ?? $this->askForBranch();

        if ($branch === $merge) {
            $this->components->error('You can not merge the same branch 🤷‍♂️...');
        }

        if (!$this->branchExists($branch)) {
            throw new \Exception(sprintf('Branch [%s] does not exist 🤷‍♂️...', $branch));
        }

        $commands = [
            sprintf('checkout %s', $branch),
            sprintf('pull origin %s', $branch),
            sprintf('merge %s', $merge),
            sprintf('push origin %s', $branch),
            sprintf('checkout %s', $merge),
        ];

        $this->components->info('Merging changes 🚀...');

        foreach ($commands as $command) {
            $this->executeCommand($command);
        }

        $this->components->info('Merged successfully 🚀...');
    }

    /**
     * @throws \Exception
     */
    protected function checkoutAction(): void
    {
        $branch = $this->options['branch'] ?? $this->components->ask('Enter the branch name to checkout?');

        if (!$branch) {
            $this->components->error('You must enter the branch name 🤷‍♂️...');
            $this->checkoutAction();
        }

        $commands = [
            sprintf('checkout %s', $branch),
            sprintf('pull origin %s', $branch),
        ];

        $this->components->info('Checking out branch 🚀...');

        foreach ($commands as $command) {
            $this->executeCommand($command);
        }

        $this->components->info('Checked out successfully 🚀...');
    }

    /**
     * @throws \Exception
     */
    protected function branchAction(): void
    {
        $branch = $this->options['branch'] ?? $this->components->ask('Enter the branch name to create?');

        if (!$branch) {
            $this->components->error('You must enter the branch name 🤷‍♂️...');
            $this->branchAction();
        }

        $branchType = $this->command->choice('Select the branch type', ['feature', 'fix', 'hotfix'], 'feature');
        $isFor      = $this->command->choice('Select the branch is for', ['api', 'dashboard', 'other'], 'other');
        $branch     = str($branch)->slug()->replace($branchType, '')->afterLast('/')->value();

        if ($isFor === 'other') {
            $branch = str($branch)->replace($isFor, '')->value();
            $branch = sprintf('%s/%s', $branchType, $branch);
        } else {
            $branch = sprintf('%s/%s/%s', $branchType, $isFor, $branch);
        }

        $commands = [
            sprintf('checkout -b %s', $branch),
            sprintf('push --set-upstream origin %s', $branch),
        ];

        $this->components->info('Creating branch 🚀...');

        foreach ($commands as $command) {
            $this->executeCommand($command);
        }

        $this->components->info('Branch created successfully 🚀...');
    }

    /**
     * @throws \Exception
     */
    protected function pushBranchAction(): void
    {
        $branch = $this->options['branch'] ?? $this->components->ask('Enter the branch name to push?');

        if (!$branch) {
            $this->components->error('You must enter the branch name 🤷‍♂️...');
            $this->pushBranchAction();
        }

        $this->components->info('Pushing branch 🚀...');

        $this->executeCommand(sprintf('push origin %s', $branch));

        $this->components->info('Branch pushed successfully 🚀...');
    }

    /**
     * @throws \Exception
     */
    protected function deleteBranchAction(): void
    {
        $branch = $this->options['branch'] ?? $this->components->ask('Enter the branch name to delete?');

        if (!$branch) {
            $this->components->error('You must enter the branch name 🤷‍♂️...');
            $this->deleteBranchAction();
        }

        $commands = [
            sprintf('branch -d %s', $branch),
            sprintf('push origin --delete %s', $branch),
        ];

        $this->components->info('Deleting branch 🚀...');

        foreach ($commands as $command) {
            $this->executeCommand($command);
        }

        $this->components->info('Branch deleted successfully 🚀...');
    }

    /**
     * @throws \Exception
     */
    protected function logAction(): void
    {
        $this->components->info('Showing the log 🚀...');

        $this->executeCommand('log');

        $this->components->info('Log showed successfully 🚀...');
    }

    /**
     * @throws \Exception
     */
    protected function diffAction(): void
    {
        $this->components->info('Showing the difference 🚀...');

        $this->executeCommand('diff');

        $this->components->info('Difference showed successfully 🚀...');
    }

    /**
     * @throws \Exception
     */
    protected function pullAction(): void
    {
        $branch = $this->options['branch'] ?? $this->askForBranch();

        $this->components->info('Pulling changes 🚀...');

        $this->executeCommand(sprintf('pull origin %s', $branch));

        $this->components->info('Pulled successfully 🚀...');
    }
}
