<?php

namespace Ahmedessam\LaravelGitToolkit\Console\Commands;

use Illuminate\Console\Command;
use Ahmedessam\LaravelGitToolkit\Actions\GitActionRegistry;
use Ahmedessam\LaravelGitToolkit\Services\Console\ArtisanConsoleIO;
use Ahmedessam\LaravelGitToolkit\Exceptions\UnsupportedAction;

class GitCommand extends Command
{
    protected $signature = 'git {action?} 
    {--branch= : The branch name} 
    {--message= : The commit message} 
    {--type= : The commit type}
    {--merge= : The branch name to merge} 
    {--return= : The branch name to return to}
    {--commit= : The commit to reset to}
    {--source= : The source branch to merge from}
    {--target= : The target branch(es) to merge into (comma-separated)}';

    protected $description = 'Execute git commands from the console';

    public function __construct(
        protected GitActionRegistry $actionRegistry
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $action    = $this->argument('action');
            $consoleIO = new ArtisanConsoleIO($this, $this->components);

            // Show available actions if none provided
            if (!$action) {
                $this->displayAvailableActions();
                return self::SUCCESS;
            }

            // Execute the action through the registry
            $gitAction = $this->actionRegistry->resolve($action);
            $result    = $gitAction->execute($this->options(), $consoleIO);

            if ($result->isSuccess()) {
                $this->components->info($result->getMessage());
                return self::SUCCESS;
            } else {
                $this->components->error($result->getMessage());
                return self::FAILURE;
            }
        } catch (UnsupportedAction $e) {
            $this->components->error($e->getMessage());
            $this->displayAvailableActions();
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->components->error('An error occurred: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    protected function displayAvailableActions(): void
    {
        $this->components->info('Available Git Actions:');

        $actions = $this->actionRegistry->getAllActions();

        $this->table(
            ['Action', 'Description'],
            collect($actions)->map(function ($actionClass, $actionName) {
                $action = app($actionClass);
                return [$actionName, $action->getDescription()];
            })->toArray()
        );

        $this->components->info('Usage: php artisan git {action} [options]');
    }
}
