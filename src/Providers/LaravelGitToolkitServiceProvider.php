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

        $this->mergeConfigFrom(__DIR__ . '/../config/git-toolkit.php', 'git-toolkit-config');
    }

    public function boot(): void
    {
        $this->commands([
            GitCommand::class,
            GitFlowCommand::class,
        ]);

        $this->publishes([
            __DIR__ . '/../config/git-toolkit.php' => config_path('git-toolkit.php'),
        ], 'git-toolkit-config');
    }
}
