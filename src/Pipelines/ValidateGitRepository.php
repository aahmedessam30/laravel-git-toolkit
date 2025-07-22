<?php

namespace Ahmedessam\LaravelGitToolkit\Pipelines;

use Ahmedessam\LaravelGitToolkit\Exceptions\GitRepositoryNotFound;
use Closure;

class ValidateGitRepository
{
    public function handle(array $payload, Closure $next)
    {
        if (!is_dir(getcwd() . '/.git')) {
            throw new GitRepositoryNotFound(getcwd());
        }

        return $next($payload);
    }
}
