<?php

namespace Ahmedessam\LaravelGitToolkit\Providers;

use Illuminate\Support\ServiceProvider;
use Ahmedessam\LaravelGitToolkit\Console\Commands\{GitCommand, GitFlowCommand};
use Ahmedessam\LaravelGitToolkit\Services\{GitToolkit, GitFlow\GitFlowToolkit, GitToolkitConfig};
use Ahmedessam\LaravelGitToolkit\Services\Git\GitRepository;
use Ahmedessam\LaravelGitToolkit\Services\Branch\BranchService;
use Ahmedessam\LaravelGitToolkit\Services\Commit\CommitMessageBuilder;
use Ahmedessam\LaravelGitToolkit\Contracts\{GitRepositoryInterface, ConfigInterface};

class LaravelGitToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind contracts to implementations
        $this->app->singleton(ConfigInterface::class, GitToolkitConfig::class);
        $this->app->singleton(GitRepositoryInterface::class, GitRepository::class);
        
        // Bind services
        $this->app->singleton(BranchService::class);
        $this->app->singleton(CommitMessageBuilder::class);

        // Bind legacy facades
        $this->app->bind('git-toolkit', function () {
            return new GitToolkit();
        });

        $this->app->bind('gitflow-toolkit', function () {
            return new GitFlowToolkit();
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/git-toolkit.php', 'git-toolkit');
    }

    public function boot(): void
    {
        $this->commands([
            GitCommand::class,
            GitFlowCommand::class,
        ]);

        $this->publishes([
            __DIR__ . '/../config/git-toolkit.php' => $this->app->configPath('git-toolkit.php'),
        ], 'git-toolkit-config');
    }
}
