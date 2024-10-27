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
        return $this->getConfig('commit_types');
    }

    private function getCommitEmoji(string $commit_type): string
    {
        $emojis = $this->getConfig('commit_emojis');

        return $emojis[$commit_type];
    }

    private function getCommitMessage(): string
    {
        if ($this->getConfig('push_with_default_message')) {
            $default_commit  = $this->getConfig('default_commit_message');
            $default_message = str_contains($default_commit, '%s') ? sprintf($default_commit, $this->getCurrentBranch()) : $default_commit;
            $type            = $this->getConfig('default_commit_type');
            return sprintf('%s %s: %s', $this->getCommitEmoji($type), $type, $default_message);
        }

        $default_message = sprintf($this->getConfig('default_commit_message'), $this->getCurrentBranch());
        $commit          = $this->components->ask(sprintf('Enter the commit message, Leave empty to use the default message [%s]', $default_message)) ?: $default_message;
        $commit_type     =  $this->command->choice('Enter the commit type', $this->getCommitChoices(), 'feat');

        return sprintf('%s %s: %s', $this->getCommitEmoji($commit_type), $commit_type, $commit);
    }

    /**
     * @throws \Exception
     */
    protected function pushLocalCommits(): void
    {
        $this->components->info('Pushing local commits 🚀...');

        $this->executeCommand(sprintf('push origin %s', $this->getCurrentBranch()));

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

        $configDefaultBranch = $this->getConfig('default_branch');
        $shouldPushToDefault = $this->getConfig('push_to_default_branch');

        if ($shouldPushToDefault) {
            $branch = $configDefaultBranch === 'current' ? $this->getCurrentBranch() : $configDefaultBranch;
        } else {
            $branch = $this->options['branch'] ?? $this->askForBranch();
        }

        $commands = [
            sprintf('checkout %s', $branch),
            sprintf('pull origin %s', $branch),
            'add .',
            sprintf('commit -m "%s"', $this->getCommitMessage()),
        ];

        if ($this->getConfig('push_after_commit')) {
            $commands[] = sprintf('push origin %s', $branch);
        }

        if ($this->getConfig('return_to_previous_branch')) {
            $previousBranch = $this->options['return'] ?? $this->getCurrentBranch();
            $commands[]     = sprintf('checkout %s', $previousBranch);
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

        $commands = [
            sprintf('checkout %s', $branch),
            sprintf('pull origin %s', $branch),
            sprintf('merge %s', $merge),
            sprintf('push origin %s', $branch),
        ];

        if ($this->getConfig('delete_after_merge')) {
            $commands[] = sprintf('branch -d %s', $merge);
            $commands[] = sprintf('push origin --delete %s', $merge);
        }

        if ($this->getConfig('return_to_previous_branch') && !$this->getConfig('delete_after_merge')) {
            $commands[] = sprintf('checkout %s', $this->options['return'] ?? $merge);
        }

        $this->components->info('Merging changes 🚀...');

        if (!$this->branchExists($branch)) {
            throw new \Exception(sprintf('Branch [%s] does not exist 🤷‍♂️...', $branch));
        }

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

        $branch = $this->getBranchName($branch);

        $commands = [
            sprintf('checkout -b %s', $branch),
            sprintf('push --set-upstream origin %s', $branch),
        ];

        $this->components->info('Creating branch 🚀...');

        if ($this->branchExists($branch)) {
            throw new \Exception(sprintf('Branch [%s] already exists 🤷‍♂️...', $branch));
        }

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

    /**
     * @throws \Exception
     */
    protected function fetchAction(): void
    {
        $this->components->info('Fetching changes 🚀...');

        $this->executeCommand('fetch');

        $this->components->info('Fetched successfully 🚀...');
    }

    /**
     * @throws \Exception
     */
    protected function resetAction(): void
    {
        $commit = $this->options['commit'] ?? $this->components->ask('Enter the commit hash to reset to?');

        if (!$commit) {
            $this->components->error('You must enter the commit hash 🤷‍♂️...');
            $this->resetAction();
        }

        $commands = [
            sprintf('reset --hard %s', $commit),
        ];

        $this->components->info('Resetting changes 🚀...');

        foreach ($commands as $command) {
            $this->executeCommand($command);
        }

        $this->components->info('Reset successfully 🚀...');
    }
}
