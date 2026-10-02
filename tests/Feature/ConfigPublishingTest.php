<?php

namespace Tests\Feature;

use Ahmedessam\LaravelGitToolkit\Providers\LaravelGitToolkitServiceProvider;
use Illuminate\Support\ServiceProvider;
use Tests\TestCase;

class ConfigPublishingTest extends TestCase
{
    private string $packageConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->packageConfig = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'git-toolkit.php';
    }

    protected function tearDown(): void
    {
        @unlink(config_path('git-toolkit.php'));

        parent::tearDown();
    }

    public function test_config_lives_in_the_package_config_directory(): void
    {
        $this->assertFileExists($this->packageConfig);
        $this->assertDirectoryDoesNotExist(dirname(__DIR__, 2).'/src/config');
    }

    public function test_provider_merges_the_package_config(): void
    {
        $this->assertSame(include $this->packageConfig, config('git-toolkit'));
    }

    public function test_publish_tag_maps_the_package_config_to_the_app_config_path(): void
    {
        $paths = ServiceProvider::pathsToPublish(LaravelGitToolkitServiceProvider::class, 'git-toolkit-config');

        $this->assertCount(1, $paths);
        $this->assertSame(realpath($this->packageConfig), realpath(array_key_first($paths)));
        $this->assertSame(config_path('git-toolkit.php'), reset($paths));
    }

    public function test_vendor_publish_copies_the_config(): void
    {
        @unlink(config_path('git-toolkit.php'));

        $this->artisan('vendor:publish', ['--tag' => 'git-toolkit-config'])->assertExitCode(0);

        $this->assertFileEquals($this->packageConfig, config_path('git-toolkit.php'));
    }
}
