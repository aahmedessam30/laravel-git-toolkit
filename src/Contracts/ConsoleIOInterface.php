<?php

namespace Ahmedessam\LaravelGitToolkit\Contracts;

interface ConsoleIOInterface
{
    /**
     * Ask a question and return the answer
     */
    public function ask(string $question, ?string $default = null): string;

    /**
     * Present a choice and return the selected option
     */
    public function choice(string $question, array $choices, ?string $default = null): string;

    /**
     * Display an info message
     */
    public function info(string $message): void;

    /**
     * Display an error message
     */
    public function error(string $message): void;

    /**
     * Display a warning message
     */
    public function warn(string $message): void;

    /**
     * Confirm a yes/no question
     */
    public function confirm(string $question, bool $default = false): bool;
}
