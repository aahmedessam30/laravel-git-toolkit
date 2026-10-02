<?php

namespace Tests\Unit\Git;

use Ahmedessam\LaravelGitToolkit\Exceptions\GitCommandFailed;
use Ahmedessam\LaravelGitToolkit\Exceptions\GitToolkitException;
use Ahmedessam\LaravelGitToolkit\Git\GitResult;
use PHPUnit\Framework\TestCase;

class GitResultTest extends TestCase
{
    public function test_success_helpers(): void
    {
        $result = new GitResult(['status'], "  M a.txt\n?? b.txt\n\n", '', 0);

        $this->assertTrue($result->successful());
        $this->assertFalse($result->failed());
        $this->assertSame("M a.txt\n?? b.txt", $result->trimmedOutput());
        $this->assertSame(['M a.txt', '?? b.txt'], $result->lines());
        $this->assertSame($result, $result->throw());
    }

    public function test_throw_raises_git_command_failed_with_the_result(): void
    {
        $result = new GitResult(['checkout', 'nope'], '', "error: pathspec 'nope' did not match\n", 1);

        try {
            $result->throw();
            $this->fail('Expected GitCommandFailed.');
        } catch (GitCommandFailed $exception) {
            $this->assertInstanceOf(GitToolkitException::class, $exception);
            $this->assertSame($result, $exception->result);
            $this->assertSame("git checkout nope: error: pathspec 'nope' did not match", $exception->getMessage());
        }
    }

    public function test_failure_message_falls_back_to_stdout(): void
    {
        $result = new GitResult(['merge', 'feature'], "CONFLICT (content): Merge conflict in f.txt\nAutomatic merge failed\n", '', 1);

        $this->expectException(GitCommandFailed::class);
        $this->expectExceptionMessage("git merge feature: CONFLICT (content): Merge conflict in f.txt\nAutomatic merge failed");

        $result->throw();
    }
}
