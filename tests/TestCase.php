<?php

namespace Tests;

use Ahmedessam\LaravelGitToolkit\Providers\LaravelGitToolkitServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Mockery;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up application configuration for testing
        config()->set('git-toolkit', include __DIR__.'/../src/config/git-toolkit.php');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelGitToolkitServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set up environment configuration for testing
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
