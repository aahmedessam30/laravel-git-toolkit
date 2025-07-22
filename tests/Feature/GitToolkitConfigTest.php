<?php

namespace Tests\Feature;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Services\GitToolkitConfig;
use Ahmedessam\LaravelGitToolkit\Contracts\ConfigInterface;

class GitToolkitConfigTest extends TestCase
{
    protected ConfigInterface $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = $this->app->make(ConfigInterface::class);
    }

    public function test_can_get_default_commit_type(): void
    {
        // This test assumes the config has default values
        $defaultType = $this->config->getDefaultCommitType();
        $this->assertIsString($defaultType);
        $this->assertNotEmpty($defaultType);
    }

    public function test_can_get_commit_emoji(): void
    {
        $emoji = $this->config->getCommitEmoji('feat');
        $this->assertIsString($emoji);
        $this->assertNotEmpty($emoji);
    }

    public function test_can_get_default_branches(): void
    {
        $branches = $this->config->getDefaultBranches();
        $this->assertIsArray($branches);
        $this->assertNotEmpty($branches);
        $this->assertContains('main', $branches);
    }

    public function test_boolean_configs_return_proper_types(): void
    {
        $this->assertIsBool($this->config->shouldPushToDefaultBranch());
        $this->assertIsBool($this->config->shouldUseDefaultMessage());
        $this->assertIsBool($this->config->shouldPushAfterCommit());
        $this->assertIsBool($this->config->shouldReturnToPreviousBranch());
        $this->assertIsBool($this->config->shouldDeleteAfterMerge());
    }
}
