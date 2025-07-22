<?php

namespace Ahmedessam\LaravelGitToolkit\Services\Console;

use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Illuminate\Console\Command;

class ArtisanConsoleIO implements ConsoleIOInterface
{
    public function __construct(
        private Command $command,
        private mixed $components = null
    ) {}

    public function ask(string $question, ?string $default = null): string
    {
        return $this->command->ask($question, $default) ?? '';
    }

    public function choice(string $question, array $choices, ?string $default = null): string
    {
        return $this->command->choice($question, $choices, $default) ?? '';
    }

    public function info(string $message): void
    {
        $this->components->info($message);
    }

    public function success(string $message): void
    {
        $this->components->success($message);
    }

    public function error(string $message): void
    {
        $this->components->error($message);
    }

    public function warn(string $message): void
    {
        $this->components->warn($message);
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $this->components->confirm($question, $default);
    }
}