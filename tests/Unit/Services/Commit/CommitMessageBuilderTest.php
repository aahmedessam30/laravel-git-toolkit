<?php

namespace Tests\Unit\Services\Commit;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Services\Commit\CommitMessageBuilder;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;
use Ahmedessam\LaravelGitToolkit\Contracts\ConsoleIOInterface;
use Mockery;

class CommitMessageBuilderTest extends TestCase
{
    private $mockConfig;
    private $mockConsoleIO;
    private CommitMessageBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockConfig = Mockery::mock(ConfigInterface::class);
        $this->mockConsoleIO = Mockery::mock(ConsoleIOInterface::class);
        $this->builder = new CommitMessageBuilder($this->mockConfig);
    }

    public function test_build_commit_message_with_default()
    {
        $this->mockConfig->shouldReceive('shouldUseDefaultMessage')
            ->once()
            ->andReturn(true);

        // Since we're passing 'feat' as the type, getDefaultCommitType won't be called
        // $this->mockConfig->shouldReceive('getDefaultCommitType') - NOT NEEDED

        $this->mockConfig->shouldReceive('get')
            ->with('default_commit_message', 'Update [%s] branch with latest changes.')
            ->once()
            ->andReturn('Update [%s] branch with latest changes.');

        $this->mockConfig->shouldReceive('getCommitEmoji')
            ->with('feat')
            ->once()
            ->andReturn('✨');

        // When shouldUseDefaultMessage is true, the provided message is ignored
        // and the default template is used instead
        $result = $this->builder->buildCommitMessage('feat', 'Test message', 'main');

        $this->assertEquals('✨ feat: Update [main] branch with latest changes.', $result);
    }

    public function test_build_interactive_commit_message()
    {
        $this->mockConfig->shouldReceive('get')
            ->with('default_commit_message', 'Update [%s] branch with latest changes.')
            ->once()
            ->andReturn('Update [%s] branch with latest changes.');

        $this->mockConsoleIO->shouldReceive('ask')
            ->with('Enter the commit message, Leave empty to use the default message [Update [main] branch with latest changes.]')
            ->once()
            ->andReturn('Custom commit message');

        $this->mockConfig->shouldReceive('getCommitTypes')
            ->once()
            ->andReturn(['feat' => 'Feature', 'fix' => 'Bug fix']);

        $this->mockConsoleIO->shouldReceive('choice')
            ->with('Enter the commit type', ['feat' => 'Feature', 'fix' => 'Bug fix'], 'feat')
            ->once()
            ->andReturn('fix');

        $this->mockConfig->shouldReceive('getCommitEmoji')
            ->with('fix')
            ->once()
            ->andReturn('🐛');

        $result = $this->builder->buildInteractiveCommitMessage('main', $this->mockConsoleIO);

        $this->assertEquals('🐛 fix: Custom commit message', $result);
    }

    public function test_build_interactive_commit_message_with_default_fallback()
    {
        $this->mockConfig->shouldReceive('get')
            ->with('default_commit_message', 'Update [%s] branch with latest changes.')
            ->once()
            ->andReturn('Update [%s] branch with latest changes.');

        $this->mockConsoleIO->shouldReceive('ask')
            ->with('Enter the commit message, Leave empty to use the default message [Update [main] branch with latest changes.]')
            ->once()
            ->andReturn(''); // Empty response, should use default

        $this->mockConfig->shouldReceive('getCommitTypes')
            ->once()
            ->andReturn(['feat' => 'Feature', 'fix' => 'Bug fix']);

        $this->mockConsoleIO->shouldReceive('choice')
            ->with('Enter the commit type', ['feat' => 'Feature', 'fix' => 'Bug fix'], 'feat')
            ->once()
            ->andReturn('feat');

        $this->mockConfig->shouldReceive('getCommitEmoji')
            ->with('feat')
            ->once()
            ->andReturn('✨');

        $result = $this->builder->buildInteractiveCommitMessage('main', $this->mockConsoleIO);

        $this->assertEquals('✨ feat: Update [main] branch with latest changes.', $result);
    }

    public function test_build_interactive_commit_message_without_io_falls_back_to_default()
    {
        $this->mockConfig->shouldReceive('getDefaultCommitType')
            ->once()
            ->andReturn('feat');

        $this->mockConfig->shouldReceive('get')
            ->with('default_commit_message', 'Update [%s] branch with latest changes.')
            ->once()
            ->andReturn('Update [%s] branch with latest changes.');

        $this->mockConfig->shouldReceive('getCommitEmoji')
            ->with('feat')
            ->once()
            ->andReturn('✨');

        $result = $this->builder->buildInteractiveCommitMessage('main', null);

        $this->assertEquals('✨ feat: Update [main] branch with latest changes.', $result);
    }

    public function test_get_available_types()
    {
        $types = ['feat' => 'Feature', 'fix' => 'Bug fix', 'docs' => 'Documentation'];

        $this->mockConfig->shouldReceive('getCommitTypes')
            ->once()
            ->andReturn($types);

        $result = $this->builder->getAvailableTypes();

        $this->assertEquals($types, $result);
    }

    public function test_validate_commit_type()
    {
        $types = ['feat' => 'Feature', 'fix' => 'Bug fix'];

        $this->mockConfig->shouldReceive('getCommitTypes')
            ->twice()
            ->andReturn($types);

        $this->assertTrue($this->builder->validateCommitType('feat'));
        $this->assertFalse($this->builder->validateCommitType('invalid'));
    }
}
