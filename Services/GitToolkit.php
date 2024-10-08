<?php

namespace Ahmedessam\LaravelGitToolkit\Services;

class GitToolkit extends GitCommands
{
    protected string $action;
    protected array $options = [];
    protected array $actions = [
        'pull',
        'push',
        'merge',
        'checkout',
        'branch',
        'push-branch',
        'delete-branch',
        'log',
        'diff'
    ];

    /**
     * @throws \Exception
     */
    public function run(string $action, array $options): void
    {
        $this->action  = $action;
        $this->options = $options;

        $this->execute();
    }

    /**
     * @throws \Exception
     */
    protected function execute(): void
    {
        try {
            $this->validateAction()->performAction();
        } catch (\Exception $e) {
            $this->components->error($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    protected function validateAction(): static
    {
        if (!in_array($this->action, $this->actions)) {
            throw new \Exception(sprintf("Action [%s] is not supported 🤷‍♂️.", $this->action));
        }

        if (!is_dir(base_path('.git'))) {
            throw new \Exception("This is not a git repository 🤷‍♂️, please initialize Git first.");
        }

        return $this;
    }

    protected function performAction(): void
    {
        $method = sprintf('%sAction', str($this->action)->camel()->title());
        $this->$method();
    }
}
