<?php

namespace Ahmedessam\LaravelGitToolkit\Actions;

class ActionResult
{
    public function __construct(
        private bool $success,
        private string $message = '',
        private mixed $data = null
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public static function success(string $message = '', mixed $data = null): self
    {
        return new self(true, $message, $data);
    }

    public static function failure(string $message = '', mixed $data = null): self
    {
        return new self(false, $message, $data);
    }
}
