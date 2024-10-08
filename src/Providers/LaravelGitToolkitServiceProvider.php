<?php

namespace Ahmedessam\LaravelGitToolkit\Providers;

use Illuminate\Support\ServiceProvider;
use Ahmedessam\LaravelGitToolkit\Console\Commands\{GitCommand, GitFlowCommand};
use Ahmedessam\LaravelGitToolkit\Services\{GitToolkit, GitFlow\GitFlowToolkit};

class LaravelGitToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('git-toolkit', function () {
            return new GitToolkit();
        });

        $this->app->bind('gitflow-toolkit', function () {
            return new GitFlowToolkit();
        });
    }

    public function boot(): void
    {
        $this->commands([
            GitCommand::class,
            GitFlowCommand::class,
        ]);
    }
}
