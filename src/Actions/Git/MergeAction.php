<?php

namespace Ahmedessam\LaravelGitToolkit\Actions\Git;

use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

class MergeAction extends BaseGitAction
{
    private const MERGE_SUCCESS_EMOJI = '✅';
    private const MERGE_FAILURE_EMOJI = '❌';
    private const PUSH_SUCCESS_EMOJI = '📤';
    private const PUSH_WARNING_EMOJI = '⚠️';

    public function execute(array $options, ConsoleIOInterface $io): ActionResult
    {
        try {
            $sourceBranch   = $this->getSourceBranch($options, $io);
            $targetBranches = $this->parseTargetBranches($options);
            $originalBranch = $this->repository->getCurrentBranch();

            $result = $this->executeMergeOperations($sourceBranch, $targetBranches, $io);

            $this->returnToOriginalBranch($originalBranch, $targetBranches, $io);

            return $this->buildActionResult($sourceBranch, $result['merged'], $result['failed']);
        } catch (\Exception $e) {
            return $this->handleMergeFailure($e, $io);
        }
    }

    /**
     * Get source branch from options or user input
     */
    private function getSourceBranch(array $options, ConsoleIOInterface $io): string
    {
        // Check new preferred option name first, then fallback to legacy option
        if (isset($options['source'])) {
            return $options['source'];
        }

        if (isset($options['merge'])) {
            return $options['merge'];
        }

        $currentBranch = $this->repository->getCurrentBranch();
        $sourceBranch = $io->ask('Source branch to merge: [blank for current branch `' . $currentBranch . '`]');

        // If user provided empty input, use current branch
        return empty($sourceBranch) ? $currentBranch : $sourceBranch;
    }

    /**
     * Execute merge operations for all target branches
     */
    private function executeMergeOperations(string $sourceBranch, array $targetBranches, ConsoleIOInterface $io): array
    {
        $mergedBranches = [];
        $failedBranches = [];

        foreach ($targetBranches as $targetBranch) {
            try {
                $this->processSingleMerge($sourceBranch, $targetBranch, $io);
                $mergedBranches[] = $targetBranch;
            } catch (\Exception $e) {
                $this->handleSingleMergeFailure($sourceBranch, $targetBranch, $e, $io);
                $failedBranches[] = ['branch' => $targetBranch, 'error' => $e->getMessage()];
            }
        }

        return [
            'merged' => $mergedBranches,
            'failed' => $failedBranches
        ];
    }

    /**
     * Process merge for a single target branch
     */
    private function processSingleMerge(string $sourceBranch, string $targetBranch, ConsoleIOInterface $io): void
    {
        $io->info("Merging {$sourceBranch} into {$targetBranch}...");

        $this->performMerge($sourceBranch, $targetBranch);
        $this->reportMergeSuccess($sourceBranch, $targetBranch, $io);

        $this->pushBranchSafely($targetBranch, $io);
    }

    /**
     * Perform the actual git merge operation
     */
    private function performMerge(string $sourceBranch, string $targetBranch): void
    {
        $this->repository->executeGitCommand(['checkout', $targetBranch]);
        $this->repository->executeGitCommand(['merge', $sourceBranch]);
    }

    /**
     * Report successful merge
     */
    private function reportMergeSuccess(string $sourceBranch, string $targetBranch, ConsoleIOInterface $io): void
    {
        $io->info(self::MERGE_SUCCESS_EMOJI . " Successfully merged {$sourceBranch} into {$targetBranch}");
    }

    /**
     * Handle single merge failure
     */
    private function handleSingleMergeFailure(string $sourceBranch, string $targetBranch, \Exception $e, ConsoleIOInterface $io): void
    {
        $io->error(self::MERGE_FAILURE_EMOJI . " Failed to merge {$sourceBranch} into {$targetBranch}: " . $e->getMessage());
    }

    /**
     * Return to original branch if needed
     */
    private function returnToOriginalBranch(?string $originalBranch, array $targetBranches, ConsoleIOInterface $io): void
    {
        $lastTargetBranch = end($targetBranches);

        if ($originalBranch && $originalBranch !== $lastTargetBranch) {
            $this->repository->executeGitCommand(['checkout', $originalBranch]);
            $io->info("Returned to original branch: {$originalBranch}");
        }
    }

    /**
     * Handle merge operation failure
     */
    private function handleMergeFailure(\Exception $e, ConsoleIOInterface $io): ActionResult
    {
        $io->error("Merge operation failed: " . $e->getMessage());
        return $this->failure("Merge operation failed: " . $e->getMessage());
    }

