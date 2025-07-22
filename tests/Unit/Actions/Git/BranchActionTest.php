<?php

namespace Tests\Unit\Actions\Git;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Actions\Git\BranchAction;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;
use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Ahmedessam\LaravelGitToolkit\Services\Branch\BranchService;
use Mockery;

class BranchActionTest extends TestCase
{
    private $mockRepository;
    private $mockConfig;
    private $mockBranchService;
    private $mockConsoleIO;
    private BranchAction $branchAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(GitRepositoryInterface::class);
        $this->mockConfig = Mockery::mock(ConfigInterface::class);
        $this->mockBranchService = Mockery::mock(BranchService::class);
        $this->mockConsoleIO = Mockery::mock(ConsoleIOInterface::class);

        $this->branchAction = new BranchAction(
            $this->mockRepository,
            $this->mockConfig,
            $this->mockBranchService
        );
    }

    public function test_get_name_returns_branch()
    {
        $this->assertEquals('branch', $this->branchAction->getName());
    }

    public function test_get_description_returns_correct_description()
    {
        $this->assertEquals('Create a new branch and switch to it', $this->branchAction->getDescription());
    }

    public function test_execute_with_branch_option()
    {
        $options = ['branch' => 'feature/test-branch'];

        $this->mockBranchService->shouldReceive('sanitizeBranchName')
            ->with('feature/test-branch')
            ->once()
            ->andReturn('feature/test-branch');

        $this->mockRepository->shouldReceive('getCurrentBranch')
            ->once()
            ->andReturn('main');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['checkout', '-b', 'feature/test-branch'])
            ->once()
            ->andReturn('Switched to new branch');

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Created and switched to branch: feature/test-branch')
            ->once();

        // Expect error call if there's an exception
        $this->mockConsoleIO->shouldReceive('error')
            ->never();

        $result = $this->branchAction->execute($options, $this->mockConsoleIO);

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Successfully created branch feature/test-branch', $result->getMessage());
    }

    public function test_execute_with_interactive_branch_creation()
    {
        $options = ['branch' => null];
        $branchTypes = ['feature' => 'Feature branch', 'bugfix' => 'Bug fix branch'];

        $this->mockConfig->shouldReceive('getBranchTypes')
            ->once()
            ->andReturn($branchTypes);

        $this->mockConsoleIO->shouldReceive('choice')
            ->with('Branch type:', array_keys($branchTypes))
            ->once()
            ->andReturn('feature');

        $this->mockConsoleIO->shouldReceive('ask')
            ->with('Branch name:')
            ->once()
            ->andReturn('new-feature');

        $this->mockConsoleIO->shouldReceive('ask')
            ->with('Feature area (optional):', 'general')
            ->once()
            ->andReturn('auth');

        $this->mockConsoleIO->shouldReceive('ask')
            ->with('Prefix (optional):')
            ->once()
            ->andReturn('');

        $this->mockBranchService->shouldReceive('formatBranchName')
            ->with('new-feature', 'feature', 'auth', '')
            ->once()
            ->andReturn('feature/auth/new-feature');

        $this->mockRepository->shouldReceive('getCurrentBranch')
            ->once()
            ->andReturn('main');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['checkout', '-b', 'feature/auth/new-feature'])
            ->once()
            ->andReturn('Switched to new branch');

        $this->mockConsoleIO->shouldReceive('info')
            ->with('Created and switched to branch: feature/auth/new-feature')
            ->once();

        // Expect error method to be available but never called
        $this->mockConsoleIO->shouldReceive('error')
            ->never();

        $result = $this->branchAction->execute($options, $this->mockConsoleIO);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Successfully created branch feature/auth/new-feature', $result->getMessage());
    }

    public function test_execute_handles_exception()
    {
        $options = ['branch' => 'test-branch'];

        $this->mockBranchService->shouldReceive('sanitizeBranchName')
            ->with('test-branch')
            ->once()
            ->andReturn('test-branch');

        $this->mockRepository->shouldReceive('executeGitCommand')
            ->with(['checkout', '-b', 'test-branch'])
            ->once()
            ->andThrow(new \Exception('Branch already exists'));

        $this->mockConsoleIO->shouldReceive('error')
            ->with('Branch creation failed: Branch already exists')
            ->once();

        $result = $this->branchAction->execute($options, $this->mockConsoleIO);

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Branch creation failed: Branch already exists', $result->getMessage());
    }
}
