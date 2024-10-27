# Laravel Git Toolkit

![Laravel Git Toolkit](https://banners.beyondco.de/Laravel%20Git%20Toolkit.png?theme=light&packageManager=composer+require&packageName=ahmedessam%2Flaravel-git-toolkit&pattern=architect&style=style_1&description=Integrate+Git+operations+within+your+Laravel+projects+to+manage+Git+workflows+more+efficiently&md=1&showWatermark=1&fontSize=100px&images=code)

Laravel Git Toolkit is a simple package that integrates Git operations within your Laravel projects. It provides commands to help manage Git workflows more efficiently, such as initializing Git flow branches, committing changes, and other Git-related tasks.

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
- `GIT_RETURN_TO_PREVIOUS_BRANCH` - Whether to return to the previous branch after creating a new branch. Default is `true`.
- `GIT_DELETE_AFTER_MERGE` - Whether to delete the merged branch after merging. Default is `false`.
- `GIT_FLOW_ENABLED` - Whether to enable Git flow branches. Default is `true`.

You can add these environment variables to your `.env` file.

copy the following code to your `.env` file:

```bash
GIT_PUSH_TO_DEFAULT_BRANCH=
GIT_DEFAULT_BRANCH=
GIT_PUSH_WITH_DEFAULT_MESSAGE=
GIT_DEFAULT_COMMIT_TYPE=
GIT_DEFAULT_COMMIT_MESSAGE=
GIT_PUSH_AFTER_COMMIT=
GIT_RETURN_TO_PREVIOUS_BRANCH=
GIT_DELETE_AFTER_MERGE=
GIT_FLOW_ENABLED=
```

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
php artisan git merge
```

## Features

- Initialize Git flow branches
- Commit changes and push them to the current branch
- Create branches for features, fixes, releases, hotfixes or other custom branches
- Pull changes from the remote repository
- Merge branches

## Requirements

- Git should be installed on your machine
- You should have a remote repository set up
- You should have SSH keys set up for your remote repository
- You should have a Laravel project set up
- You should have a basic understanding of Git

## License

The Laravel Git Toolkit is open-sourced software licensed under the [MIT license](https://opensource.org/license/MIT).


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
