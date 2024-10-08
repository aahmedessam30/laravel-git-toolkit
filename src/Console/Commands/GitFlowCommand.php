<?php

namespace Ahmedessam\LaravelGitToolkit\Console\Commands;

use Illuminate\Console\Command;
use Ahmedessam\LaravelGitToolkit\Facade\GitFlowToolkit;

class GitFlowCommand extends Command
{
    protected $signature = 'git:flow';
    protected $description = 'Initialize Git Flow branches for the project';

    public function handle()
    {
        GitFlowToolkit::setCommand($this, $this->components)->run();
    }
}
