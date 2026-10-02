<?php

namespace Tests;

use Ahmedessam\LaravelGitToolkit\Providers\LaravelGitToolkitServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
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
}
