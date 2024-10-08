<?php

namespace Ahmedessam\LaravelGitToolkit\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static run()
 * @method static setCommand(\Illuminate\Console\Command $command, mixed $components = null)
 */
class GitFlowToolkit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'gitflow-toolkit';
    }
}
