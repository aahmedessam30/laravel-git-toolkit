<?php

namespace Ahmedessam\LaravelGitToolkit\Services\Console;

use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Illuminate\Console\Command;

class ArtisanConsoleIO implements ConsoleIOInterface
{
    public function __construct(
        private Command $command
    ) {}

    public function ask(string $question, ?string $default = null): string
    {
        return $this->command->ask($question, $default);
    }

    public function choice(string $question, array $choices, string $default = null): string
    {
        return $this->command->choice($question, $choices, $default);
    }

    public function info(string $message): void
    {
        $this->command->info($message);
    }

    public function error(string $message): void
    {
        $this->command->error($message);
    }

    public function warn(string $message): void
    {
        $this->command->warn($message);
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $this->command->confirm($question, $default);
    }
}
