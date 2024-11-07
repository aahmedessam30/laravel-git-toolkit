<?php

return [
    /**
     * The default branch to push to.
     *
     * If set to true, the current branch will be pushed to the default branch.
     */
    'push_to_default_branch' => env('GIT_PUSH_TO_DEFAULT_BRANCH', false),

    /**
     * The default branch.
     *
     * This will be used when pushing changes to the default branch, default is 'current' to push to the current branch.
     */
    'default_branch' => env('GIT_DEFAULT_BRANCH', 'current'),

    /**
     * The default branch to push to.
     *
     * If set to true, the default message will be used when pushing changes.
     */
    'push_with_default_message' => env('GIT_PUSH_WITH_DEFAULT_MESSAGE', false),

    /**
     * The default commit type.
     *
     * This will be used when pushing changes.
     */
    'default_commit_type' => env('GIT_DEFAULT_COMMIT_TYPE', 'feat'),

    /**
     * The default commit message.
     *
     * This will be used when pushing changes, [%s] will be replaced with the current branch name.
     */
    'default_commit_message' => env('GIT_DEFAULT_COMMIT_MESSAGE', 'Update [%s] branch with latest changes.'),

    /**
     * Push after commit.
     *
     * If set to true, the changes will be pushed after committing.
     */
    'push_after_commit' => env('GIT_PUSH_AFTER_COMMIT', true),

    /**
     * Return to the previous branch after pushing.
     *
     * If set to true, the tool will return to the previous branch after pushing changes.
     * If you specify a branch to return to, it will return to it,
     * otherwise, it will return to the current branch before pushing (previous branch).
     */
    'return_to_previous_branch' => env('GIT_RETURN_TO_PREVIOUS_BRANCH', true),

    /**
     * Delete the branch after merging.
     *
     * If set to true, the branch will be deleted after merging.
     */
    'delete_after_merge' => env('GIT_DELETE_AFTER_MERGE', false),

    /**
     * The default branches.
     *
     * This will be used when pushing changes, you can add more branches if you want.
     */
    'default_branches' => ['main', 'master', 'develop', 'staging', 'hotfix'],

    /**
     * The commit types.
     *
     * This will be used when pushing changes, you can add more types if you want.
     */
    'commit_types' => [
        'feat'     => '🚀 Feature: A new feature',
        'fix'      => '🐛 Fix: A bug fix',
        'docs'     => '📝 Docs: Documentation only changes',
        'style'    => '💄 Style: Changes that do not affect the meaning of the code',
        'refactor' => '♻️ Refactor: A code change that neither fixes a bug nor adds a feature',
        'pref'     => '⚡️ Perf: A code change that improves performance',
        'test'     => '🚨 Test: Adding missing tests or correcting existing tests',
        'build'    => '👷 Build: Changes that affect the build system or external dependencies',
        'ci'       => '🔧 CI: Changes to the CI configuration files and scripts',
        'chore'    => '🔧 Chore: Changes to the build process or auxiliary tools and libraries such as documentation generation',
        'revert'   => '⏪ Revert: Revert to a commit',
    ],

    /**
     * The commit emojis.
     *
     * This will be used when pushing changes, you can add more emojis if you want.
     */
    'commit_emojis' => [
        'feat'     => '🚀',
        'fix'      => '🐛',
        'docs'     => '📝',
        'style'    => '💄',
        'refactor' => '♻️',
        'perf'     => '⚡️',
        'test'     => '🚨',
        'build'    => '👷',
        'ci'       => '🔧',
        'chore'    => '🔧',
        'revert'   => '⏪',
    ],

    // New Branch (ex: feature/api/feature-name)

    /**
     * The branch types.
     *
     * This will be used when creating a new branch, you can add more types if you want.
     */
    'branch_types' => [
        'feature' => 'Features',
        'fix'     => 'Bug Fixes',
        'release' => 'Release',
        'support' => 'Support',
    ],

    /**
     * The branch uses.
     *
     * This will be used when creating a new branch, you can add more uses if you want.
     */
    'branch_uses' => [
        'api'       => 'API',
        'dashboard' => 'Dashboard',
        'other'     => 'Other',
    ],

    /**
     * The branch prefixes.
     *
     * This will be used when creating a new branch, you can add more prefixes if you want.
     */
    'branch_prefixes' => [
        'feature' => 'feature',
        'fix'     => 'fix',
        'hotfix'  => 'hotfix',
    ],

    'git_flow' => [
        /**
         * Enable Git Flow.
         *
         * If set to true, the Git Flow will be enabled.
         */
        'enabled' => env('GIT_FLOW_ENABLED', false),

        /**
         * The Git Flow branches.
         *
         * This will be used when initializing the Git Flow branches.
         */
        'branches' => ['develop', 'staging', 'hotfix'],

        /**
         * The optional Git Flow branches.
         *
         * This will be used when initializing the Git Flow branches, you can add more branches if you want.
         */
        'optional_branches' => ['release', 'support', 'feature', 'fix'],

        /**
         * The Git Flow branch prefixes.
         *
         * This will be used when creating a new branch, you can add more prefixes if you want.
         */
        'branch_prefixes' => [
            'feature' => 'feature',
            'fix'     => 'fix',
            'hotfix'  => 'hotfix',
            'release' => 'release',
            'support' => 'support',
        ],
    ],
];
