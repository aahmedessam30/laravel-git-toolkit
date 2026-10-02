<?php

namespace Ahmedessam\LaravelGitToolkit\Git;

final class GitRepository
{
    public function __construct(
        private readonly GitRunner $runner,
    ) {}

    public function currentBranch(): ?string
    {
        $result = $this->runner->query(['symbolic-ref', '--quiet', '--short', 'HEAD']);

        if ($result->failed()) {
            return null;
        }

        $branch = $result->trimmedOutput();

        return $branch !== '' ? $branch : null;
    }

    public function upstream(string $branch): ?string
    {
        $result = $this->runner->query(['rev-parse', '--abbrev-ref', '--symbolic-full-name', "{$branch}@{upstream}"]);

        if ($result->failed()) {
            return null;
        }

        $upstream = $result->trimmedOutput();

        return $upstream !== '' ? $upstream : null;
    }

    public function localBranchExists(string $branch): bool
    {
        return $this->runner->query(['show-ref', '--verify', '--quiet', "refs/heads/{$branch}"])->successful();
    }

    public function remoteBranchExists(string $branch, string $remote): bool
    {
        $result = $this->runner->query(['ls-remote', '--exit-code', '--heads', $remote, "refs/heads/{$branch}"]);

        if ($result->exitCode === 0) {
            return true;
        }

        if ($result->exitCode === 2) {
            return false;
        }

        $result->throw();

        return false;
    }

    public function hasRemote(string $remote): bool
    {
        return in_array($remote, $this->runner->query(['remote'])->throw()->lines(), true);
    }

    public function isClean(): bool
    {
        return $this->runner->query(['status', '--porcelain'])->throw()->trimmedOutput() === '';
    }

    public function aheadCount(string $branch): int
    {
        $upstream = $this->upstream($branch);

        if ($upstream === null) {
            return 0;
        }

        return (int) $this->runner->query(['rev-list', '--count', "{$upstream}..{$branch}"])->throw()->trimmedOutput();
    }

    public function operationInProgress(): ?string
    {
        $root = $this->runner->root();
        $mergeHead = $this->resolveGitPath($root, $this->runner->query(['rev-parse', '--git-path', 'MERGE_HEAD'])->throw()->trimmedOutput());
        $rebaseMerge = $this->resolveGitPath($root, $this->runner->query(['rev-parse', '--git-path', 'rebase-merge'])->throw()->trimmedOutput());
        $rebaseApply = $this->resolveGitPath($root, $this->runner->query(['rev-parse', '--git-path', 'rebase-apply'])->throw()->trimmedOutput());

        if (is_dir($rebaseMerge) || is_dir($rebaseApply)) {
            return 'rebase';
        }

        if (file_exists($mergeHead)) {
            return 'merge';
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function unmergedFiles(): array
    {
        return $this->runner->query(['diff', '--name-only', '--diff-filter=U'])->throw()->lines();
    }

    public function headCommit(): string
    {
        return $this->runner->query(['rev-parse', 'HEAD'])->throw()->trimmedOutput();
    }

    /**
     * @return list<string>
     */
    public function localBranches(): array
    {
        return $this->runner->query(['for-each-ref', '--format=%(refname:short)', 'refs/heads'])->throw()->lines();
    }

    public function isValidBranchName(string $name): bool
    {
        return $this->runner->query(['check-ref-format', '--branch', $name])->successful();
    }

    private function resolveGitPath(string $root, string $path): string
    {
        if ($path === '') {
            return $root;
        }

        if (preg_match('/^(?:[A-Za-z]:[\\\\\\/]|[\\\\\\/]{2}|\/)/', $path) === 1) {
            return $path;
        }

        return rtrim($root, '\\/').DIRECTORY_SEPARATOR.str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }
}
