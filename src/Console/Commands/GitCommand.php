<?php

namespace Ahmedessam\LaravelGitToolkit\Console\Commands;

use Illuminate\Console\Command;
use Ahmedessam\LaravelGitToolkit\Facade\GitToolkit;

class GitCommand extends Command
{
    protected $signature = 'git {action} 
    {--branch= : The branch name} 
    {--message= : The commit message} 
    {--type= : The commit type}
    {--merge= : The branch name to merge} 
    {--return= : The branch name to return to}
    {--commit= : The commit to reset to}';

    protected $description = 'Execute git commands from the console';

    public function handle()
    {
        GitToolkit::setCommand($this, $this->components)->run($this->argument('action'), $this->options());
    }
}
