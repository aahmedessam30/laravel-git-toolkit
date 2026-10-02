<?php

namespace Tests\Support;

use RuntimeException;
use Symfony\Component\Process\Process;

final class TempRepository
{
    private function __construct(
        public readonly string $path,
        public readonly string $remotePath,
        private readonly string $baseDirectory,
    ) {}

    public static function create(bool $withRemote = true): self
    {
        $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'git-toolkit-tests'.DIRECTORY_SEPARATOR.bin2hex(random_bytes(6));
        $work = $base.DIRECTORY_SEPARATOR.'work';
        $remote = $base.DIRECTORY_SEPARATOR.'remote.git';

        mkdir($work, 0777, true);

        $repository = new self($work, $remote, $base);

        $repository->git('init', '-q', '-b', 'main');
        $repository->git('config', 'user.name', 'Test User');
        $repository->git('config', 'user.email', 'test@example.com');
        $repository->git('config', 'core.autocrlf', 'false');
        $repository->git('config', 'commit.gpgsign', 'false');
        $repository->commitFile('README.md', "initial\n", 'initial commit');

        if ($withRemote) {
            self::runIn($base, ['git', 'init', '-q', '--bare', '-b', 'main', $remote]);
            $repository->git('remote', 'add', 'origin', $remote);
            $repository->git('push', '-q', '-u', 'origin', 'main');
        }

        return $repository;
    }

    public function git(string ...$arguments): string
    {
        return self::runIn($this->path, ['git', ...$arguments]);
    }

    public function tryGit(string ...$arguments): Process
    {
        $process = new Process(['git', ...$arguments], $this->path, ['GIT_EDITOR' => 'true', 'GIT_TERMINAL_PROMPT' => '0']);
        $process->run();

        return $process;
    }

    public function remoteGit(string ...$arguments): string
    {
        return self::runIn($this->remotePath, ['git', ...$arguments]);
    }

    public function writeFile(string $name, string $contents): void
    {
        $file = $this->path.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $name);

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }

        file_put_contents($file, $contents);
    }

    public function commitFile(string $name, string $contents, string $message): string
    {
        $this->writeFile($name, $contents);
        $this->git('add', '--', $name);
        $this->git('commit', '-q', '-m', $message);

        return trim($this->git('rev-parse', 'HEAD'));
    }

    public function currentBranch(): string
    {
        return trim($this->git('branch', '--show-current'));
    }

    public function lastCommitSubject(string $ref = 'HEAD'): string
    {
        return trim($this->git('log', '-1', '--format=%s', $ref));
    }

    /** @return list<string> */
    public function localBranches(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", $this->git('for-each-ref', '--format=%(refname:short)', 'refs/heads')))));
    }

    /** @return list<string> */
    public function remoteBranches(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", $this->remoteGit('for-each-ref', '--format=%(refname:short)', 'refs/heads')))));
    }

    public function fileExists(string $name): bool
    {
        return file_exists($this->path.DIRECTORY_SEPARATOR.$name);
    }

    public function delete(): void
    {
        self::removeDirectory($this->baseDirectory);
    }

    /** @param  list<string>  $command */
    private static function runIn(string $directory, array $command): string
    {
        $process = new Process($command, $directory, ['GIT_EDITOR' => 'true', 'GIT_TERMINAL_PROMPT' => '0']);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                "Test git command failed: %s\n%s%s",
                implode(' ', $command),
                $process->getOutput(),
                $process->getErrorOutput(),
            ));
        }

        return $process->getOutput();
    }

    private static function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            @chmod($item->getPathname(), 0777);
            $item->isDir() && ! $item->isLink() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($directory);
    }
}
