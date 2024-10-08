<?php

namespace Ahmedessam\LaravelGitToolkit\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static run(array|bool|string|null $argument, array $options)
 * @method static setCommand(\Illuminate\Console\Command $command, mixed $components = null)
 */
class GitToolkit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'git-toolkit';
    }
}
