<?php

namespace Tests\Integration\Git;

use Ahmedessam\LaravelGitToolkit\Git\GitRunner;
use Symfony\Component\Process\Process;
use Tests\Integration\IntegrationTestCase;

/**
 * The repository's core.editor is a script that leaves a marker file and fails.
 * If git ever opens an editor through the runner, the marker appears.
 */
class EditorSuppressionTest extends IntegrationTestCase
{
    private string $marker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->marker = dirname($this->repo->path).DIRECTORY_SEPARATOR.'editor-opened';
        $markerForSh = str_replace('\\', '/', $this->marker);
        $this->repo->git('config', 'core.editor', "sh -c 'touch \"{$markerForSh}\"; exit 1'");
    }

    public function test_the_marker_editor_really_opens_without_the_runner(): void
    {
        $this->resolveRebaseConflict();

        $process = new Process(['git', 'rebase', '--continue'], $this->repo->path, ['GIT_EDITOR' => false]);
        $process->run();

        $this->assertFileExists($this->marker, 'Control check: git should have opened the configured editor.');
        $this->assertFalse($process->isSuccessful());
    }

    public function test_rebase_continue_does_not_open_an_editor(): void
    {
        $this->resolveRebaseConflict();

        $result = app(GitRunner::class)->run(['rebase', '--continue']);

        $this->assertTrue($result->successful(), $result->errorOutput);
        $this->assertFileDoesNotExist($this->marker);
        $this->assertSame('feature change', $this->repo->lastCommitSubject());
    }

    public function test_merge_commit_does_not_open_an_editor(): void
    {
        $this->repo->git('switch', '-q', '-c', 'feature');
        $this->repo->commitFile('feature.txt', "feature\n", 'feature work');
        $this->repo->git('switch', '-q', 'main');
        $this->repo->commitFile('main.txt', "main\n", 'main work');

        $result = app(GitRunner::class)->run(['merge', 'feature']);

        $this->assertTrue($result->successful(), $result->errorOutput);
        $this->assertFileDoesNotExist($this->marker);
        $this->assertSame("Merge branch 'feature'", $this->repo->lastCommitSubject());
    }

    public function test_merge_continue_after_conflict_does_not_open_an_editor(): void
    {
        $this->createConflict();
        $this->repo->tryGit('merge', 'feature');
        $this->repo->writeFile('f.txt', "resolved\n");
        $this->repo->git('add', 'f.txt');

        $result = app(GitRunner::class)->run(['merge', '--continue']);

        $this->assertTrue($result->successful(), $result->errorOutput);
        $this->assertFileDoesNotExist($this->marker);
    }

    private function resolveRebaseConflict(): void
    {
        $this->createConflict();
        $this->repo->git('switch', '-q', 'feature');
        $this->repo->tryGit('rebase', 'main');
        $this->repo->writeFile('f.txt', "resolved\n");
        $this->repo->git('add', 'f.txt');
    }

    private function createConflict(): void
    {
        $this->repo->commitFile('f.txt', "base\n", 'base');
        $this->repo->git('switch', '-q', '-c', 'feature');
        $this->repo->commitFile('f.txt', "feature\n", 'feature change');
        $this->repo->git('switch', '-q', 'main');
        $this->repo->commitFile('f.txt', "main\n", 'main change');
    }
}
