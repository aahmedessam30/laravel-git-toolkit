<?php

namespace Tests\Unit\Actions\Git;

use Tests\TestCase;
use Mockery;
use Ahmedessam\LaravelGitToolkit\Actions\Git\RebaseAction;
use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

class RebaseActionTest extends TestCase
{
    private RebaseAction $rebaseAction;
    private $mockRepository;
    private $mockConfig;
    private $mockConsoleIO;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(GitRepositoryInterface::class);
        $this->mockConfig = Mockery::mock(ConfigInterface::class);
        $this->mockConsoleIO = Mockery::mock(ConsoleIOInterface::class);

        $this->rebaseAction = new RebaseAction($this->mockRepository, $this->mockConfig);
    }

    public function test_get_name()
    {
        $this->assertEquals('rebase', $this->rebaseAction->getName());
    }

    public function test_get_description()
    {
        $this->assertEquals('Rebase commits onto another branch', $this->rebaseAction->getDescription());
    }

    public function test_execute_with_basic_rebase()
    {
        $options = ['branch' => 'main'];

        $this->mockRepository->shouldReceive('hasUncommittedChanges')
            ->once()
            ->andReturn(false);

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['rebase', 'main'])
            ->once()
            ->andReturn('Successfully rebased');

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Starting rebase operation...')
            ->once();

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Rebase completed successfully.')
            ->once();

        $result = $this->rebaseAction->execute($options, $this->mockConsoleIO);

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Successfully rebased onto main', $result->getMessage());
    }

    public function test_execute_with_uncommitted_changes_fails()
    {
        $options = ['branch' => 'main'];

        $this->mockRepository->shouldReceive('hasUncommittedChanges')
            ->once()
            ->andReturn(true);

        $this->mockConsoleIO->shouldReceive('error')
            ->with('Cannot rebase with uncommitted changes. Please commit or stash your changes first.')
            ->once();

        $result = $this->rebaseAction->execute($options, $this->mockConsoleIO);

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Rebase failed: Uncommitted changes detected', $result->getMessage());
    }

    public function test_execute_with_abort_option()
    {
        $options = ['abort' => true];

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['rebase', '--abort'])
            ->once()
            ->andReturn('Rebase aborted');

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Aborting rebase operation...')
            ->once();

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Rebase aborted successfully.')
            ->once();

        // Add error expectation in case of exception
        $this->mockConsoleIO->shouldReceive('error')
            ->never();

        $result = $this->rebaseAction->execute($options, $this->mockConsoleIO);

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertTrue($result->isSuccess(), 'Expected rebase abort to succeed, but got: ' . $result->getMessage());
        $this->assertEquals('Rebase operation aborted', $result->getMessage());
    }

    public function test_execute_with_continue_option()
    {
        $options = ['continue' => true];

        $this->mockRepository->shouldReceive('hasUncommittedChanges')
            ->once()
            ->andReturn(false);

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['rebase', '--continue'])
            ->once()
            ->andReturn('Rebase continued');

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Continuing rebase operation...')
            ->once();

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Rebase continued successfully.')
            ->once();

        // Add error expectation in case of exception
        $this->mockConsoleIO->shouldReceive('error')
            ->never();

        $result = $this->rebaseAction->execute($options, $this->mockConsoleIO);

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Rebase operation continued', $result->getMessage());
    }

    public function test_execute_with_interactive_rebase()
    {
        $options = ['branch' => 'main', 'interactive' => true];

        $this->mockRepository->shouldReceive('hasUncommittedChanges')
            ->once()
            ->andReturn(false);

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['rebase', '--interactive', 'main'])
            ->once()
            ->andReturn('Interactive rebase completed');

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Starting rebase operation...')
            ->once();

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Interactive rebase completed. Please check the result.')
            ->once();

        $result = $this->rebaseAction->execute($options, $this->mockConsoleIO);

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Successfully rebased onto main', $result->getMessage());
    }
}
