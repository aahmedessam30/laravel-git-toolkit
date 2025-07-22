<?php

namespace Tests\Unit\Actions\Git;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Actions\Git\PushAction;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;
use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Services\Commit\CommitMessageBuilder;
use Mockery;

class PushActionTest extends TestCase
{
    private $mockRepository;
    private $mockConfig;
    private $mockCommitBuilder;
    private $mockConsoleIO;
    private PushAction $pushAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(GitRepositoryInterface::class);
        $this->mockConfig = Mockery::mock(ConfigInterface::class);
        $this->mockCommitBuilder = Mockery::mock(CommitMessageBuilder::class);
        $this->mockConsoleIO = Mockery::mock(ConsoleIOInterface::class);

        $this->pushAction = new PushAction(
            $this->mockRepository,
            $this->mockConfig,
            $this->mockCommitBuilder
        );
    }

    public function test_get_name_returns_push()
    {
        $this->assertEquals('push', $this->pushAction->getName());
    }

    public function test_get_description_returns_correct_description()
    {
        $this->assertEquals('Add, commit and push changes to remote repository', $this->pushAction->getDescription());
    }

    public function test_execute_with_message_option()
    {
        $options = ['message' => 'Test commit message', 'branch' => 'main'];

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['add', '.'])
            ->once()
            ->andReturn('Added files');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['commit', '-m', 'Test commit message'])
            ->once()
            ->andReturn('Committed');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['push', 'origin', 'main'])
            ->once()
            ->andReturn('Pushed successfully');

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Pushed to branch: main')
            ->once();

        $result = $this->pushAction->execute($options, $this->mockConsoleIO);

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Successfully pushed to main', $result->getMessage());
    }

    public function test_execute_with_interactive_message()
    {
        $options = ['message' => null, 'branch' => 'feature'];

        $this->mockConfig->shouldReceive('shouldUseDefaultMessage')
            ->once()
            ->andReturn(false);

        // The buildCommitMessage method calls getCurrentBranch() not the options branch
        $this->mockRepository->shouldReceive('getCurrentBranch')
            ->once()
            ->andReturn('main');

        $this->mockCommitBuilder->shouldReceive('buildInteractiveCommitMessage')
            ->with('main', $this->mockConsoleIO)
            ->once()
            ->andReturn('feat: Interactive commit message');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['add', '.'])
            ->once()
            ->andReturn('Added files');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['commit', '-m', 'feat: Interactive commit message'])
            ->once()
            ->andReturn('Committed');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['push', 'origin', 'feature'])
            ->once()
            ->andReturn('Pushed successfully');

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Pushed to branch: feature')
            ->once();

        // Expect error method to be available but never called
        $this->mockConsoleIO->shouldReceive('error')
            ->never();

        $result = $this->pushAction->execute($options, $this->mockConsoleIO);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Successfully pushed to feature', $result->getMessage());
    }

    public function test_execute_with_default_message()
    {
        $options = ['message' => null, 'branch' => null];

        $this->mockConfig->shouldReceive('shouldUseDefaultMessage')
            ->once()
            ->andReturn(true);

        $this->mockConfig->shouldReceive('getDefaultCommitMessage')
            ->once()
            ->andReturn('Default commit message');

        $this->mockRepository->shouldReceive('getCurrentBranch')
            ->once()
            ->andReturn('main');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['add', '.'])
            ->once()
            ->andReturn('Added files');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['commit', '-m', 'Default commit message'])
            ->once()
            ->andReturn('Committed');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['push', 'origin', 'main'])
            ->once()
            ->andReturn('Pushed successfully');

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Pushed to branch: main')
            ->once();

        // Expect error method to be available but never called
        $this->mockConsoleIO->shouldReceive('error')
            ->never();

        $result = $this->pushAction->execute($options, $this->mockConsoleIO);

        $this->assertTrue($result->isSuccess());
    }

    public function test_execute_handles_exception()
    {
        $options = ['message' => 'Test message', 'branch' => 'main'];

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['add', '.'])
            ->once()
            ->andThrow(new \Exception('Git command failed'));

        $this->mockConsoleIO->shouldReceive('error')
            ->with('Push failed: Git command failed')
            ->once();

        $result = $this->pushAction->execute($options, $this->mockConsoleIO);

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Push failed: Git command failed', $result->getMessage());
    }
}