<?php

namespace Tests\Integration\Git;

use Ahmedessam\LaravelGitToolkit\Exceptions\GitRepositoryNotFound;
use Ahmedessam\LaravelGitToolkit\Git\GitRepository;
use Ahmedessam\LaravelGitToolkit\Git\GitRunner;
use Tests\Integration\IntegrationTestCase;

class GitRepositoryTest extends IntegrationTestCase
{
    private function repository(): GitRepository
    {
        return app(GitRepository::class);
    }

    public function test_root_is_the_top_level_even_from_a_subdirectory(): void
    {
        $this->repo->writeFile('app/sub/.gitkeep', '');
        config()->set('git-toolkit.repository_path', $this->repo->path.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'sub');

        $this->assertSame(
            realpath($this->repo->path),
            realpath(app(GitRunner::class)->root()),
        );
    }

    public function test_root_works_inside_a_linked_worktree(): void
    {
        $worktree = dirname($this->repo->path).DIRECTORY_SEPARATOR.'linked';
        $this->repo->git('worktree', 'add', '-q', '-b', 'linked-branch', $worktree);
        config()->set('git-toolkit.repository_path', $worktree);

        $this->assertSame(realpath($worktree), realpath(app(GitRunner::class)->root()));
        $this->assertSame('linked-branch', $this->repository()->currentBranch());
    }

    public function test_root_throws_outside_a_repository(): void
    {
        $outside = dirname($this->repo->path).DIRECTORY_SEPARATOR.'not-a-repo';
        mkdir($outside);
        config()->set('git-toolkit.repository_path', $outside);

        $this->expectException(GitRepositoryNotFound::class);

        app(GitRunner::class)->root();
    }

    public function test_current_branch_is_never_cached(): void
    {
        $repository = $this->repository();
        $this->assertSame('main', $repository->currentBranch());

        $this->repo->git('switch', '-q', '-c', 'other');

        $this->assertSame('other', $repository->currentBranch());
    }

    public function test_current_branch_is_null_when_detached(): void
    {
        $this->repo->git('switch', '-q', '--detach');

        $this->assertNull($this->repository()->currentBranch());
    }

    public function test_upstream_and_ahead_count(): void
    {
        $repository = $this->repository();
        $this->assertSame('origin/main', $repository->upstream('main'));
        $this->assertSame(0, $repository->aheadCount('main'));

        $this->repo->commitFile('a.txt', "a\n", 'one');
        $this->repo->commitFile('b.txt', "b\n", 'two');
        $this->assertSame(2, $repository->aheadCount('main'));

        $this->repo->git('switch', '-q', '-c', 'feature/no-upstream');
        $this->assertNull($repository->upstream('feature/no-upstream'));
        $this->assertSame(0, $repository->aheadCount('feature/no-upstream'));
    }

    public function test_branch_existence_checks_are_local_and_remote_specific(): void
    {
        $this->repo->git('branch', 'local-only');
        $repository = $this->repository();

        $this->assertTrue($repository->localBranchExists('local-only'));
        $this->assertFalse($repository->remoteBranchExists('local-only', 'origin'));
        $this->assertTrue($repository->localBranchExists('main'));
        $this->assertTrue($repository->remoteBranchExists('main', 'origin'));
        $this->assertFalse($repository->localBranchExists('missing'));
        $this->assertContains('local-only', $repository->localBranches());
        $this->assertContains('main', $repository->localBranches());
    }

    public function test_branch_names_are_never_interpreted_by_a_shell(): void
    {
        $repository = $this->repository();
        $marker = $this->repo->path.DIRECTORY_SEPARATOR.'injected.txt';

        $this->assertFalse($repository->localBranchExists('x & echo INJECTED > injected.txt'));
        $this->assertFalse($repository->localBranchExists('x; touch injected.txt'));
        $this->assertFalse($repository->localBranchExists('$(touch injected.txt)'));
        $this->assertFileDoesNotExist($marker);
    }

    public function test_has_remote(): void
    {
        $this->assertTrue($this->repository()->hasRemote('origin'));
        $this->assertFalse($this->repository()->hasRemote('upstream'));
    }

    public function test_is_clean(): void
    {
        $repository = $this->repository();
        $this->assertTrue($repository->isClean());

        $this->repo->writeFile('new.txt', "new\n");
        $this->assertFalse($repository->isClean());
    }

    public function test_detects_a_merge_in_progress_and_unmerged_files(): void
    {
        $this->createConflictingBranches();
        $this->repo->tryGit('merge', 'feature');

        $repository = $this->repository();
        $this->assertSame('merge', $repository->operationInProgress());
        $this->assertSame(['f.txt'], $repository->unmergedFiles());
    }

    public function test_detects_a_rebase_in_progress(): void
    {
        $this->createConflictingBranches();
        $this->repo->git('switch', '-q', 'feature');
        $this->repo->tryGit('rebase', 'main');

        $this->assertSame('rebase', $this->repository()->operationInProgress());
        $this->assertSame(['f.txt'], $this->repository()->unmergedFiles());
    }

    public function test_no_operation_in_progress(): void
    {
        $this->assertNull($this->repository()->operationInProgress());
        $this->assertSame([], $this->repository()->unmergedFiles());
    }

    public function test_head_commit_and_branch_name_validation(): void
    {
        $this->assertSame(trim($this->repo->git('rev-parse', 'HEAD')), $this->repository()->headCommit());
        $this->assertTrue($this->repository()->isValidBranchName('feature/api/login-page'));
        $this->assertFalse($this->repository()->isValidBranchName('bad..name'));
        $this->assertFalse($this->repository()->isValidBranchName('has space'));
    }

    private function createConflictingBranches(): void
    {
        $this->repo->commitFile('f.txt', "base\n", 'base');
        $this->repo->git('switch', '-q', '-c', 'feature');
        $this->repo->commitFile('f.txt', "feature\n", 'feature change');
        $this->repo->git('switch', '-q', 'main');
        $this->repo->commitFile('f.txt', "main\n", 'main change');
    }
}
