<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use Ahmedessam\LaravelGitToolkit\Actions\ActionResult;

class ActionResultTest extends TestCase
{
    public function test_success_result_creation()
    {
        $result = ActionResult::success('Operation completed');

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
        $this->assertEquals('Operation completed', $result->getMessage());
        $this->assertNull($result->getData());
    }

    public function test_success_result_with_data()
    {
        $data = ['key' => 'value'];
        $result = ActionResult::success('Operation completed', $data);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals($data, $result->getData());
    }

    public function test_failure_result_creation()
    {
        $result = ActionResult::failure('Operation failed');

        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->isFailure());
        $this->assertEquals('Operation failed', $result->getMessage());
    }

    public function test_failure_result_with_data()
    {
        $data = ['error_code' => 500];
        $result = ActionResult::failure('Operation failed', $data);

        $this->assertTrue($result->isFailure());
        $this->assertEquals($data, $result->getData());
    }
}