    /**
     * Parse target branches from options or default to current branch
     */
    private function parseTargetBranches(array $options): array
    {
        // Check new preferred option name first, then fallback to legacy option
        $branchOption = $options['target'] ?? $options['branch'] ?? null;

        if (empty($branchOption)) {
            return [$this->repository->getCurrentBranch()];
        }

        return $this->parseBranchList($branchOption);
    }

    /**
     * Parse comma-separated branch list with validation
     */
    private function parseBranchList(string $branchOption): array
    {
        // Handle potential shell parsing issues
        $branchOption = trim($branchOption);

        // Check for common shell argument parsing mistakes
        if (str_contains($branchOption, ' ') && !str_contains($branchOption, ',')) {
            throw new \InvalidArgumentException(
                "Branch names with spaces detected. Use quotes around the entire --branch argument: --branch=\"{$branchOption}\""
            );
        }

        $branches = preg_split('/,\s*/', $branchOption);
        $filteredBranches = array_filter(array_map('trim', $branches));

        if (empty($filteredBranches)) {
            throw new \InvalidArgumentException("No valid branch names found in: {$branchOption}");
        }

        return $filteredBranches;
    }

    /**
     * Push a branch to remote repository safely
     */
    private function pushBranchSafely(string $branch, ConsoleIOInterface $io): void
    {
        try {
            $this->repository->executeGitCommand(['push', 'origin', $branch]);
            $io->info(self::PUSH_SUCCESS_EMOJI . " Pushed {$branch} to remote repository");
        } catch (\Exception $e) {
            $io->warn(self::PUSH_WARNING_EMOJI . " Failed to push {$branch}: " . $e->getMessage());
        }
    }

    /**
     * Build the final ActionResult based on merge operations
     */
    private function buildActionResult(string $sourceBranch, array $mergedBranches, array $failedBranches): ActionResult
    {
        $mergedCount = count($mergedBranches);
        $failedCount = count($failedBranches);

        if ($failedCount === 0) {
            return $this->buildSuccessResult($sourceBranch, $mergedBranches);
        }

        if ($mergedCount === 0) {
            return $this->buildFailureResult($sourceBranch, $failedBranches);
        }

        return $this->buildPartialSuccessResult($sourceBranch, $mergedBranches, $failedBranches);
    }

    /**
     * Build success result for all merges succeeded
     */
    private function buildSuccessResult(string $sourceBranch, array $mergedBranches): ActionResult
    {
        $message = $this->formatSuccessMessage($sourceBranch, $mergedBranches);

        return $this->success($message, [
            'source'          => $sourceBranch,
            'merged_branches' => $mergedBranches,
            'total_count'     => count($mergedBranches),
            'success_count'   => count($mergedBranches)
        ]);
    }

    /**
     * Build failure result for all merges failed
     */
    private function buildFailureResult(string $sourceBranch, array $failedBranches): ActionResult
    {
        $message = "Failed to merge {$sourceBranch} into any target branches";

        return $this->failure($message, [
            'source'          => $sourceBranch,
            'failed_branches' => $failedBranches,
            'total_count'     => count($failedBranches),
            'failure_count'   => count($failedBranches)
        ]);
    }

    /**
     * Build partial success result
     */
    private function buildPartialSuccessResult(string $sourceBranch, array $mergedBranches, array $failedBranches): ActionResult
    {
        $successCount = count($mergedBranches);
        $failureCount = count($failedBranches);
        $message = "Partially merged {$sourceBranch}: {$successCount} succeeded, {$failureCount} failed";

        return $this->success($message, [
            'source'          => $sourceBranch,
            'merged_branches' => $mergedBranches,
            'failed_branches' => $failedBranches,
            'total_count'     => $successCount + $failureCount,
            'success_count'   => $successCount,
            'failure_count'   => $failureCount
        ]);
    }

    /**
     * Format success message based on number of branches
     */
    private function formatSuccessMessage(string $sourceBranch, array $branches): string
    {
        $branchCount = count($branches);

        if ($branchCount === 1) {
            return "Successfully merged {$sourceBranch} into {$branches[0]} and pushed to remote";
        }

        $branchList = implode(', ', $branches);
        return "Successfully merged {$sourceBranch} into {$branchCount} branches: {$branchList} and pushed to remote";
    }

    public function getName(): string
    {
        return 'merge';
    }

    public function getDescription(): string
    {
        return 'Merge one branch into one or multiple target branches with auto-push. Use --source and --target for clarity, or --merge and --branch for legacy support.';
    }
}