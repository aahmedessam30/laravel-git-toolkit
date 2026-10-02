<?php

namespace Ahmedessam\LaravelGitToolkit\Console\Commands;

use Ahmedessam\LaravelGitToolkit\Facade\GitFlowToolkit;
use Illuminate\Console\Command;

class GitFlowCommand extends Command
{
    protected $signature = 'git:flow';

    protected $description = 'Initialize Git Flow branches for the project';

    public function handle()
    {
        GitFlowToolkit::setCommand($this, $this->components)->run();
    }
}
