<?php

namespace Tests\Unit\Git;

use Ahmedessam\LaravelGitToolkit\Exceptions\GitRepositoryNotFound;
use Ahmedessam\LaravelGitToolkit\Exceptions\TtyNotAvailable;
use Ahmedessam\LaravelGitToolkit\Git\GitResult;
use Ahmedessam\LaravelGitToolkit\Git\GitRunner;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class GitRunnerTest extends TestCase
{
    private const ROOT = '/work/repo';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('git-toolkit.repository_path', '/work/repo/app');
        config()->set('git-toolkit.timeout', 120);
    }

    private function fakeGit(array $results = []): void
    {
        Process::fake(function (PendingProcess $process) use ($results) {
            if ($process->command === ['git', 'rev-parse', '--show-toplevel']) {
                return Process::result(self::ROOT."\n");
            }

            $key = implode(' ', array_slice($process->command, 1));

            return $results[$key] ?? Process::result('');
        });
    }

    public function test_root_is_resolved_with_rev_parse_from_the_configured_path(): void
    {
        $this->fakeGit();

        $this->assertSame(self::ROOT, app(GitRunner::class)->root());

        Process::assertRan(fn (PendingProcess $process) => $process->command === ['git', 'rev-parse', '--show-toplevel']
            && $process->path === '/work/repo/app');
    }

    public function test_root_falls_back_to_the_base_path(): void
    {
        config()->set('git-toolkit.repository_path', null);
        $this->fakeGit();

        app(GitRunner::class)->root();

        Process::assertRan(fn (PendingProcess $process) => $process->command === ['git', 'rev-parse', '--show-toplevel']
            && $process->path === base_path());
    }

    public function test_root_throws_when_the_path_is_not_inside_a_repository(): void
    {
        Process::fake([
            '*' => Process::result('', 'fatal: not a git repository (or any of the parent directories): .git', 128),
        ]);

        $this->expectException(GitRepositoryNotFound::class);

        app(GitRunner::class)->root();
    }

    public function test_root_is_resolved_once_per_runner(): void
    {
        $this->fakeGit();
        $runner = app(GitRunner::class);

        $runner->query(['status', '--porcelain']);
        $runner->query(['status', '--porcelain']);

        Process::assertRanTimes(fn (PendingProcess $process) => $process->command === ['git', 'rev-parse', '--show-toplevel'], 1);
    }

    public function test_run_executes_an_argv_array_in_the_repository_root(): void
    {
        $this->fakeGit(['commit -m fix 100% of "quoted" bugs! & echo no' => Process::result("[main abc123] done\n")]);

        $result = app(GitRunner::class)->run(['commit', '-m', 'fix 100% of "quoted" bugs! & echo no']);

        $this->assertInstanceOf(GitResult::class, $result);
        $this->assertTrue($result->successful());
        $this->assertSame(['commit', '-m', 'fix 100% of "quoted" bugs! & echo no'], $result->command);
        $this->assertSame("[main abc123] done\n", $result->output);

        Process::assertRan(fn (PendingProcess $process) => $process->command === ['git', 'commit', '-m', 'fix 100% of "quoted" bugs! & echo no']
            && $process->path === self::ROOT);
    }

    public function test_run_sets_a_non_interactive_environment_and_the_configured_timeout(): void
    {
        $this->fakeGit();

        app(GitRunner::class)->run(['merge', 'feature']);

        Process::assertRan(function (PendingProcess $process) {
            return $process->command === ['git', 'merge', 'feature']
                && ($process->environment['GIT_TERMINAL_PROMPT'] ?? null) === '0'
                && ($process->environment['GIT_EDITOR'] ?? null) === 'true'
                && ($process->environment['GIT_MERGE_AUTOEDIT'] ?? null) === 'no'
                && $process->timeout === 120;
        });
    }

    public function test_query_uses_the_same_environment(): void
    {
        $this->fakeGit();

        app(GitRunner::class)->query(['status', '--porcelain']);

        Process::assertRan(fn (PendingProcess $process) => $process->command === ['git', 'status', '--porcelain']
            && $process->path === self::ROOT
            && ($process->environment['GIT_EDITOR'] ?? null) === 'true');
    }

    public function test_run_passes_input_on_stdin(): void
    {
        $this->fakeGit();

        app(GitRunner::class)->run(['commit', '-F', '-'], "feat: message from stdin\n");

        Process::assertRan(fn (PendingProcess $process) => $process->command === ['git', 'commit', '-F', '-']
            && $process->input === "feat: message from stdin\n");
    }

    public function test_a_failed_command_returns_a_failed_result_without_throwing(): void
    {
        $this->fakeGit(['push origin main' => Process::result('', "fatal: 'origin' does not appear to be a git repository\n", 128)]);

        $result = app(GitRunner::class)->run(['push', 'origin', 'main']);

        $this->assertTrue($result->failed());
        $this->assertSame(128, $result->exitCode);
        $this->assertSame("fatal: 'origin' does not appear to be a git repository\n", $result->errorOutput);
    }

    public function test_tty_support_mirrors_symfony_process(): void
    {
        $this->assertSame(\Symfony\Component\Process\Process::isTtySupported(), GitRunner::ttySupported());
    }

    public function test_interactive_run_requires_a_tty(): void
    {
        if (GitRunner::ttySupported()) {
            $this->markTestSkipped('A TTY is available in this environment.');
        }

        $this->fakeGit();

        $this->expectException(TtyNotAvailable::class);

        app(GitRunner::class)->runInteractive(['rebase', '--interactive', 'main']);
    }
}
