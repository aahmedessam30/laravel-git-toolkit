<?php

namespace Ahmedessam\LaravelGitToolkit\Actions\Git;

use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

class RebaseAction extends BaseGitAction
{
    public function execute(array $options, ConsoleIOInterface $io): ActionResult
    {
        try {
            $interactive = $options['interactive'] ?? false;
            $onto        = $options['onto'] ?? null;
            $abort       = $options['abort'] ?? false;
            $continue    = $options['continue'] ?? false;
            $skip        = $options['skip'] ?? false;

            // Handle rebase control options
            if ($abort) {
                return $this->handleAbort($io);
            }

            if ($continue) {
                return $this->handleContinue($io);
            }

            if ($skip) {
                return $this->handleSkip($io);
            }

            // For regular rebase operations, get the branch
            $branch = $options['branch'] ?? $this->repository->getCurrentBranch();

            // Validate repository state before rebase
            if ($this->repository->hasUncommittedChanges()) {
                $io->error("Cannot rebase with uncommitted changes. Please commit or stash your changes first.");
                return $this->failure("Rebase failed: Uncommitted changes detected");
            }

            // Perform rebase operation
            return $this->performRebase($branch, $interactive, $onto, $io);
        } catch (\Exception $e) {
            $io->error("Rebase failed: " . $e->getMessage());
            return $this->failure("Rebase failed: " . $e->getMessage());
        }
    }

    /**
     * Perform the actual rebase operation
     */
    private function performRebase(string $branch, bool $interactive, ?string $onto, ConsoleIOInterface $io): ActionResult
    {
        $command = ['rebase'];

        // Add interactive flag if requested
        if ($interactive) {
            $command[] = '--interactive';
        }

        // Handle onto option for advanced rebasing
        if ($onto) {
            $command[] = '--onto';
            $command[] = $onto;
            $command[] = $branch;
        } else {
            // Standard rebase onto the specified branch
            $command[] = $branch;
        }

        $io->info("Starting rebase operation...");

        try {
            $result = $this->repository->executeGitCommand($command);

            if ($interactive) {
                $io->info("Interactive rebase completed. Please check the result.");
            } else {
                $io->info("Rebase completed successfully.");
            }

            return $this->success("Successfully rebased onto {$branch}", [
                'branch' => $branch,
                'interactive' => $interactive,
                'onto' => $onto,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            // Check if it's a rebase conflict
            if (str_contains($e->getMessage(), 'conflict') || str_contains($e->getMessage(), 'CONFLICT')) {
                $io->warn("Rebase conflicts detected. Please resolve conflicts and run:");
                $io->info("  php artisan git rebase --continue  (after resolving conflicts)");
                $io->info("  php artisan git rebase --abort     (to cancel rebase)");
                $io->info("  php artisan git rebase --skip      (to skip current commit)");

                return $this->failure("Rebase conflicts require manual resolution", [
                    'conflicts' => true,
                    'branch' => $branch
                ]);
            }

            throw $e;
        }
    }

    /**
     * Handle rebase abort
     */
    private function handleAbort(ConsoleIOInterface $io): ActionResult
    {
        $io->info("Aborting rebase operation...");

        try {
            $result = $this->repository->executeGitCommand(['rebase', '--abort']);
            $io->info("Rebase aborted successfully.");

            return $this->success("Rebase operation aborted", [
                'action' => 'abort',
                'output' => $result
            ]);
        } catch (\Exception $e) {
            $io->error("Failed to abort rebase: " . $e->getMessage());
            return $this->failure("Rebase abort failed: " . $e->getMessage());
        }
    }

    /**
     * Handle rebase continue
     */
    private function handleContinue(ConsoleIOInterface $io): ActionResult
    {
        // Check if there are still uncommitted changes (conflicts not resolved)
        if ($this->repository->hasUncommittedChanges()) {
            $io->error("Please resolve all conflicts and stage your changes before continuing.");
            $io->info("Use 'git add <file>' to stage resolved files, then run rebase --continue again.");
            return $this->failure("Rebase continue failed: Unresolved conflicts");
        }

        $io->info("Continuing rebase operation...");

        try {
            $result = $this->repository->executeGitCommand(['rebase', '--continue']);
            $io->info("Rebase continued successfully.");

            return $this->success("Rebase operation continued", [
                'action' => 'continue',
                'output' => $result
            ]);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'conflict') || str_contains($e->getMessage(), 'CONFLICT')) {
                $io->warn("Additional conflicts detected during continue.");
                return $this->failure("Rebase continue failed: Additional conflicts require resolution");
            }

            throw $e;
        }
    }

    /**
     * Handle rebase skip
     */
    private function handleSkip(ConsoleIOInterface $io): ActionResult
    {
        $io->info("Skipping current commit in rebase...");

        try {
            $result = $this->repository->executeGitCommand(['rebase', '--skip']);
            $io->info("Current commit skipped successfully.");

            return $this->success("Rebase skip completed", [
                'action' => 'skip',
                'output' => $result
            ]);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'conflict') || str_contains($e->getMessage(), 'CONFLICT')) {
                $io->warn("Additional conflicts detected after skip.");
                return $this->failure("Rebase skip failed: Additional conflicts require resolution");
            }

            throw $e;
        }
    }

    public function getName(): string
    {
        return 'rebase';
    }

    public function getDescription(): string
    {
        return 'Rebase commits onto another branch';
    }
}
