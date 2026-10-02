<?php

namespace Ahmedessam\LaravelGitToolkit\Git;

use Ahmedessam\LaravelGitToolkit\Exceptions\GitRepositoryNotFound;
use Ahmedessam\LaravelGitToolkit\Exceptions\TtyNotAvailable;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;

final class GitRunner
{
    private ?string $rootPath = null;

    public function __construct(
        private readonly ConfigRepository $config,
    ) {}

    public function root(): string
    {
        if ($this->rootPath !== null) {
            return $this->rootPath;
        }

        $start = $this->config->get('git-toolkit.repository_path') ?: base_path();
        $result = Process::path($start)->run(['git', 'rev-parse', '--show-toplevel']);

        if ($result->failed()) {
            throw new GitRepositoryNotFound($start);
        }

        $this->rootPath = trim($result->output());

        return $this->rootPath;
    }

    /**
     * @param  list<string>  $arguments
     */
    public function run(array $arguments, ?string $input = null): GitResult
    {
        return $this->execute($arguments, $input);
    }

    /**
     * @param  list<string>  $arguments
     */
    public function query(array $arguments): GitResult
    {
        return $this->execute($arguments);
    }

    /**
     * @param  list<string>  $arguments
     */
    public function runInteractive(array $arguments): GitResult
    {
        if (! self::ttySupported()) {
            throw new TtyNotAvailable;
        }

        $result = Process::path($this->root())
            ->forever()
            ->tty()
            ->env([
                'GIT_TERMINAL_PROMPT' => '0',
            ])
            ->run(['git', ...$arguments]);

        return new GitResult($arguments, $result->output(), $result->errorOutput(), $result->exitCode());
    }

    public static function ttySupported(): bool
    {
        return SymfonyProcess::isTtySupported();
    }

    /**
     * @param  list<string>  $arguments
     */
    private function execute(array $arguments, ?string $input = null): GitResult
    {
        $process = Process::path($this->root())
            ->timeout((int) $this->config->get('git-toolkit.timeout', 300))
            ->env([
                'GIT_TERMINAL_PROMPT' => '0',
                'GIT_EDITOR' => 'true',
                'GIT_MERGE_AUTOEDIT' => 'no',
            ]);

        if ($input !== null) {
            $process = $process->input($input);
        }

        $result = $process->run(['git', ...$arguments]);

        return new GitResult($arguments, $result->output(), $result->errorOutput(), $result->exitCode());
    }
}
