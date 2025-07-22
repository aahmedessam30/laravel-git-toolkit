<?php

namespace Ahmedessam\LaravelGitToolkit\Providers;

use Illuminate\Support\ServiceProvider;
use Ahmedessam\LaravelGitToolkit\Console\Commands\{GitCommand, GitFlowCommand};
use Ahmedessam\LaravelGitToolkit\Services\{GitToolkit, GitFlow\GitFlowToolkit, GitToolkitConfig};
use Ahmedessam\LaravelGitToolkit\Services\Git\GitRepository;
use Ahmedessam\LaravelGitToolkit\Services\Branch\BranchService;
use Ahmedessam\LaravelGitToolkit\Services\Commit\CommitMessageBuilder;
use Ahmedessam\LaravelGitToolkit\Contracts\{GitRepositoryInterface, ConfigInterface};
use Ahmedessam\LaravelGitToolkit\Actions\{GitActionRegistry};
use Ahmedessam\LaravelGitToolkit\Actions\Git\{
    PushAction,
    PullAction,
    BranchAction,
    MergeAction,
    CheckoutAction,
    FetchAction
};

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

        // Git Actions
        $this->app->bind(PushAction::class);
        $this->app->bind(PullAction::class);
        $this->app->bind(BranchAction::class);
        $this->app->bind(MergeAction::class);
        $this->app->bind(CheckoutAction::class);
        $this->app->bind(FetchAction::class);

        // Action Registry
        $this->app->singleton(GitActionRegistry::class, function ($app) {
            $registry = new GitActionRegistry();

            // Register all git actions
            $registry->register('push', PushAction::class);
            $registry->register('pull', PullAction::class);
            $registry->register('branch', BranchAction::class);
            $registry->register('merge', MergeAction::class);
            $registry->register('checkout', CheckoutAction::class);
            $registry->register('fetch', FetchAction::class);

            return $registry;
        });

        // Bind legacy facades
        // Remove old facade binding as we now use dependency injection
        // $this->app->bind('git-toolkit', function () {
        //     return new GitToolkit();
        // });

        $this->app->bind('gitflow-toolkit', function () {
            return new GitFlowToolkit(
                $this->app->make(GitRepositoryInterface::class),
                $this->app->make(ConfigInterface::class)
            );
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
