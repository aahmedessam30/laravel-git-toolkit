<?php

namespace Ahmedessam\LaravelGitToolkit\Services\Commit;

use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;

class CommitMessageBuilder
{
    protected ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function buildCommitMessage(?string $type = null, ?string $message = null, ?string $currentBranch = null, ?ConsoleIOInterface $io = null): string
    {
        // Use default message if configured
        if ($this->config->shouldUseDefaultMessage()) {
            return $this->buildDefaultMessage($type, $currentBranch);
        }

        // Build interactive message if IO is available
        if ($io) {
            return $this->buildInteractiveMessage($type, $message, $currentBranch, $io);
        }

        // Fallback to default
        return $this->buildDefaultMessage($type, $currentBranch);
    }

    public function buildInteractiveCommitMessage(?string $currentBranch = null, ?ConsoleIOInterface $io = null): string
    {
        if (!$io) {
            return $this->buildDefaultMessage(null, $currentBranch);
        }

        return $this->buildInteractiveMessage(null, null, $currentBranch, $io);
    }

    protected function buildDefaultMessage(?string $type, ?string $currentBranch): string
    {
        $type = $type ?? $this->config->getDefaultCommitType();
        $template = $this->config->get('default_commit_message', 'Update [%s] branch with latest changes.');
        $message = str_contains($template, '%s')
            ? sprintf($template, $currentBranch ?? 'current')
            : $template;

        return $this->formatCommitMessage($type, $message);
    }

    protected function buildInteractiveMessage(?string $type, ?string $message, ?string $currentBranch, ConsoleIOInterface $io): string
    {
        // Get message from user if not provided
        if (!$message) {
            $defaultTemplate = $this->config->get('default_commit_message', 'Update [%s] branch with latest changes.');
            $defaultMessage = sprintf($defaultTemplate, $currentBranch ?? 'current');

            $message = $io->ask(
                sprintf('Enter the commit message, Leave empty to use the default message [%s]', $defaultMessage)
            ) ?: $defaultMessage;
        }

        // Get commit type from user if not provided
        if (!$type) {
            $commitTypes = $this->config->getCommitTypes();
            $type = $io->choice('Enter the commit type', $commitTypes, 'feat');
        }

        return $this->formatCommitMessage($type, $message);
    }

    protected function formatCommitMessage(string $type, string $message): string
    {
        $emoji = $this->config->getCommitEmoji($type);
        return sprintf('%s %s: %s', $emoji, $type, $message);
    }

    public function getAvailableTypes(): array
    {
        return $this->config->getCommitTypes();
    }

    public function validateCommitType(string $type): bool
    {
        return array_key_exists($type, $this->config->getCommitTypes());
    }
}
