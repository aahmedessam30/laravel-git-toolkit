<?php

namespace Tests\Unit;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Services\Branch\BranchService;
use Ahmedessam\LaravelGitToolkit\Exceptions\InvalidBranchName;
use Ahmedessam\LaravelGitToolkit\Contracts\GitRepositoryInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Mockery;

class BranchServiceTest extends TestCase
{
    protected BranchService $branchService;
    protected $mockRepository;
    protected $mockConfig;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockRepository = Mockery::mock(GitRepositoryInterface::class);
        $this->mockConfig = Mockery::mock(ConfigInterface::class);
        
        $this->branchService = new BranchService($this->mockRepository, $this->mockConfig);
    }

    public function test_sanitize_branch_name_removes_invalid_characters(): void
    {
        $result = $this->branchService->sanitizeBranchName('feature/test@branch#123');
        $this->assertEquals('feature/test-branch-123', $result);
    }

    public function test_sanitize_branch_name_throws_exception_for_empty_result(): void
    {
        $this->expectException(InvalidBranchName::class);
        $this->branchService->sanitizeBranchName('###');
    }

    public function test_format_branch_name_creates_proper_structure(): void
    {
        $result = $this->branchService->formatBranchName('test-branch', 'feature', 'backend', 'feat');
        $this->assertEquals('feature/backend/test-branch', $result);
    }

    public function test_is_default_branch_returns_correct_result(): void
    {
        $this->mockConfig
            ->shouldReceive('getDefaultBranches')
            ->twice()
            ->andReturn(['main', 'master', 'develop']);

        $this->assertTrue($this->branchService->isDefaultBranch('main'));
        $this->assertFalse($this->branchService->isDefaultBranch('feature/test'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
