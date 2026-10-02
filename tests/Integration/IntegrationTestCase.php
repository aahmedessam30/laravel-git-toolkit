<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Tests\Support\TempRepository;
use Tests\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    protected TempRepository $repo;

    protected bool $withRemote = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = TempRepository::create($this->withRemote);

        config()->set('git-toolkit.repository_path', $this->repo->path);
    }

    protected function tearDown(): void
    {
        $this->repo->delete();

        parent::tearDown();
    }

    /**
     * Run an artisan command non-interactively and capture its exit code and output.
     *
     * @param  array<string, mixed>  $parameters
     * @return array{exit: int, output: string}
     */
    protected function runCommand(string $command, array $parameters = []): array
    {
        $exit = Artisan::call($command, $parameters + ['--no-interaction' => true]);

        return ['exit' => $exit, 'output' => Artisan::output()];
    }

    protected function assertOutputContainsOnce(string $needle, string $output): void
    {
        $this->assertSame(1, substr_count($output, $needle), "Expected [{$needle}] exactly once in:\n{$output}");
    }

    /**
     * Commit a file on another clone of the remote and push it, so this repository falls behind.
     */
    protected function pushFromAnotherClone(string $branch, string $file, string $message): void
    {
        $other = dirname($this->repo->path).DIRECTORY_SEPARATOR.'other-'.bin2hex(random_bytes(3));
        $env = ['GIT_EDITOR' => 'true', 'GIT_TERMINAL_PROMPT' => '0'];
        $run = fn (array $command, string $cwd) => (new Process($command, $cwd, $env))->mustRun();

        $run(['git', 'clone', '-q', '--branch', $branch, $this->repo->remotePath, $other], dirname($other));
        $run(['git', 'config', 'user.name', 'Other'], $other);
        $run(['git', 'config', 'user.email', 'other@example.com'], $other);
        file_put_contents($other.DIRECTORY_SEPARATOR.$file, "{$file} from another clone
");
        $run(['git', 'add', $file], $other);
        $run(['git', 'commit', '-q', '-m', $message], $other);
        $run(['git', 'push', '-q', 'origin', $branch], $other);
    }
}
