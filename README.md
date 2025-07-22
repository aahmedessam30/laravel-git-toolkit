# Laravel Git Toolkit

![Laravel Git Toolkit](https://banners.beyondco.de/Laravel%20Git%20Toolkit.png?theme=light&packageManager=composer+require&packageName=ahmedessam%2Flaravel-git-toolkit&pattern=architect&style=style_1&description=Integrate+Git+operations+within+your+Laravel+projects+to+manage+Git+workflows+more+efficiently&md=1&showWatermark=1&fontSize=100px&images=code)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ahmedessam/laravel-git-toolkit.svg?style=flat-square)](https://packagist.org/packages/ahmedessam/laravel-git-toolkit)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/your-username/laravel-git-toolkit/run-tests?label=tests)](https://github.com/your-username/laravel-git-toolkit/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/your-username/laravel-git-toolkit/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/your-username/laravel-git-toolkit/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ahmedessam/laravel-git-toolkit.svg?style=flat-square)](https://packagist.org/packages/ahmedessam/laravel-git-toolkit)

Laravel Git Toolkit is a comprehensive package that integrates Git operations within your Laravel projects. It provides commands and services to help manage Git workflows more efficiently, with support for Git Flow, conventional commits, and modern Laravel architecture patterns.

## Installation

You can install the package via Composer:

```bash
composer require ahmedessam/laravel-git-toolkit
```

You can publish the configuration file with:

```bash
php artisan vendor:publish --tag=git-toolkit-config
```

## Environment Variables

The package uses the following environment variables:

- `GIT_PUSH_TO_DEFAULT_BRANCH` - Whether to push changes to the default branch. Default is `false`.
- `GIT_DEFAULT_BRANCH` - The default branch to push changes to. Default is `current`.
- `GIT_PUSH_WITH_DEFAULT_MESSAGE` - Whether to push changes with the default commit message. Default is `false`.
- `GIT_DEFAULT_COMMIT_TYPE` - The default commit type. Default is `feat`.
- `GIT_DEFAULT_COMMIT_MESSAGE` - The default commit message. Default is `Update [%s] branch with latest changes.`.
- `GIT_PUSH_AFTER_COMMIT` - Whether to push changes after committing. Default is `true`.
- `GIT_RETURN_TO_PREVIOUS_BRANCH` - Whether to return to the previous branch after committing. Default is `true`.
- `GIT_DELETE_AFTER_MERGE` - Whether to delete the merged branch after merging. Default is `false`.
- `GIT_FLOW_ENABLED` - Whether to enable Git flow branches. Default is `true`.

You can add these environment variables to your `.env` file.

copy the following code to your `.env` file:

```env
GIT_PUSH_TO_DEFAULT_BRANCH=false
GIT_DEFAULT_BRANCH='current'
GIT_PUSH_WITH_DEFAULT_MESSAGE=false
GIT_DEFAULT_COMMIT_TYPE='feat'
GIT_DEFAULT_COMMIT_MESSAGE='Update [%s] branch with latest changes.'
GIT_PUSH_AFTER_COMMIT=true
GIT_RETURN_TO_PREVIOUS_BRANCH=true
GIT_DELETE_AFTER_MERGE=false
GIT_FLOW_ENABLED=true
```

## Notes

- The package uses the `git` command to perform Git operations.
- The package asks few questions to get the required data to perform the operation.
- The package provides a list of flags to specify the required data to perform the operation.
- The package uses the following flags:
    - `--branch` flag to specify the branch name.
    - `--message` flag to specify the commit message.
    - `--type` flag to specify the commit type.
    - `--merge` flag to specify the branch to merge.
    - `--return` flag to specify whether to return to the previous branch.
    - `--commit` flag to specify the commit hash to reset to.

## Usage

To initialize git flow branches, run the following command:

```bash
php artisan git:flow
```

This command will create the following main branches:

- `main`
- `develop`
- `staging`
- `hotfix`

You can also create feature, fix, and release branches, package ask for theses branches names.

To commit changes, run the following command:

```bash
php artisan git push
```

This command will add all changes, commit them, and push them to the current branch.

To create a new branch, run the following command:

```bash
php artisan git branch
```

This command will create a new branch based on the type you choose.

To pull changes from the remote repository, run the following command:

```bash
php artisan git pull
```

This command will pull changes from the remote repository to the current branch or the branch you specify.

To merge branches, run the following command:

```bash
php artisan git merge --merge=<source-branch> --branch=<target-branch>
```

### Flags 
- `--merge` flag to specify the branch to merge from.
- `--branch` flag to specify the branch or branches sperated by comma or space to merge into.
- **ex:** `php artisan git merge --merge=feature --branch=develop,main`

This command will merge the specified branch into the branch you specify.

To delete a branch, run the following command:

```bash
php artisan git delete-branch
```

This command will delete the specified branch.

To Push new branch to remote repository, run the following command:

```bash
php artisan git push-branch
```

To Fetch changes from the remote repository, run the following command:

```bash
php artisan git fetch
```

This command will fetch changes from the remote repository.

To Rebase changes from the remote repository, run the following command:

```bash
php artisan git rebase
```

This command will rebase changes from the remote repository.

To Reset changes from the remote repository, run the following command:

```bash
php artisan git reset --commit=<commit-hash>
```

This command will reset changes from the remote repository.

## Features

✨ **Core Git Operations**
- Initialize Git flow branches with customizable naming
- Commit changes with conventional commit messages
- Create branches for features, fixes, releases, hotfixes, or custom types
- Pull, push, merge, delete, fetch, rebase, and reset operations

🏗️ **Modern Architecture**
- Contract-based service architecture
- Event-driven operations for extensibility
- Pipeline validation system
- Comprehensive exception handling
- Dependency injection throughout

🎯 **Laravel Integration**
- Laravel 9-12 compatibility
- Artisan command integration
- Service provider with automatic discovery
- Configuration management
- Event system integration

## Requirements

- PHP 8.2 or higher
- Laravel 9.x, 10.x, 11.x, or 12.x
- Git installed and configured
- Remote repository access (SSH recommended)

## Architecture

### Contracts
The package uses contract interfaces for clean dependency injection:

```php
// GitRepositoryInterface for Git operations
app(GitRepositoryInterface::class)->getCurrentBranch();

// ConfigInterface for configuration access  
app(ConfigInterface::class)->get('commit_types');
```

### Events
Listen to Git operations in your application:

```php
Event::listen(BranchCreated::class, function ($event) {
    Log::info("Branch created: {$event->branchName}");
});

Event::listen(CommitPushed::class, function ($event) {
    Log::info("Commit pushed: {$event->commitHash}");
});
```

### Services
Access core services directly:

```php
// Branch operations
$branchService = app(BranchService::class);
$branchName = $branchService->sanitizeBranchName('feature/my-feature');

// Commit message building
$commitBuilder = app(CommitMessageBuilder::class);
$message = $commitBuilder->buildCommitMessage('feat', 'Add new feature');

// Git repository operations
$gitRepo = app(GitRepository::class);
$branches = $gitRepo->getAllBranches();
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details on how to contribute.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## License

The Laravel Git Toolkit is open-sourced software licensed under the [MIT license](LICENSE).


## Author

- **Ahmed Essam**
    - [GitHub Profile](https://github.com/aahmedessam30)
    - [Packagist](https://packagist.org/packages/ahmedessam/api-versionizer)
    - [LinkedIn](https://www.linkedin.com/in/aahmedessam30)
    - [Email](mailto:aahmedessam30@gmail.com)


## Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

## Issues
If you find any issues with the package or have any questions, please feel free to open an issue on the GitHub repository.

Enjoy using Laravel Git Toolkit! 🚀
