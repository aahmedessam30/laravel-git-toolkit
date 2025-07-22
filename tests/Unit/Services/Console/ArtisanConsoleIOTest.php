<?php

namespace Tests\Unit\Services\Console;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Services\Console\ArtisanConsoleIO;
use Illuminate\Console\Command;
use Mockery;

class ArtisanConsoleIOTest extends TestCase
{
    private $mockCommand;
    private $mockComponents;
    private ArtisanConsoleIO $consoleIO;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCommand = Mockery::mock(Command::class);
        $this->mockComponents = Mockery::mock();
        $this->consoleIO = new ArtisanConsoleIO($this->mockCommand, $this->mockComponents);
    }

    public function test_ask_delegates_to_command()
    {
        $this->mockCommand->shouldReceive('ask')
            ->with('What is your name?', null)
            ->once()
            ->andReturn('John Doe');

        $result = $this->consoleIO->ask('What is your name?');

        $this->assertEquals('John Doe', $result);
    }

    public function test_ask_with_default_delegates_to_command()
    {
        $this->mockCommand->shouldReceive('ask')
            ->with('What is your name?', 'Anonymous')
            ->once()
            ->andReturn('Jane Doe');

        $result = $this->consoleIO->ask('What is your name?', 'Anonymous');

        $this->assertEquals('Jane Doe', $result);
    }

    public function test_choice_delegates_to_command()
    {
        $options = ['option1', 'option2', 'option3'];

        $this->mockCommand->shouldReceive('choice')
            ->with('Select an option:', $options, 'option1')
            ->once()
            ->andReturn('option2');

        $result = $this->consoleIO->choice('Select an option:', $options, 'option1');

        $this->assertEquals('option2', $result);
    }

    public function test_info_delegates_to_command()
    {
        $this->mockComponents->shouldReceive('info')
            ->with('Information message')
            ->once();

        $this->consoleIO->info('Information message');

        $this->assertTrue(true); // Assert that no exception was thrown
    }

    public function test_error_delegates_to_command()
    {
        $this->mockComponents->shouldReceive('error')
            ->with('Error message')
            ->once();

        $this->consoleIO->error('Error message');

        $this->assertTrue(true); // Assert that no exception was thrown
    }

    public function test_warn_delegates_to_command()
    {
        $this->mockComponents->shouldReceive('warn')
            ->with('Warning message')
            ->once();

        $this->consoleIO->warn('Warning message');

        $this->assertTrue(true); // Assert that no exception was thrown
    }

    public function test_confirm_delegates_to_command()
    {
        $this->mockComponents->shouldReceive('confirm')
            ->with('Are you sure?', false)
            ->once()
            ->andReturn(true);

        $result = $this->consoleIO->confirm('Are you sure?');

        $this->assertTrue($result);
    }

    public function test_confirm_with_default_delegates_to_command()
    {
        $this->mockComponents->shouldReceive('confirm')
            ->with('Are you sure?', true)
            ->once()
            ->andReturn(false);

        $result = $this->consoleIO->confirm('Are you sure?', true);

        $this->assertFalse($result);
    }
}
